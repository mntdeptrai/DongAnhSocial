<?php

namespace App\Services;

use App\Models\Checkin;
use App\Models\FoodTourDiary;
use App\Models\CheckinReaction;
use App\Models\Comment;
use App\Models\Review;
use App\Models\User;
use App\Models\Eatery;
use App\Models\Friendship;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Lấy danh sách thông báo đã tổng hợp (Aggregated Notifications) theo dạng Facebook
     */
    public static function getNotificationsForUser(int $userId): array
    {
        $notifications = [];

        try {
            $user = User::find($userId);
            if (!$user) return [];

            // 1. Tải danh sách ID nội dung thuộc sở hữu của user
            $myCheckinIds = Checkin::where('user_id', $userId)->pluck('id')->toArray();
            $myDiaryIds   = FoodTourDiary::where('user_id', $userId)->pluck('id')->toArray();
            $myPostIds    = DB::table('posts')->where('user_id', $userId)->pluck('id')->toArray();
            $myEateryIds  = Eatery::where('user_id', $userId)->pluck('id')->toArray();
            if ($user && !empty($user->eatery_id) && !in_array($user->eatery_id, $myEateryIds)) {
                $myEateryIds[] = $user->eatery_id;
            }
            $myEduIds     = !empty($myEateryIds) 
                ? DB::table('education_programs')->whereIn('eatery_id', $myEateryIds)->pluck('id')->toArray() 
                : [];

            // ========================================================
            // A. THÔNG BÁO THẢ CẢM XÚC (Reactions) — TỔNG HỢP KIỂU FACEBOOK
            // ========================================================
            if (!empty($myCheckinIds) || !empty($myDiaryIds) || !empty($myPostIds) || !empty($myEduIds) || !empty($myEateryIds)) {
                $reactionsQuery = CheckinReaction::where(function($q) use ($myCheckinIds, $myDiaryIds, $myPostIds, $myEduIds, $myEateryIds) {
                    if (!empty($myCheckinIds)) {
                        $q->orWhere(function($sub) use ($myCheckinIds) {
                            $sub->whereIn('reactionable_type', ['checkin', 'App\\Models\\Checkin'])->whereIn('reactionable_id', $myCheckinIds);
                        });
                    }
                    if (!empty($myDiaryIds)) {
                        $q->orWhere(function($sub) use ($myDiaryIds) {
                            $sub->whereIn('reactionable_type', ['diary', 'App\\Models\\FoodTourDiary'])->whereIn('reactionable_id', $myDiaryIds);
                        });
                    }
                    if (!empty($myPostIds)) {
                        $q->orWhere(function($sub) use ($myPostIds) {
                            $sub->whereIn('reactionable_type', ['post', 'App\\Models\\Post'])->whereIn('reactionable_id', $myPostIds);
                        });
                    }
                    if (!empty($myEduIds)) {
                        $q->orWhere(function($sub) use ($myEduIds) {
                            $sub->whereIn('reactionable_type', ['education', 'App\\Models\\EducationProgram'])->whereIn('reactionable_id', $myEduIds);
                        });
                    }
                    if (!empty($myEateryIds)) {
                        $q->orWhere(function($sub) use ($myEateryIds) {
                            $sub->whereIn('reactionable_type', ['eatery', 'App\\Models\\Eatery'])->whereIn('reactionable_id', $myEateryIds);
                        });
                    }
                })
                ->where(function($q) use ($userId) {
                    $q->whereNull('user_id')->orWhere('user_id', '!=', $userId);
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function($r) {
                    $normType = match (strtolower($r->reactionable_type)) {
                        'checkin', 'app\models\checkin' => 'checkin',
                        'diary', 'app\models\foodtourdiary' => 'diary',
                        'eatery', 'app\models\eatery' => 'eatery',
                        'education', 'app\models\educationprogram' => 'education',
                        default => 'post',
                    };
                    return $normType . '_' . $r->reactionable_id;
                });

                foreach ($reactionsQuery as $key => $group) {
                    $first = $group->first();
                    $totalReactors = $group->pluck('user_id')->filter()->unique()->count();
                    if ($totalReactors === 0) {
                        $totalReactors = $group->count();
                    }

                    $latestUser = $first->user ? $first->user->name : 'Thành viên Đông Anh';
                    $othersCount = max(0, $totalReactors - 1);
                    $emoji = $first->emoji ?? '👍';

                    $postTypeLabel = match (strtolower($first->reactionable_type)) {
                        'checkin', 'app\models\checkin' => 'check-in',
                        'diary', 'app\models\foodtourdiary' => 'hành trình',
                        'eatery', 'app\models\eatery' => 'cơ sở/gian hàng',
                        default => 'bài viết',
                    };

                    if ($othersCount > 0) {
                        $body = "{$latestUser} và {$othersCount} người khác đã thích/thả cảm xúc bài viết {$postTypeLabel} của bạn.";
                    } else {
                        $body = "{$latestUser} đã thả {$emoji} bài viết {$postTypeLabel} của bạn.";
                    }

                    $targetUrl = match (strtolower($first->reactionable_type)) {
                        'checkin', 'app\models\checkin' => '/checkin',
                        'diary', 'app\models\foodtourdiary' => '/food-tour',
                        'eatery', 'app\models\eatery' => '/dia-diem/' . (optional(Eatery::find($first->reactionable_id))->slug ?? ''),
                        default => '/ban-tin?post=' . (optional(\App\Models\Post::find($first->reactionable_id))->hashid ?? $first->reactionable_id),
                    };

                    $notifications[] = [
                        'id'         => 'react_' . $key . '_' . strtotime($first->created_at),
                        'title'      => '👍 Cảm xúc mới bài ' . $postTypeLabel,
                        'body'       => $body,
                        'time'       => Carbon::parse($first->created_at)->diffForHumans(),
                        'time_ts'    => strtotime($first->created_at),
                        'type'       => 'reaction',
                        'icon'       => 'favorite',
                        'is_read'    => false,
                        'post_type'  => $first->reactionable_type,
                        'post_id'    => $first->reactionable_id,
                        'target_url' => $targetUrl,
                    ];
                }
            }

            // ========================================================
            // B. THÔNG BÁO BÌNH LUẬN (Comments) — TỔNG HỢP KIỂU FACEBOOK
            // ========================================================
            if (!empty($myCheckinIds) || !empty($myDiaryIds) || !empty($myPostIds) || !empty($myEduIds) || !empty($myEateryIds)) {
                $commentsQuery = Comment::where(function($q) use ($myCheckinIds, $myDiaryIds, $myPostIds, $myEduIds, $myEateryIds) {
                    if (!empty($myCheckinIds)) {
                        $q->orWhere(function($sub) use ($myCheckinIds) {
                            $sub->whereIn('commentable_type', ['App\\Models\\Checkin', 'checkin'])->whereIn('commentable_id', $myCheckinIds);
                        });
                    }
                    if (!empty($myDiaryIds)) {
                        $q->orWhere(function($sub) use ($myDiaryIds) {
                            $sub->whereIn('commentable_type', ['App\\Models\\FoodTourDiary', 'diary'])->whereIn('commentable_id', $myDiaryIds);
                        });
                    }
                    if (!empty($myPostIds)) {
                        $q->orWhere(function($sub) use ($myPostIds) {
                            $sub->whereIn('commentable_type', ['App\\Models\\Post', 'post'])->whereIn('commentable_id', $myPostIds);
                        });
                    }
                    if (!empty($myEduIds)) {
                        $q->orWhere(function($sub) use ($myEduIds) {
                            $sub->whereIn('commentable_type', ['App\\Models\\EducationProgram', 'education'])->whereIn('commentable_id', $myEduIds);
                        });
                    }
                    if (!empty($myEateryIds)) {
                        $q->orWhere(function($sub) use ($myEateryIds) {
                            $sub->whereIn('commentable_type', ['App\\Models\\Eatery', 'eatery'])->whereIn('commentable_id', $myEateryIds);
                        });
                    }
                })
                ->where(function($q) use ($userId) {
                    $q->whereNull('user_id')->orWhere('user_id', '!=', $userId);
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function($c) {
                    $normType = match (true) {
                        str_contains($c->commentable_type, 'Checkin') => 'checkin',
                        str_contains($c->commentable_type, 'FoodTourDiary') => 'diary',
                        str_contains($c->commentable_type, 'Eatery') => 'eatery',
                        str_contains($c->commentable_type, 'EducationProgram') => 'education',
                        default => 'post',
                    };
                    return $normType . '_' . $c->commentable_id;
                });

                foreach ($commentsQuery as $key => $group) {
                    $first = $group->first();
                    $totalCommenters = $group->pluck('user_id')->filter()->unique()->count();
                    if ($totalCommenters === 0) {
                        $totalCommenters = $group->count();
                    }

                    $latestUser = $first->display_name ?? 'Một thành viên';
                    $othersCount = max(0, $totalCommenters - 1);
                    $postTypeLabel = str_contains($first->commentable_type, 'Checkin') ? 'check-in' : (str_contains($first->commentable_type, 'Eatery') ? 'cơ sở' : 'bài viết');

                    if ($othersCount > 0) {
                        $body = "{$latestUser} và {$othersCount} người khác đã bình luận về bài viết {$postTypeLabel} của bạn.";
                    } else {
                        $snippet = Str::limit($first->content ?? '', 45);
                        $body = "{$latestUser} đã bình luận bài viết {$postTypeLabel} của bạn: \"{$snippet}\"";
                    }

                    $targetUrl = match (true) {
                        str_contains($first->commentable_type, 'Checkin') => '/checkin',
                        str_contains($first->commentable_type, 'FoodTourDiary') => '/food-tour',
                        str_contains($first->commentable_type, 'Eatery') => '/dia-diem/' . (optional(Eatery::find($first->commentable_id))->slug ?? ''),
                        default => '/ban-tin?post=' . (optional(\App\Models\Post::find($first->commentable_id))->hashid ?? $first->commentable_id),
                    };

                    $notifications[] = [
                        'id'         => 'comment_' . $key . '_' . strtotime($first->created_at),
                        'title'      => '💬 Bình luận mới bài ' . $postTypeLabel,
                        'body'       => $body,
                        'time'       => Carbon::parse($first->created_at)->diffForHumans(),
                        'time_ts'    => strtotime($first->created_at),
                        'type'       => 'comment',
                        'icon'       => 'comment',
                        'is_read'    => false,
                        'post_type'  => str_contains($first->commentable_type, 'Checkin') ? 'checkin' : 'post',
                        'post_id'    => $first->commentable_id,
                        'target_url' => $targetUrl,
                    ];
                }
            }

            // ========================================================
            // B2. THÔNG BÁO LƯỢT CHIA SẺ (Shares Aggregated)
            // ========================================================
            if (!empty($myPostIds)) {
                $sharedPosts = DB::table('posts')->whereIn('id', $myPostIds)->where('shares_count', '>', 0)->get();
                foreach ($sharedPosts as $sp) {
                    $notifications[] = [
                        'id'         => 'share_post_' . $sp->id,
                        'title'      => '🔄 Lượt chia sẻ bài viết mới',
                        'body'       => "Bài viết của bạn đã đạt {$sp->shares_count} lượt chia sẻ từ cộng đồng!",
                        'time'       => isset($sp->updated_at) ? Carbon::parse($sp->updated_at)->diffForHumans() : 'Vừa xong',
                        'time_ts'    => isset($sp->updated_at) ? strtotime($sp->updated_at) : time(),
                        'type'       => 'share',
                        'icon'       => 'share',
                        'is_read'    => false,
                        'post_type'  => 'post',
                        'post_id'    => $sp->id,
                        'target_url' => '/ban-tin?post=' . ($sp->hashid ?? $sp->id),
                    ];
                }
            }

            if (!empty($myCheckinIds)) {
                $sharedCheckins = Checkin::whereIn('id', $myCheckinIds)->where('shares_count', '>', 0)->get();
                foreach ($sharedCheckins as $sc) {
                    $notifications[] = [
                        'id'         => 'share_checkin_' . $sc->id,
                        'title'      => '🔄 Lượt chia sẻ bài viết check-in',
                        'body'       => "Bài viết check-in của bạn đã đạt {$sc->shares_count} lượt chia sẻ từ cộng đồng!",
                        'time'       => Carbon::parse($sc->updated_at ?? $sc->created_at)->diffForHumans(),
                        'time_ts'    => strtotime($sc->updated_at ?? $sc->created_at),
                        'type'       => 'share',
                        'icon'       => 'share',
                        'is_read'    => false,
                        'post_type'  => 'checkin',
                        'post_id'    => $sc->id,
                        'target_url' => '/checkin',
                    ];
                }
            }

            // ========================================================
            // C. THÔNG BÁO ĐÁNH GIÁ SẢN PHẨM / GIAN HÀNG (Reviews Aggregated)
            // ========================================================
            if (!empty($myEateryIds)) {
                $reviewsQuery = Review::whereIn('eatery_id', $myEateryIds)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->groupBy('eatery_id');

                foreach ($reviewsQuery as $eateryId => $group) {
                    $first = $group->first();
                    $totalReviewers = $group->count();
                    $latestUser = $first->user_name ?? 'Một khách hàng';
                    $othersCount = max(0, $totalReviewers - 1);
                    $eatery = Eatery::find($eateryId);

                    if ($othersCount > 0) {
                        $body = "{$latestUser} và {$othersCount} người khác đã gửi đánh giá về sản phẩm/gian hàng của bạn.";
                    } else {
                        $body = "{$latestUser} đã gửi đánh giá {$first->rating}⭐ về gian hàng/sản phẩm của bạn.";
                    }

                    $notifications[] = [
                        'id'         => 'review_' . $eateryId . '_' . strtotime($first->created_at),
                        'title'      => '⭐ Đánh giá mới cho gian hàng của bạn',
                        'body'       => $body,
                        'time'       => Carbon::parse($first->created_at)->diffForHumans(),
                        'time_ts'    => strtotime($first->created_at),
                        'type'       => 'review',
                        'icon'       => 'star',
                        'is_read'    => false,
                        'target_url' => $eatery ? ('/dia-diem/' . $eatery->slug) : '/seller/orders',
                    ];
                }
            }

            // ========================================================
            // D. THÔNG BÁO ĐƠN HÀNG CỬA HÀNG (Seller Orders)
            // ========================================================
            if (!empty($myEateryIds)) {
                $sellerOrders = DB::table('orders')
                    ->whereIn('eatery_id', $myEateryIds)
                    ->latest()
                    ->take(3)
                    ->get();

                foreach ($sellerOrders as $ord) {
                    $notifications[] = [
                        'id'         => 'seller_ord_' . $ord->id,
                        'title'      => '🛒 Đơn hàng mới cho cửa hàng!',
                        'body'       => 'Khách hàng vừa đặt đơn #' . ($ord->code ?? $ord->id) . ' với giá trị ' . number_format($ord->total_amount ?? $ord->total ?? 150000) . 'đ.',
                        'time'       => isset($ord->created_at) ? Carbon::parse($ord->created_at)->diffForHumans() : 'Vừa xong',
                        'time_ts'    => isset($ord->created_at) ? strtotime($ord->created_at) : time(),
                        'type'       => 'seller_order',
                        'icon'       => 'storefront',
                        'is_read'    => false,
                        'target_url' => '/seller/orders',
                    ];
                }
            }

            // ========================================================
            // E. THÔNG BÁO ĐƠN HÀNG CÁ NHÂN (Buyer Orders)
            // ========================================================
            $myOrders = DB::table('orders')
                ->where('user_id', $userId)
                ->latest()
                ->take(3)
                ->get();

            foreach ($myOrders as $ord) {
                $statusText = match ($ord->status ?? 'pending') {
                    'completed'  => 'giao thành công 🎉',
                    'shipping'   => 'đang trên đường vận chuyển 🚚',
                    'processing' => 'đang được người bán chuẩn bị 📦',
                    default      => 'đã được xác nhận thành công ⏳',
                };

                $notifications[] = [
                    'id'         => 'my_ord_' . $ord->id,
                    'title'      => '📦 Đơn hàng #' . ($ord->code ?? $ord->id) . ' của bạn',
                    'body'       => 'Đơn hàng mua đặc sản OCOP của bạn ' . $statusText,
                    'time'       => isset($ord->created_at) ? Carbon::parse($ord->created_at)->diffForHumans() : 'Vừa xong',
                    'time_ts'    => isset($ord->created_at) ? strtotime($ord->created_at) : time(),
                    'type'       => 'my_order',
                    'icon'       => 'local_shipping',
                    'is_read'    => false,
                    'target_url' => '/orders',
                ];
            }

            // ========================================================
            // F. THÔNG BÁO LỜI MỜI KẾT BẠN (Friend Requests)
            // ========================================================
            $friendRequests = DB::table('friendships')
                ->where('friend_id', $userId)
                ->where('status', 'pending')
                ->latest()
                ->take(3)
                ->get();

            foreach ($friendRequests as $fr) {
                $sender = User::find($fr->user_id);
                if ($sender) {
                    $notifications[] = [
                        'id'         => 'fr_' . $fr->id,
                        'title'      => '👥 Lời mời kết bạn mới',
                        'body'       => $sender->name . ' đã gửi cho bạn một lời mời kết bạn mới.',
                        'time'       => isset($fr->created_at) ? Carbon::parse($fr->created_at)->diffForHumans() : 'Vừa xong',
                        'time_ts'    => isset($fr->created_at) ? strtotime($fr->created_at) : time(),
                        'type'       => 'friend',
                        'icon'       => 'person_add',
                        'is_read'    => false,
                        'target_url' => '/social',
                    ];
                }
            }

            // ========================================================
            // G. THÔNG BÁO BÀI VIẾT MỚI TỪ BẠN BÈ VÀ NGƯỜI THEO DÕI
            // ========================================================
            $friendIds = DB::table('friendships')
                ->where('status', 'accepted')
                ->where(function($q) use ($userId) {
                    $q->where('user_id', $userId)->orWhere('friend_id', $userId);
                })
                ->get()
                ->map(function($f) use ($userId) {
                    return $f->user_id == $userId ? $f->friend_id : $f->user_id;
                })
                ->toArray();

            $followedUserIds = \Illuminate\Support\Facades\Schema::hasTable('follows') ? DB::table('follows')
                ->where('user_id', $userId)
                ->pluck('followed_id')
                ->filter()
                ->toArray() : [];

            $followedEateryIds = \Illuminate\Support\Facades\Schema::hasTable('follows') ? DB::table('follows')
                ->where('user_id', $userId)
                ->pluck('eatery_id')
                ->filter()
                ->toArray() : [];

            $targetUserIds = array_unique(array_merge($friendIds, $followedUserIds));

            if (!empty($targetUserIds) || !empty($followedEateryIds)) {
                // Tải bài viết từ EducationProgram chỉ từ eatery mà user theo dõi hoặc bạn bè đăng
                $newEduPosts = collect();
                if (!empty($followedEateryIds)) {
                    $newEduPosts = DB::table('education_programs')
                        ->whereIn('eatery_id', $followedEateryIds)
                        ->latest()
                        ->take(10)
                        ->get();
                }

                foreach ($newEduPosts as $ep) {
                    $eatery = Eatery::find($ep->eatery_id);
                    $authorName = $eatery ? $eatery->standardized_name : 'Một trang bạn theo dõi';
                    $snippet = Str::limit($ep->name ?: $ep->description, 50);

                    $notifications[] = [
                        'id'         => 'new_edu_' . $ep->id . '_' . strtotime($ep->created_at),
                        'title'      => '📣 Bài viết mới từ ' . $authorName,
                        'body'       => "{$authorName} vừa đăng bài viết mới: \"{$snippet}\"",
                        'time'       => Carbon::parse($ep->created_at)->diffForHumans(),
                        'time_ts'    => strtotime($ep->created_at),
                        'type'       => 'new_post',
                        'icon'       => 'article',
                        'is_read'    => false,
                        'post_id'    => $ep->id,
                        'target_url' => '/ban-tin?post=' . ($ep->hashid ?? $ep->id),
                    ];
                }

                // Tải checkin mới từ bạn bè
                if (!empty($targetUserIds)) {
                    $newCheckins = Checkin::whereIn('user_id', $targetUserIds)
                        ->latest()
                        ->take(10)
                        ->get();

                    foreach ($newCheckins as $chk) {
                        $author = User::find($chk->user_id);
                        $authorName = $author ? $author->name : 'Một người bạn';
                        $snippet = Str::limit($chk->content ?: ($chk->caption ?: 'bài viết mới'), 50);

                        $notifications[] = [
                            'id'         => 'new_chk_' . $chk->id . '_' . strtotime($chk->created_at),
                            'title'      => '📝 Bài viết mới từ ' . $authorName,
                            'body'       => "{$authorName} vừa chia sẻ một bài viết mới: \"{$snippet}\"",
                            'time'       => Carbon::parse($chk->created_at)->diffForHumans(),
                            'time_ts'    => strtotime($chk->created_at),
                            'type'       => 'new_post',
                            'icon'       => 'article',
                            'is_read'    => false,
                            'post_id'    => $chk->id,
                            'target_url' => '/ban-tin?post=' . ($chk->hashid ?? $chk->id),
                        ];
                    }
                }
            }

            // Sắp xếp lại theo mốc thời gian mới nhất xếp trên cùng
            usort($notifications, function($a, $b) {
                return ($b['time_ts'] ?? 0) <=> ($a['time_ts'] ?? 0);
            });

            // Ghi nhận trạng thái đã đọc/chưa đọc dựa trên mốc thời gian xem thông báo gần nhất
            $lastReadTs = (int) (\Illuminate\Support\Facades\Cache::get("user_notif_read_{$userId}") ?? session('notifications_last_read_at', 0));
            foreach ($notifications as &$notif) {
                if (isset($notif['time_ts'])) {
                    $notif['is_read'] = ($notif['time_ts'] <= $lastReadTs);
                }
            }
            unset($notif);

        } catch (\Throwable $e) {
            \Log::error('Error generating notifications: ' . $e->getMessage());
        }

        return $notifications;
    }

    /**
     * Đánh dấu đã đọc tất cả thông báo của người dùng
     */
    public static function markAsRead(int $userId): void
    {
        $now = time();
        \Illuminate\Support\Facades\Cache::put("user_notif_read_{$userId}", $now, now()->addDays(60));
        session(['notifications_last_read_at' => $now]);
    }

    /**
     * Bắn thông báo đẩy (FCM) & tự động ghi nhận bài viết mới cho bạn bè & người theo dõi
     */
    public static function notifyNewPost($authorUser, string $postTitle = '', string $postContent = '', ?int $eateryId = null): void
    {
        try {
            if (!$authorUser) return;

            // 1. Lấy danh sách ID bạn bè
            $friendIds = DB::table('friendships')
                ->where('status', 'accepted')
                ->where(function($q) use ($authorUser) {
                    $q->where('user_id', $authorUser->id)->orWhere('friend_id', $authorUser->id);
                })
                ->get()
                ->map(function($f) use ($authorUser) {
                    return $f->user_id == $authorUser->id ? $f->friend_id : $f->user_id;
                })
                ->toArray();

            // 2. Lấy danh sách ID người theo dõi (followers)
            $followerQuery = DB::table('follows')
                ->where('followed_id', $authorUser->id);
            
            if ($eateryId) {
                $followerQuery->orWhere('eatery_id', $eateryId);
            }
            
            $followerIds = $followerQuery->pluck('user_id')->toArray();

            // 3. Tổng hợp danh sách người nhận (không bao gồm chính tác giả)
            $recipientIds = array_unique(array_filter(array_merge($friendIds, $followerIds)));
            $recipientIds = array_diff($recipientIds, [$authorUser->id]);

            if (empty($recipientIds)) return;

            $recipients = User::whereIn('id', $recipientIds)->get();
            $authorName = $authorUser->name;
            $snippet = Str::limit($postTitle ?: $postContent, 55);

            $title = "📣 Bài viết mới từ {$authorName}";
            $body  = "{$authorName} vừa đăng bài viết mới: \"{$snippet}\"";

            foreach ($recipients as $recipient) {
                // Bắn FCM Push Notification ngay lập tức nếu người dùng có fcm_token
                if (!empty($recipient->fcm_token)) {
                    FcmService::sendNotification($recipient->fcm_token, $title, $body, [
                        'type'        => 'new_post',
                        'author_name' => $authorName,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Log::error('notifyNewPost Exception: ' . $e->getMessage());
        }
    }

    /**
     * Bắn FCM Push Notification khi có lượt Thả Cảm Xúc mới
     */
    public static function notifyReaction(int $postId, string $type, string $emoji, ?int $reactorUserId): void
    {
        try {
            $post = null;
            if ($type === 'checkin' || $type === 'App\\Models\\Checkin') {
                $post = Checkin::find($postId);
            } else if ($type === 'diary' || $type === 'App\\Models\\FoodTourDiary') {
                $post = FoodTourDiary::find($postId);
            } else if ($type === 'education' || $type === 'App\\Models\\EducationProgram') {
                $post = \App\Models\EducationProgram::find($postId);
            } else if ($type === 'eatery' || $type === 'App\\Models\\Eatery') {
                $post = Eatery::find($postId);
            } else {
                $post = \App\Models\Post::find($postId);
            }

            if (!$post) return;
            $ownerUserId = $post->user_id ?? ($post->eatery ? $post->eatery->user_id : null);
            if (!$ownerUserId) return;
            if ($reactorUserId && (int)$reactorUserId === (int)$ownerUserId) return;

            $author = User::find($ownerUserId);
            if (!$author) return;

            // Tính tổng người thả cảm xúc bài này
            $allReactors = CheckinReaction::whereIn('reactionable_type', [$type, 'App\\Models\\' . ucfirst($type)])
                ->where('reactionable_id', $postId)
                ->where(function($q) use ($ownerUserId) {
                    $q->whereNull('user_id')->orWhere('user_id', '!=', $ownerUserId);
                })
                ->latest()
                ->get();

            $totalReactors = $allReactors->pluck('user_id')->filter()->unique()->count();
            if ($totalReactors === 0) $totalReactors = $allReactors->count();

            $first = $allReactors->first();
            $latestName = $first && $first->user ? $first->user->name : 'Một thành viên';
            $othersCount = max(0, $totalReactors - 1);
            $postTypeLabel = match(strtolower($type)) {
                'checkin' => 'check-in',
                'diary' => 'hành trình',
                'eatery' => 'cơ sở/gian hàng',
                default => 'bài viết',
            };

            if ($othersCount > 0) {
                $title = "👍 Cảm xúc mới bài {$postTypeLabel}";
                $body  = "{$latestName} và {$othersCount} người khác đã thả cảm xúc bài viết {$postTypeLabel} của bạn.";
            } else {
                $title = "👍 Cảm xúc mới bài {$postTypeLabel}";
                $body  = "{$latestName} đã thả {$emoji} bài viết {$postTypeLabel} của bạn.";
            }

            if (!empty($author->fcm_token)) {
                FcmService::sendNotification($author->fcm_token, $title, $body, [
                    'type' => 'reaction',
                    'post_id' => (string)$postId,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('notifyReaction Exception: ' . $e->getMessage());
        }
    }

    /**
     * Bắn FCM Push Notification khi có Bình Luận mới
     */
    public static function notifyComment(Comment $comment): void
    {
        try {
            $post = null;
            $type = 'post';
            if (str_contains($comment->commentable_type, 'Checkin') || $comment->commentable_type === 'checkin') {
                $post = Checkin::find($comment->commentable_id);
                $type = 'checkin';
            } else if (str_contains($comment->commentable_type, 'FoodTourDiary') || $comment->commentable_type === 'diary') {
                $post = FoodTourDiary::find($comment->commentable_id);
                $type = 'diary';
            } else if (str_contains($comment->commentable_type, 'EducationProgram') || $comment->commentable_type === 'education') {
                $post = \App\Models\EducationProgram::find($comment->commentable_id);
                $type = 'education';
            } else if (str_contains($comment->commentable_type, 'Eatery') || $comment->commentable_type === 'eatery') {
                $post = Eatery::find($comment->commentable_id);
                $type = 'eatery';
            } else {
                $post = \App\Models\Post::find($comment->commentable_id);
                $type = 'post';
            }

            if (!$post) return;
            $ownerUserId = $post->user_id ?? ($post->eatery ? $post->eatery->user_id : null);
            if (!$ownerUserId) return;
            if ($comment->user_id && (int)$comment->user_id === (int)$ownerUserId) return;

            $author = User::find($ownerUserId);
            if (!$author) return;

            // Tính tổng người đã bình luận bài này
            $allComments = Comment::where('commentable_type', $comment->commentable_type)
                ->where('commentable_id', $comment->commentable_id)
                ->where(function($q) use ($ownerUserId) {
                    $q->whereNull('user_id')->orWhere('user_id', '!=', $ownerUserId);
                })
                ->latest()
                ->get();

            $totalCommenters = $allComments->pluck('user_id')->filter()->unique()->count();
            if ($totalCommenters === 0) $totalCommenters = $allComments->count();

            $latestName = $comment->display_name ?? 'Một thành viên';
            $othersCount = max(0, $totalCommenters - 1);
            $postTypeLabel = match(strtolower($type)) {
                'checkin' => 'check-in',
                'diary' => 'hành trình',
                'eatery' => 'cơ sở',
                default => 'bài viết',
            };

            if ($othersCount > 0) {
                $title = "💬 Bình luận mới bài {$postTypeLabel}";
                $body  = "{$latestName} và {$othersCount} người khác đã bình luận về bài viết {$postTypeLabel} của bạn.";
            } else {
                $snippet = Str::limit($comment->content ?? '', 45);
                $title = "💬 Bình luận mới bài {$postTypeLabel}";
                $body  = "{$latestName} đã bình luận bài viết {$postTypeLabel} của bạn: \"{$snippet}\"";
            }

            if (!empty($author->fcm_token)) {
                FcmService::sendNotification($author->fcm_token, $title, $body, [
                    'type' => 'comment',
                    'post_id' => (string)$comment->commentable_id,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('notifyComment Exception: ' . $e->getMessage());
        }
    }

    /**
     * Bắn FCM Push Notification khi có Lượt Chia Sẻ mới
     */
    public static function notifyShare(int $postId, string $type, ?int $sharerUserId = null): void
    {
        try {
            $post = null;
            if ($type === 'checkin' || $type === 'App\\Models\\Checkin') {
                $post = Checkin::find($postId);
            } else if ($type === 'diary' || $type === 'App\\Models\\FoodTourDiary') {
                $post = FoodTourDiary::find($postId);
            } else if ($type === 'education' || $type === 'App\\Models\\EducationProgram') {
                $post = \App\Models\EducationProgram::find($postId);
            } else if ($type === 'eatery' || $type === 'App\\Models\\Eatery') {
                $post = Eatery::find($postId);
            } else {
                $post = \App\Models\Post::find($postId);
            }

            if (!$post) return;
            $ownerUserId = $post->user_id ?? ($post->eatery ? $post->eatery->user_id : null);
            if (!$ownerUserId) return;
            if ($sharerUserId && (int)$sharerUserId === (int)$ownerUserId) return;

            $author = User::find($ownerUserId);
            if (!$author || empty($author->fcm_token)) return;

            $title = "🔄 Lượt chia sẻ mới";
            $body = "Bài viết / gian hàng của bạn vừa có thêm lượt chia sẻ mới từ cộng đồng!";

            FcmService::sendNotification($author->fcm_token, $title, $body, [
                'type' => 'share',
                'post_id' => (string)$postId,
            ]);
        } catch (\Throwable $e) {
            \Log::error('notifyShare Exception: ' . $e->getMessage());
        }
    }
}
