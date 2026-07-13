<?php

namespace App\Domain\Room\Actions;

use App\Domain\Room\RoomData;
use App\Models\Room;

class CreateRoomAction
{
    public function execute(RoomData $data, ?string $imagePath): Room
    {
        $room = new Room();
        $room->fill([
            'eatery_id' => $data->eatery_id,
            'name' => $data->name,
            'price' => $data->price,
            'description' => $data->description,
            'image_path' => $imagePath,
            'bed_type' => $data->bed_type,
            'capacity' => $data->capacity,
        ]);
        $room->save();
        return $room;
    }
}
