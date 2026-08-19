<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Eatery;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $marketId = 17; // Chợ Tó

        // 1. Cập nhật thông tin Chợ Tó trong eateries
        DB::table('eateries')->where('id', $marketId)->update([
            'name' => 'Chợ Tó',
            'category_id' => 8, // Chợ truyền thống
            'address' => '4VP4+V46, Thị trấn Đông Anh, Huyện Đông Anh, Hà Nội',
            'updated_at' => now(),
        ]);

        // 2. Tạo / Cập nhật tài khoản Ban Quản lý Chợ Tó
        $bql = User::where('email', 'bql.choto@foodmap.vn')->first();
        if (!$bql) {
            DB::table('users')->insert([
                'name' => 'Ban Quản lý Chợ Tó',
                'email' => 'bql.choto@foodmap.vn',
                'phone' => '0123654888',
                'password' => Hash::make('123456@'),
                'role' => 'seller',
                'status' => 'active',
                'eatery_id' => $marketId,
                'avatar' => '🏛️',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $bql->update([
                'name' => 'Ban Quản lý Chợ Tó',
                'eatery_id' => $marketId,
                'role' => 'seller',
                'status' => 'active',
            ]);
        }

        // 3. Danh sách 169 hộ kinh doanh Chợ Tó từ Excel
        $merchants = array (
  1 => 
  array (
    'stt' => 1,
    'name' => 'Nguyễn Vân Anh',
    'birth_year' => '1977',
    'address' => 'Xóm Ngoài, Uy Nỗ',
    'cccd' => '001178015051',
    'phone' => '0817298828',
    'category' => 'Chè, Ốc',
  ),
  2 => 
  array (
    'stt' => 2,
    'name' => 'Nguyễn Thuỳ Dung',
    'birth_year' => '1982',
    'address' => 'Xóm Ngoài, Uy Nỗ',
    'cccd' => '034182002461',
    'phone' => '0986702543',
    'category' => 'Quần áo',
  ),
  3 => 
  array (
    'stt' => 3,
    'name' => 'Cao Thị Bích',
    'birth_year' => '',
    'address' => 'Dục nội, Đông Anh',
    'cccd' => '',
    'phone' => '',
    'category' => 'Quần áo',
  ),
  4 => 
  array (
    'stt' => 4,
    'name' => 'Nguyễn Thị Thanh Hương',
    'birth_year' => '1971',
    'address' => 'Tổ 12 Thị trấn Đông Anh',
    'cccd' => '001171004362',
    'phone' => '0852122158',
    'category' => 'Túi xách, giày dép',
  ),
  5 => 
  array (
    'stt' => 5,
    'name' => 'Cao Thị Hạnh',
    'birth_year' => '1967',
    'address' => 'Dục nội, Việt Hùng',
    'cccd' => '001167000639',
    'phone' => '0344838471',
    'category' => 'Túi xách, giày dép',
  ),
  6 => 
  array (
    'stt' => 6,
    'name' => 'Hoàng Thu Hằng',
    'birth_year' => '1978',
    'address' => 'Xóm Chợ, Uy Nỗ',
    'cccd' => '001178018118',
    'phone' => '0984420605',
    'category' => 'Quần áo',
  ),
  7 => 
  array (
    'stt' => 7,
    'name' => 'Cao Thị Lạng',
    'birth_year' => '1967',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001167028938',
    'phone' => '0352528808',
    'category' => 'Quần áo',
  ),
  8 => 
  array (
    'stt' => 8,
    'name' => 'Trần Thị Biên',
    'birth_year' => '1974',
    'address' => 'Dục nội, Việt Hùng',
    'cccd' => '001174016856',
    'phone' => '0388998974',
    'category' => 'Quần áo',
  ),
  9 => 
  array (
    'stt' => 9,
    'name' => 'Trần Thị Thuỳ',
    'birth_year' => '1978',
    'address' => 'Dục nội, Việt Hùng',
    'cccd' => '001178044102',
    'phone' => '0982456057',
    'category' => 'Quần áo',
  ),
  10 => 
  array (
    'stt' => 10,
    'name' => 'Ngô Thị Thanh Huyền',
    'birth_year' => '1991',
    'address' => '',
    'cccd' => '001191047216',
    'phone' => '',
    'category' => 'Quần áo',
  ),
  11 => 
  array (
    'stt' => 11,
    'name' => 'Đình Thị Dậu',
    'birth_year' => '1969',
    'address' => 'Dục nội, Việt Hùng',
    'cccd' => '001169034872',
    'phone' => '0866602536',
    'category' => 'Quần áo',
  ),
  12 => 
  array (
    'stt' => 12,
    'name' => 'Trần Thị Chanh',
    'birth_year' => '1968',
    'address' => 'Dục nội, Việt Hùng',
    'cccd' => '001168024085',
    'phone' => '0345244268',
    'category' => 'Quần áo',
  ),
  13 => 
  array (
    'stt' => 13,
    'name' => 'Hoàng Thị Vui',
    'birth_year' => '1971',
    'address' => 'Dục nội, Việt Hùng',
    'cccd' => '001171021089',
    'phone' => '0397430343',
    'category' => 'Vải, Quần áo',
  ),
  14 => 
  array (
    'stt' => 14,
    'name' => 'Hữu Thị Ninh',
    'birth_year' => '1976',
    'address' => 'Dục nội, Việt Hùng',
    'cccd' => '001176009946',
    'phone' => '0972435795',
    'category' => 'Vải',
  ),
  15 => 
  array (
    'stt' => 15,
    'name' => 'Nguyễn Thị May',
    'birth_year' => '',
    'address' => '',
    'cccd' => '',
    'phone' => '',
    'category' => 'Vải',
  ),
  16 => 
  array (
    'stt' => 16,
    'name' => 'Hoàng Thị Hiệp',
    'birth_year' => '1973',
    'address' => 'Xóm Thượng, Uy Nỗ',
    'cccd' => '001173012609',
    'phone' => '0365468539',
    'category' => 'Quần áo',
  ),
  17 => 
  array (
    'stt' => 17,
    'name' => 'Ngô Thị Thơ',
    'birth_year' => '1961',
    'address' => 'Dục nội, Việt Hùng',
    'cccd' => '010564573',
    'phone' => '0971853093',
    'category' => 'Quần áo',
  ),
  18 => 
  array (
    'stt' => 18,
    'name' => 'Lê Thị Kim Luyến',
    'birth_year' => '1986',
    'address' => 'Nguyên khê, Đông Anh',
    'cccd' => '001186029371',
    'phone' => '0984846228',
    'category' => 'Quần áo',
  ),
  19 => 
  array (
    'stt' => 19,
    'name' => 'Nguyễn Thị Lan Anh',
    'birth_year' => '1985',
    'address' => 'Cổ dương, Tiên Dương',
    'cccd' => '001185007488',
    'phone' => '0396163430',
    'category' => 'Phụ kiện mĩ kí',
  ),
  20 => 
  array (
    'stt' => 20,
    'name' => 'Lê Thị Kim Oanh',
    'birth_year' => '1969',
    'address' => 'Xóm Ngoài, Uy Nỗ',
    'cccd' => '001169040353',
    'phone' => '0382822945',
    'category' => 'Quần áo',
  ),
  21 => 
  array (
    'stt' => 21,
    'name' => 'Nguyễn Thị Lan',
    'birth_year' => '1976',
    'address' => 'Xóm Bến, Kim Nõ',
    'cccd' => '001176002907',
    'phone' => '0366555476',
    'category' => 'Quần áo',
  ),
  22 => 
  array (
    'stt' => 22,
    'name' => 'Đặng Thị Quyên',
    'birth_year' => '1978',
    'address' => 'Văn Thượng, Xuân Canh',
    'cccd' => '001178018524',
    'phone' => '0984036228',
    'category' => 'Quần áo',
  ),
  23 => 
  array (
    'stt' => 23,
    'name' => 'Vũ Thị Thu',
    'birth_year' => '1969',
    'address' => 'Cầu Cả, Cổ Loa',
    'cccd' => '001169016059',
    'phone' => '0984386612',
    'category' => 'Quần áo',
  ),
  24 => 
  array (
    'stt' => 24,
    'name' => 'Nguyễn Thị Lan Ngọc',
    'birth_year' => '1958',
    'address' => 'Xóm Thượng, Uy Nỗ',
    'cccd' => '001158040677',
    'phone' => '0989418822',
    'category' => 'Quần áo',
  ),
  25 => 
  array (
    'stt' => 25,
    'name' => 'Hoàng Thị Yến',
    'birth_year' => '1989',
    'address' => 'Xóm Thượng, Uy Nỗ',
    'cccd' => '025189000070',
    'phone' => '0962556170',
    'category' => 'Quần áo',
  ),
  26 => 
  array (
    'stt' => 26,
    'name' => 'Đặng Thị Hường',
    'birth_year' => '1981',
    'address' => 'Dục nội, Việt Hùng',
    'cccd' => '001181002386',
    'phone' => '0985561955',
    'category' => 'Quần áo',
  ),
  27 => 
  array (
    'stt' => 27,
    'name' => 'Lê Thị Kim Uyên',
    'birth_year' => '1973',
    'address' => 'Đản Dị, Uy nỗ',
    'cccd' => '001173038526',
    'phone' => '0335297460',
    'category' => 'Quần áo',
  ),
  28 => 
  array (
    'stt' => 28,
    'name' => 'Hoàng Thị Luyên',
    'birth_year' => '1983',
    'address' => 'Dục nội, Việt Hùng',
    'cccd' => '001183027731',
    'phone' => '0984391816',
    'category' => 'Quần áo',
  ),
  29 => 
  array (
    'stt' => 29,
    'name' => 'Trần Thị Sự',
    'birth_year' => '1976',
    'address' => 'Xóm Thượng, Uy Nỗ',
    'cccd' => '001176018538',
    'phone' => '0936237905',
    'category' => 'Quần áo',
  ),
  30 => 
  array (
    'stt' => 30,
    'name' => 'Hoàng Thị Hằng',
    'birth_year' => '1964',
    'address' => 'Xóm Hậu, Uy Nỗ',
    'cccd' => '001164045162',
    'phone' => '0355372364',
    'category' => 'Quần áo',
  ),
  31 => 
  array (
    'stt' => 31,
    'name' => 'Đỗ Thị Ngân',
    'birth_year' => '1967',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001167035291',
    'phone' => '0914608365',
    'category' => 'Quần áo',
  ),
  32 => 
  array (
    'stt' => 32,
    'name' => 'Đặng Thị Sen',
    'birth_year' => '1971',
    'address' => 'Xóm Ngoài, Uy Nỗ',
    'cccd' => '001171008168',
    'phone' => '0862364980',
    'category' => 'Quần áo',
  ),
  33 => 
  array (
    'stt' => 33,
    'name' => 'Dương Thị Kim Oanh',
    'birth_year' => '1983',
    'address' => 'Dục nội, Việt Hùng',
    'cccd' => '001183008792',
    'phone' => '094990668',
    'category' => 'Quần áo',
  ),
  34 => 
  array (
    'stt' => 34,
    'name' => 'Dương Thị Kim Oanh',
    'birth_year' => '1982',
    'address' => 'Xóm Thượng, Uy Nỗ',
    'cccd' => '001182029333',
    'phone' => '0824345186',
    'category' => 'Quần áo',
  ),
  35 => 
  array (
    'stt' => 35,
    'name' => 'Hoàng Thị Tuyết',
    'birth_year' => '1968',
    'address' => 'Xóm Hậu, Uy Nỗ',
    'cccd' => '001168040763',
    'phone' => '0359590053',
    'category' => 'Quần áo',
  ),
  36 => 
  array (
    'stt' => 36,
    'name' => 'Cao Thị Ngọc Cánh',
    'birth_year' => '1976',
    'address' => 'Dục nội, Việt Hùng',
    'cccd' => '001176040693',
    'phone' => '0392124929',
    'category' => 'Quần áo',
  ),
  37 => 
  array (
    'stt' => 37,
    'name' => 'Vương Thị Phương',
    'birth_year' => '1974',
    'address' => 'Dục nội, Việt Hùng',
    'cccd' => '001174023309',
    'phone' => '0386039893',
    'category' => 'Quần áo',
  ),
  38 => 
  array (
    'stt' => 38,
    'name' => 'Hoàng Thị Hanh',
    'birth_year' => '1971',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001171010011',
    'phone' => '0972462305',
    'category' => 'Quần áo',
  ),
  39 => 
  array (
    'stt' => 39,
    'name' => 'Nguyễn Cao Miền',
    'birth_year' => '1969',
    'address' => 'Dục nội, Việt Hùng',
    'cccd' => '001070045076',
    'phone' => '0915021004',
    'category' => 'Quần áo',
  ),
  40 => 
  array (
    'stt' => 40,
    'name' => 'Hoàng Thị Hoài',
    'birth_year' => '25853',
    'address' => 'Xóm Thượng, Đông Anh',
    'cccd' => '001170016288',
    'phone' => '0363694384',
    'category' => 'Quần áo',
  ),
  41 => 
  array (
    'stt' => 41,
    'name' => 'Hoàng Thị Hương',
    'birth_year' => '1984',
    'address' => 'Phố Tó, Uy Nỗ',
    'cccd' => '001184030987',
    'phone' => '0916541565',
    'category' => 'Quần áo',
  ),
  42 => 
  array (
    'stt' => 42,
    'name' => 'Lê Thị Hiền Hạnh',
    'birth_year' => '1960',
    'address' => 'Văn Thượng, Xuân Canh',
    'cccd' => '001160020048',
    'phone' => '0966149960',
    'category' => 'Quần áo',
  ),
  43 => 
  array (
    'stt' => 43,
    'name' => 'Đặng Thị Phượng',
    'birth_year' => '1967',
    'address' => 'Phúc Lộc, Uy Nỗ',
    'cccd' => '001167011665',
    'phone' => '0366488172',
    'category' => 'Quần áo',
  ),
  44 => 
  array (
    'stt' => 44,
    'name' => 'Đặng Thị Nguyện',
    'birth_year' => '1962',
    'address' => 'Dục nội, Việt Hùng',
    'cccd' => '001162035887',
    'phone' => '0969101744',
    'category' => 'Quần áo',
  ),
  45 => 
  array (
    'stt' => 45,
    'name' => 'Đinh Thị Thu Hiền',
    'birth_year' => '1987',
    'address' => 'Lỗ Khê, Liên Hà',
    'cccd' => '001187002089',
    'phone' => '0983168407',
    'category' => 'Quần áo',
  ),
  46 => 
  array (
    'stt' => 46,
    'name' => 'Lê Thị Thuý Hằng',
    'birth_year' => '1961',
    'address' => 'Đản Mỗ, Uy nỗ',
    'cccd' => '038161005647',
    'phone' => '0384708099',
    'category' => 'Quần áo',
  ),
  47 => 
  array (
    'stt' => 47,
    'name' => 'Trần Thị Thuý',
    'birth_year' => '1974',
    'address' => 'Xóm Chùa, Cổ Loa',
    'cccd' => '001174011029',
    'phone' => '0983753532',
    'category' => 'Quần áo',
  ),
  48 => 
  array (
    'stt' => 48,
    'name' => 'Trần Thị Quyến',
    'birth_year' => '1970',
    'address' => 'Xóm Mít, Cổ Loa',
    'cccd' => '001170004262',
    'phone' => '0989252741',
    'category' => 'Quần áo',
  ),
  49 => 
  array (
    'stt' => 49,
    'name' => 'Hoàng Thị Lương',
    'birth_year' => '1972',
    'address' => 'Xóm Ngoài, Uy Nỗ',
    'cccd' => '001172043941',
    'phone' => '0976842867',
    'category' => 'Quần áo',
  ),
  50 => 
  array (
    'stt' => 50,
    'name' => 'Đăng Thị Ngọc',
    'birth_year' => '1976',
    'address' => 'Xóm Bãi, Oai nỗ',
    'cccd' => '001176041139',
    'phone' => '0888520490',
    'category' => 'Quần áo',
  ),
  51 => 
  array (
    'stt' => 51,
    'name' => 'Nguyễn Thị Hậu',
    'birth_year' => '1969',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '012260960',
    'phone' => '0932245339',
    'category' => 'Quần áo',
  ),
  52 => 
  array (
    'stt' => 52,
    'name' => 'Nguyễn Thị Yêm',
    'birth_year' => '1963',
    'address' => 'Cầu Cả, Cổ Loa',
    'cccd' => '001163029710',
    'phone' => '0964638571',
    'category' => 'Quần áo',
  ),
  53 => 
  array (
    'stt' => 53,
    'name' => 'Nguyễn Thị Thực',
    'birth_year' => '1969',
    'address' => 'Xóm Thượng, Uy Nỗ',
    'cccd' => '001169042008',
    'phone' => '0389394320',
    'category' => 'Quần áo',
  ),
  54 => 
  array (
    'stt' => 54,
    'name' => 'Vương Thị Mây',
    'birth_year' => '1978',
    'address' => 'Xóm Hậu, Uy Nỗ',
    'cccd' => '001178007095',
    'phone' => '0372693796',
    'category' => 'Quần áo',
  ),
  55 => 
  array (
    'stt' => 55,
    'name' => 'Đặng Thị Kim Nga',
    'birth_year' => '1976',
    'address' => 'Xóm Thượng, Uy Nỗ',
    'cccd' => '001176015943',
    'phone' => '0369961688',
    'category' => 'Quần áo',
  ),
  56 => 
  array (
    'stt' => 56,
    'name' => 'Nguyễn Thị Kiều Liêm',
    'birth_year' => '1984',
    'address' => 'Xóm Hậu, Uy Nỗ',
    'cccd' => '001184024333',
    'phone' => '0833824908',
    'category' => 'Quần áo',
  ),
  57 => 
  array (
    'stt' => 57,
    'name' => 'Nguyễn Hoa Khôi
( Nguyễn Văn Huy)',
    'birth_year' => '1970',
    'address' => 'Cầu Cả, Cổ Loa',
    'cccd' => '001070016657',
    'phone' => '0333250673',
    'category' => 'Quần áo',
  ),
  58 => 
  array (
    'stt' => 58,
    'name' => 'Nguyễn Thị Lan Phương',
    'birth_year' => '1980',
    'address' => 'Xóm Ngoài, Uy Nỗ',
    'cccd' => '001180015991',
    'phone' => '0368882408',
    'category' => 'Quần áo',
  ),
  59 => 
  array (
    'stt' => 59,
    'name' => 'Nguyễn Thị Dung',
    'birth_year' => '1968',
    'address' => 'Cầu Cả, Cổ Loa',
    'cccd' => '001168026065',
    'phone' => '',
    'category' => 'Quần áo',
  ),
  60 => 
  array (
    'stt' => 60,
    'name' => 'Trần Thị Thạch',
    'birth_year' => '1973',
    'address' => 'Xóm Mít, Cổ Loa',
    'cccd' => '001173001275',
    'phone' => '0987505772',
    'category' => 'Quần áo',
  ),
  61 => 
  array (
    'stt' => 61,
    'name' => 'Lê Thị Ánh',
    'birth_year' => '1968',
    'address' => 'Phúc Lộc, Uy Nỗ',
    'cccd' => '011621411',
    'phone' => '',
    'category' => 'Quần áo',
  ),
  62 => 
  array (
    'stt' => 62,
    'name' => 'Nguyễn Thị Dung',
    'birth_year' => '1957',
    'address' => 'Xóm Thượng, Uy Nỗ',
    'cccd' => '024157000159',
    'phone' => '0362632703',
    'category' => 'Mỹ Phẩm',
  ),
  63 => 
  array (
    'stt' => 63,
    'name' => 'Đào Thị Tuyết',
    'birth_year' => '',
    'address' => '',
    'cccd' => '',
    'phone' => '',
    'category' => '',
  ),
  64 => 
  array (
    'stt' => 64,
    'name' => 'Trịnh Thị Bích Hạnh',
    'birth_year' => '1983',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001083036012',
    'phone' => '0947929898',
    'category' => 'Thiết bị điện, nước',
  ),
  65 => 
  array (
    'stt' => 65,
    'name' => 'Mạc Thị Đìa',
    'birth_year' => '1951',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001151005007',
    'phone' => '0966579651',
    'category' => 'Thiết bị điện, nước',
  ),
  66 => 
  array (
    'stt' => 66,
    'name' => 'Vũ Thị Bích Vượng ( Bích)',
    'birth_year' => '1987',
    'address' => 'Xóm Hậu, Uy Nỗ',
    'cccd' => '025187000069',
    'phone' => '0813359650',
    'category' => 'Thiết bị điện, nước',
  ),
  67 => 
  array (
    'stt' => 67,
    'name' => 'Nguyễn Thị Thuỷ',
    'birth_year' => '1975',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001075007508',
    'phone' => '0912354500',
    'category' => 'Thiết bị điện, nước',
  ),
  68 => 
  array (
    'stt' => 68,
    'name' => 'Phạm Đắc Phú',
    'birth_year' => '1956',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001056010863',
    'phone' => '0969749180',
    'category' => '',
  ),
  69 => 
  array (
    'stt' => 69,
    'name' => 'Dương Thu Hường',
    'birth_year' => '1982',
    'address' => 'Xóm Vang, Cổ Loa',
    'cccd' => '001182017273',
    'phone' => '0904023416',
    'category' => 'chăn ga gối đệm',
  ),
  70 => 
  array (
    'stt' => 70,
    'name' => 'Nguyễn Thị Thêm',
    'birth_year' => '1969',
    'address' => 'Tổ 10 Thị trấn Đông Anh',
    'cccd' => '001169013780',
    'phone' => '0389941783',
    'category' => 'chăn ga gối đệm',
  ),
  71 => 
  array (
    'stt' => 71,
    'name' => 'Phạm Ngọc Yến',
    'birth_year' => '1974',
    'address' => 'Xóm Ngoài, Uy Nỗ',
    'cccd' => '001174017100',
    'phone' => '0988443042',
    'category' => '',
  ),
  72 => 
  array (
    'stt' => 72,
    'name' => 'Nguyễn Thị Xuyên',
    'birth_year' => '1980',
    'address' => 'Phố Tó, Uy Nỗ',
    'cccd' => '001180005874',
    'phone' => '0973687780',
    'category' => 'sửa chữa quần áo',
  ),
  73 => 
  array (
    'stt' => 73,
    'name' => 'Tô Xuân Quảng',
    'birth_year' => '1971',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '012260939',
    'phone' => '0904028728',
    'category' => 'Quần áo',
  ),
  74 => 
  array (
    'stt' => 74,
    'name' => 'Nguyễn Thị Thu Hồng',
    'birth_year' => '1947',
    'address' => 'Xóm Ngoài, Uy Nỗ',
    'cccd' => '001147009979',
    'phone' => '0944381966',
    'category' => 'Quần áo',
  ),
  75 => 
  array (
    'stt' => 75,
    'name' => 'Đặng Thị Dung',
    'birth_year' => '1966',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '037058002003',
    'phone' => '0366381588',
    'category' => 'Giầy dép',
  ),
  76 => 
  array (
    'stt' => 76,
    'name' => 'Nguyễn Thị Thanh Thuỷ',
    'birth_year' => '1978',
    'address' => 'Xóm Ngoài, Uy Nỗ',
    'cccd' => '',
    'phone' => '',
    'category' => 'Va li túi sách',
  ),
  77 => 
  array (
    'stt' => 77,
    'name' => 'Ngô Thị Thu Hà',
    'birth_year' => '1979',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001179020899',
    'phone' => '0943308573',
    'category' => 'Quần áo,  giầy dép',
  ),
  78 => 
  array (
    'stt' => 78,
    'name' => 'Nguyễn Thị Mạnh',
    'birth_year' => '1968',
    'address' => 'Xóm Thượng, Uy Nỗ',
    'cccd' => '001168039825',
    'phone' => '0338552081',
    'category' => 'Giầy dép',
  ),
  79 => 
  array (
    'stt' => 79,
    'name' => 'Nguyễn Đức Hải',
    'birth_year' => '1974',
    'address' => 'Phố Tó, Uy Nỗ',
    'cccd' => '001074008422',
    'phone' => '0987264291',
    'category' => 'Giầy dép',
  ),
  80 => 
  array (
    'stt' => 80,
    'name' => 'Hà Bích Nhụ',
    'birth_year' => '1966',
    'address' => 'Xóm Ngoài, Uy Nỗ',
    'cccd' => '001166007364',
    'phone' => '0966213424',
    'category' => 'Đồ gia dụng',
  ),
  81 => 
  array (
    'stt' => 81,
    'name' => 'Nguyễn Thị Kim Dung',
    'birth_year' => '1967',
    'address' => 'Xóm Trongi Uy Nỗ',
    'cccd' => '008167000062',
    'phone' => '0869106908',
    'category' => 'Mỹ Phẩm',
  ),
  82 => 
  array (
    'stt' => 82,
    'name' => 'Đinh Thị Nguyệt Nga',
    'birth_year' => '1971',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '030171001580',
    'phone' => '0948412046',
    'category' => 'sửa chữa quần áo',
  ),
  83 => 
  array (
    'stt' => 83,
    'name' => 'Nguyễn Thị Thu Hà',
    'birth_year' => '1985',
    'address' => 'Xóm Chùa, Cổ Loa',
    'cccd' => '001185013095',
    'phone' => '0975734457',
    'category' => 'Quần áo',
  ),
  84 => 
  array (
    'stt' => 84,
    'name' => 'Nguyễn Thị Thu Hương',
    'birth_year' => '1973',
    'address' => 'Xóm Chùa, Cổ Loa',
    'cccd' => '001173007016',
    'phone' => '0356783153',
    'category' => 'Quần áo',
  ),
  85 => 
  array (
    'stt' => 85,
    'name' => 'Đặng Thị Huệ',
    'birth_year' => '1984',
    'address' => 'Thôn Đìa, Nam Hồng, Đông Anh',
    'cccd' => '001184026589',
    'phone' => '0393073486',
    'category' => 'Quần áo',
  ),
  86 => 
  array (
    'stt' => 86,
    'name' => 'Đặng Thị Vọng',
    'birth_year' => '1967',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001167010456',
    'phone' => '',
    'category' => 'May rèm',
  ),
  87 => 
  array (
    'stt' => 87,
    'name' => 'Nguyễn Thị Thiện',
    'birth_year' => '1968',
    'address' => 'Xóm Ngoài, Uy Nỗ',
    'cccd' => '',
    'phone' => '',
    'category' => 'Đông Lạnh',
  ),
  88 => 
  array (
    'stt' => 88,
    'name' => 'Hoàng Thị Mỹ',
    'birth_year' => '1965',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001165016327',
    'phone' => '',
    'category' => 'Sữa chữa quần áo',
  ),
  89 => 
  array (
    'stt' => 89,
    'name' => 'Đặng Bá Tâm',
    'birth_year' => '1966',
    'address' => 'Xóm Hậu, Uy Nỗ',
    'cccd' => '001066048474',
    'phone' => '0978225513',
    'category' => 'Hàng hoa',
  ),
  90 => 
  array (
    'stt' => 90,
    'name' => 'Nguyễn Thị Lan Anh',
    'birth_year' => '',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '',
    'phone' => '',
    'category' => 'chăn ga gối đệm',
  ),
  91 => 
  array (
    'stt' => 91,
    'name' => 'Nguyễn Thị Quyền',
    'birth_year' => '1959',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '011277310',
    'phone' => '0976998219',
    'category' => 'Hàng gia dụng',
  ),
  92 => 
  array (
    'stt' => 92,
    'name' => 'Nguyễn Thị Huyền',
    'birth_year' => '1976',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001176046027',
    'phone' => '0359305824',
    'category' => 'Túi xách, giày dép',
  ),
  93 => 
  array (
    'stt' => 93,
    'name' => 'Cao Thị Kiểm',
    'birth_year' => '1971',
    'address' => 'Giao Tác, Liên Hà',
    'cccd' => '001171013861',
    'phone' => '0986546215',
    'category' => 'Quần áo',
  ),
  94 => 
  array (
    'stt' => 94,
    'name' => 'Vũ Thị Yến',
    'birth_year' => '1994',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '036194004036',
    'phone' => '0978835792',
    'category' => 'Quần áo',
  ),
  95 => 
  array (
    'stt' => 95,
    'name' => 'Nguyễn Thị Bích',
    'birth_year' => '1960',
    'address' => 'Tuân Lề, Tiên Dương',
    'cccd' => '',
    'phone' => '0904602000',
    'category' => 'sửa chữa quần áo',
  ),
  96 => 
  array (
    'stt' => 96,
    'name' => 'Vương Thị Thu',
    'birth_year' => '1979',
    'address' => 'Xóm Hậu, Uy Nỗ',
    'cccd' => '001179021540',
    'phone' => '0975706358',
    'category' => 'Quần áo',
  ),
  97 => 
  array (
    'stt' => 97,
    'name' => 'Hoàng Thị Nga',
    'birth_year' => '1986',
    'address' => 'Đài Bi, Uy Nỗ',
    'cccd' => '001186013227',
    'phone' => '0969625705',
    'category' => 'Quần áo',
  ),
  98 => 
  array (
    'stt' => 98,
    'name' => 'Vương Thị Nhung',
    'birth_year' => '',
    'address' => '',
    'cccd' => '',
    'phone' => '',
    'category' => 'Quần áo',
  ),
  99 => 
  array (
    'stt' => 99,
    'name' => 'Nguyễn Thị Tứ',
    'birth_year' => '1972',
    'address' => 'Xóm Thượng, Uy Nỗ',
    'cccd' => '001172010476',
    'phone' => '0338380461',
    'category' => 'Quần áo',
  ),
  100 => 
  array (
    'stt' => 100,
    'name' => 'Cao Thị Mai Loan',
    'birth_year' => '1997',
    'address' => 'Dục Nội, Việt Hùng, Đông Anh',
    'cccd' => '001197020667',
    'phone' => '0355784642',
    'category' => 'Quần áo',
  ),
  101 => 
  array (
    'stt' => 101,
    'name' => 'Nguyễn Thị Mến',
    'birth_year' => '1971',
    'address' => 'Xóm Ngoài, Uy Nỗ',
    'cccd' => '001171011164',
    'phone' => '0366222934',
    'category' => 'Quần áo',
  ),
  102 => 
  array (
    'stt' => 102,
    'name' => 'Đào Thị Hồng Thắm',
    'birth_year' => '1989',
    'address' => 'Xóm Gà, Cổ Loa',
    'cccd' => '001189000864',
    'phone' => '0978001098',
    'category' => 'Quần áo',
  ),
  103 => 
  array (
    'stt' => 103,
    'name' => 'Vương Thị Hương',
    'birth_year' => '1990',
    'address' => 'Tổ 1, Thị Trấn Đông Anh',
    'cccd' => '001190020485',
    'phone' => '0376204511',
    'category' => 'Quần áo',
  ),
  104 => 
  array (
    'stt' => 104,
    'name' => 'Nguyễn Thị Mơ',
    'birth_year' => '1984',
    'address' => 'Phan Xá, Uy Nỗ',
    'cccd' => '001184020202',
    'phone' => '0377797368',
    'category' => '',
  ),
  105 => 
  array (
    'stt' => 105,
    'name' => 'Chu Thị Minh',
    'birth_year' => '1983',
    'address' => 'Lỗ Khê, Liên Hà',
    'cccd' => '001183018775',
    'phone' => '0338438256',
    'category' => '',
  ),
  106 => 
  array (
    'stt' => 106,
    'name' => 'Nguyễn Thị Hiền',
    'birth_year' => '1967',
    'address' => 'Xóm Thượng, Uy Nỗ',
    'cccd' => '001167027447',
    'phone' => '0393069113',
    'category' => 'Giầy dép',
  ),
  107 => 
  array (
    'stt' => 107,
    'name' => 'Phạm Thị Thanh Thuỷ',
    'birth_year' => '1984',
    'address' => 'Dục Nội, Việt Hùng, Đông Anh',
    'cccd' => '001184018328',
    'phone' => '0984510582',
    'category' => '',
  ),
  108 => 
  array (
    'stt' => 108,
    'name' => 'Cao Thị Đông',
    'birth_year' => '',
    'address' => 'Dục Nội, Việt Hùng, Đông Anh',
    'cccd' => '',
    'phone' => '',
    'category' => 'Hàng khô',
  ),
  109 => 
  array (
    'stt' => 109,
    'name' => 'Nguyễn Thị Chăm',
    'birth_year' => '1964',
    'address' => 'Khu Đoài, Dục Nội, Việt Hùng',
    'cccd' => '001164011777',
    'phone' => '0971167496',
    'category' => 'Hàng khô',
  ),
  110 => 
  array (
    'stt' => 110,
    'name' => 'Nguyễn Thị An',
    'birth_year' => '1978',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001178018930',
    'phone' => '0849862356',
    'category' => 'Quần áo',
  ),
  111 => 
  array (
    'stt' => 111,
    'name' => 'Nguyễn Thị Hương',
    'birth_year' => '1969',
    'address' => 'Khu Trung, Dục Nội, Việt Hùng',
    'cccd' => '001169039866',
    'phone' => '0399574394',
    'category' => 'Hàng khô',
  ),
  112 => 
  array (
    'stt' => 112,
    'name' => 'Đỗ Thị Thuý Nga',
    'birth_year' => '1978',
    'address' => 'Xóm Thượng, Uy Nỗ',
    'cccd' => '035178008109',
    'phone' => '0962572407',
    'category' => 'sửa chữa quần áo',
  ),
  113 => 
  array (
    'stt' => 113,
    'name' => 'Nguyễn Thị Thanh Hà',
    'birth_year' => '1976',
    'address' => 'Cầu Cả, Cổ Loa',
    'cccd' => '001176003015',
    'phone' => '0352458637',
    'category' => 'Hàng khô',
  ),
  114 => 
  array (
    'stt' => 114,
    'name' => 'Đặng Thị Phượng',
    'birth_year' => '1968',
    'address' => 'Khu Đoài, Dục Nội, Việt Hùng',
    'cccd' => '001168018354',
    'phone' => '0375286097',
    'category' => 'Hàng khô',
  ),
  115 => 
  array (
    'stt' => 115,
    'name' => 'Đăng Thị Hằng Hà',
    'birth_year' => '1992',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001192042610',
    'phone' => '0373391992',
    'category' => 'Quần áo',
  ),
  116 => 
  array (
    'stt' => 116,
    'name' => 'Nguyễn Thuỳ Dương',
    'birth_year' => '1981',
    'address' => 'Đài Bi, Uy Nỗ',
    'cccd' => '001181044256',
    'phone' => '0974382681',
    'category' => 'Quần áo',
  ),
  117 => 
  array (
    'stt' => 117,
    'name' => 'Hà Thị Nhung',
    'birth_year' => '1983',
    'address' => 'Xóm Thượng, Cổ Loa',
    'cccd' => '034183005274',
    'phone' => '0969569022',
    'category' => 'SC quần áo',
  ),
  118 => 
  array (
    'stt' => 118,
    'name' => 'Đào Thị Nhàn',
    'birth_year' => '',
    'address' => '',
    'cccd' => '',
    'phone' => '',
    'category' => 'SC quần áo',
  ),
  119 => 
  array (
    'stt' => 119,
    'name' => 'Nguyễn Thị Mây',
    'birth_year' => '1989',
    'address' => '',
    'cccd' => '001189007764',
    'phone' => '0339461518',
    'category' => 'Quần áo',
  ),
  120 => 
  array (
    'stt' => 120,
    'name' => 'Nguyễn Thị Hạnh',
    'birth_year' => '1974',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => ';001174016876',
    'phone' => '0332239386',
    'category' => 'sửa chữa quần áo',
  ),
  121 => 
  array (
    'stt' => 121,
    'name' => 'Nguyễn Thị Tuyết',
    'birth_year' => '1983',
    'address' => 'Xóm Hậu, Uy Nỗ',
    'cccd' => '001183009448',
    'phone' => '0912213090',
    'category' => 'Quần áo',
  ),
  122 => 
  array (
    'stt' => 122,
    'name' => 'Nguyễn Thị Hường',
    'birth_year' => '1990',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '030190001478',
    'phone' => '0986526790',
    'category' => 'SC quần áo',
  ),
  123 => 
  array (
    'stt' => 123,
    'name' => 'Nguyễn Thị Hoàn',
    'birth_year' => '1979`',
    'address' => 'Xóm Hậu, Uy Nỗ',
    'cccd' => '001179018479',
    'phone' => '0983573301',
    'category' => 'Quần áo',
  ),
  124 => 
  array (
    'stt' => 124,
    'name' => 'Nguyễn Thị Ngọc Bích',
    'birth_year' => '1973',
    'address' => 'Đài Bi, Uy Nỗ',
    'cccd' => '',
    'phone' => '0987479019',
    'category' => 'Sành xứ',
  ),
  125 => 
  array (
    'stt' => 125,
    'name' => 'Hoàng Thị Thanh Hải',
    'birth_year' => '1981',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001181023345',
    'phone' => '0977528581',
    'category' => 'Quần áo',
  ),
  126 => 
  array (
    'stt' => 126,
    'name' => 'Hoàng Thị Tiện',
    'birth_year' => '17/2/1983',
    'address' => 'Xóm trong, Uy Nỗ',
    'cccd' => '001183029214',
    'phone' => '0337953208',
    'category' => 'Giầy dép',
  ),
  127 => 
  array (
    'stt' => 127,
    'name' => 'Hoàng Thị Hương',
    'birth_year' => '25/9/1978',
    'address' => 'Đài Bi, Uy Nỗ',
    'cccd' => '001178018929',
    'phone' => '0358719003',
    'category' => 'Giầy dép',
  ),
  128 => 
  array (
    'stt' => 128,
    'name' => 'Nguyễn Thị Thu Hoài',
    'birth_year' => '28165',
    'address' => 'Phúc Lộc , Uy Nỗ',
    'cccd' => '001177019661',
    'phone' => '0943679223',
    'category' => 'Giầy dép',
  ),
  129 => 
  array (
    'stt' => 129,
    'name' => 'Nguyễn Thị Thanh Hà',
    'birth_year' => '23/7/1967',
    'address' => 'Tổ 13 Thị trấn Đông Anh',
    'cccd' => '001167008911',
    'phone' => '0983763745',
    'category' => 'Giầy dép',
  ),
  130 => 
  array (
    'stt' => 130,
    'name' => 'Nguyễn Thị Sáu',
    'birth_year' => '14/6/1968',
    'address' => 'Lực Canh, Xuân Canh',
    'cccd' => '001168013727',
    'phone' => '0983891814',
    'category' => 'Giầy dép',
  ),
  131 => 
  array (
    'stt' => 131,
    'name' => 'Đặng Bá Vui',
    'birth_year' => '26215',
    'address' => 'Xóm Hậu, Uy Nỗ',
    'cccd' => '001071009306',
    'phone' => '0987325310',
    'category' => 'Giầy dép',
  ),
  132 => 
  array (
    'stt' => 132,
    'name' => 'Vương Thị Loan',
    'birth_year' => '17/10/1977',
    'address' => 'Xóm trong, Uy Nỗ',
    'cccd' => '001177019662',
    'phone' => '0766115683',
    'category' => 'Quần áo',
  ),
  133 => 
  array (
    'stt' => 133,
    'name' => 'Nguyễn Thị Hoa',
    'birth_year' => '23377',
    'address' => 'Xóm Ngoài, Uy Nỗ',
    'cccd' => '010484091',
    'phone' => '0989424128',
    'category' => 'Quần áo',
  ),
  134 => 
  array (
    'stt' => 134,
    'name' => 'Nguyễn Thị Bích Ngọc',
    'birth_year' => '33733',
    'address' => 'Xóm Hậu, Uy Nỗ',
    'cccd' => '013172073',
    'phone' => '0373569412',
    'category' => 'Quần áo',
  ),
  135 => 
  array (
    'stt' => 135,
    'name' => 'Nguyễn Thị Lan Hương',
    'birth_year' => '20/7/1989',
    'address' => 'Phan Xá, Uy nỗ',
    'cccd' => '001189016427',
    'phone' => '0914880166',
    'category' => 'Quần áo',
  ),
  136 => 
  array (
    'stt' => 136,
    'name' => 'Nguyễn Thị Hà',
    'birth_year' => '17/5/1979',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001179001370',
    'phone' => '0386317299',
    'category' => 'Quần áo',
  ),
  137 => 
  array (
    'stt' => 137,
    'name' => 'Đồng Đạo Đương',
    'birth_year' => '24746',
    'address' => 'Cầu Cả, cổ Loa',
    'cccd' => '011884190',
    'phone' => '0976842867',
    'category' => 'Quần áo',
  ),
  138 => 
  array (
    'stt' => 138,
    'name' => 'Nguyễn Thị Nhinh',
    'birth_year' => '30113',
    'address' => 'XómTrong, Uy Nỗ',
    'cccd' => '001182022974',
    'phone' => '0961235568',
    'category' => 'Quần áo',
  ),
  139 => 
  array (
    'stt' => 139,
    'name' => 'Nguyễn Văn Hiếu',
    'birth_year' => '29257',
    'address' => 'Đài Bi, Uy Nỗ',
    'cccd' => '001080015000',
    'phone' => '0983573530',
    'category' => 'Quần áo',
  ),
  140 => 
  array (
    'stt' => 140,
    'name' => 'Hoàng Thị Giang',
    'birth_year' => '27396',
    'address' => 'Xóm Ngoài, Uy Nỗ',
    'cccd' => '012685467',
    'phone' => '0968015437',
    'category' => 'Chè, Ốc',
  ),
  141 => 
  array (
    'stt' => 141,
    'name' => 'Hoàng Thị Gấm',
    'birth_year' => '27405',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001175023520',
    'phone' => '0987942827',
    'category' => 'Mũ nón',
  ),
  142 => 
  array (
    'stt' => 142,
    'name' => 'Vương Thị Nga',
    'birth_year' => '28036',
    'address' => 'Đản Mỗ, Uy Nỗ',
    'cccd' => '01211570',
    'phone' => '0984088357',
    'category' => 'Mỹ Phẩm',
  ),
  143 => 
  array (
    'stt' => 143,
    'name' => 'Nguyễn Thị Lượng',
    'birth_year' => '29806',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001181049579',
    'phone' => '0343041477',
    'category' => 'Quần áo',
  ),
  144 => 
  array (
    'stt' => 144,
    'name' => 'Ngô Thị Chí',
    'birth_year' => '24450',
    'address' => 'Lực Canh, Xuân Canh',
    'cccd' => '001166007562',
    'phone' => '0944387011',
    'category' => 'Giầy dép',
  ),
  145 => 
  array (
    'stt' => 145,
    'name' => 'Đặng Thị Đoan Trang',
    'birth_year' => '30137',
    'address' => 'Xóm Trong, Uy Nỗ',
    'cccd' => '001182020563',
    'phone' => '0966061925',
    'category' => 'Quần áo',
  ),
  146 => 
  array (
    'stt' => 146,
    'name' => 'Nguyễn Thị Thu Hà',
    'birth_year' => '29/5/1974',
    'address' => 'Xóm Nhì, Vân Nội',
    'cccd' => '001174001296',
    'phone' => '0984032159',
    'category' => 'Giầy dép',
  ),
  147 => 
  array (
    'stt' => 147,
    'name' => 'Nguyễn Kim Nhung',
    'birth_year' => '28/2/1981',
    'address' => 'Phố Tó, Uy Nỗ',
    'cccd' => '001181024461',
    'phone' => '0977765472',
    'category' => 'Quần áo,  giầy dép',
  ),
  148 => 
  array (
    'stt' => 148,
    'name' => 'Nguyễn Thị Ngà',
    'birth_year' => '30/5/1995',
    'address' => 'Mạch Tràng, Cổ Loa',
    'cccd' => '001195001802',
    'phone' => '0395227031',
    'category' => 'Quần áo',
  ),
  149 => 
  array (
    'stt' => 149,
    'name' => 'Nguyễn Thị Hiền',
    'birth_year' => '',
    'address' => 'Xóm Ngoài, Uy Nỗ',
    'cccd' => '',
    'phone' => '',
    'category' => 'SC Quần áo',
  ),
  150 => 
  array (
    'stt' => 150,
    'name' => 'Nguyễn Thị Huệ
Nguyễn Thị Liên',
    'birth_year' => '24998',
    'address' => 'Xóm Ngoài, Uy Nỗ',
    'cccd' => '001168009136',
    'phone' => '0974587468',
    'category' => 'Quần áo',
  ),
  152 => 
  array (
    'stt' => 152,
    'name' => 'Trần Thị Phượng',
    'birth_year' => '29/10/1984',
    'address' => 'Đản Dị, Uy Nỗ',
    'cccd' => '001184030411',
    'phone' => '0912453159',
    'category' => 'Quần áo',
  ),
  153 => 
  array (
    'stt' => 153,
    'name' => 'Trần Thị Lưu',
    'birth_year' => '18/5/1990',
    'address' => 'Phú Khu, Văn Lang,
 Hưng Hà, Thái Bình',
    'cccd' => '027190000754',
    'phone' => '0394404547',
    'category' => 'Quần áo',
  ),
  154 => 
  array (
    'stt' => 154,
    'name' => 'Vũ Thị Tuyết',
    'birth_year' => '32116',
    'address' => 'Tổ 38 Thị Trấn Đông Anh',
    'cccd' => '036187009459',
    'phone' => '0915664588',
    'category' => 'Quần áo',
  ),
  155 => 
  array (
    'stt' => 155,
    'name' => 'Hưũ Thị Phượng',
    'birth_year' => '28/2/1976',
    'address' => 'Khu Trung,  Việt Hùng',
    'cccd' => '001176013012',
    'phone' => '0372281467',
    'category' => 'Quần áo',
  ),
  156 => 
  array (
    'stt' => 156,
    'name' => 'Nguyễn Việt Hà',
    'birth_year' => '27030',
    'address' => 'Cổ Dương, Tiên Dương',
    'cccd' => '001174005589',
    'phone' => '0979327671',
    'category' => 'Quần áo',
  ),
  157 => 
  array (
    'stt' => 157,
    'name' => 'Nguyễn Thị Ngọc',
    'birth_year' => '25/11/1983',
    'address' => 'Xóm trong, Uy Nỗ',
    'cccd' => '001183014901',
    'phone' => '0334793850',
    'category' => 'Mỹ Phẩm',
  ),
  158 => 
  array (
    'stt' => 158,
    'name' => 'Nguyễn Việt Hòa',
    'birth_year' => '25/12/1978',
    'address' => 'Tổ 39 Thị Trấn Đông Anh',
    'cccd' => '015178000096',
    'phone' => '0986228463',
    'category' => 'Quần áo',
  ),
  159 => 
  array (
    'stt' => 159,
    'name' => 'Trịnh Thị Vượng',
    'birth_year' => '20/10/1978',
    'address' => 'Xóm Thượng, Uy Nỗ',
    'cccd' => '025178000130',
    'phone' => '0978860278',
    'category' => 'Giầy dép',
  ),
  160 => 
  array (
    'stt' => 160,
    'name' => 'Nguyễn Ngọc Lương',
    'birth_year' => '28308',
    'address' => 'Tổ 36 Thị Trấn Đông Anh',
    'cccd' => '001177014514',
    'phone' => '0966537177',
    'category' => 'Mũ nón',
  ),
  161 => 
  array (
    'stt' => 161,
    'name' => 'Nguyễn Minh Ngọc',
    'birth_year' => '28501',
    'address' => 'Xóm Trong,Uy Nỗ- ĐA',
    'cccd' => '001178008743',
    'phone' => '0906435499',
    'category' => 'Mũ nón',
  ),
  162 => 
  array (
    'stt' => 162,
    'name' => 'Hoàng Thị Vóc( Nhung)',
    'birth_year' => '21/5/1979',
    'address' => 'Phan Xá, Uy Nỗ',
    'cccd' => '001179009398',
    'phone' => '0366555696',
    'category' => 'Quần áo',
  ),
  163 => 
  array (
    'stt' => 163,
    'name' => 'Hoàng Thị Tú Anh',
    'birth_year' => '26035',
    'address' => 'Đình Trung, Xuân Nộn',
    'cccd' => '001171006048',
    'phone' => '0977973410',
    'category' => 'Phụ kiện mĩ kí',
  ),
  164 => 
  array (
    'stt' => 164,
    'name' => 'Nguyễn Văn Tuyến',
    'birth_year' => '',
    'address' => '',
    'cccd' => '',
    'phone' => '',
    'category' => 'tạp hoá Bánh kẹo',
  ),
  165 => 
  array (
    'stt' => 165,
    'name' => 'Lê Thị Thân',
    'birth_year' => '',
    'address' => '',
    'cccd' => '',
    'phone' => '',
    'category' => 'tạp hoá Bánh kẹo',
  ),
  166 => 
  array (
    'stt' => 166,
    'name' => 'Đỗ Thị Hoà',
    'birth_year' => '',
    'address' => '',
    'cccd' => '',
    'phone' => '',
    'category' => 'tạp hoá Bánh kẹo',
  ),
  167 => 
  array (
    'stt' => 167,
    'name' => 'Nguyễn Thị Hồng Minh',
    'birth_year' => '',
    'address' => '',
    'cccd' => '',
    'phone' => '',
    'category' => 'tạp hoá Bánh kẹo',
  ),
  168 => 
  array (
    'stt' => 168,
    'name' => 'Nguyễn Thị Hà',
    'birth_year' => '',
    'address' => '',
    'cccd' => '',
    'phone' => '',
    'category' => 'tạp hoá Bánh kẹo',
  ),
  169 => 
  array (
    'stt' => 169,
    'name' => 'Hoàng Thị Thuý',
    'birth_year' => '',
    'address' => '',
    'cccd' => '',
    'phone' => '',
    'category' => 'tạp hoá Bánh kẹo',
  ),
  170 => 
  array (
    'stt' => 170,
    'name' => 'Trần Thị Kim Oanh',
    'birth_year' => '',
    'address' => '',
    'cccd' => '',
    'phone' => '',
    'category' => 'tạp hoá Bánh kẹo',
  ),
);

        // Xóa các gian hàng cũ thuộc Chợ Tó để nạp lại đúng chuẩn 169 sạp độc lập
        DB::table('ocop_products')->where('eatery_id', $marketId)->delete();

        foreach ($merchants as $stt => $m) {
            $stallId = 2055 + $stt;
            $email = "seller.choto.{$stt}@foodmap.vn";
            $phone = $m['phone'] ?: '';
            $name = $m['name'] ?: "Hộ kinh doanh số {$stt}";
            $cat = $m['category'] ?: 'Bách hóa tổng hợp';

            // Tạo mã QR VietQR nếu có số điện thoại
            $hasPhone = !empty($phone);
            $bankName = $hasPhone ? 'MBBank' : null;
            $bankAccount = $hasPhone ? $phone : null;
            $qrUrl = $hasPhone ? "https://api.vietqr.io/image/970422-{$phone}-compact.png?accountName=" . urlencode($name) : null;

            // Xử lý tạo / cập nhật User
            $user = User::where('email', $email)->first();

            if (!$user) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone ?: 'Cần cập nhật thông tin',
                    'password' => Hash::make('123456@'),
                    'role' => 'seller',
                    'status' => 'active',
                    'eatery_id' => $marketId,
                    'stall_id' => $stallId,
                    'bank_name' => $bankName,
                    'bank_account' => $bankAccount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $user->update([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone ?: 'Cần cập nhật thông tin',
                    'role' => 'seller',
                    'status' => 'active',
                    'eatery_id' => $marketId,
                    'stall_id' => $stallId,
                    'bank_name' => $bankName,
                    'bank_account' => $bankAccount,
                ]);
                $userId = $user->id;
            }

            // Xử lý tạo Gian hàng với stall_name duy nhất
            $stallName = "Gian hàng " . $cat . " " . $name . " (Hộ {$stt})";
            $descParts = [];
            if (!empty($m['address'])) $descParts[] = "Địa chỉ: " . $m['address'];
            if (!empty($m['birth_year'])) $descParts[] = "Năm sinh: " . $m['birth_year'];
            if (!empty($m['cccd'])) $descParts[] = "CCCD: " . $m['cccd'];
            $desc = implode(' | ', $descParts);

            DB::table('ocop_products')->insert([
                'id' => $stallId,
                'eatery_id' => $marketId,
                'user_id' => $userId,
                'stall_name' => $stallName,
                'name' => $cat,
                'seller_name' => $name,
                'seller_phone' => $phone ?: 'Cần cập nhật thông tin',
                'description' => $desc,
                'bank_name' => $bankName,
                'bank_account' => $bankAccount,
                'bank_holder' => $hasPhone ? $name : null,
                'qr_code_path' => $qrUrl,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
