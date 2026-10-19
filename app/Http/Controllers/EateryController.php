<?php

namespace App\Http\Controllers;

use App\Contracts\EateryServiceInterface;
use App\Http\Requests\StoreEateryReviewRequest;
use App\Services\SpamProtectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EateryController extends Controller
{
    public function __construct(
        protected EateryServiceInterface $eateryService
    ) {}

    /**
     * Display detailed landing view for an eatery / school / hospital / heritage site.
     */
    public function show(string $slug): View
    {
        $currentUserId = Auth::id() ?? session('user_id');
        $currentSessionId = session()->getId();

        $detail = $this->eateryService->getEateryDetailViewData($slug, $currentUserId, $currentSessionId);
        if (!$detail) {
            abort(404, 'Không tìm thấy địa điểm này.');
        }

        return view($detail['view'], $detail['data']);
    }

    /**
     * Submit a user review with spam protection and optional media uploads.
     */
    public function storeReview(StoreEateryReviewRequest $request, int|string $id): RedirectResponse
    {
        $commentText = (string) ($request->input('comment') ?? '');
        if (!empty($commentText)) {
            $spamCheck = SpamProtectionService::check($request, $commentText, 'review');
            if ($spamCheck['is_spam']) {
                return redirect()->back()
                    ->withErrors(['comment' => $spamCheck['reason']])
                    ->with('error', $spamCheck['reason'])
                    ->withInput();
            }
        }

        $mediaFiles = [];
        if ($request->hasFile('media')) {
            $mediaFiles = is_array($request->file('media')) ? $request->file('media') : [$request->file('media')];
        }

        $this->eateryService->submitEateryReview((int)$id, $request->validated(), $mediaFiles);

        return redirect()->back()->with('success', 'Cảm ơn bạn đã gửi đánh giá! Nhận xét của bạn giúp ích cho cộng đồng du lịch Đông Anh.');
    }
}
