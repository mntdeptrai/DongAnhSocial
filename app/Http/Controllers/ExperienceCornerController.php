<?php

namespace App\Http\Controllers;

use App\Contracts\ExperienceCornerServiceInterface;
use App\Http\Requests\StoreExperienceFeedbackRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExperienceCornerController extends Controller
{
    public function __construct(
        protected ExperienceCornerServiceInterface $experienceCornerService
    ) {}

    /**
     * Display a listing of cultural craft experiences, folk activities & monument services.
     * GET /goc-trai-nghiem
     */
    public function index(Request $request): View
    {
        $tours = $this->experienceCornerService->getCookingTours();
        $activities = $this->experienceCornerService->getCulturalActivities();
        $feedbacks = $this->experienceCornerService->getCommunityFeedbacks();

        return view('exp-corner.index', compact('tours', 'activities', 'feedbacks'));
    }

    /**
     * Store feedback / experience diary from visitor directly into MySQL database.
     * POST /goc-trai-nghiem/feedback
     */
    public function storeFeedback(StoreExperienceFeedbackRequest $request): JsonResponse
    {
        $feedback = $this->experienceCornerService->storeFeedback(
            $request->validated(),
            Auth::user()
        );

        $feedbackData = [
            'id'            => 'fb_' . $feedback->id,
            'author_name'   => $feedback->author_name,
            'activity_name' => $feedback->activity_name,
            'rating'        => (int) $feedback->rating,
            'verified'      => (bool) $feedback->is_verified,
            'helpful_count' => (int) $feedback->helpful_count,
            'comment'       => $feedback->comment,
            'image_url'     => $feedback->image_url,
            'created_at'    => 'Vừa đăng xong',
        ];

        return response()->json([
            'success'  => true,
            'message'  => 'Nhật ký trải nghiệm của bạn đã được lưu vào cơ sở dữ liệu và hiển thị lên bảng tin cộng đồng!',
            'feedback' => $feedbackData,
        ]);
    }
}
