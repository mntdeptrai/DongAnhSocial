<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tạo bảng cultural_activities trên tất cả database cần thiết:
 *  - mysql           (food_map)         → danh mục hanh-trinh-di-san
 *  - mysql_culture   (dong_anh_culture_hub) → danh mục discover-dong-anh-community-culture-hub
 */
return new class extends Migration
{
    /** Danh sách các kết nối cần tạo bảng này */
    private array $connections = ['mysql', 'mysql_culture'];

    public function up(): void
    {
        $default = config('database.default');
        $conns = config("database.connections.{$default}.driver") === 'sqlite' ? [$default] : $this->connections;

        foreach ($conns as $conn) {
            if (!Schema::connection($conn)->hasTable('cultural_activities')) {
                Schema::connection($conn)->create('cultural_activities', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('eatery_id');
                    $table->string('name');
                    $table->string('type')->nullable();       // experience, ticket, service, other
                    $table->decimal('price', 12, 2)->nullable();
                    $table->string('unit')->nullable();       // đoàn (10 người), vé, lượt…
                    $table->string('discount_note')->nullable(); // hs/sv/người già giảm 50%
                    $table->text('description')->nullable();
                    $table->string('image_path')->nullable();
                    $table->timestamps();

                    $table->index('eatery_id');
                });
            }
        }
    }

    public function down(): void
    {
        $default = config('database.default');
        $conns = config("database.connections.{$default}.driver") === 'sqlite' ? [$default] : $this->connections;

        foreach ($conns as $conn) {
            Schema::connection($conn)->dropIfExists('cultural_activities');
        }
    }
};
