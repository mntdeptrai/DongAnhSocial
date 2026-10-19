<?php

namespace App\Services;

use App\Contracts\MarketChatServiceInterface;
use App\Helpers\R2Helper;
use App\Models\MarketMessage;
use App\Models\OcopProduct;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class MarketChatService implements MarketChatServiceInterface
{
    /**
     * Get chat messages and room context for an eatery.
     */
    public function getChatFeed(int|string $eateryId, array $filters, ?User $authUser): array
    {
        $room = $filters['room'] ?? 'public';
        $privateStall = $filters['private_stall_name'] ?? null;
        $privateUserId = $filters['private_user_id'] ?? null;

        $query = MarketMessage::where('eatery_id', $eateryId);

        // Check if the current user is a merchant of this market
        $isStallOwner = false;
        $userStallName = null;
        if ($authUser && $authUser->phone) {
            $merchantStall = OcopProduct::on('mysql_market')
                ->where('eatery_id', $eateryId)
                ->where('seller_phone', $authUser->phone)
                ->whereNotNull('stall_name')
                ->first();
            if ($merchantStall) {
                $isStallOwner = true;
                $userStallName = $merchantStall->stall_name;
            }
        }

        if ($room === 'private' && $privateStall) {
            if ($authUser) {
                $currUserId = $authUser->id;

                // Case A: Customer chatting with stall
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
                // Case B: Stall owner replying to customer
                elseif ($privateUserId) {
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
                    return [
                        'messages'           => [],
                        'active_chats'       => [],
                        'latest_message_ids' => [],
                    ];
                }
            } else {
                return [
                    'messages'           => [],
                    'active_chats'       => [],
                    'latest_message_ids' => [],
                ];
            }
        } else {
            // Public Group Chat
            $query->whereNull('private_stall_name')->whereNull('private_user_id');
        }

        $messages = $query->orderBy('id', 'asc')->take(50)->get();

        $formatted = $messages->map(function ($msg) use ($authUser) {
            $productData = null;
            if ($msg->product_id && $msg->product) {
                $p = $msg->product;
                $productData = [
                    'id'         => $p->id,
                    'name'       => $p->name,
                    'price'      => (float) $p->price,
                    'image'      => $p->image_path ? asset($p->image_path) : 'https://placehold.co/80x80/00A86B/ffffff?text=OCOP',
                    'stall_name' => $p->stall_name,
                ];
            }

            $dateGroup = 'Lịch sử';
            if ($msg->created_at->isToday()) {
                $dateGroup = 'Hôm nay';
            } elseif ($msg->created_at->isYesterday()) {
                $dateGroup = 'Hôm qua';
            } else {
                $dateGroup = $msg->created_at->format('d/m/Y');
            }

            return [
                'id'             => $msg->id,
                'sender_name'    => $msg->sender_name,
                'sender_role'    => $msg->sender_role,
                'stall_name'     => $msg->stall_name,
                'message_text'   => $msg->message_text,
                'image_url'      => $msg->image_path ? asset($msg->image_path) : null,
                'product'        => $productData,
                'time_formatted' => $msg->created_at->format('H:i'),
                'is_own'         => $authUser && $msg->user_id === $authUser->id,
                'date_group'     => $dateGroup,
            ];
        });

        // Active private chats for merchant
        $activeChats = [];
        if ($authUser && $authUser->phone) {
            $merchantStall = OcopProduct::on('mysql_market')
                ->where('eatery_id', $eateryId)
                ->where('seller_phone', $authUser->phone)
                ->whereNotNull('stall_name')
                ->first();

            if ($merchantStall) {
                $customerIds = MarketMessage::where('eatery_id', $eateryId)
                    ->where('private_stall_name', $merchantStall->stall_name)
                    ->whereNotNull('user_id')
                    ->distinct()
                    ->pluck('user_id');

                $activeChats = User::whereIn('id', $customerIds)
                    ->get(['id', 'name'])
                    ->toArray();
            }
        }

        // Latest message IDs for indicators
        $latestMessageIds = [];
        $latestMessageIds['public'] = MarketMessage::where('eatery_id', $eateryId)
            ->whereNull('private_stall_name')
            ->whereNull('private_user_id')
            ->max('id') ?: 0;

        if ($authUser) {
            $currUserId = $authUser->id;
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

                if ($msg->private_user_id) {
                    $latestMessageIds['customer_' . $msg->private_user_id] = max(
                        $latestMessageIds['customer_' . $msg->private_user_id] ?? 0,
                        $msg->id
                    );
                }
            }
        }

        return [
            'messages'           => $formatted,
            'active_chats'       => $activeChats,
            'latest_message_ids' => $latestMessageIds,
        ];
    }

    /**
     * Send a market chat message.
     */
    public function sendMessage(int|string $eateryId, array $data, ?User $authUser, ?UploadedFile $imageFile): MarketMessage
    {
        $privateStallName = $data['private_stall_name'] ?? null;
        $privateUserId = $data['private_user_id'] ?? null;

        $userId = $authUser ? $authUser->id : null;
        $role = 'user';
        $stallName = null;
        $senderName = 'Khách đi chợ';

        if ($authUser) {
            $senderName = $authUser->name;

            // Trường hợp 1: Chủ sạp trả lời riêng cho khách hàng
            if ($privateUserId && $privateStallName) {
                $role = 'merchant';
                $stallName = $privateStallName;
                $senderName = 'Chủ sạp ' . $authUser->name;
            }
            // Trường hợp 2: Chat trong phòng công khai của Chợ
            elseif (empty($privateStallName)) {
                if ($authUser->phone) {
                    $merchantStall = OcopProduct::on('mysql_market')
                        ->where('eatery_id', $eateryId)
                        ->where('seller_phone', $authUser->phone)
                        ->whereNotNull('stall_name')
                        ->first();

                    if ($merchantStall) {
                        $role = 'merchant';
                        $stallName = $merchantStall->stall_name;
                        $senderName = 'Chủ sạp ' . ($merchantStall->seller_name ?: $authUser->name);
                    }
                }

                if ($authUser->role === 'admin' && str_contains(strtolower($authUser->name . ' ' . $authUser->username), 'bql')) {
                    $role = 'admin';
                    $senderName = '🛡️ BQL Chợ ' . $authUser->name;
                }
            }
            // Trường hợp 3: Khách hàng nhắn riêng cho sạp
            else {
                $role = 'user';
                $senderName = $authUser->name;
            }
        } else {
            $senderName = $data['sender_name'] ?? 'Khách Đông Anh';
            $senderName = strip_tags($senderName);
            if (mb_strlen($senderName) > 25) {
                $senderName = mb_substr($senderName, 0, 25) . '...';
            }
        }

        $messageText = $data['message_text'] ?? '';
        if (empty($messageText) && !empty($data['product_id'])) {
            $product = OcopProduct::on('mysql_market')->find($data['product_id']);
            if ($product) {
                $messageText = 'Tôi muốn chia sẻ sản phẩm này!';
            }
        }

        $imagePath = null;
        if ($imageFile) {
            $imagePath = R2Helper::upload($imageFile, 'market_chat');
        }

        return MarketMessage::create([
            'eatery_id'          => $eateryId,
            'user_id'            => $userId,
            'sender_name'        => $senderName,
            'sender_role'        => $role,
            'stall_name'         => $stallName,
            'message_text'       => $messageText ?: '',
            'image_path'         => $imagePath,
            'product_id'         => $data['product_id'] ?? null,
            'private_stall_name' => $privateStallName ?: null,
            'private_user_id'    => $privateUserId ?: null,
        ]);
    }
}
