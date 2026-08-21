<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class SpamProtectionService
{
    /**
     * Danh sách từ khóa cấm/nhạy cảm spam (cờ bạc, cá độ, lừa đảo, khiêu dâm, vay nặng lãi, bot telegram/zalo)
     */
    protected static array $spamKeywords = [
        'kubet', 'thabet', '88bet', 'shbet', 'jun88', 'hi88', 'fb88', 'w88', 'fun88', 'm88',
        'nohu', 'nổ hũ', 'baccarat', 'kèo nhà cái', 'keonhacai', 'tài xỉu', 'tai xiu', 'soi cầu',
        'lô đề', 'lo de', 'xổ số online', 'xo so online', 'đánh bạc', 'casino online',
        'gái gọi', 'gai goi', 'sex show', 'phim sex', 'jav', 'khiêu dâm', 'cave',
        'vay tiền nhanh', 'vay tien nhanh', 'bốc bát họ', 'boc bat ho', 'vay nóng', 'vay nong',
        'tăng like', 'tang like', 'tăng follow', 'hack nick', 'chạy quảng cáo chiết khấu',
        't.me/', 'telegram.me/', 'chat.whatsapp.com/', 'zalo.me/g/',
        'hfjnuiyz', 'sleep(', 'redirtest', '1be7d4csvy0', '!(o&&!*'
    ];

    /**
     * Kiểm tra toàn diện xem request có phải spam/bot không
     *
     * @param Request $request
     * @param string $content
     * @param string $type
     * @return array{is_spam: bool, reason: string|null, code: int}
     */
    public static function check(Request $request, string $content, string $type = 'comment'): array
    {
        $ip = $request->ip() ?? '127.0.0.1';
        $userId = auth()->id() ?? session('user_id');
        $identifier = $userId ? "user_{$userId}" : "ip_" . md5($ip);

        // 1. Kiểm tra Honeypot (Bẫy bot tự động điền form)
        if (self::checkHoneypot($request)) {
            return [
                'is_spam' => true,
                'reason' => 'Phát hiện hành vi tự động không hợp lệ.',
                'code' => 403
            ];
        }

        // 2. Kiểm tra độ dài và định dạng nội dung
        $trimmed = trim($content);
        if (mb_strlen($trimmed, 'UTF-8') < 2) {
            return [
                'is_spam' => true,
                'reason' => 'Nội dung bình luận quá ngắn (tối thiểu 2 ký tự).',
                'code' => 422
            ];
        }

        if (mb_strlen($trimmed, 'UTF-8') > 1000) {
            return [
                'is_spam' => true,
                'reason' => 'Nội dung bình luận quá dài (tối đa 1000 ký tự).',
                'code' => 422
            ];
        }

        // 3. Kiểm tra spam chuỗi lặp lại vô nghĩa (vd: aaaaaaaa, 1111111, hehehehehe 20 lần)
        if (self::hasRepetitiveSpam($trimmed)) {
            return [
                'is_spam' => true,
                'reason' => 'Bình luận chứa chuỗi ký tự lặp lại bất thường.',
                'code' => 422
            ];
        }

        // 4. Kiểm tra từ khóa rác & link spam (chứa từ cấm hoặc quá nhiều URL)
        $spamWord = self::findSpamKeyword($trimmed);
        if ($spamWord) {
            return [
                'is_spam' => true,
                'reason' => 'Nội dung bình luận chứa từ khóa bị hạn chế hoặc không phù hợp.',
                'code' => 422
            ];
        }

        if (self::hasExcessiveLinks($trimmed)) {
            return [
                'is_spam' => true,
                'reason' => 'Bình luận không được chứa quá nhiều liên kết (tối đa 1 liên kết).',
                'code' => 422
            ];
        }

        // 5. Kiểm tra tần suất đăng (Cooldown nhanh: tối thiểu 4 giây giữa 2 lần bình luận liên tiếp)
        $cooldownKey = "spam_cooldown_{$type}_{$identifier}";
        if (Cache::has($cooldownKey)) {
            return [
                'is_spam' => true,
                'reason' => 'Bạn đang gửi quá nhanh! Vui lòng chờ 4 giây trước khi gửi tiếp.',
                'code' => 429
            ];
        }

        // 6. Kiểm tra giới hạn số lượng (Max 6 bình luận / phút, 30 bình luận / giờ)
        $rateKeyMinute = "spam_rate_min_{$type}_{$identifier}";
        $minuteAttempts = (int) Cache::get($rateKeyMinute, 0);
        if ($minuteAttempts >= 6) {
            return [
                'is_spam' => true,
                'reason' => 'Bạn đã gửi quá nhiều bình luận trong thời gian ngắn. Vui lòng thử lại sau 1 phút.',
                'code' => 429
            ];
        }

        // 7. Kiểm tra trùng lặp nội dung liên tiếp trong 5 phút
        $contentHash = md5(mb_strtolower(preg_replace('/\s+/', '', $trimmed), 'UTF-8'));
        $duplicateKey = "spam_dup_{$identifier}_{$contentHash}";
        if (Cache::has($duplicateKey)) {
            return [
                'is_spam' => true,
                'reason' => 'Nội dung bình luận trùng lặp với bình luận vừa gửi gần đây.',
                'code' => 422
            ];
        }

        return [
            'is_spam' => false,
            'reason' => null,
            'code' => 200
        ];
    }

    /**
     * Ghi nhận bình luận hợp lệ để kích hoạt cooldown và chống trùng lặp
     */
    public static function recordSuccess(Request $request, string $content, string $type = 'comment'): void
    {
        $ip = $request->ip() ?? '127.0.0.1';
        $userId = auth()->id() ?? session('user_id');
        $identifier = $userId ? "user_{$userId}" : "ip_" . md5($ip);

        // Đặt Cooldown 4 giây
        $cooldownKey = "spam_cooldown_{$type}_{$identifier}";
        Cache::put($cooldownKey, 1, now()->addSeconds(4));

        // Tăng đếm trong 1 phút
        $rateKeyMinute = "spam_rate_min_{$type}_{$identifier}";
        $currentMinuteCount = (int) Cache::get($rateKeyMinute, 0);
        Cache::put($rateKeyMinute, $currentMinuteCount + 1, now()->addMinutes(1));

        // Lưu hash nội dung trong 5 phút để chống trùng lặp
        $trimmed = trim($content);
        $contentHash = md5(mb_strtolower(preg_replace('/\s+/', '', $trimmed), 'UTF-8'));
        $duplicateKey = "spam_dup_{$identifier}_{$contentHash}";
        Cache::put($duplicateKey, 1, now()->addMinutes(5));
    }

    /**
     * Kiểm tra Honeypot fields (Các input ẩn mà người dùng thật không bao giờ nhập)
     */
    public static function checkHoneypot(Request $request): bool
    {
        $honeypotFields = [
            '_hp_author_url',
            '_hp_website',
            'website_url_hp',
            'hp_comment_check',
            'author_website'
        ];

        foreach ($honeypotFields as $field) {
            if ($request->filled($field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Phát hiện chuỗi lặp lại bất thường (vd: 'aaaaaa', '.....', 'hahahahahaha' lặp quá nhiều)
     */
    public static function hasRepetitiveSpam(string $text): bool
    {
        // 1. Ký tự đơn lặp lại 10 lần trở lên liên tiếp (vd: aaaaaaaaaa, 1111111111)
        if (preg_match('/(.)\1{9,}/u', $text)) {
            return true;
        }

        // 2. Cụm từ 2-6 ký tự lặp lại 6 lần trở lên (vd: abcabcabcabcabcabc)
        if (preg_match('/(.{2,6})\1{5,}/u', $text)) {
            return true;
        }

        return false;
    }

    /**
     * Tìm từ khóa spam trong nội dung
     */
    public static function findSpamKeyword(string $text): ?string
    {
        $lowerText = mb_strtolower($text, 'UTF-8');
        foreach (self::$spamKeywords as $kw) {
            if (str_contains($lowerText, $kw)) {
                return $kw;
            }
        }
        return null;
    }

    /**
     * Kiểm tra số lượng đường dẫn (Link) trong bình luận (tối đa 1 URL)
     */
    public static function hasExcessiveLinks(string $text): bool
    {
        $urlCount = preg_match_all('/https?:\/\/|www\./i', $text);
        return $urlCount > 1;
    }
}
