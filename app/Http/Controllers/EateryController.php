<?php

namespace App\Http\Controllers;

use App\Models\Eatery;
use App\Models\Review;
use Illuminate\Http\Request;

use App\Services\EateryApiService;
use App\Helpers\R2Helper;

class EateryController extends Controller
{
    public function show($slug)
    {
        $eatery = EateryApiService::getEateryBySlug($slug);
        if (!$eatery) {
            abort(404);
        }
        
        // Tự động phân tích danh mục để chọn Schema Type thích hợp của Google
        $schemaType = 'LocalBusiness';
        $categorySlug = $eatery->category->slug;
        if ($categorySlug === 'dong-anh-food-map') {
            $schemaType = 'Restaurant';
        } elseif ($categorySlug === 'stay-in-dong-anh') {
            $schemaType = 'Hotel';
        } elseif ($categorySlug === 'wellness-care') {
            $schemaType = 'HealthAndBeautyBusiness';
        } elseif ($categorySlug === 'dong-anh-market' || $categorySlug === 'traditional-market' || $categorySlug === 'co-so-kinh-doanh') {
            $schemaType = 'Store';
        } elseif ($categorySlug === 'smart-education-map') {
            $schemaType = 'School';
        }
        
        $currentUrl = request()->url();
        
        // Khởi tạo mảng dữ liệu có cấu trúc Schema.org chuẩn Google
        $schemaData = [
            '@context' => 'https://schema.org',
            '@type' => $schemaType,
            'name' => $eatery->name,
            'image' => $eatery->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80',
            'telephone' => $eatery->phone ?: 'Chưa cập nhật',
            'priceRange' => $eatery->price_range ?: 'Đang cập nhật',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $eatery->address,
                'addressLocality' => $eatery->commune->name,
                'addressRegion' => 'Đông Anh, Hà Nội',
                'addressCountry' => 'VN'
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $eatery->latitude,
                'longitude' => $eatery->longitude
            ],
            'url' => $currentUrl
        ];
        
        if ($schemaType === 'Restaurant' || $schemaType === 'CafeOrCoffeeShop') {
            $schemaData['servesCuisine'] = $eatery->category->name;
        }
        
        // Thêm đánh giá trung bình vào Schema nếu đã có bình luận
        if ($eatery->reviews->count() > 0) {
            $schemaData['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $eatery->average_rating,
                'reviewCount' => $eatery->reviews->count()
            ];
            
            $schemaData['review'] = [];
            foreach ($eatery->reviews->take(3) as $rev) {
                $schemaData['review'][] = [
                    '@type' => 'Review',
                    'author' => [
                        '@type' => 'Person',
                        'name' => $rev->user_name
                    ],
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' => $rev->rating
                    ],
                    'reviewBody' => $rev->comment
                ];
            }
        }
        
        $sameEateryIds = [$eatery->id];

        // Lấy danh sách ảnh check-in thực tế của thực khách tại địa điểm này
        $checkinPhotos = \App\Models\Checkin::with('user')
            ->whereIn('eatery_id', $sameEateryIds)
            ->whereNotNull('image_path')
            ->where('image_path', '!=', '')
            ->latest()
            ->take(15)
            ->get();

        $checkinReviews = \App\Models\Checkin::with('user')
            ->whereIn('eatery_id', $sameEateryIds)
            ->latest()
            ->get();

        // Lấy thông tin tài khoản cá nhân & bài viết mới nhất từ Hiệu trưởng/Nhà trường liên kết
        $principalUser = null;
        if ($eatery->user_id) {
            $principalUser = \App\Models\User::find($eatery->user_id);
        } else {
            $principalUser = \App\Models\User::where('eatery_id', $eatery->id)->first();
        }

        $principalPosts = \App\Models\EducationProgram::on('mysql_education')
            ->where('eatery_id', $eatery->id)
            ->orderBy('created_at', 'desc')
            ->get();
        if ($principalPosts->isEmpty()) {
            $principalPosts = \App\Models\EducationProgram::on('mysql')
                ->where('eatery_id', $eatery->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $currentUserId = \Illuminate\Support\Facades\Auth::id() ?? session('user_id');
        $currentSessionId = session()->getId();
        $postIds = $principalPosts->pluck('id')->toArray();

        if (!empty($postIds)) {
            $realLikesMap = \App\Models\CheckinReaction::where('reactionable_type', 'post')
                ->whereIn('reactionable_id', $postIds)
                ->selectRaw('reactionable_id, count(*) as total')
                ->groupBy('reactionable_id')
                ->pluck('total', 'reactionable_id')
                ->toArray();

            $uQuery = \App\Models\CheckinReaction::where('reactionable_type', 'post')
                ->whereIn('reactionable_id', $postIds);
            if ($currentUserId) {
                $uQuery->where('user_id', $currentUserId);
            } else if (!empty($currentSessionId)) {
                $uQuery->whereNull('user_id')->where('session_id', $currentSessionId);
            } else {
                $uQuery->whereRaw('1 = 0');
            }
            $userLikedMap = $uQuery->pluck('reactionable_id')->toArray();

            $realCommentsMap = \App\Models\Comment::where('commentable_type', 'post')
                ->whereIn('commentable_id', $postIds)
                ->selectRaw('commentable_id, count(*) as total')
                ->groupBy('commentable_id')
                ->pluck('total', 'commentable_id')
                ->toArray();

            foreach ($principalPosts as $p) {
                $p->real_likes_count = (int) ($realLikesMap[$p->id] ?? $p->likes_count ?? 0);
                $p->is_liked = in_array($p->id, $userLikedMap);
                $p->real_comments_count = (int) ($realCommentsMap[$p->id] ?? 0);
                $p->real_shares_count = (int) ($p->shares_count ?? 0);
            }
        }

        // Mã hóa Schema dữ liệu có cấu trúc sang định dạng thẻ script JSON-LD
        $jsonLd = '<script type="application/ld+json">' . PHP_EOL . 
                  json_encode($schemaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL . 
                  '</script>';
        
        $categorySlug = $eatery->category->slug;
        $viewData = compact('eatery', 'jsonLd', 'checkinPhotos', 'checkinReviews', 'principalPosts', 'principalUser');

        if ($categorySlug === 'traditional-market') {
            return view('detail-market', $viewData);
        }
        if ($categorySlug === 'dong-anh-market') {
            return view('detail-ocop', $viewData);
        }
        if ($categorySlug === 'co-so-kinh-doanh') {
            return view('detail-business', $viewData);
        }
        if ($categorySlug === 'stay-in-dong-anh') {
            return view('detail-stay', $viewData);
        }
        if ($categorySlug === 'wellness-care') {
            return view('detail-wellness', $viewData);
        }
        if ($categorySlug === 'smart-education-map') {
            return view('detail-education', $viewData);
        }
        if ($categorySlug === 'discover-dong-anh-community-culture-hub' || $categorySlug === 'hanh-trinh-di-san') {
            return view('detail-culture', $viewData);
        }
        if ($categorySlug === 'dong-anh-food-map') {
            return view('detail-food', $viewData);
        }
        
        return view('detail', $viewData);
    }

    public function storeReview(Request $request, $id)
    {
        $request->validate([
            'user_name' => 'required|string|max:50',
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'media.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480'
        ]);
        
        $commentText = (string) ($request->input('comment') ?? '');
        if (!empty($commentText)) {
            $spamCheck = \App\Services\SpamProtectionService::check($request, $commentText, 'review');
            if ($spamCheck['is_spam']) {
                return redirect()->back()
                    ->withErrors(['comment' => $spamCheck['reason']])
                    ->with('error', $spamCheck['reason'])
                    ->withInput();
            }
        }

        $eatery = Eatery::with('category')->find($id);
        if (!$eatery) {
            abort(404);
        }
        
        $mediaFiles = [];
        if ($request->hasFile('media')) {
            $files = is_array($request->file('media')) ? $request->file('media') : [$request->file('media')];
            $uploaded = R2Helper::uploadMultiple($files, 'reviews');
            foreach ($uploaded as $item) {
                $mediaFiles[] = [
                    'path' => $item['url'],
                    'type' => $item['file_type']
                ];
            }
        }
        
        $review = EateryApiService::storeReview($eatery->category->slug, $eatery->id, [
            'user_name' => $request->user_name,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'media_files' => $mediaFiles
        ]);
        
        return redirect()->back()->with('success', 'Cảm ơn bạn đã gửi đánh giá! Nhận xét của bạn giúp ích cho cộng đồng du lịch Đông Anh.');
    }
}
