<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connections = ['mysql', 'mysql_market'];

        foreach ($connections as $conn) {
            try {
                if (!Schema::connection($conn)->hasTable('ocop_certified_products')) {
                    Schema::connection($conn)->create('ocop_certified_products', function (Blueprint $table) {
                        $table->id();
                        $table->unsignedBigInteger('eatery_id')->nullable()->index();
                        $table->unsignedBigInteger('user_id')->nullable()->index();
                        $table->string('name');
                        $table->string('slug')->nullable()->index();
                        $table->decimal('price', 12, 2)->nullable();
                        $table->string('unit', 100)->nullable();
                        $table->string('star_rating', 50)->nullable(); // e.g. "3 sao", "4 sao", "5 sao"
                        $table->text('description')->nullable();
                        $table->longText('story')->nullable();
                        $table->string('artisans')->nullable();
                        $table->string('heritage_year', 50)->nullable();
                        $table->text('fun_fact')->nullable();
                        $table->string('audio_narrative', 500)->nullable();
                        $table->string('image_path', 500)->nullable();
                        $table->json('ingredients')->nullable();
                        $table->json('timeline')->nullable();
                        $table->timestamps();
                    });
                }

                // Copy legitimate OCOP products from ocop_products table into ocop_certified_products
                if (Schema::connection($conn)->hasTable('ocop_products')) {
                    $existingOcop = DB::connection($conn)->table('ocop_products')
                        ->where(function ($query) {
                            $query->whereNotNull('star_rating')
                                  ->where('star_rating', '!=', '')
                                  ->orWhereBetween('id', [1, 28]);
                        })
                        ->whereNotIn(DB::raw('LOWER(TRIM(name))'), [
                            'ăn chín',
                            'bách hóa tổng hợp',
                            'hàng khô',
                            'rau củ quả',
                            'thịt gia súc gia cầm',
                            'thực phẩm tươi sống',
                            'quần áo may mặc',
                            'giày dép túi xách',
                            'đồ gia dụng'
                        ])
                        ->get();

                    foreach ($existingOcop as $item) {
                        $slug = $item->slug ?? Str::slug($item->name);
                        
                        // Check if already copied
                        $exists = DB::connection($conn)->table('ocop_certified_products')
                            ->where('id', $item->id)
                            ->exists();

                        $data = [
                            'id'              => $item->id,
                            'eatery_id'       => $item->eatery_id ?? null,
                            'user_id'         => $item->user_id ?? null,
                            'name'            => $item->name,
                            'slug'            => $slug,
                            'price'           => $item->price ?? 0,
                            'unit'            => $item->unit ?? 'sản phẩm',
                            'star_rating'     => $item->star_rating ?? '4 sao',
                            'description'     => $item->description ?? null,
                            'story'           => $item->story ?? null,
                            'artisans'        => $item->artisans ?? null,
                            'heritage_year'   => $item->heritage_year ?? null,
                            'fun_fact'        => $item->fun_fact ?? null,
                            'audio_narrative' => $item->audio_narrative ?? null,
                            'image_path'      => $item->image_path ?? null,
                            'ingredients'     => $item->ingredients ?? null,
                            'timeline'        => $item->timeline ?? null,
                            'created_at'      => $item->created_at ?? now(),
                            'updated_at'      => $item->updated_at ?? now(),
                        ];

                        if ($exists) {
                            DB::connection($conn)->table('ocop_certified_products')
                                ->where('id', $item->id)
                                ->update($data);
                        } else {
                            DB::connection($conn)->table('ocop_certified_products')->insert($data);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Ignore connection errors if database connection is optional
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connections = ['mysql', 'mysql_market'];
        foreach ($connections as $conn) {
            try {
                Schema::connection($conn)->dropIfExists('ocop_certified_products');
            } catch (\Throwable $e) {}
        }
    }
};
