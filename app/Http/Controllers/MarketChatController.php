<?php

namespace App\Http\Controllers;

use App\Models\MarketMessage;
use App\Models\OcopProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketChatController extends Controller
{
    /**
     * Get the latest chat messages for a specific market (public or private)
     */
    public function getMessages(Request $request, $eateryId)
    {
        $room = $request->input('room', 'public'); // public or private
        $privateStall = $request->input('private_stall_name');
        $privateUserId = $request->input('private_user_id');

        $query = MarketMessage::where('eatery_id', $eateryId);

        // Check if the current user is a merchant of this market
        $isStallOwner = false;
        $userStallName = null;
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->phone) {
                $merchantStall = OcopProduct::on('mysql_market')
                    ->where('eatery_id', $eateryId)
                    ->where('seller_phone', $user->phone)
                    ->whereNotNull('stall_name')
                    ->first();
                if ($merchantStall) {
                    $isStallOwner = true;
                    $userStallName = $merchantStall->stall_name;
                }
            }
        }

        if ($room === 'private' && $privateStall) {
            if (Auth::check()) {
                $currUserId = Auth::user()->id;

                // Case A: Current user is a customer chatting with the stall
                if ($userStallName !== $privateStall) {
                    $query->where(function ($q) use ($privateStall, $currUserId) {
                        $q->where(function ($sub) use ($privateStall, $currUserId) {
                            $sub->where('private_stall_name', $privateStall)
                                ->where('user_id', $currUserId);
                        })
                        ->orWhere(function ($sub) use ($privateStall, $currUserId) {
                            $sub->where('stall_name', $privateStall)
                                ->where('private_user_id', $currUserId);
                        });
                    });
                } 
                // Case B: Current user IS the stall owner replying to a specific customer ($privateUserId)
                else if ($privateUserId) {
                    $query->where(function ($q) use ($privateStall, $privateUserId) {
                        $q->where(function ($sub) use ($privateStall, $privateUserId) {
                            $sub->where('private_stall_name', $privateStall)
                                ->where('user_id', $privateUserId);
                        })
                        ->orWhere(function ($sub) use ($privateStall, $privateUserId) {
                            $sub->where('stall_name', $privateStall)
                                ->where('private_user_id', $privateUserId);
                        });
                    });
                } else {
                    return response()->json([
                        'success' => true,
                        'messages' => []
                    ]);
                }
            } else {
                return response()->json([
                    'success' => true,
                    'messages' => []
                ]);
            }
        } else {
            // Public Group Chat
            $query->whereNull('private_stall_name')->whereNull('private_user_id');
        }

        $messages = $query->orderBy('id', 'asc')->take(50)->get();

        $formatted = $messages->map(function ($msg) {
            $productData = null;
            if ($msg->product_id && $msg->product) {
                $p = $msg->product;
                $productData = [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => (float)$p->price,
                    'image' => $p->image_path ? asset($p->image_path) : 'https://placehold.co/80x80/00A86B/ffffff?text=OCOP',
                    'stall_name' => $p->stall_name
                ];
            }

            // Create localized date label
            $dateGroup = 'Lịch sử';
            if ($msg->created_at->isToday()) {
                $dateGroup = 'Hôm nay';
            } elseif ($msg->created_at->isYesterday()) {
                $dateGroup = 'Hôm qua';
            } else {
                $dateGroup = $msg->created_at->format('d/m/Y');
            }

            return [
                'id' => $msg->id,
                'sender_name' => $msg->sender_name,
                'sender_role' => $msg->sender_role,
                'stall_name' => $msg->stall_name,
                'message_text' => $msg->message_text,
                'image_url' => $msg->image_path ? asset($msg->image_path) : null,
                'product' => $productData,
                'time_formatted' => $msg->created_at->format('H:i'),
                'is_own' => Auth::check() && $msg->user_id === Auth::user()->id,
                'date_group' => $dateGroup
            ];
        });

        // Get list of active private chats for this merchant (if logged in)
        $activeChats = [];
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->phone) {
                $merchantStall = OcopProduct::on('mysql_market')
                    ->where('eatery_id', $eateryId)
                    ->where('seller_phone', $user->phone)
                    ->whereNotNull('stall_name')
                    ->first();

                if ($merchantStall) {
                    $customerIds = MarketMessage::where('eatery_id', $eateryId)
                        ->where('private_stall_name', $merchantStall->stall_name)
                        ->whereNotNull('user_id')
                        ->distinct()
                        ->pluck('user_id');

                    $activeChats = \App\Models\User::whereIn('id', $customerIds)
                        ->get(['id', 'name'])
                        ->toArray();
                }
            }
        }

        // Calculate latest message IDs for unread indicators
        $latestMessageIds = [];
        $latestMessageIds['public'] = MarketMessage::where('eatery_id', $eateryId)
            ->whereNull('private_stall_name')
            ->whereNull('private_user_id')
            ->max('id') ?: 0;

        if (Auth::check()) {
            $currUserId = Auth::user()->id;
            $pmLogs = MarketMessage::where('eatery_id', $eateryId)
                ->where(function ($q) use ($currUserId) {
                    $q->where('user_id', $currUserId)
                      ->orWhere('private_user_id', $currUserId);
                })
                ->get();

            foreach ($pmLogs as $msg) {
                $sName = $msg->private_stall_name ?: $msg->stall_name;
                if ($sName) {
                    $latestMessageIds['private_' . $sName] = max(
                        $latestMessageIds['private_' . $sName] ?? 0,
                        $msg->id
                    );
                }
                
                // For merchant replying to user
                if ($msg->private_user_id) {
                    $latestMessageIds['customer_' . $msg->private_user_id] = max(
                        $latestMessageIds['customer_' . $msg->private_user_id] ?? 0,
                        $msg->id
                    );
                }
            }
        }

        return response()->json([
            'success' => true,
            'messages' => $formatted,
            'active_chats' => $activeChats,
            'latest_message_ids' => $latestMessageIds
        ]);
    }

    /**
     * Send a new message to the market chat (public or private)
     */
    public function sendMessage(Request $request, $eateryId)
    {
        $request->validate([
            'message_text' => 'required_without_all:product_id,image|nullable|string|max:500',
            'product_id' => 'nullable|integer',
            'image' => 'nullable|image|max:5120', // Max 5MB
            'sender_name' => 'nullable|string|max:50',
            'private_stall_name' => 'nullable|string|max:100',
            'private_user_id' => 'nullable|integer'
        ]);

        $userId = Auth::check() ? Auth::user()->id : null;
        $role = 'user';
        $stallName = null;
        $senderName = 'Khách đi chợ';

        if (Auth::check()) {
            $user = Auth::user();
            $senderName = $user->name;

            // Check if user is merchant of this market
            if ($user->phone) {
                $merchantStall = OcopProduct::on('mysql_market')
                    ->where('eatery_id', $eateryId)
                    ->where('seller_phone', $user->phone)
                    ->whereNotNull('stall_name')
                    ->first();

                if ($merchantStall) {
                    $role = 'merchant';
                    $stallName = $merchantStall->stall_name;
                    $senderName = 'Chủ sạp ' . ($merchantStall->seller_name ?: $user->name);
                }
            }

            // Check if user is admin
            if ($user->role === 'admin') {
                $role = 'admin';
                $senderName = '🛡️ BQL Chợ ' . $user->name;
            }
        } else {
            $senderName = $request->input('sender_name') ?: 'Khách Đông Anh';
            $senderName = strip_tags($senderName);
            if (mb_strlen($senderName) > 25) {
                $senderName = mb_substr($senderName, 0, 25) . '...';
            }
        }

        // Handle text when sharing a product/image without message
        $messageText = $request->input('message_text');
        if (empty($messageText) && $request->filled('product_id')) {
            $product = OcopProduct::on('mysql_market')->find($request->product_id);
            if ($product) {
                $messageText = 'Tôi muốn chia sẻ sản phẩm này!';
            }
        }

        // Handle Image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/market_chat');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $fileName);
            $imagePath = 'uploads/market_chat/' . $fileName;
        }

        $privateStallName = $request->input('private_stall_name');
        $privateUserId = $request->input('private_user_id');

        if (($privateStallName || $privateUserId) && !Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để gửi tin nhắn riêng.'], 403);
        }

        $message = MarketMessage::create([
            'eatery_id' => $eateryId,
            'user_id' => $userId,
            'sender_name' => $senderName,
            'sender_role' => $role,
            'stall_name' => $stallName,
            'message_text' => $messageText ?: '',
            'image_path' => $imagePath,
            'product_id' => $request->input('product_id'),
            'private_stall_name' => $privateStallName ?: null,
            'private_user_id' => $privateUserId ?: null,
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_name' => $message->sender_name,
                'sender_role' => $message->sender_role,
                'stall_name' => $message->stall_name,
                'message_text' => $message->message_text,
                'image_url' => $message->image_path ? asset($message->image_path) : null,
                'time_formatted' => $message->created_at->format('H:i'),
                'is_own' => true
            ]
        ]);
    }

    /**
     * Get all reviews for a specific market stall
     */
    public function getStallReviews($eateryId, \Illuminate\Http\Request $request)
    {
        $stallName = $request->query('stall_name');
        if (!$stallName) {
            return response()->json(['success' => false, 'message' => 'Thiếu tên sạp.'], 400);
        }

        $connection = 'mysql';

        $reviews = \App\Models\Review::on($connection)->where('eatery_id', $eateryId)
            ->where('stall_name', $stallName)
            ->orderBy('created_at', 'desc')
            ->get();

        $formatted = $reviews->map(function ($rev) {
            return [
                'id' => $rev->id,
                'user_name' => $rev->user_name,
                'rating' => $rev->rating,
                'comment' => $rev->comment,
                'time_formatted' => $rev->created_at ? $rev->created_at->diffForHumans() : 'Vừa xong'
            ];
        });

        return response()->json([
            'success' => true,
            'reviews' => $formatted
        ]);
    }

    /**
     * Store a new review for a specific market stall
     */
    public function storeStallReview($eateryId, \Illuminate\Http\Request $request)
    {
        $stallName = $request->input('stall_name');
        $comment = $request->input('comment');
        $rating = (int) $request->input('rating', 5);

        if (!$stallName || !$comment) {
            return response()->json(['success' => false, 'message' => 'Thiếu thông tin đánh giá.'], 400);
        }

        $connection = 'mysql';

        $userName = 'Khách vãng lai';
        if (Auth::check()) {
            $userName = Auth::user()->name;
        } elseif ($request->input('user_name')) {
            $userName = $request->input('user_name');
        }

        $review = new \App\Models\Review();
        $review->setConnection($connection);
        $review->fill([
            'eatery_id' => $eateryId,
            'stall_name' => $stallName,
            'user_name' => $userName,
            'rating' => $rating,
            'comment' => $comment
        ]);
        $review->save();

        return response()->json([
            'success' => true,
            'review' => [
                'id' => $review->id,
                'user_name' => $review->user_name,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'time_formatted' => 'Vừa xong'
            ]
        ]);
    }
}
