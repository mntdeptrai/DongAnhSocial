<?php

namespace App\Helpers;

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

        // Đảm bảo có tiền tố "Trường" nếu chưa có
        if (!preg_match('/^Trường\b/ui', $clean)) {
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
                return "{$base}, địa điểm ăn uống Đông Anh, quán ăn ngon Đông Anh, ẩm thực Đông Anh, đặc sản Đông Anh, món ngon Đông Anh, ăn gì ở Đông Anh, nhà hàng Đông Anh, quán cà phê Đông Anh";

            case 'wellness-care':
                return "{$base}, bệnh viện Đông Anh, phòng khám Đông Anh, trung tâm y tế Đông Anh, y tế Đông Anh, chăm sóc sức khỏe Đông Anh, khám chữa bệnh Đông Anh, nhà thuốc Đông Anh";

            case 'dong-anh-market':
            case 'traditional-market':
                return "{$base}, chợ truyền thống Đông Anh, chợ Đông Anh, gian hàng chợ Đông Anh, chợ số Đông Anh, sản phẩm OCOP Đông Anh, nông sản sạch Đông Anh, đặc sản Đông Anh, mua sắm Đông Anh";

            case 'stay-in-dong-anh':
                return "{$base}, nhà nghỉ Đông Anh, khách sạn Đông Anh, homestay Đông Anh, lưu trú Đông Anh, du lịch Đông Anh, phòng nghỉ Đông Anh";

            case 'hanh-trinh-di-san':
            case 'discover-dong-anh-community-culture-hub':
                return "{$base}, khám phá Đông Anh, di sản văn hóa Đông Anh, di tích lịch sử Đông Anh, Cổ Loa Đông Anh, du lịch di sản Đông Anh, văn hóa Đông Anh, địa điểm tham quan Đông Anh";

            default:
                return "{$base}, Đông Anh Social, bản đồ thông minh Đông Anh, tra cứu địa điểm Đông Anh, tiện ích Đông Anh";
        }
    }
}
