<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\OcopProduct;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('ocop_products', 'slug')) {
            Schema::table('ocop_products', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name')->index();
            });

            // Populate existing products with unique slugs
            $products = OcopProduct::all();
            $usedSlugs = [];

            foreach ($products as $product) {
                $baseSlug = Str::slug($product->name);
                if (empty($baseSlug)) {
                    $baseSlug = 'san-pham-ocop-' . $product->id;
                }

                $slug = $baseSlug;
                $count = 1;
                while (in_array($slug, $usedSlugs) || OcopProduct::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                    $count++;
                    $slug = $baseSlug . '-' . $count;
                }

                $usedSlugs[] = $slug;
                $product->slug = $slug;
                $product->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('ocop_products', 'slug')) {
            Schema::table('ocop_products', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};
