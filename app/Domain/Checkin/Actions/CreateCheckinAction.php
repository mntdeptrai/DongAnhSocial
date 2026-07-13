<?php

namespace App\Domain\Checkin\Actions;

use App\Domain\Checkin\CheckinData;
use App\Models\Checkin;
use App\Models\Eatery;
use App\Models\Category;
use App\Models\Commune;
use App\Services\EateryApiService;

class CreateCheckinAction
{
    public function execute(CheckinData $data, ?string $imagePath): Checkin
    {
        $eateryId = $data->eatery_id;

        if (!empty($data->eatery_slug)) {
            $localEatery = Eatery::where('slug', $data->eatery_slug)->first();
            if ($localEatery) {
                $eateryId = $localEatery->id;
            } else {
                // Fetch external eatery
                $externalEatery = EateryApiService::getEateryBySlug($data->eatery_slug);
                if ($externalEatery) {
                    // Find or create Category
                    $cat = null;
                    if ($externalEatery->category) {
                        $cat = Category::where('slug', $externalEatery->category->slug)->first();
                        if (!$cat) {
                            $cat = Category::create([
                                'name' => $externalEatery->category->name,
                                'slug' => $externalEatery->category->slug,
                            ]);
                        }
                    }

                    // Find or create Commune
                    $com = null;
                    if ($externalEatery->commune) {
                        $com = Commune::where('slug', $externalEatery->commune->slug)->first();
                        if (!$com) {
                            $com = Commune::create([
                                'name' => $externalEatery->commune->name,
                                'slug' => $externalEatery->commune->slug,
                            ]);
                        }
                    }

                    // Create Shadow Eatery
                    $localEatery = Eatery::create([
                        'name' => $externalEatery->name,
                        'slug' => $externalEatery->slug,
                        'address' => $externalEatery->address,
                        'latitude' => $externalEatery->latitude,
                        'longitude' => $externalEatery->longitude,
                        'description' => $externalEatery->description,
                        'image_path' => $externalEatery->image_path,
                        'rating' => $externalEatery->rating,
                        'status' => 'active',
                        'category_id' => $cat ? $cat->id : null,
                        'commune_id' => $com ? $com->id : null,
                    ]);

                    $eateryId = $localEatery->id;
                }
            }
        }

        return Checkin::create([
            'user_id'    => $data->user_id,
            'eatery_id'  => $eateryId,
            'guest_name' => $data->user_id ? null : ($data->guest_name ?? 'Khách vãng lai'),
            'rating'     => $data->rating,
            'comment'    => $data->comment,
            'image_path' => $imagePath,
            'status'     => 'published',
        ]);
    }
}
