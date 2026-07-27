<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EateryApiService;
use App\Models\OcopProduct;
use App\Models\Review;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Helpers\R2Helper;

class MarketStallController extends Controller
{
    /**
     * Resolve the DB connection name for the given eatery.
     * We probe all known connections and return the first match.
     */
    private function resolveConnection(int $eateryId): string
    {
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

        // Parse bank info & Auto-generate VietQR URL
        $bankName   = $first->bank_name ?: 'MBBank';
        $bankAcct   = $first->bank_account ?: '';
        $bankHolder = mb_strtoupper($first->bank_holder ?: $sellerName);
        $bankInfo   = $bankAcct ? ($bankName . ' · ' . $bankAcct) : '';

        $qrCodeUrl = $first->qr_code_path ?: '';
        if (empty($qrCodeUrl) && !empty($bankAcct)) {
            $bankCodeMap = [
                'MBBANK' => 'MB', 'MB' => 'MB', 'VIETCOMBANK' => 'VCB', 'VCB' => 'VCB',
                'AGRIBANK' => 'VBA', 'VBA' => 'VBA', 'TECHCOMBANK' => 'TCB', 'TCB' => 'TCB',
                'BIDV' => 'BIDV', 'VPBANK' => 'VPB', 'VPB' => 'VPB', 'VIETINBANK' => 'CTG',
                'CTG' => 'CTG', 'TPBANK' => 'TPB', 'TPB' => 'TPB', 'SACOMBANK' => 'STB', 'STB' => 'STB'
            ];
            $cleanBankKey = strtoupper(str_replace([' ', 'NGÂN HÀNG', 'NH'], '', $bankName));
            $bankCode = $bankCodeMap[$cleanBankKey] ?? 'MB';
            $qrCodeUrl = "https://img.vietqr.io/image/{$bankCode}-{$bankAcct}-compact.png?accountName=" . urlencode($bankHolder) . "&addInfo=" . urlencode("TT " . $stallName);
        }

        // Detect origin
        $originText = $first->origin ?: 'Tự sản xuất';
        if ($originText === 'Tự sản xuất' && $first->description && preg_match('/Nguồn gốc[:\s]+(.*?)[\.\n]/u', $first->description, $m)) {
            $originText = trim($m[1]);
        }

        $hasSmartphone = true;
        $hasQr         = !empty($qrCodeUrl) || !empty($bankAcct);

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
            ->with('media')
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
            'bankHolder',
            'qrCodeUrl',
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
            'media.*'   => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi|max:20480',
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

        // Process media uploads
        if ($request->hasFile('media')) {
            $files = is_array($request->file('media')) ? $request->file('media') : [$request->file('media')];
            $uploaded = R2Helper::uploadMultiple($files, 'reviews');
            foreach ($uploaded as $item) {
                $reviewMedia = new \App\Models\ReviewMedia();
                $reviewMedia->setConnection($connection);
                $reviewMedia->fill([
                    'review_id' => $review->id,
                    'file_path' => $item['url'],
                    'file_type' => $item['file_type'],
                ]);
                $reviewMedia->save();
            }
        }

        return back()->with('review_success', 'Cảm ơn bạn đã đánh giá gian hàng!');
    }
}
