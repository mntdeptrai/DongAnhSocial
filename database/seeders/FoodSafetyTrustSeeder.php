<?php

namespace Database\Seeders;

use App\Models\Eatery;
use App\Models\FoodSafetyCertificate;
use App\Models\FoodSupplyContract;
use App\Models\PurchaseInvoice;
use App\Models\DailyFoodLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FoodSafetyTrustSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tìm quán Bún Mạch Tràng Cổ Loa
        $bunMachTrang = Eatery::where('slug', 'bun-mach-trang-co-loa')->first();
        if ($bunMachTrang) {
            // Xóa dữ liệu cũ nếu có
            $bunMachTrang->foodSafetyCertificate()->delete();
            $bunMachTrang->foodSupplyContracts()->delete();
            $bunMachTrang->purchaseInvoices()->delete();
            $bunMachTrang->dailyFoodLogs()->delete();

            // Gieo Giấy chứng chỉ VSATTP
            FoodSafetyCertificate::create([
                'eatery_id' => $bunMachTrang->id,
                'certificate_number' => '124/2024/ATTP-HN',
                'issued_by' => 'Sở Y tế Hà Nội - Chi cục An toàn Vệ sinh Thực phẩm',
                'issued_at' => Carbon::parse('2024-03-15'),
                'expired_at' => Carbon::parse('2027-03-15'),
                'image_path' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=800&q=80',
                'status' => 'active'
            ]);

            // Gieo Hợp đồng cung ứng
            FoodSupplyContract::create([
                'eatery_id' => $bunMachTrang->id,
                'supplier_name' => 'HTX Nông nghiệp Hữu cơ Dịch vụ Cổ Loa',
                'items_supplied' => 'Gạo tẻ sạch vụ mùa cũ làm bún ngâm ủ',
                'signed_at' => Carbon::parse('2024-01-10'),
                'expired_at' => Carbon::parse('2026-01-10'),
                'image_path' => 'https://images.unsplash.com/photo-1450133064473-71024230f91b?auto=format&fit=crop&w=800&q=80'
            ]);

            FoodSupplyContract::create([
                'eatery_id' => $bunMachTrang->id,
                'supplier_name' => 'Cơ sở sản xuất thực phẩm sạch Liêm Hiệp',
                'items_supplied' => 'Thịt heo hữu cơ tươi nóng xào chưng hành củ',
                'signed_at' => Carbon::parse('2024-02-18'),
                'expired_at' => Carbon::parse('2026-02-18'),
                'image_path' => 'https://images.unsplash.com/photo-1450133064473-71024230f91b?auto=format&fit=crop&w=800&q=80'
            ]);

            // Gieo Hóa đơn mua bán thực tế mới nhất
            PurchaseInvoice::create([
                'eatery_id' => $bunMachTrang->id,
                'invoice_number' => 'HD-2026-0520-CL',
                'supplier_name' => 'HTX Nông nghiệp Hữu cơ Cổ Loa',
                'invoice_date' => Carbon::parse('2026-05-20'),
                'total_amount' => 12500000,
                'items_summary' => 'Mua 500kg Gạo tẻ tuyển chọn vụ mùa 2025 để ngâm ủ bột lên men',
                'image_path' => 'https://images.unsplash.com/photo-1554415707-6e8cfc93fe23?auto=format&fit=crop&w=800&q=80'
            ]);

            PurchaseInvoice::create([
                'eatery_id' => $bunMachTrang->id,
                'invoice_number' => 'HD-255-LH',
                'supplier_name' => 'Cơ sở Thực phẩm sạch Liêm Hiệp',
                'invoice_date' => Carbon::parse('2026-05-21'),
                'total_amount' => 4800000,
                'items_summary' => 'Nhập 40kg Thịt nạc vai heo tươi dẻo nguyên miếng phi thơm hành củ',
                'image_path' => 'https://images.unsplash.com/photo-1554415707-6e8cfc93fe23?auto=format&fit=crop&w=800&q=80'
            ]);

            // Gieo Nhật ký an toàn thực phẩm hàng ngày (7 ngày vừa qua)
            for ($i = 0; $i < 7; $i++) {
                $logDate = Carbon::now()->subDays($i);
                if ($i === 2) {
                    $checker = 'Đoàn liên ngành VSATTP UBND Huyện Đông Anh (Thanh tra Y tế)';
                    $origin = 'Gạo tẻ đạt chuẩn từ HTX Nông nghiệp Hữu cơ Dịch vụ Cổ Loa, Thịt heo hữu cơ từ Cơ sở Liêm Hiệp (Có đóng dấu thú y)';
                    $storage = 'Kho gạo khô ráo đạt chuẩn 25°C. Thịt heo bảo quản tủ lạnh chuyên dụng ở nhiệt độ chuẩn 3.8°C. Đạt tiêu chuẩn vệ sinh phòng bếp.';
                } elseif ($i === 5) {
                    $checker = 'Chi cục An toàn Vệ sinh Thực phẩm - Sở Y tế Hà Nội (Thanh tra kiểm tra)';
                    $origin = 'Mẫu gạo tẻ làm bún và nước ngâm bột lấy từ bể ngầm đạt chỉ số lý hóa. Thịt heo có giấy kiểm dịch nguồn gốc rõ ràng.';
                    $storage = 'Hệ thống tủ bảo quản đông lạnh đạt -18°C và tủ mát đạt 4.2°C. Khu vực sơ chế và chế biến phân chia rõ ràng.';
                } else {
                    $checker = 'Nghệ nhân Nguyễn Văn Cường (Chủ cơ sở tự kiểm tra)';
                    $origin = 'Gạo tẻ tuyển chọn vụ mùa 2025 từ HTX Cổ Loa, Thịt heo vai nạc tươi nguyên miếng từ cơ sở sạch Liêm Hiệp';
                    $storage = 'Gạo tẻ bảo quản kho thoáng mát chống ẩm mốc. Thịt heo nóng nhập mới lúc 5h sáng chế biến ngay, tủ mát duy trì ổn định ở 4°C.';
                }

                DailyFoodLog::create([
                    'eatery_id' => $bunMachTrang->id,
                    'log_date' => $logDate,
                    'checker_name' => $checker,
                    'ingredients_origin' => $origin,
                    'storage_condition' => $storage,
                    'status' => 'compliant'
                ]);
            }
        }

        // 2. Tìm quán Cháo se Liên Hà
        $chaoSeLienHa = Eatery::where('slug', 'chao-se-gia-truyen-lien-ha')->first();
        if ($chaoSeLienHa) {
            $chaoSeLienHa->foodSafetyCertificate()->delete();
            $chaoSeLienHa->foodSupplyContracts()->delete();
            $chaoSeLienHa->purchaseInvoices()->delete();
            $chaoSeLienHa->dailyFoodLogs()->delete();

            // Gieo Giấy chứng chỉ VSATTP
            FoodSafetyCertificate::create([
                'eatery_id' => $chaoSeLienHa->id,
                'certificate_number' => '89/2025/ATTP-DA',
                'issued_by' => 'Ủy ban nhân dân huyện Đông Anh - Phòng Y tế cấp quận huyện',
                'issued_at' => Carbon::parse('2025-01-20'),
                'expired_at' => Carbon::parse('2028-01-20'),
                'image_path' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=800&q=80',
                'status' => 'active'
            ]);

            // Gieo Hợp đồng cung ứng
            FoodSupplyContract::create([
                'eatery_id' => $chaoSeLienHa->id,
                'supplier_name' => 'Cơ sở sản xuất thực phẩm sạch Liêm Hiệp',
                'items_supplied' => 'Xương ống heo nguyên chất và thịt băm tươi sạch',
                'signed_at' => Carbon::parse('2025-01-05'),
                'expired_at' => Carbon::parse('2027-01-05'),
                'image_path' => 'https://images.unsplash.com/photo-1450133064473-71024230f91b?auto=format&fit=crop&w=800&q=80'
            ]);

            // Gieo Hóa đơn mua bán thực tế mới nhất
            PurchaseInvoice::create([
                'eatery_id' => $chaoSeLienHa->id,
                'invoice_number' => 'HD-889-LH',
                'supplier_name' => 'Cơ sở Thực phẩm sạch Liêm Hiệp',
                'invoice_date' => Carbon::parse('2026-05-21'),
                'total_amount' => 3200000,
                'items_summary' => 'Nhập 30kg xương ống ngọt tủy và 15kg thịt heo vai nạc băm nhuyễn xào hành',
                'image_path' => 'https://images.unsplash.com/photo-1554415707-6e8cfc93fe23?auto=format&fit=crop&w=800&q=80'
            ]);

            // Gieo Nhật ký an toàn thực phẩm hàng ngày (7 ngày vừa qua)
            for ($i = 0; $i < 7; $i++) {
                $logDate = Carbon::now()->subDays($i);
                if ($i === 3) {
                    $checker = 'Đoàn liên ngành VSATTP UBND Huyện Đông Anh (Thanh tra Y tế)';
                    $origin = 'Xương ống heo sạch và thịt nạc vai xay từ cơ sở HTX Liêm Hiệp, Gạo nếp xay Đại Vĩ có đầy đủ hóa đơn mua hàng';
                    $storage = 'Nồi ninh nước dùng đạt chuẩn inox 304 vệ sinh sạch sẽ. Bột lọc gạo nếp được ép ráo nước mát lành ở nhiệt độ 22°C.';
                } else {
                    $checker = 'Nghệ nhân Nguyễn Thị Nhị (Chủ cơ sở tự kiểm tra)';
                    $origin = 'Xương ống tủy heo hữu cơ dẻo nóng nhập tại HTX Liêm Hiệp lúc 4h30 sáng, Gạo nếp hoa vàng giã tay làng Đại Vĩ';
                    $storage = 'Xương ống rửa sạch, chần nước sôi rồi ninh liên tục 8 tiếng từ 4h sáng. Bột nếp được xay tươi mỗi ngày bảo quản mát chuẩn.';
                }

                DailyFoodLog::create([
                    'eatery_id' => $chaoSeLienHa->id,
                    'log_date' => $logDate,
                    'checker_name' => $checker,
                    'ingredients_origin' => $origin,
                    'storage_condition' => $storage,
                    'status' => 'compliant'
                ]);
            }
        }
    }
}
