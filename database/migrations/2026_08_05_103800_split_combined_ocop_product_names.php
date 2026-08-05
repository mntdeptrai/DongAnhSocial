<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tách các sản phẩm OCOP có tên gộp (chứa & hoặc ,) thành từng sản phẩm riêng biệt.
 * 
 * Ví dụ:
 *   "Thuốc tân dược & Dụng cụ y tế" → "Thuốc tân dược" + "Dụng cụ y tế"
 *   "Thịt bò, lợn, gà"             → "Thịt bò" + "Thịt lợn" + "Thịt gà"
 *   "Cá, trứng"                     → "Cá" + "Trứng"
 */
return new class extends Migration
{
    public function up(): void
    {
        // Lấy tất cả sản phẩm có dấu & hoặc , trong tên
        $products = DB::table('ocop_products')
            ->where(function ($q) {
                $q->where('name', 'LIKE', '%&%')
                  ->orWhere('name', 'LIKE', '%,%');
            })
            ->get();

        foreach ($products as $product) {
            // Tách bằng & và , (hỗ trợ cả hai)
            $parts = preg_split('/\s*[&,]\s*/', $product->name);
            $parts = array_values(array_filter(array_map('trim', $parts)));

            // Nếu không tách được hoặc chỉ 1 phần → bỏ qua
            if (count($parts) <= 1) {
                continue;
            }

            // Xử lý đặc biệt: nếu phần tách ra quá ngắn hoặc thiếu ngữ cảnh,
            // thêm tiền tố từ phần đầu tiên cho rõ nghĩa
            $contextParts = $this->enrichShortParts($parts);

            // Cập nhật sản phẩm gốc thành phần đầu tiên
            DB::table('ocop_products')
                ->where('id', $product->id)
                ->update([
                    'name' => $contextParts[0],
                    'updated_at' => now(),
                ]);

            // Tạo sản phẩm mới cho các phần còn lại
            for ($i = 1; $i < count($contextParts); $i++) {
                DB::table('ocop_products')->insert([
                    'eatery_id'       => $product->eatery_id,
                    'stall_name'      => $product->stall_name,
                    'seller_name'     => $product->seller_name,
                    'seller_phone'    => $product->seller_phone,
                    'name'            => $contextParts[$i],
                    'price'           => $product->price,
                    'unit'            => $product->unit,
                    'description'     => $product->description,
                    'image_path'      => $product->image_path,
                    'star_rating'     => $product->star_rating,
                    'heritage_year'   => $product->heritage_year ?? null,
                    'story'           => $product->story ?? null,
                    'artisans'        => $product->artisans ?? null,
                    'fun_fact'        => $product->fun_fact ?? null,
                    'audio_narrative' => $product->audio_narrative ?? null,
                    'ingredients'     => $product->ingredients ?? null,
                    'timeline'        => $product->timeline ?? null,
                    'created_at'      => $product->created_at,
                    'updated_at'      => now(),
                ]);
            }
        }
    }

    /**
     * Xử lý các phần tách ra bị ngắn / thiếu ngữ cảnh.
     * Ví dụ: "Thịt bò, lợn, gà" → ["Thịt bò", "Thịt lợn", "Thịt gà"]
     *         "Gà, vịt" → ["Gà", "Vịt"]
     *         "Quần áo nữ & trẻ em" → ["Quần áo nữ", "Quần áo trẻ em"]
     */
    private function enrichShortParts(array $parts): array
    {
        $result = [];
        $firstPart = $parts[0];
        
        // Tìm tiền tố chung từ phần đầu tiên (lấy từ đầu tiên nếu có 2+ từ)
        $firstWords = explode(' ', $firstPart);
        $prefix = '';
        
        // Nếu phần đầu có nhiều hơn 1 từ, lấy từ đầu tiên làm tiền tố tiềm năng
        if (count($firstWords) > 1) {
            $prefix = $firstWords[0]; // VD: "Thịt" từ "Thịt bò"
        }
        
        $result[] = $this->mbUcfirst(trim($firstPart));
        
        for ($i = 1; $i < count($parts); $i++) {
            $part = trim($parts[$i]);
            
            if (empty($part)) continue;
            
            // Nếu phần hiện tại quá ngắn (1 từ) VÀ có tiền tố từ phần đầu
            // thì thêm tiền tố cho rõ nghĩa
            $partWords = explode(' ', $part);
            
            if (count($partWords) === 1 && !empty($prefix) && $this->shouldAddPrefix($prefix, $part)) {
                $part = $prefix . ' ' . $part;
            }
            
            $result[] = $this->mbUcfirst(trim($part));
        }
        
        return $result;
    }

    /**
     * Kiểm tra xem có nên thêm tiền tố cho phần ngắn hay không.
     * Ví dụ: prefix="Thịt", part="lợn" → true (Thịt lợn)
     *         prefix="Gà", part="vịt" → false (chỉ cần "Vịt")
     */
    private function shouldAddPrefix(string $prefix, string $part): bool
    {
        // Danh sách tiền tố nên ghép
        $prefixesOk = [
            'Thịt', 'Quần', 'Bánh', 'Thuốc', 'Giày', 'Đồ',
        ];
        
        foreach ($prefixesOk as $ok) {
            if (mb_strtolower($prefix) === mb_strtolower($ok)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Viết hoa ký tự đầu tiên hỗ trợ UTF-8 tiếng Việt.
     */
    private function mbUcfirst(string $str): string
    {
        return mb_strtoupper(mb_substr($str, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($str, 1, null, 'UTF-8');
    }

    public function down(): void
    {
        // Rollback: Không thể tự động rollback vì đã tách sản phẩm.
        // Để khôi phục, cần restore từ backup hoặc re-import dữ liệu gốc.
    }
};
