<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Eatery;

class DistributeBusinessCoordinates extends Command
{
    protected $signature = 'map:distribute-coordinates {--force : Overwrite existing non-default coordinates}';
    protected $description = 'Phân bổ lại tọa độ GPS cho các cơ sở kinh doanh, doanh nghiệp trên địa bàn huyện Đông Anh';

    // Bảng tọa độ trung tâm các xã / thôn / khu vực tiêu biểu tại huyện Đông Anh
    private $locations = [
        // Các xã & thôn Đông Anh
        'cổ loa'      => ['lat' => 21.1392, 'lng' => 105.8654],
        'chùa cổ loa' => ['lat' => 21.1392, 'lng' => 105.8654],
        'hồng lạc'    => ['lat' => 21.1380, 'lng' => 105.8620],
        'cầu cả'      => ['lat' => 21.1365, 'lng' => 105.8670],
        'mạch tràng'  => ['lat' => 21.1320, 'lng' => 105.8640],
        'thục vương'  => ['lat' => 21.1410, 'lng' => 105.8610],

        'uy nỗ'       => ['lat' => 21.1390, 'lng' => 105.8500],
        'cường nỗ'    => ['lat' => 21.1415, 'lng' => 105.8530],
        'thượng oai'  => ['lat' => 21.1375, 'lng' => 105.8510],
        'oai nỗ'      => ['lat' => 21.1360, 'lng' => 105.8540],
        'phan xá'     => ['lat' => 21.1440, 'lng' => 105.8580],

        'thị trấn đông anh' => ['lat' => 21.1365, 'lng' => 105.8450],
        'cao lỗ'            => ['lat' => 21.1370, 'lng' => 105.8465],
        'tổ 1'              => ['lat' => 21.1345, 'lng' => 105.8430],
        'tổ 2'              => ['lat' => 21.1355, 'lng' => 105.8440],
        'tổ 3'              => ['lat' => 21.1365, 'lng' => 105.8450],
        'tổ 4'              => ['lat' => 21.1375, 'lng' => 105.8460],
        'tổ 5'              => ['lat' => 21.1385, 'lng' => 105.8470],
        'tổ 6'              => ['lat' => 21.1395, 'lng' => 105.8480],
        'tổ 7'              => ['lat' => 21.1405, 'lng' => 105.8490],
        'tổ 8'              => ['lat' => 21.1415, 'lng' => 105.8500],
        'đản mỗ'            => ['lat' => 21.1420, 'lng' => 105.8430],

        'dục tú'      => ['lat' => 21.1250, 'lng' => 105.8950],
        'đồng dầu'    => ['lat' => 21.1210, 'lng' => 105.8910],
        'phúc hậu'    => ['lat' => 21.1280, 'lng' => 105.8980],
        'nghĩa vũ'    => ['lat' => 21.1190, 'lng' => 105.8890],

        'việt hùng'   => ['lat' => 21.1450, 'lng' => 105.8780],
        'cổ vân'      => ['lat' => 21.1480, 'lng' => 105.8750],
        'dục nội'     => ['lat' => 21.1430, 'lng' => 105.8810],
        'gia lương'   => ['lat' => 21.1420, 'lng' => 105.8690],
        'lương quán'  => ['lat' => 21.1460, 'lng' => 105.8720],

        'đông hội'    => ['lat' => 21.0930, 'lng' => 105.8680],
        'đông trù'    => ['lat' => 21.0850, 'lng' => 105.8620],
        'lại đà'      => ['lat' => 21.0960, 'lng' => 105.8690],
        'tiên hội'    => ['lat' => 21.0980, 'lng' => 105.8640],
        'hội phụ'     => ['lat' => 21.0910, 'lng' => 105.8720],
        'mai hiên'    => ['lat' => 21.0880, 'lng' => 105.8700],

        'mai lâm'     => ['lat' => 21.1080, 'lng' => 105.8820],
        'du nội'      => ['lat' => 21.1050, 'lng' => 105.8850],
        'du ngoại'    => ['lat' => 21.1110, 'lng' => 105.8790],
        'lê xá'       => ['lat' => 21.1020, 'lng' => 105.8870],

        'xuân canh'   => ['lat' => 21.0890, 'lng' => 105.8560],
        'lực canh'    => ['lat' => 21.0850, 'lng' => 105.8520],
        'xuân trạch'  => ['lat' => 21.0920, 'lng' => 105.8590],
        'vạn tinh'    => ['lat' => 21.0870, 'lng' => 105.8580],

        'vân nội'     => ['lat' => 21.1480, 'lng' => 105.8150],
        'vân thượng'  => ['lat' => 21.1460, 'lng' => 105.8120],
        'thượng lộc'  => ['lat' => 21.1500, 'lng' => 105.8180],

        'hải bối'     => ['lat' => 21.1020, 'lng' => 105.8150],
        'đồng nhân'   => ['lat' => 21.0980, 'lng' => 105.8120],
        'kim chung'   => ['lat' => 21.1250, 'lng' => 105.7820],
        'thôn bầu'    => ['lat' => 21.1220, 'lng' => 105.7850],
        'kim nỗ'      => ['lat' => 21.1220, 'lng' => 105.8050],
        'vĩnh ngọc'   => ['lat' => 21.1050, 'lng' => 105.8380],
        'tàm xá'      => ['lat' => 21.0850, 'lng' => 105.8450],
        'tiên dương'  => ['lat' => 21.1380, 'lng' => 105.8280],
        'nguyên khê'  => ['lat' => 21.1680, 'lng' => 105.8350],
        'bắc hồng'    => ['lat' => 21.1750, 'lng' => 105.8100],
        'nam hồng'    => ['lat' => 21.1550, 'lng' => 105.7850],
        'liên hà'     => ['lat' => 21.1550, 'lng' => 105.8950],
        'vân hà'      => ['lat' => 21.1580, 'lng' => 105.9150],
        'thụy lâm'    => ['lat' => 21.1850, 'lng' => 105.8950],
        'đại mạch'    => ['lat' => 21.1080, 'lng' => 105.7580],
        'võng la'     => ['lat' => 21.1020, 'lng' => 105.7750],
        'lộc hà'      => ['lat' => 21.1150, 'lng' => 105.8650],
        'lý nhân'     => ['lat' => 21.1200, 'lng' => 105.8700],
        'hùng sơn'    => ['lat' => 21.1410, 'lng' => 105.8550],
        'đại bi'      => ['lat' => 21.1420, 'lng' => 105.8570],
        'nghĩa lại'   => ['lat' => 21.1400, 'lng' => 105.8520],
        'phúc lộc'    => ['lat' => 21.1380, 'lng' => 105.8510],
    ];

    public function handle()
    {
        $this->info("Bắt đầu phân bổ tọa độ cho các cơ sở kinh doanh Đông Anh...");

        $cat = Category::where('slug', 'co-so-kinh-doanh')->first();
        if (!$cat) {
            $this->error("Không tìm thấy danh mục co-so-kinh-doanh!");
            return 1;
        }

        // 1. Nạp tọa độ gốc từ file hkd_with_phones.json nếu có
        $jsonPath = database_path('data/hkd_with_phones.json');
        $hkdCoordsByPhone = [];
        $hkdCoordsByName = [];
        if (file_exists($jsonPath)) {
            $hkdData = json_decode(file_get_contents($jsonPath), true) ?: [];
            foreach ($hkdData as $item) {
                $lat = (float)($item['latitude'] ?? 0);
                $lng = (float)($item['longitude'] ?? 0);
                // Chỉ lấy các tọa độ thực tế (khác 21.1352, 105.8458)
                if ($lat > 20 && $lng > 105 && !($lat == 21.1352 && $lng == 105.8458)) {
                    if (!empty($item['phone'])) {
                        $hkdCoordsByPhone[trim($item['phone'])] = ['lat' => $lat, 'lng' => $lng];
                    }
                    if (!empty($item['name'])) {
                        $hkdCoordsByName[mb_strtolower(trim($item['name']))] = ['lat' => $lat, 'lng' => $lng];
                    }
                }
            }
            $this->info("Đã nạp " . count($hkdCoordsByPhone) . " tọa độ cụ thể từ hkd_with_phones.json.");
        }

        // 2. Lấy danh sách cơ sở kinh doanh cần cập nhật
        $query = Eatery::where('category_id', $cat->id);
        if (!$this->option('force')) {
            // Chỉ cập nhật những mục đang bị dính tọa độ mặc định
            $query->where(function($q) {
                $q->whereNull('latitude')
                  ->orWhere('latitude', 0)
                  ->orWhere(function($sub) {
                      $sub->where('latitude', 21.1352)->where('longitude', 105.8458);
                  });
            });
        }

        $eateries = $query->get();
        $this->info("Tìm thấy " . $eateries->count() . " cơ sở cần phân bổ lại tọa độ.");

        $updatedCount = 0;
        $matchedJsonCount = 0;
        $matchedAddressCount = 0;
        $randomizedCount = 0;

        foreach ($eateries as $eat) {
            $phone = trim($eat->phone ?? '');
            $nameKey = mb_strtolower(trim($eat->name ?? ''));
            $addrLower = mb_strtolower($eat->address ?? '');

            $targetLat = null;
            $targetLng = null;

            // Ưu tiên 1: Tọa độ chính xác từ JSON gốc
            if ($phone && isset($hkdCoordsByPhone[$phone])) {
                $targetLat = $hkdCoordsByPhone[$phone]['lat'];
                $targetLng = $hkdCoordsByPhone[$phone]['lng'];
                $matchedJsonCount++;
            } elseif ($nameKey && isset($hkdCoordsByName[$nameKey])) {
                $targetLat = $hkdCoordsByName[$nameKey]['lat'];
                $targetLng = $hkdCoordsByName[$nameKey]['lng'];
                $matchedJsonCount++;
            }

            // Ưu tiên 2: Phân tích địa chỉ theo tên xã / thôn tại Đông Anh
            if (!$targetLat && $addrLower) {
                foreach ($this->locations as $keyword => $coord) {
                    if (str_contains($addrLower, $keyword)) {
                        // Thêm độ lệch tự nhiên ngẫu nhiên (~50m - 200m) dựa theo ID để các điểm không trùng khít 100%
                        $offsetLat = (($eat->id * 13) % 200 - 100) / 40000;
                        $offsetLng = (($eat->id * 17) % 200 - 100) / 40000;
                        $targetLat = round($coord['lat'] + $offsetLat, 6);
                        $targetLng = round($coord['lng'] + $offsetLng, 6);
                        $matchedAddressCount++;
                        break;
                    }
                }
            }

            // Ưu tiên 3: Nếu địa chỉ chỉ ghi "Xã Đông Anh" chung chung, phân tán đều quanh trung tâm Đông Anh (~1km)
            if (!$targetLat) {
                $offsetLat = (($eat->id * 31) % 500 - 250) / 25000;
                $offsetLng = (($eat->id * 37) % 500 - 250) / 25000;
                $targetLat = round(21.1365 + $offsetLat, 6);
                $targetLng = round(105.8450 + $offsetLng, 6);
                $randomizedCount++;
            }

            $eat->update([
                'latitude'  => $targetLat,
                'longitude' => $targetLng
            ]);
            $updatedCount++;
        }

        $this->newLine();
        $this->info("=== HOÀN TẤT PHÂN BỔ TỌA ĐỘ ===");
        $this->info("- Tổng số đã cập nhật: {$updatedCount}");
        $this->info("  + Khớp tọa độ thực từ file JSON: {$matchedJsonCount}");
        $this->info("  + Phân bổ theo địa chỉ xã/thôn: {$matchedAddressCount}");
        $this->info("  + Phân bổ đều bán kính Đông Anh: {$randomizedCount}");

        return 0;
    }
}
