<?php

namespace App\Services;

use App\Domain\Room\RoomData;
use App\Domain\Room\Actions\CreateRoomAction;
use App\Domain\Room\Actions\UpdateRoomAction;
use App\Helpers\R2Helper;
use App\Models\Room;
use App\Services\EateryApiService;

class RoomService
{
    public function __construct(
        protected CreateRoomAction $createAction,
        protected UpdateRoomAction $updateAction
    ) {}

    public function create(RoomData $data, ?string $connName = null): Room
    {
        $imagePath = $this->resolveImagePath($data->image, $data->image_url);
        
        $action = $this->createAction;
        if ($connName) {
            \App\Models\Room::setConnectionResolver(app('db'));
        }
        
        $room = $action->execute($data, $imagePath);
        if ($connName) {
            $room->setConnection($connName);
            $room->save();
        }
        return $room;
    }

    public function update($id, RoomData $data, ?string $connName = null): Room
    {
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        $room = null;
        $activeConn = $connName;

        if ($connName) {
            $room = Room::on($connName)->find($id);
        } else {
            foreach ($connections as $conn) {
                $rm = Room::on($conn)->find($id);
                if ($rm) {
                    $room = $rm;
                    $activeConn = $conn;
                    break;
                }
            }
        }

        if (!$room) {
            throw new \Exception('Phòng nghỉ không tồn tại!');
        }

        $imagePath = $room->image_path;
        if ($data->image) {
            $imagePath = R2Helper::upload($data->image, 'rooms');
        } elseif ($data->image_url) {
            $imagePath = $this->resolveImagePath(null, $data->image_url);
        }

        return $this->updateAction->execute($room, $data, $imagePath);
    }

    public function delete($id): bool
    {
        return EateryApiService::deleteRoom($id);
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
