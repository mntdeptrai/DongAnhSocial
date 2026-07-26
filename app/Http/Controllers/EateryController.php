<?php

namespace App\Http\Controllers;

use App\Models\Eatery;
use App\Models\Review;
use Illuminate\Http\Request;

use App\Services\EateryApiService;

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
        } elseif ($categorySlug === 'dong-anh-market' || $categorySlug === 'traditional-market') {
            $schemaType = 'ShoppingCenter';
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

        // Mã hóa Schema dữ liệu có cấu trúc sang định dạng thẻ script JSON-LD
        $jsonLd = '<script type="application/ld+json">' . PHP_EOL . 
                  json_encode($schemaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL . 
                  '</script>';
        
        $categorySlug = $eatery->category->slug;

        if ($categorySlug === 'traditional-market') {
            return view('detail-market', compact('eatery', 'jsonLd', 'checkinPhotos', 'checkinReviews'));
        }
        if ($categorySlug === 'dong-anh-market') {
            return view('detail-ocop', compact('eatery', 'jsonLd', 'checkinPhotos', 'checkinReviews'));
        }
        if ($categorySlug === 'stay-in-dong-anh') {
            return view('detail-stay', compact('eatery', 'jsonLd', 'checkinPhotos', 'checkinReviews'));
        }
        if ($categorySlug === 'wellness-care') {
            return view('detail-wellness', compact('eatery', 'jsonLd', 'checkinPhotos', 'checkinReviews'));
        }
        if ($categorySlug === 'smart-education-map') {
            return view('detail-education', compact('eatery', 'jsonLd', 'checkinPhotos', 'checkinReviews'));
        }
        if ($categorySlug === 'discover-dong-anh-community-culture-hub' || $categorySlug === 'hanh-trinh-di-san') {
            return view('detail-culture', compact('eatery', 'jsonLd', 'checkinPhotos', 'checkinReviews'));
        }
        if ($categorySlug === 'dong-anh-food-map') {
            return view('detail-food', compact('eatery', 'jsonLd', 'checkinPhotos', 'checkinReviews'));
        }
        
        return view('detail', compact('eatery', 'jsonLd', 'checkinPhotos', 'checkinReviews'));
    }

    public function storeReview(Request $request, $id)
    {
        $request->validate([
            'user_name' => 'required|string|max:50',
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'media.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480'
        ]);
        
        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $id);
        if (!$eatery) {
            abort(404);
        }
        
        $mediaFiles = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('reviews', 'public');
                $type = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';
                $mediaFiles[] = [
                    'path' => '/storage/' . $path,
                    'type' => $type
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
