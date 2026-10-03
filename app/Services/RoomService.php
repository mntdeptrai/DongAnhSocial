<?php

namespace App\Services;

use App\Domain\Room\RoomData;
use App\Helpers\R2Helper;
use App\Models\Eatery;
use App\Models\Room;

class RoomService
{
    public function create(RoomData|array $data): ?Room
    {
        if ($data instanceof RoomData) {
            $imagePath = $this->resolveImagePath($data->image, $data->image_url);
            $attributes = [
                'eatery_id' => $data->eatery_id,
                'name' => $data->name,
                'room_type' => $data->room_type,
                'price' => $data->price,
                'capacity' => $data->capacity,
                'amenities' => $data->amenities,
                'description' => $data->description,
                'image_path' => $imagePath,
            ];
        } else {
            $attributes = $data;
        }

        return $this->storeRoom($attributes);
    }

    public function update($id, RoomData|array $data): ?Room
    {
        $room = Room::find($id);
        if (!$room) return null;

        if ($data instanceof RoomData) {
            $imagePath = $this->resolveImagePath($data->image, $data->image_url) ?? $room->image_path;
            $attributes = [
                'eatery_id' => $data->eatery_id,
                'name' => $data->name,
                'room_type' => $data->room_type,
                'price' => $data->price,
                'capacity' => $data->capacity,
                'amenities' => $data->amenities,
                'description' => $data->description,
                'image_path' => $imagePath,
            ];
        } else {
            $attributes = $data;
        }

        $room->update($attributes);
        return $room;
    }

    public function storeRoom(array $data): ?Room
    {
        $eatery = Eatery::find($data['eatery_id'] ?? null);
        if (!$eatery) return null;

        return Room::create($data);
    }

    public function updateRoom($id, array $data): ?Room
    {
        $room = Room::find($id);
        if (!$room) return null;

        $room->update($data);
        return $room;
    }

    public function delete($id): bool
    {
        $room = Room::find($id);
        if (!$room) return false;

        return (bool) $room->delete();
    }

    public function deleteRoom($id): bool
    {
        return $this->delete($id);
    }

    protected function resolveImagePath($imageFile, ?string $imageUrl): ?string
    {
        if ($imageFile) {
            return R2Helper::upload($imageFile, 'rooms');
        }

        if ($imageUrl) {
            if (preg_match('/(?:drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?id=))([a-zA-Z0-9_-]{25,50})/i', $imageUrl, $matches)) {
                return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
            }
            return $imageUrl;
        }

        return null;
    }
}
