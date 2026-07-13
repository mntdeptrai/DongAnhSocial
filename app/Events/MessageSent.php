<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Message $message)
    {
        $this->message->loadMissing(['sender', 'receiver', 'foodTour']);
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('chat.' . $this->message->receiver_id);
    }

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'message' => $this->message->message,
            'food_tour_id' => $this->message->food_tour_id,
            'media_path' => $this->message->media_path,
            'media_type' => $this->message->media_type,
            'food_tour' => $this->message->foodTour ? [
                'id' => $this->message->foodTour->id,
                'name' => $this->message->foodTour->name,
                'slug' => $this->message->foodTour->slug,
                'description' => $this->message->foodTour->description,
                'duration' => $this->message->foodTour->duration,
                'distance' => $this->message->foodTour->distance,
                'budget' => $this->message->foodTour->budget,
                'difficulty' => $this->message->foodTour->difficulty,
                'best_time' => $this->message->foodTour->best_time,
                'thumbnail' => $this->message->foodTour->thumbnail,
            ] : null,
            'is_read' => (bool)$this->message->is_read,
            'created_at_human' => $this->message->created_at->diffForHumans(),
            'created_at_format' => $this->message->created_at->format('d/m/Y H:i'),
            'sender' => [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->name,
                'avatar' => $this->message->sender->avatar ?? '👤',
            ]
        ];
    }
}
