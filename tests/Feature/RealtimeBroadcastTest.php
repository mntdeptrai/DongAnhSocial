<?php

namespace Tests\Feature;

use App\Events\NewCheckinPosted;
use App\Events\NewCommentPosted;
use App\Events\NewFoodTourDiaryPosted;
use App\Models\Checkin;
use App\Models\Comment;
use App\Models\FoodTour;
use App\Models\FoodTourDiary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealtimeBroadcastTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify that creating a Checkin triggers the NewCheckinPosted event.
     */
    public function test_checkin_creation_broadcasts_new_checkin_posted_event(): void
    {
        Event::fake([NewCheckinPosted::class]);

        $user = User::factory()->create();

        // Create Checkin
        $checkin = Checkin::create([
            'user_id' => $user->id,
            'comment' => 'Check-in real-time test!',
            'rating' => 5,
        ]);

        Event::assertDispatched(NewCheckinPosted::class, function ($event) use ($checkin) {
            return $event->checkin->id === $checkin->id;
        });
    }

    /**
     * Verify that creating a FoodTourDiary triggers the NewFoodTourDiaryPosted event.
     */
    public function test_diary_creation_broadcasts_new_food_tour_diary_posted_event(): void
    {
        Event::fake([NewFoodTourDiaryPosted::class]);

        $user = User::factory()->create();
        $tour = FoodTour::create([
            'name' => 'Food Tour Test',
            'slug' => 'food-tour-test',
            'description' => 'Tour mô tả',
            'duration' => '2 giờ',
            'distance' => '5 km',
            'budget' => '100.000đ',
            'difficulty' => 'Dễ',
            'best_time' => 'Chiều tối',
        ]);

        // Create Diary
        $diary = FoodTourDiary::create([
            'user_id' => $user->id,
            'food_tour_id' => $tour->id,
            'comment' => 'Nhật ký Food Tour real-time test!',
            'rating' => 4,
            'stop_reviews' => [],
        ]);

        Event::assertDispatched(NewFoodTourDiaryPosted::class, function ($event) use ($diary) {
            return $event->diary->id === $diary->id;
        });
    }

    /**
     * Verify that creating a Comment triggers the NewCommentPosted event.
     */
    public function test_comment_creation_broadcasts_new_comment_posted_event(): void
    {
        Event::fake([NewCommentPosted::class]);

        $user = User::factory()->create();
        $checkin = Checkin::create([
            'user_id' => $user->id,
            'comment' => 'Bài viết gốc',
            'rating' => 5,
        ]);

        // Create Comment on Checkin
        $comment = Comment::create([
            'user_id' => $user->id,
            'display_name' => $user->name,
            'content' => 'Bình luận thời gian thực!',
            'commentable_id' => $checkin->id,
            'commentable_type' => Checkin::class,
        ]);

        Event::assertDispatched(NewCommentPosted::class, function ($event) use ($comment) {
            return $event->comment->id === $comment->id;
        });
    }
}
