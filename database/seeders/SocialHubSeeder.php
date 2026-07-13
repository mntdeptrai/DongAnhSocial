<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Friendship;
use App\Models\Message;
use Illuminate\Database\Seeder;

class SocialHubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Cập nhật tọa độ GPS và thời gian hoạt động cho các user mẫu ở Đông Anh
        // Tọa độ UBND huyện Đông Anh làm gốc: 21.1345, 105.8425
        $coords = [
            'admin@foodmap.vn'  => ['lat' => 21.1350, 'lon' => 105.8430], // cách ~0.1km
            'seller@foodmap.vn' => ['lat' => 21.1420, 'lon' => 105.8450], // cách ~1.0km
            'user@foodmap.vn'   => ['lat' => 21.1340, 'lon' => 105.8410], // cách ~0.2km
            'member@foodmap.vn' => ['lat' => 21.1550, 'lon' => 105.8500], // cách ~2.5km
        ];

        foreach ($coords as $email => $pos) {
            User::where('email', $email)->update([
                'latitude' => $pos['lat'],
                'longitude' => $pos['lon'],
                'last_active_at' => now(),
            ]);
        }

        // Tạo thêm vài user xung quanh để "Ở gần" và "Gợi ý" phong phú hơn
        $extraUsers = [
            [
                'name'     => 'Lê Văn Nam (Đông Anh)',
                'email'    => 'namle@foodmap.vn',
                'password' => bcrypt('password123'),
                'role'     => 'user',
                'avatar'   => '🧑‍💻',
                'phone'    => '0909090901',
                'status'   => 'active',
                'latitude' => 21.1380, // ~0.5km
                'longitude' => 105.8440,
                'last_active_at' => now()->subMinutes(5),
            ],
            [
                'name'     => 'Hoàng Thị Lan (Cổ Loa)',
                'email'    => 'lanhoang@foodmap.vn',
                'password' => bcrypt('password123'),
                'role'     => 'user',
                'avatar'   => '👩',
                'phone'    => '0909090902',
                'status'   => 'active',
                'latitude' => 21.1680, // ~4km
                'longitude' => 105.8350,
                'last_active_at' => now()->subMinutes(12),
            ],
            [
                'name'     => 'Phan Anh Tuấn (Vân Trì)',
                'email'    => 'tuanphan@foodmap.vn',
                'password' => bcrypt('password123'),
                'role'     => 'user',
                'avatar'   => '👦',
                'phone'    => '0909090903',
                'status'   => 'active',
                'latitude' => 21.1520, // ~3km
                'longitude' => 105.7950,
                'last_active_at' => now()->subMinutes(25),
            ],
        ];

        foreach ($extraUsers as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                $u
            );
        }

        // Lấy các user để tạo quan hệ bạn bè
        $user = User::where('email', 'user@foodmap.vn')->first();
        $member = User::where('email', 'member@foodmap.vn')->first();
        $nam = User::where('email', 'namle@foodmap.vn')->first();
        $lan = User::where('email', 'lanhoang@foodmap.vn')->first();
        $tuan = User::where('email', 'tuanphan@foodmap.vn')->first();

        if ($user && $member && $nam && $lan && $tuan) {
            // 2. Tạo bạn bè đã đồng ý (Accepted)
            // user và member là bạn bè
            Friendship::updateOrCreate(
                ['user_id' => $user->id, 'friend_id' => $member->id],
                ['status' => 'accepted']
            );

            // user và nam là bạn bè
            Friendship::updateOrCreate(
                ['user_id' => $user->id, 'friend_id' => $nam->id],
                ['status' => 'accepted']
            );

            // 3. Tạo yêu cầu kết bạn đang chờ (Pending)
            // lan gửi yêu cầu cho user (Received request)
            Friendship::updateOrCreate(
                ['user_id' => $lan->id, 'friend_id' => $user->id],
                ['status' => 'pending']
            );

            // user gửi yêu cầu cho tuan (Sent request)
            Friendship::updateOrCreate(
                ['user_id' => $user->id, 'friend_id' => $tuan->id],
                ['status' => 'pending']
            );

            // 4. Gửi một số tin nhắn mẫu giữa user và member
            Message::create([
                'sender_id' => $member->id,
                'receiver_id' => $user->id,
                'message' => 'Chào bạn! Mình cũng ở Đông Anh nè. Cuối tuần này bạn có đi food tour Cổ Loa không?',
                'is_read' => true,
                'created_at' => now()->subHours(2),
            ]);

            Message::create([
                'sender_id' => $user->id,
                'receiver_id' => $member->id,
                'message' => 'Chào bạn! Có nha, mình định đi thử bún mạch tràng đây.',
                'is_read' => true,
                'created_at' => now()->subHours(1),
            ]);

            Message::create([
                'sender_id' => $member->id,
                'receiver_id' => $user->id,
                'message' => 'Hay quá! Đi chung cho vui nhé, để mình rủ thêm mấy bạn nữa.',
                'is_read' => false,
                'created_at' => now()->subMinutes(15),
            ]);
        }
    }
}
