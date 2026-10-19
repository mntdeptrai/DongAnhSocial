<?php

namespace App\Http\Controllers;

use App\Contracts\ModerationServiceInterface;
use App\Http\Requests\CreateModerationReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModerationController extends Controller
{
    public function __construct(
        protected ModerationServiceInterface $moderationService
    ) {}

    public function report(CreateModerationReportRequest $request): JsonResponse
    {
        $currentUser = Auth::user() ?? (session('user_id') ? \App\Models\User::find(session('user_id')) : null);

        $report = $this->moderationService::createReport([
            'target_id'      => $request->input('target_id'),
            'target_type'    => $request->input('target_type', 'post'),
            'target_title'   => $request->input('target_title', 'Nội dung người dùng'),
            'target_summary' => $request->input('target_summary', ''),
            'author_id'      => $request->filled('author_id') ? (int) $request->input('author_id') : null,
            'author_name'    => $request->input('author_name', 'Người dùng'),
            'reporter_id'    => $currentUser?->id,
            'reporter_name'  => $currentUser?->name ?? 'Thành viên ẩn danh',
            'reason'         => $request->input('reason'),
            'details'        => $request->input('details', ''),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn bạn đã gửi báo cáo! Nội dung này đã được ẩn khỏi bảng tin của bạn và gửi đến Ban Kiểm Duyệt xử lý trong vòng 24 giờ.',
            'report'  => $report,
        ]);
    }

    public function checkBan(Request $request): JsonResponse
    {
        $currentUser = Auth::user() ?? (session('user_id') ? \App\Models\User::find(session('user_id')) : null);
        if (!$currentUser) {
            return response()->json(['is_banned' => false]);
        }

        $isBanned = $this->moderationService::isUserBanned($currentUser->id);
        $remainingText = $isBanned ? $this->moderationService::getBanRemainingText($currentUser->id) : null;

        return response()->json([
            'is_banned'      => $isBanned,
            'remaining_text' => $remainingText,
        ]);
    }
}
