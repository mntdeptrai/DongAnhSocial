<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * UserSeeder — Tạo dữ liệu mẫu bảng users
 *
 * Chạy: php artisan db:seed --class=UserSeeder
 * Lưu ý: Seeder này dùng firstOrCreate để không tạo trùng nếu user đã tồn tại.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Nguyễn Văn Admin',
                'email'    => 'admin@foodmap.vn',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
                'avatar'   => '👨‍💼',
                'phone'    => '0901234567',
                'status'   => 'active',
            ],
            [
                'name'     => 'Trần Thị Bích',
                'email'    => 'seller@foodmap.vn',
                'password' => Hash::make('seller123'),
                'role'     => 'seller',
                'avatar'   => '👨‍🍳',
                'phone'    => '0912345678',
                'status'   => 'active',
            ],
            [
                'name'     => 'Thực Thần Đông Anh',
                'email'    => 'user@foodmap.vn',
                'password' => Hash::make('user123'),
                'role'     => 'user',
                'avatar'   => '🧑',
                'phone'    => '0987654321',
                'status'   => 'active',
            ],
            [
                'name'     => 'Thành viên Đông Anh',
                'email'    => 'member@foodmap.vn',
                'password' => Hash::make('member123'),
                'role'     => 'user',
                'avatar'   => '👧',
                'phone'    => '0977665544',
                'status'   => 'active',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
