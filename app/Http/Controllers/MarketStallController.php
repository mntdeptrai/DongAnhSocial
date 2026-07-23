<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EateryApiService;
use App\Models\OcopProduct;
use App\Models\Review;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class MarketStallController extends Controller
{
    /**
     * Resolve the DB connection name for the given eatery.
     * We probe all known connections and return the first match.
     */
    private function resolveConnection(int $eateryId): string
    {
        foreach (['mysql', 'mysql_market', 'mysql_stay', 'mysql_wellness', 'mysql_education', 'mysql_culture'] as $conn) {
            try {
                if (\App\Models\Eatery::on($conn)->where('id', $eateryId)->exists()) {
                    return $conn;
                }
            } catch (\Exception $e) {
                // connection unavailable
            }
        }
        return 'mysql';
    }

    /**
     * Display the full stall detail page.
     */
    public function show($marketSlug, $stallSlug)
    {
        // 1. Resolve market eatery
        $eatery = EateryApiService::getEateryBySlug($marketSlug);
        if (!$eatery) {
            abort(404, 'Không tìm thấy chợ này.');
        }

        // 2. Load all products and group by stall_name
        $products  = $eatery->ocopProducts ?? collect();
        $allStalls = $products->groupBy('stall_name');

        // 3. Match stall by slug
        $stallName     = null;
        $stallProducts = collect();

        foreach ($allStalls as $name => $prods) {
            if (Str::slug($name) === $stallSlug) {
                $stallName     = $name;
                $stallProducts = $prods;
                break;
            }
        }

        if (!$stallName) {
            abort(404, 'Không tìm thấy gian hàng này.');
        }

        $first       = $stallProducts->first();
        $sellerName  = $first->seller_name ?? 'Tiểu thương';
        $sellerPhone = $first->seller_phone ?? '';
        $lat         = ($first->latitude ?? null) ?: ($eatery->latitude ?? 21.1571);
        $lng         = ($first->longitude ?? null) ?: ($eatery->longitude ?? 105.8448);

        // Parse bank info: "Hỗ trợ thanh toán VietQR ngân hàng MB: 0965194462"
        $bankInfo = '';
        $bankName = '';
        $bankAcct = '';
        if ($first->description) {
            // Pattern: "ngân hàng MB: 0965194462" or "ngân hàng Techcombank: 2003198099"
            if (preg_match('/ng[aâ]n h[aà]ng\s+([A-Za-z0-9]+)[:\s]+(\d+)/ui', $first->description, $m)) {
                $bankName = strtoupper(trim($m[1]));
                $bankAcct = trim($m[2]);
                $bankInfo = $bankName . ' · ' . $bankAcct;
            }
        }

        // Detect origin
        $originText = 'Tự sản xuất';
        if ($first->description && preg_match('/Nguồn gốc[:\s]+(.*?)[\.\n]/u', $first->description, $m)) {
            $originText = trim($m[1]);
        }

        $hasSmartphone = $first->description ? str_contains($first->description, 'Có sử dụng smartphone') : false;
        $hasQr         = !empty($bankInfo);

        // Detect category badge from stall_name keywords
        $category = 'Khác';
        $kwMap = [
            'Rau củ' => ['Rau', 'Củ', 'Rau củ'],
            'Ăn uống' => ['Ăn', 'Uống', 'Ẩm thực', 'Bún', 'Phở'],
            'Thịt tươi' => ['Thịt', 'Giò', 'Chả'],
            'Thực phẩm khô' => ['Hoa quả', 'Khô', 'Đặc sản'],
        ];
        foreach ($kwMap as $cat => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($stallName, $kw)) {
                    $category = $cat;
                    break 2;
                }
            }
        }

        // 4. Load reviews
        $connection = $this->resolveConnection($eatery->id);
        $reviews    = Review::on($connection)
            ->where('eatery_id', $eatery->id)
            ->where('stall_name', $stallName)
            ->latest()
            ->get();

        $avgRating = $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : 0;

        // 5. Merchant ownership check
        $isMerchantOwner = false;
        if (Auth::check() && Auth::user()->phone) {
            $ms = OcopProduct::on('mysql_market')
                ->where('eatery_id', $eatery->id)
                ->where('seller_phone', Auth::user()->phone)
                ->where('stall_name', $stallName)
                ->exists();
            $isMerchantOwner = $ms;
        }

        return view('stall-detail', compact(
            'eatery',
            'marketSlug',
            'stallName',
            'stallSlug',
            'stallProducts',
            'sellerName',
            'sellerPhone',
            'lat',
            'lng',
            'category',
            'bankInfo',
            'bankName',
            'bankAcct',
            'originText',
            'hasQr',
            'hasSmartphone',
            'reviews',
            'avgRating',
            'isMerchantOwner',
            'connection'
        ));
    }

    /**
     * Store a review submitted from the stall detail page.
     */
    public function storeReview(Request $request, $marketSlug, $stallSlug)
    {
        $eatery = EateryApiService::getEateryBySlug($marketSlug);
        if (!$eatery) abort(404);

        $request->validate([
            'rating'    => 'required|integer|min:1|max:5',
            'comment'   => 'required|string|min:5|max:500',
            'user_name' => 'nullable|string|max:50',
        ]);

        $connection = $this->resolveConnection($eatery->id);
        $userName   = Auth::check() ? Auth::user()->name : ($request->input('user_name') ?: 'Khách vãng lai');

        // Resolve stall name from slug
        $products  = $eatery->ocopProducts ?? collect();
        $stallName = null;
        foreach ($products->groupBy('stall_name') as $name => $_) {
            if (Str::slug($name) === $stallSlug) {
                $stallName = $name;
                break;
            }
        }

        if (!$stallName) abort(404);

        $review = new Review();
        $review->setConnection($connection);
        $review->fill([
            'eatery_id'  => $eatery->id,
            'stall_name' => $stallName,
            'user_name'  => strip_tags($userName),
            'rating'     => (int) $request->input('rating'),
            'comment'    => strip_tags($request->input('comment')),
        ]);
        $review->save();

        return back()->with('review_success', 'Cảm ơn bạn đã đánh giá gian hàng!');
    }
}
