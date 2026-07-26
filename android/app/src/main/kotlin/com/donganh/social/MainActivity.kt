package com.donganh.social

import android.Manifest
import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.net.Uri
import android.os.Build
import android.os.Handler
import android.os.Looper
import android.provider.Settings
import android.util.Log
import androidx.annotation.NonNull
import androidx.core.app.ActivityCompat
import androidx.core.app.NotificationCompat
import androidx.core.app.Person
import androidx.core.graphics.drawable.IconCompat
import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel
import org.json.JSONArray
import org.json.JSONObject
import java.net.HttpURLConnection
import java.net.URL

class MainActivity : FlutterActivity() {
    private val CHANNEL = "com.example.mobile/notifications"
    private var isPollingActive = false
    private val handler = Handler(Looper.getMainLooper())
    private var lastNotifiedMsgId: Long = 0
    private var authToken: String? = null

    private var pendingNotificationPayload: Map<String, Any>? = null

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        handleNotificationIntent(intent)
    }

    private fun handleNotificationIntent(intent: Intent?) {
        if (intent == null) return
        val target = intent.getStringExtra("target_screen")
        if (target != null) {
            val payload = mapOf(
                "target" to target,
                "sender_id" to intent.getIntExtra("sender_id", 0),
                "sender_name" to (intent.getStringExtra("sender_name") ?: "")
            )
            pendingNotificationPayload = payload
            notifyFlutterNotificationTapped(payload)
        }
    }

    private fun notifyFlutterNotificationTapped(payload: Map<String, Any>) {
        handler.post {
            flutterEngine?.dartExecutor?.binaryMessenger?.let { messenger ->
                MethodChannel(messenger, CHANNEL).invokeMethod("onNotificationTapped", payload)
            }
        }
    }

    override fun configureFlutterEngine(@NonNull flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)

        // Handle initial notification intent if app started from cold boot
        handleNotificationIntent(intent)

        // Create default Notification Channel
        createNotificationChannel()

        MethodChannel(flutterEngine.dartExecutor.binaryMessenger, CHANNEL).setMethodCallHandler { call, result ->
            when (call.method) {
                "getInitialNotification" -> {
                    val payload = pendingNotificationPayload
                    pendingNotificationPayload = null
                    result.success(payload)
                }
                "requestNotificationPermission" -> {
                    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
                        ActivityCompat.requestPermissions(
                            this,
                            arrayOf(Manifest.permission.POST_NOTIFICATIONS),
                            101
                        )
                    }
                    result.success(true)
                }
                "openNotificationSettings" -> {
                    try {
                        val intent = Intent()
                        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                            intent.action = Settings.ACTION_APP_NOTIFICATION_SETTINGS
                            intent.putExtra(Settings.EXTRA_APP_PACKAGE, packageName)
                        } else {
                            intent.action = Settings.ACTION_APPLICATION_DETAILS_SETTINGS
                            intent.data = Uri.fromParts("package", packageName, null)
                        }
                        startActivity(intent)
                        result.success(true)
                    } catch (e: Exception) {
                        result.error("ERROR", e.message, null)
                    }
                }
                "requestOverlayPermission" -> {
                    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M && !Settings.canDrawOverlays(this)) {
                        val intent = Intent(
                            Settings.ACTION_MANAGE_OVERLAY_PERMISSION,
                            Uri.parse("package:$packageName")
                        )
                        startActivity(intent)
                        result.success(false)
                    } else {
                        result.success(true)
                    }
                }
                "canDrawOverlays" -> {
                    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                        result.success(Settings.canDrawOverlays(this))
                    } else {
                        result.success(true)
                    }
                }
                "showNotification" -> {
                    val title = call.argument<String>("title") ?: "Đông Anh Social"
                    val body = call.argument<String>("body") ?: "Bạn có tin nhắn mới"
                    sendSystemNotification(title, body)
                    result.success(true)
                }
                "setAuthToken" -> {
                    val token = call.argument<String>("token")
                    if (!token.isNullOrEmpty()) {
                        authToken = token
                        val appPrefs = getSharedPreferences("AppPrefs", Context.MODE_PRIVATE)
                        appPrefs.edit().putString("auth_token", token).apply()
                        Log.d("NativePolling", "Auth token received & saved to disk: ${token.take(10)}...")
                        startBackgroundService(token)
                    }
                    result.success(true)
                }
                else -> {
                    result.notImplemented()
                }
            }
        }
    }

    private fun startBackgroundService(token: String? = null) {
        val serviceIntent = Intent(this, BackgroundPollingService::class.java)
        if (!token.isNullOrEmpty()) {
            serviceIntent.putExtra("token", token)
        }
        try {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                startForegroundService(serviceIntent)
            } else {
                startService(serviceIntent)
            }
        } catch (e: Exception) {
            Log.e("MainActivity", "Failed to start BackgroundPollingService: ${e.message}")
        }
    }

    private fun getStoredAuthToken(): String? {
        if (!authToken.isNullOrEmpty()) return authToken

        val flutterPrefs = getSharedPreferences("FlutterSharedPreferences", Context.MODE_PRIVATE)
        val tokenFromFlutter = flutterPrefs.getString("flutter.auth_token", null)
        if (!tokenFromFlutter.isNullOrEmpty()) {
            authToken = tokenFromFlutter
            return authToken
        }

        val appPrefs = getSharedPreferences("AppPrefs", Context.MODE_PRIVATE)
        val tokenFromApp = appPrefs.getString("auth_token", null)
        if (!tokenFromApp.isNullOrEmpty()) {
            authToken = tokenFromApp
            return authToken
        }

        return null
    }

    private fun startNativeBackgroundPolling() {
        if (isPollingActive) return
        isPollingActive = true

        val runnable = object : Runnable {
            override fun run() {
                val token = getStoredAuthToken()
                if (token == null) {
                    Log.d("NativePolling", "Waiting for auth token...")
                    handler.postDelayed(this, 5000)
                    return
                }
                Thread {
                    try {
                        val url = URL("https://donganhdiscovery.xadonganh.com/api/v1/social/unread-check")
                        val conn = url.openConnection() as HttpURLConnection
                        conn.requestMethod = "GET"
                        conn.connectTimeout = 5000
                        conn.readTimeout = 5000
                        conn.setRequestProperty("Authorization", "Bearer $token")
                        conn.setRequestProperty("Accept", "application/json")

                        val code = conn.responseCode
                        Log.d("NativePolling", "HTTP response code: $code")

                        if (code == 200) {
                            val body = conn.inputStream.bufferedReader().use { it.readText() }
                            Log.d("NativePolling", "Response body: $body")

                            val json = JSONObject(body)
                            if (json.optBoolean("has_unread", false)) {
                                val msgId = json.optLong("message_id", 0L)
                                if (msgId != lastNotifiedMsgId) {
                                    lastNotifiedMsgId = msgId

                                    val senderName = json.optString("sender_name", "Bạn bè")
                                    val senderId = json.optInt("sender_id", 0)
                                    val lastMessage = json.optString("last_message", "Tin nhắn mới")

                                    Log.d("NativePolling", "Triggering notification for msgId: $msgId from $senderName")
                                    handler.post {
                                        sendSystemNotification(senderName, "💬 $lastMessage", senderId)
                                    }
                                }
                            }
                        } else if (code == 404) {
                            Log.d("NativePolling", "Endpoint 404, running fallback polling on /friends...")
                            performFallbackPolling()
                        }
                        conn.disconnect()
                    } catch (e: Exception) {
                        Log.e("NativePolling", "Error during polling: ${e.message}")
                    }
                }.start()

                handler.postDelayed(this, 5000)
            }
        }
        handler.post(runnable)
    }

    private fun performFallbackPolling() {
        val token = getStoredAuthToken() ?: return
        try {
            val friendsUrl = URL("https://donganhdiscovery.xadonganh.com/api/v1/friends")
            val conn = friendsUrl.openConnection() as HttpURLConnection
            conn.requestMethod = "GET"
            conn.connectTimeout = 5000
            conn.readTimeout = 5000
            conn.setRequestProperty("Authorization", "Bearer $token")
            conn.setRequestProperty("Accept", "application/json")

            if (conn.responseCode == 200) {
                val body = conn.inputStream.bufferedReader().use { it.readText() }
                val friendsArray = JSONArray(body)
                if (friendsArray.length() > 0) {
                    val firstFriend = friendsArray.getJSONObject(0)
                    val friendId = firstFriend.optInt("id")
                    val friendName = firstFriend.optString("name", "Bạn bè")

                    if (friendId > 0) {
                        val msgUrl = URL("https://donganhdiscovery.xadonganh.com/api/v1/messages/$friendId")
                        val msgConn = msgUrl.openConnection() as HttpURLConnection
                        msgConn.requestMethod = "GET"
                        msgConn.connectTimeout = 5000
                        msgConn.readTimeout = 5000
                        msgConn.setRequestProperty("Authorization", "Bearer $token")
                        msgConn.setRequestProperty("Accept", "application/json")

                        if (msgConn.responseCode == 200) {
                            val msgBody = msgConn.inputStream.bufferedReader().use { it.readText() }
                            val msgObj = JSONObject(msgBody)
                            val messagesArray = msgObj.optJSONArray("messages")
                            if (messagesArray != null && messagesArray.length() > 0) {
                                var latestUnreadId = 0L
                                var latestUnreadText = "Tin nhắn mới"

                                for (i in 0 until messagesArray.length()) {
                                    val msg = messagesArray.getJSONObject(i)
                                    val isRead = msg.optBoolean("is_read", true)
                                    if (!isRead) {
                                        val id = msg.optLong("id")
                                        if (id > latestUnreadId) {
                                            latestUnreadId = id
                                            latestUnreadText = msg.optString("message", "Tin nhắn mới")
                                        }
                                    }
                                }

                                if (latestUnreadId > 0 && latestUnreadId != lastNotifiedMsgId) {
                                    lastNotifiedMsgId = latestUnreadId
                                    handler.post {
                                        sendSystemNotification(friendName, "💬 $latestUnreadText", friendId)
                                    }
                                }
                            }
                        }
                        msgConn.disconnect()
                    }
                }
            }
            conn.disconnect()
        } catch (e: Exception) {
            Log.e("NativePolling", "Fallback error: ${e.message}")
        }
    }

    private fun sendSystemNotification(title: String, body: String, senderId: Int = 0) {
        val intent = Intent(this, MainActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP
            putExtra("target_screen", "chat")
            putExtra("sender_id", senderId)
            putExtra("sender_name", title)
        }
        val contentPendingIntent = PendingIntent.getActivity(
            this,
            (System.currentTimeMillis() % 10000).toInt(),
            intent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )

        val icon = IconCompat.createWithResource(this, R.mipmap.ic_launcher)

        val person = Person.Builder()
            .setName(title)
            .setIcon(icon)
            .build()

        val messagingStyle = NotificationCompat.MessagingStyle(person)
            .addMessage(body, System.currentTimeMillis(), person)

        val bubbleMetadata = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
            val bubblePendingIntent = PendingIntent.getActivity(
                this,
                1,
                intent,
                PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_MUTABLE
            )
            NotificationCompat.BubbleMetadata.Builder(bubblePendingIntent, icon)
                .setDesiredHeight(600)
                .setAutoExpandBubble(true)
                .setSuppressNotification(false)
                .build()
        } else null

        val builder = NotificationCompat.Builder(this, "dong_anh_social_channel")
            .setSmallIcon(R.mipmap.ic_launcher)
            .setContentTitle(title)
            .setContentText(body)
            .setStyle(messagingStyle)
            .setCategory(NotificationCompat.CATEGORY_MESSAGE)
            .setPriority(NotificationCompat.PRIORITY_MAX)
            .setVisibility(NotificationCompat.VISIBILITY_PUBLIC)
            .setDefaults(NotificationCompat.DEFAULT_ALL)
            .setAutoCancel(true)
            .setContentIntent(contentPendingIntent)

        if (bubbleMetadata != null) {
            builder.setBubbleMetadata(bubbleMetadata)
        }

        val notificationManager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
        notificationManager.notify((System.currentTimeMillis() % 10000).toInt(), builder.build())
    }

    private fun createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val name = "Đông Anh Social Notifications"
            val descriptionText = "Thông báo tin nhắn và địa điểm mới"
            val importance = NotificationManager.IMPORTANCE_HIGH
            val channel = NotificationChannel("dong_anh_social_channel", name, importance).apply {
                description = descriptionText
                lockscreenVisibility = android.app.Notification.VISIBILITY_PUBLIC
                enableVibration(true)
                setShowBadge(true)
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
                    setAllowBubbles(true)
                }
            }
            val notificationManager: NotificationManager =
                getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            notificationManager.createNotificationChannel(channel)
        }
    }
}
