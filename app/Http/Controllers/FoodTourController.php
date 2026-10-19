<?php

namespace App\Http\Controllers;

use App\Contracts\FoodTourServiceInterface;
use App\Http\Requests\SaveCustomFoodTourRequest;
use App\Services\FoodTourDiaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FoodTourController extends Controller
{
    public function __construct(
        protected FoodTourServiceInterface $foodTourService
    ) {}

    /**
     * Display a listing of the pre-designed food tours.
     * GET /food-tours
     */
    public function index(Request $request): View
    {
        $mood = $request->query('mood');
        $data = $this->foodTourService->getFoodTours($mood);

        return view('food-tours.index', [
            'tours'          => $data['tours'],
            'mood'           => $mood,
            'communityTours' => $data['community_tours'],
        ]);
    }

    /**
     * Display the specified food tour details.
     * GET /food-tours/{slug}
     */
    public function show(Request $request, string $slug): View
    {
        $data = $this->foodTourService->getFoodTourBySlug($slug);

        return view('food-tours.show', [
            'tour'    => $data['tour'],
            'diaries' => $data['diaries'],
        ]);
    }

    /**
     * Advanced Simulated AI Tour Generator with Gemini.
     * POST /food-tours/generate-ai
     */
    public function generateAI(Request $request): JsonResponse
    {
        $budgetLimit = (int) $request->input('budget', 300000);
        $mood = $request->input('mood', 'chill');

        $result = $this->foodTourService->generateAITourWithGemini($budgetLimit, $mood, Auth::user());

        return response()->json($result);
    }

    /**
     * Store completed food tour diary.
     * POST /food-tours/{id}/diaries
     */
    public function storeDiary(Request $request, int|string $id, FoodTourDiaryService $diaryService): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để lưu trữ nhật ký hành trình và chia sẻ ảnh kỷ niệm!',
            ], 401);
        }

        $request->validate([
            'rating'          => 'nullable|integer|min:1|max:5',
            'comment'         => 'nullable|string',
            'image'           => 'nullable|string',
            'completed_stops' => 'nullable|array',
            'stop_reviews'    => 'nullable|array',
        ]);

        $dto = \App\Domain\FoodTour\FoodTourDiaryData::fromRequest($request, (int)$id);
        $diary = $diaryService->createDiary($dto, $request->boolean('share_to_community'));

        return response()->json([
            'success'   => true,
            'message'   => 'Lưu nhật ký hành trình thành công!',
            'image_url' => $diary->image_path,
        ]);
    }

    /**
     * Show form to manually create a custom food tour.
     * GET /food-tours/create
     */
    public function create(): View
    {
        $eateries = $this->foodTourService->getEateriesDropdown();
        return view('food-tours.form', compact('eateries'));
    }

    /**
     * Store the manually created custom food tour.
     * POST /food-tours
     */
    public function store(SaveCustomFoodTourRequest $request): RedirectResponse
    {
        $tour = $this->foodTourService->createCustomTour(
            $request->validated(),
            Auth::user(),
            $request->file('thumbnail_file')
        );

        return redirect()->route('food-tours.show', $tour->slug)->with('success', 'Tạo lộ trình thành công!');
    }

    /**
     * Show form to edit a custom food tour.
     * GET /food-tours/{slug}/edit
     */
    public function edit(string $slug): View
    {
        $tour = $this->foodTourService->getTourForManage($slug, Auth::user());
        $eateries = $this->foodTourService->getEateriesDropdown();

        return view('food-tours.form', compact('tour', 'eateries'));
    }

    /**
     * Update the custom food tour.
     * PUT /food-tours/{slug}
     */
    public function update(SaveCustomFoodTourRequest $request, string $slug): RedirectResponse
    {
        $tour = $this->foodTourService->getTourForManage($slug, Auth::user());
        $this->foodTourService->updateCustomTour($tour, $request->validated(), $request->file('thumbnail_file'));

        return redirect()->route('food-tours.show', $tour->slug)->with('success', 'Cập nhật lộ trình thành công!');
    }

    /**
     * Delete the custom food tour.
     * DELETE /food-tours/{slug}
     */
    public function destroy(string $slug): RedirectResponse
    {
        $tour = $this->foodTourService->getTourForManage($slug, Auth::user());
        $this->foodTourService->deleteCustomTour($tour);

        return redirect()->route('food-tours.index')->with('success', 'Xóa lộ trình thành công!');
    }

    /**
     * Toggle public sharing of custom food tour.
     * POST /food-tours/{slug}/share
     */
    public function share(string $slug): RedirectResponse
    {
        $tour = $this->foodTourService->getTourForManage($slug, Auth::user());
        $msg = $this->foodTourService->toggleShare($tour);

        return back()->with('success', $msg);
    }
}
