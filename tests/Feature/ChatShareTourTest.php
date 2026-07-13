<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Models\Friendship;
use App\Models\FoodTour;
use App\Models\User;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ChatShareTourTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_share_designed_food_tour_in_chat(): void
    {
        Event::fake([MessageSent::class]);

        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        // 1. Tạo mối quan hệ bạn bè đã chấp nhận
        Friendship::create([
            'user_id' => $sender->id,
            'friend_id' => $receiver->id,
            'status' => 'accepted',
        ]);

        // 2. Tạo Food Tour tự thiết kế bởi sender
        $tour = FoodTour::create([
            'user_id' => $sender->id,
            'name' => 'Tour đặc sản Đông Anh của tôi',
            'slug' => 'tour-dac-san-dong-anh-cua-toi',
            'description' => 'Mô tả tour',
            'duration' => '3 giờ',
            'distance' => '6 km',
            'budget' => '150.000đ',
            'difficulty' => 'Trung bình',
            'best_time' => '18:00 - 21:00',
        ]);

        // 3. Đăng nhập sender
        $this->actingAs($sender);

        // 4. Gửi yêu cầu chia sẻ lộ trình qua chat
        $response = $this->postJson(route('social.messages.send'), [
            'receiver_id' => $receiver->id,
            'message' => 'Lộ trình ngon lắm bạn ơi!',
            'food_tour_id' => $tour->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonStructure([
            'status',
            'message' => [
                'id',
                'sender_id',
                'receiver_id',
                'message',
                'food_tour_id',
                'food_tour' => [
                    'id',
                    'name',
                    'slug',
                    'duration',
                    'budget',
                ],
            ]
        ]);

        // 5. Xác minh event Broadcast MessageSent được dispatch
        Event::assertDispatched(MessageSent::class, function ($event) use ($tour, $receiver) {
            return (int)$event->message->food_tour_id === (int)$tour->id 
                && (int)$event->message->receiver_id === (int)$receiver->id;
        });

        // 6. Nhận lịch sử chat và kiểm tra thông tin lộ trình đã tải
        $historyResponse = $this->getJson(route('social.messages.get', ['friendId' => $receiver->id]));
        $historyResponse->assertStatus(200);
        $historyResponse->assertJsonFragment([
            'food_tour_id' => $tour->id,
            'name' => $tour->name,
            'slug' => $tour->slug,
        ]);
    }

    public function test_cannot_share_tour_if_not_friends(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $tour = FoodTour::create([
            'user_id' => $sender->id,
            'name' => 'Tour đặc sản Đông Anh của tôi',
            'slug' => 'tour-dac-san-dong-anh-cua-toi',
            'description' => 'Mô tả tour',
            'duration' => '3 giờ',
            'distance' => '6 km',
            'budget' => '150.000đ',
            'difficulty' => 'Trung bình',
            'best_time' => 'Chiều tối',
        ]);

        $this->actingAs($sender);

        // Không có friendship -> lỗi 403
        $response = $this->postJson(route('social.messages.send'), [
            'receiver_id' => $receiver->id,
            'message' => 'Xem lộ trình này nhé!',
            'food_tour_id' => $tour->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_can_send_media_message_in_chat(): void
    {
        Event::fake([MessageSent::class]);

        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        // 1. Tạo mối quan hệ bạn bè đã chấp nhận
        Friendship::create([
            'user_id' => $sender->id,
            'friend_id' => $receiver->id,
            'status' => 'accepted',
        ]);

        $this->actingAs($sender);

        // 2. Gửi tin nhắn chứa media
        $response = $this->postJson(route('social.messages.send'), [
            'receiver_id' => $receiver->id,
            'message' => 'Đây là video ẩm thực xịn xò!',
            'media_path' => 'https://r2.foodmap.vn/uploads/video_test.mp4',
            'media_type' => 'video',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonStructure([
            'status',
            'message' => [
                'id',
                'sender_id',
                'receiver_id',
                'message',
                'media_path',
                'media_type',
            ]
        ]);

        // 3. Xác minh event Broadcast MessageSent được dispatch với media fields
        Event::assertDispatched(MessageSent::class, function ($event) use ($receiver) {
            return $event->message->media_path === 'https://r2.foodmap.vn/uploads/video_test.mp4' 
                && $event->message->media_type === 'video';
        });

        // 4. Nhận lịch sử chat và kiểm tra thông tin media đã tải
        $historyResponse = $this->getJson(route('social.messages.get', ['friendId' => $receiver->id]));
        $historyResponse->assertStatus(200);
        $historyResponse->assertJsonFragment([
            'media_path' => 'https://r2.foodmap.vn/uploads/video_test.mp4',
            'media_type' => 'video',
        ]);
    }
}
