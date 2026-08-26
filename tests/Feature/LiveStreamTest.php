<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\LiveStream;
use App\Models\OcopProduct;
use App\Models\LiveStreamComment;
use App\Events\LiveStreamCommentSent;
use App\Events\LiveStreamReactionSent;
use App\Events\LiveStreamProductsUpdated;
use App\Events\LiveStreamProductPinned;
use Illuminate\Support\Facades\Event;

class LiveStreamTest extends TestCase
{
    public function test_can_view_livestream_index(): void
    {
        $response = $this->get('/livestream');
        $response->assertStatus(200);
        $response->assertSee('ĐÔNG ANH LIVE STUDIO');
    }

    public function test_can_view_livestream_watch_page(): void
    {
        $viewer = User::first();
        $this->assertNotNull($viewer);

        $host = User::factory()->create();

        $stream = LiveStream::create([
            'user_id'      => $host->id,
            'title'        => 'Livestream Test Nông Sản OCOP',
            'category'     => 'ocop',
            'status'       => 'live',
            'viewer_count' => 10,
            'likes_count'  => 50,
            'started_at'   => now(),
        ]);

        $response = $this->actingAs($viewer)->get('/livestream/' . $stream->id);
        $response->assertStatus(200);
        $response->assertSee('Livestream Test Nông Sản OCOP');

        $stream->delete();
        $host->delete();
    }



    public function test_livestream_api_returns_active_streams(): void
    {
        $user = User::first();
        $stream = LiveStream::create([
            'user_id'      => $user->id,
            'title'        => 'Livestream API Test',
            'category'     => 'food',
            'status'       => 'live',
            'viewer_count' => 15,
            'likes_count'  => 25,
            'started_at'   => now(),
        ]);

        $response = $this->getJson('/api/v1/livestreams');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'category',
                    'viewer_count',
                    'likes_count',
                    'user' => ['id', 'name'],
                ]
            ]
        ]);

        $stream->delete();
    }

    public function test_can_send_reaction_and_broadcast_event(): void
    {
        Event::fake([LiveStreamReactionSent::class]);

        $user = User::first();
        $stream = LiveStream::create([
            'user_id'      => $user->id,
            'title'        => 'Livestream Reaction Test',
            'category'     => 'culture',
            'status'       => 'live',
            'viewer_count' => 5,
            'likes_count'  => 10,
            'started_at'   => now(),
        ]);

        $response = $this->postJson("/livestream/{$stream->id}/reaction", [
            'type' => 'heart'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'      => 'success',
            'total_likes' => 11,
        ]);

        Event::assertDispatched(LiveStreamReactionSent::class, function ($event) use ($stream) {
            return $event->liveStreamId === $stream->id && $event->reactionType === 'heart';
        });

        $stream->delete();
    }

    public function test_authenticated_user_can_comment_and_broadcast_event(): void
    {
        Event::fake([LiveStreamCommentSent::class]);

        $user = User::first();
        $stream = LiveStream::create([
            'user_id'      => $user->id,
            'title'        => 'Livestream Comment Test',
            'category'     => 'ocop',
            'status'       => 'live',
            'viewer_count' => 5,
            'likes_count'  => 10,
            'started_at'   => now(),
        ]);

        $response = $this->actingAs($user)->postJson("/livestream/{$stream->id}/comment", [
            'message' => 'Xin chào mọi người đang xem live!',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'comment' => [
                'message' => 'Xin chào mọi người đang xem live!',
            ]
        ]);

        Event::assertDispatched(LiveStreamCommentSent::class, function ($event) use ($stream) {
            return $event->liveStreamId === $stream->id && $event->message === 'Xin chào mọi người đang xem live!';
        });

        $stream->delete();
    }

    public function test_can_attach_and_manage_multiple_products_in_livestream_cart(): void
    {
        Event::fake([LiveStreamProductsUpdated::class, LiveStreamProductPinned::class]);

        $user = User::first();

        $prods = \App\Models\OcopCertifiedProduct::take(2)->get();
        if ($prods->count() < 2) {
            $p1 = \App\Models\OcopCertifiedProduct::create([
                'name'        => 'Bánh Chưng Tranh Khúc Test 1',
                'slug'        => 'banh-chung-test-1-' . uniqid(),
                'price'       => 50000,
                'star_rating' => '4 sao',
                'unit'        => 'chiếc'
            ]);
            $p2 = \App\Models\OcopCertifiedProduct::create([
                'name'        => 'Đông Trùng Hạ Thảo Test 2',
                'slug'        => 'dong-trung-test-2-' . uniqid(),
                'price'       => 250000,
                'star_rating' => '5 sao',
                'unit'        => 'hộp'
            ]);
            $createdProds = true;
        } else {
            $p1 = $prods[0];
            $p2 = $prods[1];
            $createdProds = false;
        }



        $stream = LiveStream::create([
            'user_id'      => $user->id,
            'title'        => 'Livestream Multi Products Test',
            'category'     => 'ocop',
            'status'       => 'live',
            'viewer_count' => 1,
            'likes_count'  => 0,
            'started_at'   => now(),
        ]);

        // 1. Thêm sản phẩm 1 vào giỏ live
        $res1 = $this->actingAs($user)->postJson("/livestream/{$stream->id}/products", [
            'product_id' => $p1->id
        ]);
        $res1->assertStatus(200);
        $res1->assertJsonPath('status', 'success');

        // 2. Thêm sản phẩm 2 vào giỏ live
        $res2 = $this->actingAs($user)->postJson("/livestream/{$stream->id}/products", [
            'product_id' => $p2->id
        ]);
        $res2->assertStatus(200);

        // 3. Lấy danh sách sản phẩm trong giỏ hàng
        $resGet = $this->getJson("/livestream/{$stream->id}/products");
        $resGet->assertStatus(200);
        $resGet->assertJsonCount(2, 'products');

        // 4. Ghim sản phẩm 2 lên màn hình
        $resPin = $this->actingAs($user)->postJson("/livestream/{$stream->id}/pin-product", [
            'product_id' => $p2->id
        ]);
        $resPin->assertStatus(200);
        $resPin->assertJsonPath('pinned_product.id', $p2->id);

        // 5. Xóa sản phẩm 1 khỏi giỏ live
        $resDel = $this->actingAs($user)->deleteJson("/livestream/{$stream->id}/products/{$p1->id}");
        $resDel->assertStatus(200);
        $resDel->assertJsonCount(1, 'products');

        // Kiểm tra Event đã được dispatch
        Event::assertDispatched(LiveStreamProductsUpdated::class);
        Event::assertDispatched(LiveStreamProductPinned::class);

        $stream->delete();
        if ($createdProds) {
            $p1->delete();
            $p2->delete();
        }
    }
}

