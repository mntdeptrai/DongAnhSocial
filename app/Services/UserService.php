<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function apiLogin(string $email, string $password): array
    {
        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            $user = Auth::user();
            if ($user->status === 'disabled') {
                Auth::logout();
                return ['success' => false, 'message' => 'Tài khoản đã bị vô hiệu hóa.'];
            }
            session([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
            ]);
            return ['success' => true, 'user' => $user];
        }
        return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng.'];
    }

    public function apiRegister(array $data): array
    {
        $role = $data['role'] ?? 'user';
        if ($role === 'admin') {
            $role = 'user';
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $role,
            'phone' => $data['phone'] ?? '',
            'status' => 'active',
            'avatar' => '🧑',
        ]);

        Auth::login($user);
        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
        ]);

        return ['success' => true, 'user' => $user];
    }

    public function apiLogout(): bool
    {
        Auth::logout();
        session()->forget(['user_id', 'user_name', 'user_role']);
        return true;
    }

    public function getUsers()
    {
        return User::select('id', 'name', 'username', 'phone', 'email', 'avatar', 'role', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function storeUser(array $data): ?User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'avatar' => $data['avatar'] ?? '🧑',
            'phone' => $data['phone'] ?? '',
            'status' => 'active',
        ]);
    }

    public function updateUser($id, array $data): ?User
    {
        $user = User::findOrFail($id);
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'avatar' => $data['avatar'] ?? '🧑',
            'phone' => $data['phone'] ?? '',
            'status' => $data['status'] ?? 'active',
        ];
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }
        $user->update($updateData);
        return $user;
    }

    public function deleteUser($id): bool
    {
        $user = User::findOrFail($id);
        if ($user->id === session('user_id')) {
            return false;
        }
        return (bool) $user->delete();
    }

    public function toggleUserStatus($id): array
    {
        $user = User::findOrFail($id);
        if ($user->id === session('user_id')) {
            return ['success' => false, 'message' => 'Không tự ban chính mình.'];
        }
        $user->status = $user->status === 'active' ? 'disabled' : 'active';
        $user->save();
        return ['success' => true, 'status' => $user->status];
    }
}
