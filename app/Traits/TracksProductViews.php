<?php

namespace App\Traits;

use App\Models\RecentlyViewed;

trait TracksProductViews
{
    protected function trackProductView($productId)
    {
        $userId = auth()->id();

        if (!$userId) return;

        RecentlyViewed::updateOrCreate(
            [
                'user_id' => $userId,
                'product_id' => $productId
            ],
            [
                'viewed_at' => now()
            ]
        );
    }
}
