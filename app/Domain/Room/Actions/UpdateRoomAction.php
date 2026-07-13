<?php

namespace App\Domain\Room\Actions;

use App\Domain\Room\RoomData;
use App\Models\Room;

class UpdateRoomAction
{
    public function execute(Room $room, RoomData $data, ?string $imagePath): Room
    {
        $room->update([
            'name' => $data->name,
            'price' => $data->price,
            'description' => $data->description,
            'image_path' => $imagePath,
            'bed_type' => $data->bed_type,
            'capacity' => $data->capacity,
        ]);
        return $room;
    }
}
