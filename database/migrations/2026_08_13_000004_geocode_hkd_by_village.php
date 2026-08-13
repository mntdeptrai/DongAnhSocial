<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Phan bo toa do GPS cho 1.184 Ho Kinh Doanh & Doanh Nghiep
     * theo tung Thon / Xom / Tuyen duong tren dia ban huyen Dong Anh.
     */
    public function up(): void
    {
        $villageCoords = [
            // XA XUAN CANH
            'luc canh'      => [21.1128, 105.8425],
            'xuan canh'     => [21.1150, 105.8410],
            'xuan trach'    => [21.1190, 105.8470],
            'van tinh'      => [21.1090, 105.8390],
            'van loc'       => [21.1080, 105.8360],
            // XA DONG HOI
            'dong tru'      => [21.0965, 105.8654],
            'tien hoi'      => [21.1080, 105.8650],
            'lai da'        => [21.1010, 105.8730],
            'hoi phu'       => [21.1060, 105.8580],
            'trung thon'    => [21.0990, 105.8670],
            'dong hoi'      => [21.1020, 105.8680],
            'dong ngan'     => [21.0920, 105.8710],
            // XA MAI LAM
            'mai lam'       => [21.1190, 105.8940],
            'mai hien'      => [21.1140, 105.8980],
            'du noi'        => [21.1180, 105.8920],
            'du ngoai'      => [21.1210, 105.8910],
            'loc ha'        => [21.1210, 105.9010],
            'phuc tho'      => [21.1160, 105.9040],
            'thai binh'     => [21.1130, 105.8910],
            // XA DUC TU
            'duc tu'        => [21.1320, 105.9180],
            'ly nhan'       => [21.1280, 105.9220],
            'dong dau'      => [21.1350, 105.9140],
            'ngoc loi'      => [21.1310, 105.9260],
            'le xa'         => [21.1040, 105.8760],
            'phuc hau'      => [21.1390, 105.9080],
            'thuy loi'      => [21.1750, 105.9080],
            'dao thuc'      => [21.1820, 105.9110],
            // XA CO LOA
            'co loa'        => [21.1430, 105.8710],
            'mach trang'    => [21.1380, 105.8680],
            'cau ca'        => [21.1410, 105.8770],
            'thuc qua'      => [21.1440, 105.8750],
            'dong quan'     => [21.1410, 105.8770],
            'nhoi'          => [21.1450, 105.8690],
            'xom thuong'    => [21.1460, 105.8740],
            // TT DONG ANH & UY NO
            'uy no'         => [21.1410, 105.8560],
            'phan xa'       => [21.1440, 105.8580],
            'dai bi'        => [21.1460, 105.8650],
            'ap to'         => [21.1370, 105.8510],
            'dan di'        => [21.1390, 105.8620],
            'kinh no'       => [21.1480, 105.8520],
            'phuc loc'      => [21.1340, 105.8550],
            'dan mo'        => [21.1490, 105.8430],
            'tien duong'    => [21.1450, 105.8480],
            'co duong'      => [21.1480, 105.8590],
            'cao lo'        => [21.1390, 105.8520],
            'pho to'        => [21.1380, 105.8530],
            'xom cho'       => [21.1380, 105.8530],
            'thon cho'      => [21.1380, 105.8530],
            'xom hau'       => [21.1410, 105.8550],
            'xom dong'      => [21.1420, 105.8570],
            'xom vang'      => [21.1430, 105.8590],
            'xom ngoai'     => [21.1400, 105.8540],
            'xom huong'     => [21.1410, 105.8560],
            'xom ga'        => [21.1390, 105.8550],
            'xom cap'       => [21.1440, 105.8510],
            'xom bai'       => [21.1410, 105.8470],
            'xom trong'     => [21.1400, 105.8520],
            'thon trung'    => [21.1420, 105.8520],
            'thon dong'     => [21.1430, 105.8580],
            'nghia lai'     => [21.1430, 105.8570],
            'cho to'        => [21.1380, 105.8530],
            'quoc lo 3'     => [21.1450, 105.8490],
            'luong quan'    => [21.1570, 105.8780],
            // XA NGUYEN KHE
            'nguyen khe'    => [21.1650, 105.8480],
            'tien hung'     => [21.1645, 105.8520],
            'khoi bac'      => [21.1680, 105.8450],
            // XA VIET HUNG
            'viet hung'     => [21.1580, 105.8820],
            'duc noi'       => [21.1510, 105.8890],
            'gia luong'     => [21.1620, 105.8850],
            'luong quy'     => [21.1550, 105.8790],
            'trung oanh'    => [21.1590, 105.8750],
            'gia loc'       => [21.1610, 105.8810],
            // LIEN HA / VAN HA / THUY LAM
            'thuy lam'      => [21.1780, 105.9050],
            'lien ha'       => [21.1710, 105.9320],
            'van ha'        => [21.1890, 105.9380],
            'thiet ung'     => [21.1870, 105.9350],
            // VINH NGOC / HAI BOI
            'vinh ngoc'     => [21.1120, 105.8150],
            'vinh thanh'    => [21.1120, 105.8150],
            'ngoc chi'      => [21.1080, 105.8210],
            'phuong trach'  => [21.1150, 105.8240],
            'hai boi'       => [21.1050, 105.7950],
            'dong nhan'     => [21.1020, 105.7980],
            'co dien'       => [21.1090, 105.7910],
            // KIM CHUNG / VONG LA / DAI MACH
            'kim chung'     => [21.1210, 105.7680],
            'vong la'       => [21.1020, 105.7700],
            'dai mach'      => [21.1250, 105.7420],
            // NAM HONG / BAC HONG / VAN NOI / KIM NO
            'nam hong'      => [21.1520, 105.7720],
            'bac hong'      => [21.1720, 105.7850],
            'van noi'       => [21.1550, 105.8050],
            'kim no'        => [21.1340, 105.7980],
            // FALLBACK
            'to '           => [21.1405, 105.8495],
            'khu '          => [21.1405, 105.8495],
            'thi tran'      => [21.1405, 105.8495],
        ];

        $catId = DB::table('categories')->where('slug', 'co-so-kinh-doanh')->value('id');
        if (!$catId) return;

        $eateries = DB::table('eateries')
            ->where('category_id', $catId)
            ->select('id', 'address')
            ->get();

        $updated = 0;
        foreach ($eateries as $e) {
            // Chuyen dia chi sang khong dau de so sanh
            $addr = mb_strtolower($e->address ?? '');
            $addrAscii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $addr);
            $assignedLat = null;
            $assignedLng = null;

            foreach ($villageCoords as $kw => $coord) {
                if (str_contains($addrAscii, $kw)) {
                    $jitterLat = mt_rand(-30, 30) / 100000;
                    $jitterLng = mt_rand(-30, 30) / 100000;
                    $assignedLat = round($coord[0] + $jitterLat, 6);
                    $assignedLng = round($coord[1] + $jitterLng, 6);
                    break;
                }
            }

            if ($assignedLat && $assignedLng) {
                DB::table('eateries')->where('id', $e->id)->update([
                    'latitude'   => $assignedLat,
                    'longitude'  => $assignedLng,
                    'updated_at' => now(),
                ]);
                $updated++;
            }
        }
    }

    public function down(): void
    {
        $catId = DB::table('categories')->where('slug', 'co-so-kinh-doanh')->value('id');
        if ($catId) {
            DB::table('eateries')->where('category_id', $catId)->update([
                'latitude'  => 21.1352,
                'longitude' => 105.8458,
            ]);
        }
    }
};
