<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('wellness_doctors')) {
            Schema::create('wellness_doctors', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('eatery_id')->index();
                $table->string('name');
                $table->string('title')->nullable(); // Trưởng khoa, Bác sĩ CK1, Y sĩ, Điều dưỡng...
                $table->string('specialty')->nullable(); // Chuyên khoa: Nội, Nhi, BHYT, Cấp cứu...
                $table->string('duty_schedule')->nullable(); // Lịch trực: Thứ 2 - Thứ 6 (07:30 - 17:00), Trực 24/7...
                $table->string('phone')->nullable();
                $table->text('avatar')->nullable();
                $table->string('status')->default('active'); // active / inactive
                $table->timestamps();
            });
        }

        // Seed cán bộ y tế nòng cốt cho Trạm Y tế xã Đông Anh
        $db = DB::connection('mysql_market');
        $dongAnhStation = $db->table('eateries')->where('slug', 'tram-y-te-xa-dong-anh')->first();

        if ($dongAnhStation) {
            $doctors = [
                [
                    'eatery_id' => $dongAnhStation->id,
                    'name' => 'BS. Nguyễn Thu Hà',
                    'title' => 'Giám đốc Trạm Y tế',
                    'specialty' => 'Quản lý Y tế & Khám tổng quát',
                    'duty_schedule' => 'Thứ 2 - Thứ 6 (07:30 - 17:00)',
                    'phone' => '0389928304',
                    'avatar' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=400&q=80',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'eatery_id' => $dongAnhStation->id,
                    'name' => 'BS. Nguyễn Thị Hậu',
                    'title' => 'Phó Giám đốc Trạm Y tế',
                    'specialty' => 'Khám Chữa Bệnh & Điều trị BHYT',
                    'duty_schedule' => 'Thứ 2 - Thứ 7 (07:30 - 17:00)',
                    'phone' => '0936534226',
                    'avatar' => 'https://images.unsplash.com/photo-1594824813566-88855ce75341?auto=format&fit=crop&w=400&q=80',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'eatery_id' => $dongAnhStation->id,
                    'name' => 'BS. Ngô Thị Bích Liên',
                    'title' => 'Tổ trưởng Khám Chữa Bệnh',
                    'specialty' => 'KCB Ban đầu, Bệnh Mãn Tính & Lao/HIV',
                    'duty_schedule' => 'Trực Cấp Cứu Luân Khoa 24/7',
                    'phone' => '0976551863',
                    'avatar' => 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=400&q=80',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'eatery_id' => $dongAnhStation->id,
                    'name' => 'BS. Lê Đàm Hải Yến',
                    'title' => 'Tổ trưởng Tổ phòng bệnh',
                    'specialty' => 'Tiêm Chủng Mở Rộng & Phòng Chống Dịch',
                    'duty_schedule' => 'Thứ 2 - Thứ 6 & Lịch Tiêm Định Kỳ',
                    'phone' => '0363551036',
                    'avatar' => 'https://images.unsplash.com/photo-1582750433449-648ed127bb54?auto=format&fit=crop&w=400&q=80',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($doctors as $doc) {
                $exists = $db->table('wellness_doctors')
                    ->where('eatery_id', $doc['eatery_id'])
                    ->where('name', $doc['name'])
                    ->first();
                if (!$exists) {
                    $db->table('wellness_doctors')->insert($doc);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wellness_doctors');
    }
};
