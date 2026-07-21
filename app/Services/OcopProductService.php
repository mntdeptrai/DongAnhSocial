<?php

namespace App\Services;

use App\Domain\OcopProduct\OcopProductData;
use App\Domain\OcopProduct\Actions\CreateOcopProductAction;
use App\Domain\OcopProduct\Actions\UpdateOcopProductAction;
use App\Helpers\R2Helper;
use App\Models\OcopProduct;
use App\Services\EateryApiService;

class OcopProductService
{
    public function __construct(
        protected CreateOcopProductAction $createAction,
        protected UpdateOcopProductAction $updateAction
    ) {}

    public function create(OcopProductData $data, ?string $connName = null): OcopProduct
    {
        $imagePath = $this->resolveImagePath($data->image, $data->image_url);
        return $this->createAction->execute($data, $imagePath, $connName ?: 'mysql_market');
    }

    public function update($id, OcopProductData $data, ?string $connName = null): OcopProduct
    {
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        $product = null;
        $activeConn = $connName;

        if ($connName) {
            $product = OcopProduct::on($connName)->find($id);
        } else {
            foreach ($connections as $conn) {
                $p = OcopProduct::on($conn)->find($id);
                if ($p) {
                    $product = $p;
                    $activeConn = $conn;
                    break;
                }
            }
        }

        if (!$product) {
            throw new \Exception('Sản phẩm OCOP không tồn tại!');
        }

        $imagePath = $product->image_path;
        if ($data->image) {
            $imagePath = R2Helper::upload($data->image, 'ocop');
        } elseif ($data->image_url) {
            $imagePath = $this->resolveImagePath(null, $data->image_url);
        }

        return $this->updateAction->execute($product, $data, $imagePath);
    }

    public function delete($id): bool
    {
        return EateryApiService::deleteOcopProduct($id);
    }

    protected function resolveImagePath($imageFile, ?string $imageUrl): ?string
    {
        if ($imageFile) {
            return R2Helper::upload($imageFile, 'ocop');
        }

        if ($imageUrl) {
            if (preg_match('/(?:drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?id=))([a-zA-Z0-9_-]{25,50})/i', $imageUrl, $matches)) {
                return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
            }
            return $imageUrl;
        }

        return null;
    }
}
