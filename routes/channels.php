<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Channel public cho checkin feed real-time — không cần auth
Broadcast::channel('checkin-feed', function () {
    return true;
});

// Channel private cho chat real-time của từng user
Broadcast::channel('chat.{userId}', function ($user = null, $userId = null) {
    $currentId = $user ? $user->id : session('user_id');
    return $currentId && (int) $currentId === (int) $userId;
});

// Channel private cho WebRTC call signaling của từng user
Broadcast::channel('call.{userId}', function ($user = null, $userId = null) {
    $currentId = $user ? $user->id : session('user_id');
    return $currentId && (int) $currentId === (int) $userId;
});

// Channel public / presence cho LiveStream real-time
Broadcast::channel('live-stream.{id}', function ($user = null, $id = null) {
    return true;
});



