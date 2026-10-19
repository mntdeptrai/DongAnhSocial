<?php

namespace App\Services;

use App\Contracts\MarketStallServiceInterface;
use App\Helpers\R2Helper;
use App\Models\OcopProduct;
use App\Models\Review;
use App\Models\ReviewMedia;
use App\Models\User;
use App\Services\EateryApiService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MarketStallService implements MarketStallServiceInterface
{
    /**
     * Resolve stall data and view parameters for stall detail page.
     */
    public function getStallDetailData(string $marketSlug, string $stallSlug, ?User $authUser): array
    {
        $eatery = EateryApiService::getEateryBySlug($marketSlug);
        if (!$eatery) {
            abort(404, 'Không tìm thấy chợ này.');
        }

        $products = $eatery->ocopProducts ?? collect();
        $allStalls = $products->groupBy('stall_name');

        $stallName = null;
        $stallProducts = collect();

        foreach ($allStalls as $name => $prods) {
            if (Str::slug($name) === $stallSlug) {
                $stallName = $name;
                $stallProducts = $prods;
                break;
            }
        }

        if (!$stallName) {
            abort(404, 'Không tìm thấy gian hàng này.');
        }

        $first = $stallProducts->first();
        $sellerName = $first->seller_name ?? 'Tiểu thương';
        $sellerPhone = $first->seller_phone ?? '';

        $sellerUser = null;
        if (!empty($first->user_id)) {
            $sellerUser = User::find($first->user_id);
        } elseif (!empty($sellerPhone)) {
            $sellerUser = User::where('phone', $sellerPhone)->first();
        }

        $lat = ($first->latitude ?? null) ?: ($eatery->latitude ?? 21.1571);
        $lng = ($first->longitude ?? null) ?: ($eatery->longitude ?? 105.8448);

        // Bank info & VietQR URL
        $bankName = $first->bank_name ?: 'MBBank';
        $bankAcct = $first->bank_account ?: '';
        $bankHolder = mb_strtoupper($first->bank_holder ?: $sellerName);
        $bankInfo = $bankAcct ? ($bankName . ' · ' . $bankAcct) : '';

        $qrCodeUrl = $first->qr_code_path ?: '';
        if ($qrCodeUrl) {
            $qrCodeUrl = preg_replace_callback('/^https:\/\/img\.vietqr\.io\/image\/([^-\/]+)-/', function($m) {
                return 'https://img.vietqr.io/image/' . str_replace(' ', '', $m[1]) . '-';
            }, $qrCodeUrl);
        }
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

        // Origin
        $originText = $first->origin ?: 'Tự sản xuất';
        if ($originText === 'Tự sản xuất' && $first->description && preg_match('/Nguồn gốc[:\s]+(.*?)[\.\n]/u', $first->description, $m)) {
            $originText = trim($m[1]);
        }

        $hasSmartphone = true;
        $hasQr = !empty($qrCodeUrl) || !empty($bankAcct);

        // Category keywords
        $category = 'Khác';
        $kwMap = [
            'Rau củ'         => ['Rau', 'Củ', 'Rau củ'],
            'Ăn uống'        => ['Ăn', 'Uống', 'Ẩm thực', 'Bún', 'Phở'],
            'Thịt tươi'      => ['Thịt', 'Giò', 'Chả'],
            'Thực phẩm khô'  => ['Hoa quả', 'Khô', 'Đặc sản'],
        ];
        foreach ($kwMap as $cat => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($stallName, $kw)) {
                    $category = $cat;
                    break 2;
                }
            }
        }

        // Load reviews
        $connection = 'mysql';
        $reviews = Review::on($connection)
            ->with('media')
            ->where('eatery_id', $eatery->id)
            ->where('stall_name', $stallName)
            ->latest()
            ->get();

        $avgRating = $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : 0;

        // Merchant ownership check
        $isMerchantOwner = false;
        if ($authUser && $authUser->phone) {
            $isMerchantOwner = OcopProduct::on('mysql_market')
                ->where('eatery_id', $eatery->id)
                ->where('seller_phone', $authUser->phone)
                ->where('stall_name', $stallName)
                ->exists();
        }

        return compact(
            'eatery',
            'marketSlug',
            'stallName',
            'stallSlug',
            'stallProducts',
            'sellerName',
            'sellerPhone',
            'sellerUser',
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
        );
    }

    /**
     * Store review for a stall from market and stall slug.
     */
    public function storeReviewFromSlug(string $marketSlug, string $stallSlug, array $data, ?User $authUser, array $mediaFiles = []): Review
    {
        $eatery = EateryApiService::getEateryBySlug($marketSlug);
        if (!$eatery) {
            abort(404, 'Không tìm thấy chợ này.');
        }

        $products = $eatery->ocopProducts ?? collect();
        $stallName = null;
        foreach ($products->groupBy('stall_name') as $name => $_) {
            if (Str::slug($name) === $stallSlug) {
                $stallName = $name;
                break;
            }
        }

        if (!$stallName) {
            abort(404, 'Không tìm thấy gian hàng này.');
        }

        $userName = $authUser ? $authUser->name : ($data['user_name'] ?? 'Khách vãng lai');

        $review = new Review();
        $review->setConnection('mysql');
        $review->fill([
            'eatery_id'  => $eatery->id,
            'stall_name' => $stallName,
            'user_name'  => strip_tags($userName),
            'rating'     => (int) ($data['rating'] ?? 5),
            'comment'    => strip_tags($data['comment'] ?? ''),
        ]);
        $review->save();

        if (!empty($mediaFiles)) {
            $uploaded = R2Helper::uploadMultiple($mediaFiles, 'reviews');
            foreach ($uploaded as $item) {
                $reviewMedia = new ReviewMedia();
                $reviewMedia->setConnection('mysql');
                $reviewMedia->fill([
                    'review_id' => $review->id,
                    'file_path' => $item['url'],
                    'file_type' => $item['file_type'],
                ]);
                $reviewMedia->save();
            }
        }

        return $review;
    }

    /**
     * Get formatted reviews for a stall in an eatery.
     */
    public function getStallReviews(int|string $eateryId, string $stallName): Collection
    {
        return Review::on('mysql')
            ->where('eatery_id', $eateryId)
            ->where('stall_name', $stallName)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Store a simple stall review by eatery ID and stall name.
     */
    public function storeStallReview(int|string $eateryId, string $stallName, array $data, ?User $authUser): Review
    {
        $userName = $authUser ? $authUser->name : ($data['user_name'] ?? 'Khách vãng lai');

        $review = new Review();
        $review->setConnection('mysql');
        $review->fill([
            'eatery_id'  => $eateryId,
            'stall_name' => $stallName,
            'user_name'  => strip_tags($userName),
            'rating'     => (int) ($data['rating'] ?? 5),
            'comment'    => strip_tags($data['comment'] ?? ''),
        ]);
        $review->save();

        return $review;
    }
}
