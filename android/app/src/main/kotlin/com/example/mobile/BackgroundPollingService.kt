package com.example.mobile

import android.app.Notification
import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.app.Service
import android.content.Context
import android.content.Intent
import android.os.Build
import android.os.Handler
import android.os.IBinder
import android.os.Looper
import android.util.Log
import androidx.core.app.NotificationCompat
import androidx.core.app.Person
import androidx.core.graphics.drawable.IconCompat
import org.json.JSONArray
import org.json.JSONObject
import java.net.HttpURLConnection
import java.net.URL

class BackgroundPollingService : Service() {
    private val handler = Handler(Looper.getMainLooper())
    private var isPollingActive = false
    private var lastNotifiedMsgId: Long = 0

    override fun onBind(intent: Intent?): IBinder? = null

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        val token = intent?.getStringExtra("token")
        if (!token.isNullOrEmpty()) {
            val prefs = getSharedPreferences("AppPrefs", Context.MODE_PRIVATE)
            prefs.edit().putString("auth_token", token).apply()
        }

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            startForeground(1001, createSilentForegroundNotification())
        }

        startPollingLoop()

        // START_STICKY tells Android OS to keep running / restart service if killed
        return START_STICKY
    }

    private fun createSilentForegroundNotification(): Notification {
        val channelId = "dong_anh_background_service"
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                channelId,
                "Đông Anh Running Service",
                NotificationManager.IMPORTANCE_MIN
            ).apply {
                description = "Lắng nghe tin nhắn ngầm"
                setShowBadge(false)
            }
            val nm = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            nm.createNotificationChannel(channel)
        }
        return NotificationCompat.Builder(this, channelId)
            .setSmallIcon(R.mipmap.ic_launcher)
            .setContentTitle("Đông Anh Social")
            .setContentText("Đang chạy ngầm để nhận tin nhắn mới")
            .setPriority(NotificationCompat.PRIORITY_MIN)
            .setOngoing(true)
            .build()
    }

    private fun getStoredAuthToken(): String? {
        val flutterPrefs = getSharedPreferences("FlutterSharedPreferences", Context.MODE_PRIVATE)
        val tokenFromFlutter = flutterPrefs.getString("flutter.auth_token", null)
        if (!tokenFromFlutter.isNullOrEmpty()) return tokenFromFlutter

        val appPrefs = getSharedPreferences("AppPrefs", Context.MODE_PRIVATE)
        val tokenFromApp = appPrefs.getString("auth_token", null)
        if (!tokenFromApp.isNullOrEmpty()) return tokenFromApp

        return null
    }

    private fun startPollingLoop() {
        if (isPollingActive) return
        isPollingActive = true

        val runnable = object : Runnable {
            override fun run() {
                val token = getStoredAuthToken()
                if (token != null) {
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
                            Log.d("BackgroundService", "Polling HTTP code: $code")

                            if (code == 200) {
                                val body = conn.inputStream.bufferedReader().use { it.readText() }
                                Log.d("BackgroundService", "Response body: $body")

                                val json = JSONObject(body)
                                if (json.optBoolean("has_unread", false)) {
                                    val msgId = json.optLong("message_id", 0L)
                                    if (msgId != lastNotifiedMsgId) {
                                        lastNotifiedMsgId = msgId
                                        val senderName = json.optString("sender_name", "Bạn bè")
                                        val lastMessage = json.optString("last_message", "Tin nhắn mới")

                                        Log.d("BackgroundService", "Triggering notification for msgId: $msgId from $senderName")
                                        handler.post {
                                            sendSystemNotification(senderName, "💬 $lastMessage")
                                        }
                                    }
                                }
                            } else if (code == 404) {
                                performFallbackPolling(token)
                            }
                            conn.disconnect()
                        } catch (e: Exception) {
                            Log.e("BackgroundService", "Polling error: ${e.message}")
                        }
                    }.start()
                } else {
                    Log.d("BackgroundService", "Waiting for auth token...")
                }

                handler.postDelayed(this, 5000)
            }
        }
        handler.post(runnable)
    }

    private fun performFallbackPolling(token: String) {
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
                                        sendSystemNotification(friendName, "💬 $latestUnreadText")
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
            Log.e("BackgroundService", "Fallback error: ${e.message}")
        }
    }

    private fun sendSystemNotification(title: String, body: String) {
        val intent = Intent(this, MainActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP
        }
        val contentPendingIntent = PendingIntent.getActivity(
            this,
            0,
            intent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )

        val icon = IconCompat.createWithResource(this, R.mipmap.ic_launcher)
        val person = Person.Builder().setName(title).setIcon(icon).build()
        val messagingStyle = NotificationCompat.MessagingStyle(person).addMessage(body, System.currentTimeMillis(), person)

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
}
