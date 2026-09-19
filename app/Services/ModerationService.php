<?php

namespace App\Services;

use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ModerationService
{
    private static function getStorageFilePath(): string
    {
        return 'moderation_reports.json';
    }

    private static function readReportsFromFile(): array
    {
        try {
            if (Storage::disk('local')->exists(self::getStorageFilePath())) {
                $content = Storage::disk('local')->get(self::getStorageFilePath());
                $data = json_decode($content, true);
                if (is_array($data)) {
                    return $data;
                }
            }
        } catch (\Throwable $e) {}

        return self::getDefaultInitialReports();
    }

    private static function saveReportsToFile(array $reports): void
    {
        try {
            Storage::disk('local')->put(self::getStorageFilePath(), json_encode($reports, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {}
    }

    private static function getDefaultInitialReports(): array
    {
        return [
            [
                'id' => 'rep_1001',
                'target_id' => 'post_demo_1',
                'target_type' => 'post',
                'target_title' => 'Khuyến mãi link cá cược tặng tiền tân thủ',
                'target_summary' => 'Truy cập ngay link https://k***bet88.com nhận ngay 200k trải nghiệm game quay hũ trực tuyến...',
                'author_id' => 999,
                'author_name' => 'Nguyễn Văn Đạt (Spam)',
                'reporter_id' => 1,
                'reporter_name' => 'Trần Hoàng Long',
                'reason' => 'Spam, tin rác, lừa đảo, cờ bạc trực tuyến',
                'details' => 'Tài khoản đăng bài quảng cáo cờ bạc trái phép vào nhóm ẩm thực Đông Anh.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(3)->toIso8601String(),
                'sla_deadline' => Carbon::now()->addHours(21)->toIso8601String(),
                'resolution_action' => null,
                'resolved_by' => null,
                'resolved_at' => null,
                'ban_duration' => null,
            ],
            [
                'id' => 'rep_1002',
                'target_id' => 'post_demo_2',
                'target_type' => 'post',
                'target_title' => 'Bài viết chứa ngôn từ xúc phạm danh dự',
                'target_summary' => 'Hình ảnh quay lén cùng các phát ngôn vu khống người bán quán bún riêu...',
                'author_id' => 998,
                'author_name' => 'Hoàng Minh Tuấn',
                'reporter_id' => 2,
                'reporter_name' => 'Lê Thị Mai',
                'reason' => 'Quấy rối, đe dọa, xúc phạm nhân phẩm',
                'details' => 'Có hành vi thóa mạ và thông tin chưa kiểm chứng gây hoang mang.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(6)->toIso8601String(),
                'sla_deadline' => Carbon::now()->addHours(18)->toIso8601String(),
                'resolution_action' => null,
                'resolved_by' => null,
                'resolved_at' => null,
                'ban_duration' => null,
            ]
        ];
    }

    public static function createReport(array $data): array
    {
        $reports = self::readReportsFromFile();
        $ticketId = 'rep_' . time() . '_' . rand(100, 999);

        $now = Carbon::now();
        $newReport = [
            'id' => $ticketId,
            'target_id' => (string)($data['target_id'] ?? ''),
            'target_type' => (string)($data['target_type'] ?? 'post'),
            'target_title' => (string)($data['target_title'] ?? 'Bài viết trên Bảng tin'),
            'target_summary' => (string)($data['target_summary'] ?? ''),
            'author_id' => isset($data['author_id']) ? (int)$data['author_id'] : null,
            'author_name' => (string)($data['author_name'] ?? 'Người dùng'),
            'reporter_id' => isset($data['reporter_id']) ? (int)$data['reporter_id'] : null,
            'reporter_name' => (string)($data['reporter_name'] ?? 'Thành viên ẩn danh'),
            'reason' => (string)($data['reason'] ?? 'Nội dung vi phạm tiêu chuẩn'),
            'details' => (string)($data['details'] ?? ''),
            'status' => 'pending',
            'created_at' => $now->toIso8601String(),
            'sla_deadline' => $now->copy()->addHours(24)->toIso8601String(),
            'resolution_action' => null,
            'resolved_by' => null,
            'resolved_at' => null,
            'ban_duration' => null,
        ];

        array_unshift($reports, $newReport);
        self::saveReportsToFile($reports);

        try {
            if (DB::getSchemaBuilder()->hasTable('reports')) {
                DB::table('reports')->insert([
                    'target_id' => $newReport['target_id'],
                    'target_type' => $newReport['target_type'],
                    'author_id' => $newReport['author_id'],
                    'reporter_id' => $newReport['reporter_id'],
                    'reason' => $newReport['reason'],
                    'details' => $newReport['details'],
                    'status' => 'pending',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        } catch (\Throwable $e) {}

        return $newReport;
    }

    public static function getReports(string $status = 'all'): array
    {
        $reports = self::readReportsFromFile();

        if ($status !== 'all') {
            $reports = array_values(array_filter($reports, fn($r) => ($r['status'] ?? '') === $status));
        }

        foreach ($reports as &$r) {
            $createdAt = Carbon::parse($r['created_at']);
            $deadline = Carbon::parse($r['sla_deadline'] ?? $createdAt->copy()->addHours(24));
            $now = Carbon::now();

            if ($r['status'] === 'pending') {
                if ($now->greaterThan($deadline)) {
                    $r['sla_text'] = 'Quá hạn SLA 24h';
                    $r['sla_status'] = 'expired';
                } else {
                    $diffHours = $now->diffInHours($deadline);
                    $diffMinutes = $now->diffInMinutes($deadline) % 60;
                    $r['sla_text'] = "Còn {$diffHours}h {$diffMinutes}m";
                    $r['sla_status'] = $diffHours < 4 ? 'urgent' : 'safe';
                }
            } else {
                $r['sla_text'] = 'Đã hoàn tất';
                $r['sla_status'] = 'resolved';
            }
        }

        return $reports;
    }

    public static function getReportStats(): array
    {
        $reports = self::readReportsFromFile();

        $total = count($reports);
        $pending = 0;
        $resolvedRemoved = 0;
        $resolvedBanned = 0;
        $dismissed = 0;

        foreach ($reports as $r) {
            $st = $r['status'] ?? 'pending';
            if ($st === 'pending') $pending++;
            elseif ($st === 'resolved_removed') $resolvedRemoved++;
            elseif ($st === 'resolved_banned') $resolvedBanned++;
            elseif ($st === 'dismissed') $dismissed++;
        }

        return [
            'total' => $total,
            'pending' => $pending,
            'resolved_removed' => $resolvedRemoved,
            'resolved_banned' => $resolvedBanned,
            'dismissed' => $dismissed,
        ];
    }

    public static function resolveTicket(string $ticketId, string $action, ?int $adminId, ?string $banDuration = null, ?string $note = null): bool
    {
        $reports = self::readReportsFromFile();
        $found = false;
        $now = Carbon::now();

        foreach ($reports as &$r) {
            if ($r['id'] === $ticketId) {
                $found = true;
                $r['resolved_by'] = $adminId;
                $r['resolved_at'] = $now->toIso8601String();
                $r['resolution_note'] = $note;

                if ($action === 'remove') {
                    $r['status'] = 'resolved_removed';
                    $r['resolution_action'] = 'Đã gỡ bài viết vi phạm';
                    self::removeTargetContent($r['target_id'], $r['target_type']);
                } elseif ($action === 'ban') {
                    $r['status'] = 'resolved_banned';
                    $r['resolution_action'] = "Đã khóa tài khoản tác giả ({$banDuration})";
                    $r['ban_duration'] = $banDuration;
                    if (!empty($r['author_id'])) {
                        self::banUser((int)$r['author_id'], $banDuration, $r['reason']);
                    }
                    self::removeTargetContent($r['target_id'], $r['target_type']);
                } elseif ($action === 'dismiss') {
                    $r['status'] = 'dismissed';
                    $r['resolution_action'] = 'Bác bỏ báo cáo (Nội dung an toàn)';
                }
                break;
            }
        }

        if ($found) {
            self::saveReportsToFile($reports);
        }

        return $found;
    }

    public static function removeTargetContent(string $targetId, string $targetType): void
    {
        $cleanId = preg_replace('/[^0-9]/', '', $targetId);
        if (empty($cleanId)) return;

        $dbConnections = ['mysql', 'mysql_education', 'mysql_market', 'mysql_stay', 'mysql_wellness'];

        if ($targetType === 'post') {
            foreach ($dbConnections as $conn) {
                try {
                    DB::connection($conn)->table('posts')->where('id', $cleanId)->delete();
                } catch (\Throwable $e) {}
                try {
                    DB::connection($conn)->table('education_programs')->where('id', $cleanId)->delete();
                } catch (\Throwable $e) {}
            }
        } elseif ($targetType === 'comment') {
            foreach ($dbConnections as $conn) {
                try {
                    DB::connection($conn)->table('comments')->where('id', $cleanId)->delete();
                } catch (\Throwable $e) {}
            }
        }
    }

    public static function banUser(int $userId, ?string $duration = '24h', ?string $reason = null): void
    {
        $bannedUntil = null;
        $now = Carbon::now();

        switch ($duration) {
            case '24h':
                $bannedUntil = $now->copy()->addHours(24);
                break;
            case '3d':
                $bannedUntil = $now->copy()->addDays(3);
                break;
            case '7d':
                $bannedUntil = $now->copy()->addDays(7);
                break;
            case '30d':
                $bannedUntil = $now->copy()->addDays(30);
                break;
            case 'permanent':
            default:
                $bannedUntil = $now->copy()->addYears(100);
                break;
        }

        $bans = self::getStoredBans();
        $bans[(string)$userId] = [
            'user_id' => $userId,
            'banned_at' => $now->toIso8601String(),
            'banned_until' => $bannedUntil->toIso8601String(),
            'duration' => $duration,
            'reason' => $reason ?: 'Vi phạm tiêu chuẩn cộng đồng',
        ];
        self::saveStoredBans($bans);

        try {
            $user = User::find($userId);
            if ($user) {
                $user->status = 'disabled';
                $user->save();
            }
        } catch (\Throwable $e) {}
    }

    public static function unbanUser(int $userId): void
    {
        $bans = self::getStoredBans();
        if (isset($bans[(string)$userId])) {
            unset($bans[(string)$userId]);
            self::saveStoredBans($bans);
        }

        try {
            $user = User::find($userId);
            if ($user) {
                $user->status = 'active';
                $user->save();
            }
        } catch (\Throwable $e) {}
    }

    public static function isUserBanned(int $userId): bool
    {
        $bans = self::getStoredBans();
        $key = (string)$userId;

        if (isset($bans[$key])) {
            $banInfo = $bans[$key];
            $bannedUntil = Carbon::parse($banInfo['banned_until']);
            if (Carbon::now()->lessThan($bannedUntil)) {
                return true;
            } else {
                unset($bans[$key]);
                self::saveStoredBans($bans);
            }
        }

        try {
            $user = User::find($userId);
            if ($user && $user->status === 'disabled') {
                return true;
            }
        } catch (\Throwable $e) {}

        return false;
    }

    public static function getBanRemainingText(int $userId): ?string
    {
        $bans = self::getStoredBans();
        $key = (string)$userId;

        if (isset($bans[$key])) {
            $banInfo = $bans[$key];
            $bannedUntil = Carbon::parse($banInfo['banned_until']);
            $now = Carbon::now();

            if ($now->greaterThanOrEqualTo($bannedUntil)) {
                return null;
            }

            $diffDays = $now->diffInDays($bannedUntil);
            if ($diffDays > 365) {
                return 'Khóa vĩnh viễn';
            } elseif ($diffDays >= 1) {
                $remHours = $now->copy()->addDays($diffDays)->diffInHours($bannedUntil);
                return "Còn {$diffDays} ngày {$remHours} giờ";
            } else {
                $remHours = $now->diffInHours($bannedUntil);
                $remMinutes = $now->diffInMinutes($bannedUntil) % 60;
                return "Còn {$remHours} giờ {$remMinutes} phút";
            }
        }

        return null;
    }

    private static function getStoredBans(): array
    {
        try {
            if (Storage::disk('local')->exists('moderation_bans.json')) {
                $content = Storage::disk('local')->get('moderation_bans.json');
                $data = json_decode($content, true);
                if (is_array($data)) return $data;
            }
        } catch (\Throwable $e) {}
        return [];
    }

    private static function saveStoredBans(array $bans): void
    {
        try {
            Storage::disk('local')->put('moderation_bans.json', json_encode($bans, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {}
    }

    public static function deleteUserAccount(int $userId): bool
    {
        $user = User::find($userId);
        if (!$user) return false;

        $dbConnections = ['mysql', 'mysql_education', 'mysql_market', 'mysql_stay', 'mysql_wellness'];

        foreach ($dbConnections as $conn) {
            try {
                DB::connection($conn)->table('posts')->where('user_id', $userId)->delete();
            } catch (\Throwable $e) {}
            try {
                DB::connection($conn)->table('comments')->where('user_id', $userId)->delete();
            } catch (\Throwable $e) {}
            try {
                DB::connection($conn)->table('checkins')->where('user_id', $userId)->delete();
            } catch (\Throwable $e) {}
            try {
                DB::connection($conn)->table('checkin_reactions')->where('user_id', $userId)->delete();
            } catch (\Throwable $e) {}
            try {
                DB::connection($conn)->table('reviews')->where('user_id', $userId)->delete();
            } catch (\Throwable $e) {}
        }

        try {
            DB::table('personal_access_tokens')->where('tokenable_id', $userId)->delete();
        } catch (\Throwable $e) {}

        try {
            $user->delete();
        } catch (\Throwable $e) {
            $user->status = 'deleted';
            $user->name = 'Người dùng đã đóng tài khoản';
            $user->email = 'deleted_' . $userId . '_' . time() . '@donganh.local';
            $user->phone = null;
            $user->save();
        }

        return true;
    }
}
