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
    private val CHANNEL = "com.donganh.social/notifications"
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
                    result.success(true)
                }
                "canDrawOverlays" -> {
                    result.success(true)
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
        // Dịch vụ ngầm bị tắt hoàn toàn. Mọi thông báo đẩy được xử lý tự động bởi Firebase Cloud Messaging (FCM).
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
            }
            val notificationManager: NotificationManager =
                getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            notificationManager.createNotificationChannel(channel)
        }
    }
}
