<?php

namespace App\Services;

use App\Domain\Social\FriendshipData;
use App\Domain\Social\MessageData;
use App\Events\CallHangup;
use App\Events\CallOffer;
use App\Events\CallSignal;
use App\Models\CallLog;
use App\Models\Eatery;
use App\Models\FoodTour;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\OcopProduct;
use App\Models\User;
use App\Services\Interfaces\SocialHubServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SocialHubService implements SocialHubServiceInterface
{
    public function __construct(
        protected SocialService $socialService
    ) {}

    public function getIndexData(int $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            return [];
        }

        // 1. Danh sách bạn bè
        $sentFriendIds = Friendship::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('friend_id');
            
        $receivedFriendIds = Friendship::where('friend_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('user_id');

        $friendIds = $sentFriendIds->merge($receivedFriendIds)->unique();
        $friends = User::whereIn('id', $friendIds)->get();

        // 2. Yêu cầu kết bạn đang chờ (Pending)
        $pendingReceived = Friendship::where('friend_id', $user->id)
            ->where('status', 'pending')
            ->with('sender')
            ->get();

        $pendingSent = Friendship::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('receiver')
            ->get();

        // 3. Gợi ý kết bạn
        $nonSuggestions = $friendIds->merge([$user->id])->merge(
            Friendship::where('user_id', $user->id)->pluck('friend_id')
        )->merge(
            Friendship::where('friend_id', $user->id)->pluck('user_id')
        )->unique();

        $maxId = User::max('id') ?? 1;
        $randomStartId = rand(1, max(1, $maxId - 50));
        $suggestions = User::where('id', '>=', $randomStartId)
            ->whereNotIn('id', $nonSuggestions)
            ->take(20)
            ->get();

        if ($suggestions->count() < 20) {
            $excludeIds = $nonSuggestions->merge($suggestions->pluck('id'))->unique();
            $more = User::whereNotIn('id', $excludeIds)
                ->take(20 - $suggestions->count())
                ->get();
            $suggestions = $suggestions->merge($more);
        }
        $suggestions = $suggestions->shuffle();

        $myFoodTours = FoodTour::where('user_id', $user->id)->get();

        return [
            'friends' => $friends,
            'pendingReceived' => $pendingReceived,
            'pendingSent' => $pendingSent,
            'suggestions' => $suggestions,
            'myFoodTours' => $myFoodTours,
        ];
    }

    public function searchUsers(int $currentUserId, string $query): array
    {
        $user = User::find($currentUserId);
        if (!$user || mb_strlen($query) < 2) {
            return [];
        }

        $usersQuery = User::where('id', '!=', $user->id);
        try {
            $usersQuery->where(function ($q) use ($query) {
                $q->whereFullText(['name', 'username', 'email', 'phone'], $query)
                  ->orWhere('email', $query)
                  ->orWhere('phone', $query)
                  ->orWhere('username', $query)
                  ->orWhere('username', 'LIKE', "{$query}%")
                  ->orWhere('email', 'LIKE', "{$query}%")
                  ->orWhere('name', 'LIKE', "{$query}%");
            });
        } catch (\Throwable $e) {
            $usersQuery->where(function ($q) use ($query) {
                $q->where('username', 'LIKE', "{$query}%")
                  ->orWhere('email', 'LIKE', "{$query}%")
                  ->orWhere('phone', 'LIKE', "{$query}%")
                  ->orWhere('name', 'LIKE', "{$query}%");
            });
        }
        $users = $usersQuery->limit(30)->get();

        if ($users->isEmpty()) {
            return [];
        }

        $userIds = $users->pluck('id')->toArray();

        $sentFriendships = Friendship::where('user_id', $user->id)->whereIn('friend_id', $userIds)->get();
        $receivedFriendships = Friendship::where('friend_id', $user->id)->whereIn('user_id', $userIds)->get();
        $friendships = $sentFriendships->merge($receivedFriendships);

        $friendshipMap = [];
        foreach ($friendships as $f) {
            $key = $f->user_id . '-' . $f->friend_id;
            $friendshipMap[$key] = $f;
        }

        return $users->map(function ($u) use ($user, $friendshipMap) {
            $key1 = $user->id . '-' . $u->id;
            $key2 = $u->id . '-' . $user->id;
            $friendship = $friendshipMap[$key1] ?? $friendshipMap[$key2] ?? null;

            $avatarUrl = null;
            if ($u->avatar && str_starts_with($u->avatar, 'avatars/')) {
                $avatarUrl = rtrim(env('R2_PUBLIC_URL', ''), '/') . '/' . $u->avatar;
            }

            return [
                'id'                => $u->id,
                'name'              => $u->name,
                'username'          => $u->username,
                'email'             => $u->email,
                'phone'             => $u->phone,
                'role'              => $u->role,
                'is_verified'       => (bool) ($u->is_verified ?? false),
                'avatar'            => $u->avatar,
                'avatar_url'        => $avatarUrl,
                'friendship_status' => $friendship ? $friendship->status : 'none',
                'friendship_id'     => $friendship ? $friendship->id : null,
                'is_sender'         => $friendship ? ((int)$friendship->user_id === (int)$user->id) : false,
                'is_online'         => $u->is_online,
            ];
        })->toArray();
    }

    public function updateUserLocation(int $userId, float $latitude, float $longitude): array
    {
        $user = User::findOrFail($userId);
        $user->update([
            'latitude'       => round($latitude, 3),
            'longitude'      => round($longitude, 3),
            'last_active_at' => now(),
        ]);

        return $this->fetchNearbyUsers($user);
    }

    public function getNearbyUsers(int $userId): array
    {
        $user = User::find($userId);
        if (!$user || !$user->latitude || !$user->longitude) {
            return [];
        }

        return $this->fetchNearbyUsers($user);
    }

    public function sendFriendRequest(FriendshipData $data): Friendship
    {
        return $this->socialService->sendFriendRequest($data);
    }

    public function acceptFriendRequest(int $friendshipId, int $authUserId): Friendship
    {
        return $this->socialService->acceptFriendRequest($friendshipId, $authUserId);
    }

    public function declineFriendRequest(int $friendshipId, int $authUserId): void
    {
        $this->socialService->declineFriendRequest($friendshipId, $authUserId);
    }

    public function checkUnread(?int $userId): array
    {
        if (!$userId) {
            return ['has_unread' => false, 'unread_count' => 0];
        }

        $unreadCount = Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();

        $lastIncoming = Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastIncoming) {
            return ['has_unread' => false, 'unread_count' => 0];
        }

        $sender = User::find($lastIncoming->sender_id);

        return [
            'has_unread'   => true,
            'unread_count' => $unreadCount,
            'sender_name'  => $sender ? $sender->name : 'Bạn bè',
            'last_message' => $lastIncoming->message ?? 'Đã gửi một tin nhắn',
            'message_id'   => $lastIncoming->id,
        ];
    }

    public function getFriends(int $userId, ?string $search = null)
    {
        $user = User::find($userId);
        if ($user) {
            $user->update(['last_active_at' => now()]);
        }

        if (!empty($search)) {
            $search = mb_strtolower(trim($search));
            return User::where('id', '!=', $userId)
                ->where(function ($q) use ($search) {
                    if (mb_strlen($search) >= 2) {
                        try {
                            $q->whereFullText(['name', 'username', 'email', 'phone'], $search);
                        } catch (\Throwable $e) {}
                    }
                    $q->orWhere('username', 'like', "{$search}%")
                      ->orWhere('email', 'like', "{$search}%")
                      ->orWhere('phone', 'like', "{$search}%")
                      ->orWhere('name', 'like', "{$search}%");
                })
                ->limit(30)
                ->get()
                ->map(function ($f) {
                    return [
                        'id'         => $f->id,
                        'name'       => $f->name,
                        'email'      => $f->email,
                        'avatar'     => $f->avatar,
                        'avatar_url' => $f->avatar_url,
                        'is_online'  => $f->is_online ?? false,
                        'role'       => $f->role ?? 'user',
                    ];
                });
        }

        $sentFriendIds = Friendship::where('user_id', $userId)
            ->where('status', 'accepted')
            ->pluck('friend_id');
            
        $receivedFriendIds = Friendship::where('friend_id', $userId)
            ->where('status', 'accepted')
            ->pluck('user_id');

        $friendIds = $sentFriendIds->merge($receivedFriendIds)->unique();

        $latestMessagesMap = collect();
        $unreadCountsMap = collect();
        if ($friendIds->isNotEmpty()) {
            $latestMessagesMap = Message::where(function ($q) use ($userId, $friendIds) {
                    $q->where('sender_id', $userId)->whereIn('receiver_id', $friendIds);
                })->orWhere(function ($q) use ($userId, $friendIds) {
                    $q->whereIn('sender_id', $friendIds)->where('receiver_id', $userId);
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function ($msg) use ($userId) {
                    return $msg->sender_id == $userId ? $msg->receiver_id : $msg->sender_id;
                })
                ->map(function ($messages) {
                    return $messages->first();
                });

            $unreadCountsMap = Message::whereIn('sender_id', $friendIds)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->selectRaw('sender_id, COUNT(*) as cnt')
                ->groupBy('sender_id')
                ->pluck('cnt', 'sender_id');
        }

        return User::whereIn('id', $friendIds)->get()->map(function ($f) use ($latestMessagesMap, $unreadCountsMap) {
            $latestMessage = $latestMessagesMap->get($f->id);
            $unreadCount = $unreadCountsMap->get($f->id, 0);
            return [
                'id'                       => $f->id,
                'name'                     => $f->name,
                'email'                    => $f->email,
                'avatar'                   => $f->avatar,
                'avatar_url'               => $f->avatar_url,
                'is_online'                => $f->is_online ?? false,
                'role'                     => $f->role ?? 'user',
                'latest_message'           => $latestMessage ? $latestMessage->message : null,
                'latest_message_time'      => $latestMessage ? $latestMessage->created_at->diffForHumans() : null,
                'latest_message_timestamp' => $latestMessage ? $latestMessage->created_at->timestamp : 0,
                'unread_count'             => (int) $unreadCount,
            ];
        })->sortByDesc('latest_message_timestamp')->values();
    }

    public function searchAll(string $query): array
    {
        $q = trim($query);
        if (empty($q)) {
            return [
                'users'    => [],
                'eateries' => [],
                'products' => [],
            ];
        }

        $users = User::where(function ($uq) use ($q) {
            if (mb_strlen($q) >= 2) {
                try {
                    $uq->whereFullText(['name', 'username', 'email', 'phone'], $q);
                } catch (\Throwable $e) {}
            }
            $uq->orWhere('username', 'like', "{$q}%")
               ->orWhere('email', 'like', "{$q}%")
               ->orWhere('phone', 'like', "{$q}%")
               ->orWhere('name', 'like', "{$q}%");
        })
        ->limit(20)
        ->get()
        ->map(function ($u) {
            return [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'phone'      => $u->phone,
                'avatar'     => $u->avatar,
                'avatar_url' => $u->avatar_url,
                'role'       => $u->role ?? 'user',
                'type'       => 'user',
            ];
        });

        $eateries = Eatery::where(function ($eq) use ($q) {
            if (mb_strlen($q) >= 2) {
                try {
                    $eq->whereFullText(['name', 'address'], $q);
                } catch (\Throwable $e) {}
            }
            $eq->orWhere('name', 'like', "{$q}%")
               ->orWhere('slug', 'like', "{$q}%")
               ->orWhere('address', 'like', "{$q}%");
        })
        ->limit(20)
        ->get()
        ->map(function ($e) {
            return [
                'id'          => $e->id,
                'name'        => $e->name,
                'slug'        => $e->slug,
                'address'     => $e->address,
                'image_path'  => $e->image_path,
                'star_rating' => $e->star_rating,
                'type'        => 'eatery',
            ];
        });

        $products = OcopProduct::where(function ($pq) use ($q) {
            $pq->where('name', 'like', "{$q}%")
               ->orWhere('slug', 'like', "{$q}%")
               ->orWhere('stall_name', 'like', "{$q}%")
               ->orWhere('seller_name', 'like', "{$q}%");
        })
        ->limit(20)
        ->get()
            ->map(function ($p) {
                return [
                    'id'          => $p->id,
                    'name'        => $p->name,
                    'stall_name'  => $p->stall_name,
                    'seller_name' => $p->seller_name,
                    'price'       => $p->price,
                    'image_path'  => $p->image_path,
                    'star_rating' => $p->star_rating,
                    'type'        => 'product',
                ];
            });

        return [
            'users'    => $users,
            'eateries' => $eateries,
            'products' => $products,
        ];
    }

    public function getMessages(int $userId, int $friendId, ?int $beforeId = null, int $limit = 50): array
    {
        User::where('id', $userId)->update(['last_active_at' => now()]);

        $query = Message::with('foodTour')
            ->where(function ($q) use ($userId, $friendId) {
                $q->where(function ($sub) use ($userId, $friendId) {
                    $sub->where('sender_id', $userId)->where('receiver_id', $friendId);
                })->orWhere(function ($sub) use ($userId, $friendId) {
                    $sub->where('sender_id', $friendId)->where('receiver_id', $userId);
                });
            });

        if ($beforeId) {
            $query->where('id', '<', (int)$beforeId);
        }

        $rawMessages = $query
            ->orderBy('created_at', 'desc')
            ->take($limit + 1)
            ->get();

        $hasMore = $rawMessages->count() > $limit;
        $rawMessages = $rawMessages->take($limit)->reverse()->values();

        $messages = $rawMessages->map(function ($msg) {
            return [
                'id'               => $msg->id,
                'sender_id'        => $msg->sender_id,
                'receiver_id'      => $msg->receiver_id,
                'message'          => $msg->message,
                'food_tour_id'     => $msg->food_tour_id,
                'media_path'       => $msg->media_path,
                'media_type'       => $msg->media_type,
                'food_tour'        => $msg->foodTour ? [
                    'id'          => $msg->foodTour->id,
                    'name'        => $msg->foodTour->name,
                    'slug'        => $msg->foodTour->slug,
                    'description' => $msg->foodTour->description,
                    'duration'    => $msg->foodTour->duration,
                    'distance'    => $msg->foodTour->distance,
                    'budget'      => $msg->foodTour->budget,
                    'difficulty'  => $msg->foodTour->difficulty,
                    'best_time'   => $msg->foodTour->best_time,
                    'thumbnail'   => $msg->foodTour->thumbnail,
                ] : null,
                'is_read'           => (bool) $msg->is_read,
                'created_at_human'  => $msg->created_at->diffForHumans(),
                'created_at_format' => $msg->created_at->format('d/m/Y H:i'),
            ];
        });

        Message::where('sender_id', $friendId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return [
            'messages' => $messages,
            'has_more' => $hasMore,
        ];
    }

    public function sendMessage(MessageData $data, ?string $senderName = null): Message
    {
        $message = $this->socialService->sendMessage($data);
        $message->loadMissing('foodTour');

        // FCM push notification
        try {
            $receiver = User::find($message->receiver_id);
            if ($receiver && !empty($receiver->fcm_token)) {
                $name = $senderName ?: 'Bạn bè';
                $msgBody = $message->message ?? ($message->media_path ? '[Hình ảnh]' : 'Đã gửi một tin nhắn');
                FcmService::sendNotification(
                    $receiver->fcm_token,
                    $name,
                    "💬 " . $msgBody,
                    [
                        'target'      => 'chat',
                        'sender_id'   => (int)$message->sender_id,
                        'sender_name' => $name,
                        'message_id'  => (int)$message->id,
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('FCM Notification error on sendMessage: ' . $e->getMessage());
        }

        return $message;
    }

    public function getFriendsPresence(int $userId): array
    {
        $sentFriendIds = Friendship::where('user_id', $userId)
            ->where('status', 'accepted')
            ->pluck('friend_id');
            
        $receivedFriendIds = Friendship::where('friend_id', $userId)
            ->where('status', 'accepted')
            ->pluck('user_id');

        $friendIds = $sentFriendIds->merge($receivedFriendIds)->unique();
        
        return User::whereIn('id', $friendIds)
            ->where('last_active_at', '>=', now()->subMinutes(2))
            ->pluck('id')
            ->toArray();
    }

    public function getRecentChats(int $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            return [];
        }

        $sentFriendIds = Friendship::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('friend_id');
            
        $receivedFriendIds = Friendship::where('friend_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('user_id');

        $friendIds = $sentFriendIds->merge($receivedFriendIds)->unique();
        $friends = User::whereIn('id', $friendIds)->get();

        $latestMessagesMap = collect();
        if ($friendIds->isNotEmpty()) {
            $latestMessagesMap = Message::where(function ($q) use ($user, $friendIds) {
                    $q->where('sender_id', $user->id)->whereIn('receiver_id', $friendIds);
                })->orWhere(function ($q) use ($user, $friendIds) {
                    $q->whereIn('sender_id', $friendIds)->where('receiver_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function ($msg) use ($user) {
                    return $msg->sender_id == $user->id ? $msg->receiver_id : $msg->sender_id;
                })
                ->map(function ($messages) {
                    return $messages->first();
                });
        }

        $recentChats = $friends->map(function ($friend) use ($latestMessagesMap) {
            $latestMessage = $latestMessagesMap->get($friend->id);

            $avatarUrl = null;
            if ($friend->avatar && str_starts_with($friend->avatar, 'avatars/')) {
                $avatarUrl = rtrim(env('R2_PUBLIC_URL', ''), '/') . '/' . $friend->avatar;
            }

            return [
                'id'                       => $friend->id,
                'name'                     => $friend->name,
                'avatar'                   => $friend->avatar ?? '👤',
                'avatar_url'               => $avatarUrl,
                'is_online'                => $friend->is_online,
                'latest_message'           => $latestMessage ? $latestMessage->message : null,
                'latest_message_time'      => $latestMessage ? $latestMessage->created_at->diffForHumans() : null,
                'latest_message_timestamp' => $latestMessage ? $latestMessage->created_at->timestamp : 0,
                'is_read'                  => $latestMessage ? (bool)$latestMessage->is_read : true,
                'latest_message_sender_id' => $latestMessage ? (int)$latestMessage->sender_id : null,
            ];
        });

        return $recentChats->sortByDesc('latest_message_timestamp')->values()->toArray();
    }

    public function updateFcmToken(int $userId, string $fcmToken): void
    {
        User::where('id', $userId)->update(['fcm_token' => $fcmToken]);
    }

    public function initiateCall(int $callerId, int $receiverId, string $type, string $signalData): int
    {
        $caller = User::findOrFail($callerId);
        $call = CallLog::create([
            'caller_id'   => $caller->id,
            'receiver_id' => $receiverId,
            'type'        => $type,
            'status'      => 'ringing',
            'started_at'  => now(),
        ]);

        $callerAvatar = $caller->avatar ? asset($caller->avatar) : '👤';

        try {
            broadcast(new CallOffer(
                callId: $call->id,
                callerId: $caller->id,
                callerName: $caller->name,
                callerAvatar: $callerAvatar,
                receiverId: (int)$receiverId,
                type: $type,
                signalData: $signalData
            ))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('CallOffer broadcast warning: ' . $e->getMessage());
        }

        try {
            $receiver = User::find($receiverId);
            if ($receiver && !empty($receiver->fcm_token)) {
                $callTitle = $type === 'video' ? "📹 Cuộc gọi video từ {$caller->name}" : "📞 Cuộc gọi thoại từ {$caller->name}";
                $callBody = "Nhấn để trả lời cuộc gọi đàm thoại từ {$caller->name}";
                FcmService::sendNotification(
                    $receiver->fcm_token,
                    $callTitle,
                    $callBody,
                    [
                        'type'          => 'incoming_call',
                        'call_id'       => (string)$call->id,
                        'caller_id'     => (string)$caller->id,
                        'caller_name'   => (string)$caller->name,
                        'caller_avatar' => (string)$callerAvatar,
                        'call_type'     => (string)$type,
                        'signal_data'   => (string)$signalData,
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('FCM Call Notification error: ' . $e->getMessage());
        }

        Cache::put("call_signal_{$call->id}", $signalData, 120);

        return $call->id;
    }

    public function getPendingCall(int $userId): array
    {
        $pendingCall = CallLog::where('receiver_id', $userId)
            ->where('status', 'ringing')
            ->where('created_at', '>=', now()->subSeconds(90))
            ->latest()
            ->first();

        if (!$pendingCall) {
            return ['has_call' => false];
        }

        $caller = User::find($pendingCall->caller_id);
        $callerAvatar = $caller && $caller->avatar ? asset($caller->avatar) : '👤';
        $signalData = Cache::get("call_signal_{$pendingCall->id}");

        return [
            'has_call'      => true,
            'call_id'       => $pendingCall->id,
            'caller_id'     => $pendingCall->caller_id,
            'caller_name'   => $caller ? $caller->name : 'Người dùng',
            'caller_avatar' => $callerAvatar,
            'call_type'     => $pendingCall->type,
            'signal_data'   => $signalData,
        ];
    }

    public function signalCall(int $userId, int $callId, int $targetUserId, string $signalData): void
    {
        $call = CallLog::find($callId);
        $signalJson = json_decode($signalData, true);
        $isCaller = $call && $call->caller_id == $userId;
        $role = $isCaller ? 'caller' : 'receiver';

        Log::info("[WebRTC signalCall] User={$userId} Role={$role} CallId={$callId} SignalType=" . ($signalJson['type'] ?? 'ice_candidate'));

        if (isset($signalJson['type']) && $signalJson['type'] === 'answer') {
            if ($call) {
                $call->update(['status' => 'answered', 'started_at' => now()]);
            }
            Cache::put("call_signal_answer_{$callId}", $signalData, 120);
        } else {
            $cacheKey = "call_ice_{$role}_{$callId}";
            $existing = Cache::get($cacheKey, []);
            $existing[] = $signalData;
            Cache::put($cacheKey, $existing, 120);
        }

        try {
            broadcast(new CallSignal(
                callId: $callId,
                targetUserId: $targetUserId,
                signalData: $signalData
            ))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('CallSignal broadcast warning: ' . $e->getMessage());
        }
    }

    public function getCallStatus(int $callId, ?int $userId = null): array
    {
        $call = CallLog::find($callId);
        if (!$call) {
            return ['status' => 'ended'];
        }

        $isCaller = $userId && $call->caller_id == $userId;

        $offerSignal = Cache::get("call_signal_{$callId}");
        $answerSignal = Cache::get("call_signal_answer_{$callId}");
        $targetSignal = $isCaller ? $answerSignal : $offerSignal;

        $oppositeRole = $isCaller ? 'receiver' : 'caller';
        $iceCacheKey = "call_ice_{$oppositeRole}_{$callId}";

        $myRole = $isCaller ? 'caller' : 'receiver';
        $offsetKey = "call_ice_offset_{$myRole}_{$callId}";
        $offset = Cache::get($offsetKey, 0);

        $allIce = Cache::get($iceCacheKey, []);
        $newIce = array_slice($allIce, $offset);
        Cache::put($offsetKey, count($allIce), 120);

        return [
            'status'         => $call->status,
            'signal_data'    => $targetSignal,
            'ice_candidates' => $newIce,
        ];
    }

    public function hangupCall(int $callId, int $targetUserId, string $reason): void
    {
        $call = CallLog::find($callId);
        if ($call) {
            $duration = null;
            if ($call->started_at && $call->status === 'answered') {
                $duration = now()->diffInSeconds($call->started_at);
            }
            $call->update([
                'status'   => $reason,
                'ended_at' => now(),
                'duration' => $duration,
            ]);
        }

        try {
            broadcast(new CallHangup(
                callId: $callId,
                targetUserId: $targetUserId,
                reason: $reason
            ))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('CallHangup broadcast warning: ' . $e->getMessage());
        }
    }

    public function getCallHistory(int $userId, int $limit = 30)
    {
        return CallLog::with(['caller:id,name,avatar', 'receiver:id,name,avatar'])
            ->where('caller_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function fetchNearbyUsers(User $user): array
    {
        $latRange = 10 / 111.0;
        $cosLat = cos(deg2rad($user->latitude));
        $lonRange = $cosLat > 0 ? (10 / (111.0 * $cosLat)) : (10 / 111.0);

        $others = User::where('id', '!=', $user->id)
            ->whereBetween('latitude', [$user->latitude - $latRange, $user->latitude + $latRange])
            ->whereBetween('longitude', [$user->longitude - $lonRange, $user->longitude + $lonRange])
            ->where('last_active_at', '>=', now()->subHours(24))
            ->get();

        if ($others->isEmpty()) {
            return [];
        }

        $otherIds = $others->pluck('id')->toArray();

        $sentFriendships = Friendship::where('user_id', $user->id)->whereIn('friend_id', $otherIds)->get();
        $receivedFriendships = Friendship::where('friend_id', $user->id)->whereIn('user_id', $otherIds)->get();
        $friendships = $sentFriendships->merge($receivedFriendships);

        $friendshipMap = [];
        foreach ($friendships as $f) {
            $key = $f->user_id . '-' . $f->friend_id;
            $friendshipMap[$key] = $f;
        }

        $nearby = [];
        foreach ($others as $other) {
            $dist = $this->calculateDistance(
                $user->latitude,
                $user->longitude,
                $other->latitude,
                $other->longitude
            );

            if ($dist <= 10.0) {
                $key1 = $user->id . '-' . $other->id;
                $key2 = $other->id . '-' . $user->id;
                $friendship = $friendshipMap[$key1] ?? $friendshipMap[$key2] ?? null;

                $avatarUrl = null;
                if ($other->avatar && str_starts_with($other->avatar, 'avatars/')) {
                    $avatarUrl = rtrim(env('R2_PUBLIC_URL', ''), '/') . '/' . $other->avatar;
                }

                $nearby[] = [
                    'id'                => $other->id,
                    'name'              => $other->name,
                    'avatar'            => $other->avatar ?? '👤',
                    'avatar_url'        => $avatarUrl,
                    'distance'          => round($dist, 2),
                    'last_active'       => $other->last_active_at ? ($other->last_active_at instanceof Carbon ? $other->last_active_at->diffForHumans() : Carbon::parse($other->last_active_at)->diffForHumans()) : 'Không rõ',
                    'friendship_status' => $friendship ? $friendship->status : 'none',
                    'friendship_id'     => $friendship ? $friendship->id : null,
                    'is_sender'         => $friendship ? ((int)$friendship->user_id === (int)$user->id) : false,
                    'is_online'         => $other->is_online,
                ];
            }
        }

        usort($nearby, fn($a, $b) => $a['distance'] <=> $b['distance']);

        return $nearby;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Km
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}
