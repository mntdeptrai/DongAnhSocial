<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ModerationService;
use Illuminate\Support\Facades\Auth;

class ModerationController extends Controller
{
    public function report(Request $request)
    {
        $request->validate([
            'target_id' => 'required|string',
            'reason' => 'required|string|max:255',
        ]);

        $currentUser = Auth::user() ?? (session('user_id') ? \App\Models\User::find(session('user_id')) : null);

        $report = ModerationService::createReport([
            'target_id' => $request->target_id,
            'target_type' => $request->target_type ?? 'post',
            'target_title' => $request->target_title ?? 'Nội dung người dùng',
            'target_summary' => $request->target_summary ?? '',
            'author_id' => $request->author_id ? (int)$request->author_id : null,
            'author_name' => $request->author_name ?? 'Người dùng',
            'reporter_id' => $currentUser ? $currentUser->id : null,
            'reporter_name' => $currentUser ? $currentUser->name : 'Thành viên ẩn danh',
            'reason' => $request->reason,
            'details' => $request->details ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn bạn đã gửi báo cáo! Nội dung này đã được ẩn khỏi bảng tin của bạn và gửi đến Ban Kiểm Duyệt xử lý trong vòng 24 giờ.',
            'report' => $report,
        ]);
    }

    public function checkBan(Request $request)
    {
        $currentUser = Auth::user() ?? (session('user_id') ? \App\Models\User::find(session('user_id')) : null);
        if (!$currentUser) {
            return response()->json(['is_banned' => false]);
        }

        $isBanned = ModerationService::isUserBanned($currentUser->id);
        $remainingText = $isBanned ? ModerationService::getBanRemainingText($currentUser->id) : null;

        return response()->json([
            'is_banned' => $isBanned,
            'remaining_text' => $remainingText,
        ]);
    }
}
