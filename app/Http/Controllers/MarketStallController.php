<?php

namespace App\Http\Controllers;

use App\Contracts\MarketStallServiceInterface;
use App\Http\Requests\StoreStallReviewRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MarketStallController extends Controller
{
    public function __construct(
        protected MarketStallServiceInterface $marketStallService
    ) {}

    /**
     * Display the full stall detail page.
     */
    public function show(string $marketSlug, string $stallSlug): View
    {
        $viewData = $this->marketStallService->getStallDetailData($marketSlug, $stallSlug, Auth::user());
        return view('stall-detail', $viewData);
    }

    /**
     * Store a review submitted from the stall detail page.
     */
    public function storeReview(StoreStallReviewRequest $request, string $marketSlug, string $stallSlug): RedirectResponse
    {
        $mediaFiles = [];
        if ($request->hasFile('media')) {
            $mediaFiles = is_array($request->file('media')) ? $request->file('media') : [$request->file('media')];
        }

        $this->marketStallService->storeReviewFromSlug(
            $marketSlug,
            $stallSlug,
            $request->validated(),
            Auth::user(),
            $mediaFiles
        );

        return back()->with('review_success', 'Cảm ơn bạn đã đánh giá gian hàng!');
    }
}
