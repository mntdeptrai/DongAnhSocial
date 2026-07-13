<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPresenceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_cannot_access_heartbeat_endpoint()
    {
        $response = $this->postJson(route('user.heartbeat'));

        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_send_heartbeat_and_update_last_active_at()
    {
        $user = User::factory()->create();

        $this->assertNull($user->last_active_at);
        $this->assertFalse($user->is_online);

        $response = $this->actingAs($user)->postJson(route('user.heartbeat'));

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $user->refresh();
        $this->assertNotNull($user->last_active_at);
        $this->assertTrue($user->is_online);
    }

    /** @test */
    public function user_is_considered_offline_after_two_minutes_of_inactivity()
    {
        $user = User::factory()->create([
            'last_active_at' => now()->subMinutes(3),
        ]);

        $this->assertFalse($user->is_online);
    }

    /** @test */
    public function friends_presence_endpoint_returns_online_friend_ids()
    {
        $user = User::factory()->create();
        $onlineFriend = User::factory()->create(['last_active_at' => now()]);
        $offlineFriend = User::factory()->create(['last_active_at' => now()->subMinutes(5)]);

        // Tạo quan hệ bạn bè đã đồng ý (accepted)
        \App\Models\Friendship::create([
            'user_id' => $user->id,
            'friend_id' => $onlineFriend->id,
            'status' => 'accepted'
        ]);

        \App\Models\Friendship::create([
            'user_id' => $user->id,
            'friend_id' => $offlineFriend->id,
            'status' => 'accepted'
        ]);

        $response = $this->actingAs($user)->getJson(route('social.friends.presence'));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'online_ids' => [$onlineFriend->id]
            ]);
    }
}
