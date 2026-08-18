<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class VietnameseSeoHelper
{
    /**
     * Chuẩn hóa từ viết tắt tên trường học (MN -> Mầm non, TH -> Tiểu học, THCS, THPT)
     */
    public static function standardizeSchoolName(?string $name): string
    {
        if (!$name) return '';

        $clean = trim($name);

        // MN -> Mầm non
        $clean = preg_replace('/\bMN\b/u', 'Mầm non', $clean);
        $clean = preg_replace('/^mn\b/ui', 'Mầm non', $clean);
        $clean = preg_replace('/^Mầm Non\b/u', 'Mầm non', $clean);

        // TH -> Tiểu học (chú ý không thay thế THCS, THPT)
        $clean = preg_replace('/\bTH\b(?!\s*(CS|PT))/u', 'Tiểu học', $clean);
        $clean = preg_replace('/^th\b(?!\s*(cs|pt))/ui', 'Tiểu học', $clean);
        $clean = preg_replace('/^Tiểu Học\b/u', 'Tiểu học', $clean);

        // Đảm bảo có tiền tố "Trường" nếu là tên trường học (Mầm non, Tiểu học, THCS, THPT) và chưa có
        if (preg_match('/\b(Mầm non|Tiểu học|THCS|THPT|Mầm Non|Tiểu Học|MN|TH)\b/u', $name) && !preg_match('/^Trường\b/ui', $clean)) {
            $clean = 'Trường ' . $clean;
        }

        return $clean;
    }

    /**
     * Chuyển chuỗi tiếng Việt có dấu thành không dấu
     */
    public static function stripAccents(?string $str): string
    {
        if (!$str) return '';

        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D' => 'Đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        ];

        foreach ($unicode as $nonInput => $input) {
            $str = preg_replace("/($input)/i", $nonInput, $str);
        }

        return mb_strtolower($str);
    }

    /**
     * Sinh bộ từ khóa Meta Keywords chuẩn SEO tối ưu chuyên sâu cho Sản phẩm & Mặt hàng
     *
     * @param string $productName Tên sản phẩm / mặt hàng
     * @param string|null $sellerName Tên chủ thể sản xuất, cơ sở kinh doanh, hoặc gian hàng
     * @param string|null $communeName Tên xã/thị trấn
     * @param string|null $categoryOrType Danh mục / Loại hình (ocop, nong-san, do-uong, tieu-dung, mon-an, ...)
     * @param string|null $starRating Số sao chứng nhận OCOP (nếu có)
     * @param string|null $price Giá sản phẩm (nếu có)
     * @return string Danh sách từ khóa phân tách bằng dấu phẩy
     */
    public static function generateProductKeywords(
        string $productName,
        ?string $sellerName = null,
        ?string $communeName = 'Đông Anh',
        ?string $categoryOrType = null,
        ?string $starRating = null,
        ?string $price = null
    ): string {
        $pName = trim($productName);
        $unaccentPName = self::stripAccents($pName);
        $commune = $communeName ?: 'Đông Anh';
        $seller = trim($sellerName ?: '');
        $unaccentSeller = $seller ? self::stripAccents($seller) : '';

        $keywords = [];

        // 1. Tên sản phẩm chính xác có dấu & không dấu
        $keywords[] = $pName;
        $keywords[] = $unaccentPName;

        // 2. Từ khóa ghép địa danh Đông Anh & Hà Nội
        $keywords[] = "{$pName} Đông Anh";
        $keywords[] = "{$unaccentPName} dong anh";
        $keywords[] = "{$pName} {$commune}";
        $keywords[] = "{$pName} Hà Nội";
        $keywords[] = "{$unaccentPName} ha noi";
        $keywords[] = "đặc sản {$pName} Đông Anh";

        // 3. Từ khóa ý định mua sắm & giao thương (Commercial & Transactional Intent)
        $keywords[] = "mua {$pName}";
        $keywords[] = "mua {$pName} ở Đông Anh";
        $keywords[] = "mua {$pName} ở đâu";
        $keywords[] = "giá {$pName}";
        $keywords[] = "báo giá {$pName}";
        $keywords[] = "bán {$pName}";
        $keywords[] = "địa chỉ bán {$pName} Đông Anh";
        $keywords[] = "cửa hàng bán {$pName}";
        $keywords[] = "{$pName} chính hãng";
        $keywords[] = "{$pName} chất lượng cao";
        $keywords[] = "đặt mua {$pName} online";
        $keywords[] = "giao hàng {$pName} Đông Anh";

        // 4. Nếu có tên chủ thể / cơ sở sản xuất / cửa hàng
        if ($seller) {
            $keywords[] = "{$pName} {$seller}";
            if ($unaccentSeller) {
                $keywords[] = "{$unaccentPName} {$unaccentSeller}";
            }
            $keywords[] = $seller;
            $keywords[] = "sản phẩm của {$seller}";
        }

        // 5. Nếu là sản phẩm OCOP hoặc có số sao chứng nhận
        if ($starRating || $categoryOrType === 'ocop' || $categoryOrType === 'dong-anh-market') {
            $starText = $starRating ? (str_contains($starRating, 'sao') ? $starRating : "{$starRating} sao") : 'OCOP';
            $keywords[] = "Sản phẩm OCOP {$pName}";
            $keywords[] = "OCOP {$pName} Đông Anh";
            $keywords[] = "{$pName} OCOP {$starText}";
            $keywords[] = "nông sản OCOP Đông Anh";
            $keywords[] = "sản phẩm OCOP Hà Nội";
            $keywords[] = "đặc sản OCOP Đông Anh";
            $keywords[] = "chứng nhận OCOP {$pName}";
            $keywords[] = "hồ sơ OCOP {$pName}";
        }

        // 6. Nếu là mặt hàng cơ sở kinh doanh, chợ quê, hoặc dịch vụ
        if ($categoryOrType === 'co-so-kinh-doanh' || $categoryOrType === 'traditional-market' || $categoryOrType === 'market') {
            $keywords[] = "hàng hóa {$commune}";
            $keywords[] = "cơ sở kinh doanh {$pName}";
            $keywords[] = "gian hàng {$pName} Đông Anh";
            $keywords[] = "chợ số Đông Anh {$pName}";
            $keywords[] = "bán lẻ {$pName} Đông Anh";
            $keywords[] = "thanh toán VietQR {$pName}";
        }

        // 7. Từ khóa nền tảng & tra cứu chung
        $keywords[] = "Đông Anh Social";
        $keywords[] = "bản đồ số Đông Anh";
        $keywords[] = "tra cứu sản phẩm Đông Anh";
        $keywords[] = "nông sản sạch Đông Anh";

        // Loại bỏ trùng lặp và nối thành chuỗi
        $unique = array_filter(array_unique($keywords));
        return implode(', ', $unique);
    }

    /**
     * Sinh đoạn mô tả Meta Description hấp dẫn, chuẩn SEO 150-160 ký tự cho sản phẩm
     */
    public static function generateProductMetaDescription(
        string $productName,
        ?string $sellerName = null,
        ?string $address = null,
        ?string $price = null,
        ?string $starRating = null,
        ?string $description = null
    ): string {
        $pName = trim($productName);
        $seller = trim($sellerName ?: 'Đông Anh');
        $addr = trim($address ?: 'Đông Anh, Hà Nội');

        $starSnippet = $starRating ? " đạt chuẩn OCOP {$starRating}" : "";
        $priceSnippet = $price ? ", giá: " . (is_numeric($price) ? number_format((float)$price, 0, ',', '.') . "đ" : $price) : "";

        $descSnippet = "";
        if ($description) {
            $cleanDesc = preg_replace('/\s+/', ' ', strip_tags($description));
            $descSnippet = Str::limit($cleanDesc, 80, '...');
        }

        if ($descSnippet) {
            return "Chi tiết sản phẩm {$pName}{$starSnippet} tại {$seller} ({$addr}){$priceSnippet}. {$descSnippet} Xem báo giá, hình ảnh và đặt mua trực tiếp.";
        }

        return "Khám phá {$pName}{$starSnippet} chính hãng tại {$seller}, địa chỉ: {$addr}{$priceSnippet}. Đặt mua online, hỗ trợ thanh toán VietQR và giao hàng tận nơi.";
    }

    /**
     * Sinh bộ từ khóa Meta Keywords cho Gian hàng Chợ số / Chợ truyền thống
     */
    public static function generateStallKeywords(
        string $stallName,
        ?string $marketName = null,
        ?string $category = null,
        ?string $communeName = 'Đông Anh'
    ): string {
        $sName = trim($stallName);
        $unaccentSName = self::stripAccents($sName);
        $mName = trim($marketName ?: 'Chợ Đông Anh');
        $unaccentMName = self::stripAccents($mName);
        $commune = $communeName ?: 'Đông Anh';

        $keywords = [
            $sName,
            $unaccentSName,
            "{$sName} {$mName}",
            "{$unaccentSName} {$unaccentMName}",
            "gian hàng {$sName}",
            "tiểu thương {$sName}",
            "chợ {$mName}",
            "gian hàng chợ {$commune}",
            "chợ truyền thống Đông Anh",
            "chợ số Đông Anh",
            "nông sản chợ {$mName}",
            "thực phẩm sạch chợ Đông Anh",
            "mua sắm tại {$mName}",
            "thanh toán VietQR {$sName}",
            "Đông Anh Social",
            "bản đồ số chợ Đông Anh"
        ];

        if ($category) {
            $keywords[] = "{$category} {$mName}";
            $keywords[] = "gian hàng {$category} Đông Anh";
        }

        return implode(', ', array_filter(array_unique($keywords)));
    }

    /**
     * Sinh bộ từ khóa Meta Keywords chuẩn SEO theo từng danh mục tích hợp đa dạng từ khóa về Đông Anh
     */
    public static function generateKeywords(string $name, ?string $categorySlug, ?string $communeName = 'Đông Anh'): string
    {
        $stdName = self::standardizeSchoolName($name);
        $unaccentName = self::stripAccents($stdName);
        $commune = $communeName ?: 'Đông Anh';
        $baseDongAnh = "Đông Anh, dong anh, donganh, xã Đông Anh, Xã Đông Anh, {$commune}, Hà Nội, ha noi, hanoi, Đông Anh Social, bản đồ số Đông Anh, tra cứu Đông Anh";
        $base = "{$stdName}, {$unaccentName}, {$baseDongAnh}";

        switch ($categorySlug) {
            case 'smart-education-map':
                return "{$base}, trường mầm non, mầm non, mầm non Đông Anh, trường tiểu học, tiểu học, tiểu học Đông Anh, THCS Đông Anh, THPT Đông Anh, giáo dục thông minh Đông Anh, trường học Đông Anh, sáp nhập trường học Đông Anh, tuyển sinh Đông Anh, bản đồ giáo dục Đông Anh";

            case 'dong-anh-food-map':
                return "{$base}, địa điểm ăn uống Đông Anh, quán ăn ngon Đông Anh, ẩm thực Đông Anh, đặc sản Đông Anh, món ngon Đông Anh, ăn gì ở Đông Anh, nhà hàng Đông Anh, quán cà phê Đông Anh, đặt món online Đông Anh, menu quán ăn Đông Anh";

            case 'wellness-care':
                return "{$base}, bệnh viện Đông Anh, phòng khám Đông Anh, trung tâm y tế Đông Anh, y tế Đông Anh, chăm sóc sức khỏe Đông Anh, khám chữa bệnh Đông Anh, nhà thuốc Đông Anh, cơ sở y tế Đông Anh";

            case 'dong-anh-market':
                return "{$base}, sản phẩm OCOP Đông Anh, đặc sản OCOP Đông Anh, nông sản sạch Đông Anh, OCOP 3 sao, OCOP 4 sao, OCOP 5 sao Đông Anh, gạo nếp cái hoa vàng Đông Anh, mua đặc sản Đông Anh, chợ OCOP Đông Anh, hồ sơ OCOP Đông Anh";

            case 'traditional-market':
                return "{$base}, chợ truyền thống Đông Anh, chợ Đông Anh, gian hàng chợ Đông Anh, chợ số Đông Anh, nông sản sạch Đông Anh, rau củ quả Đông Anh, thịt tươi Đông Anh, chợ quê Đông Anh, mua sắm chợ Đông Anh, tiểu thương chợ Đông Anh";

            case 'co-so-kinh-doanh':
                return "{$base}, cơ sở kinh doanh Đông Anh, doanh nghiệp Đông Anh, cửa hàng Đông Anh, siêu thị mini Đông Anh, bán lẻ Đông Anh, mua sắm Đông Anh, dịch vụ thương mại Đông Anh, hàng hóa Đông Anh, tra cứu hộ kinh doanh Đông Anh";

            case 'stay-in-dong-anh':
                return "{$base}, nhà nghỉ Đông Anh, khách sạn Đông Anh, homestay Đông Anh, lưu trú Đông Anh, du lịch Đông Anh, phòng nghỉ Đông Anh, đặt phòng Đông Anh";

            case 'hanh-trinh-di-san':
            case 'discover-dong-anh-community-culture-hub':
                return "{$base}, khám phá Đông Anh, di sản văn hóa Đông Anh, di tích lịch sử Đông Anh, Cổ Loa Đông Anh, du lịch di sản Đông Anh, văn hóa Đông Anh, địa điểm tham quan Đông Anh, lễ hội Đông Anh";

            default:
                return "{$base}, Đông Anh Social, bản đồ thông minh Đông Anh, tra cứu địa điểm Đông Anh, tiện ích Đông Anh, sản phẩm đặc sản Đông Anh";
        }
    }
}
