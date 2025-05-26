<?php

namespace Modules\Shared\Helpers;

class CommonHelper
{

    public static function calculateRewardPoints(float|int $total): int
    {
        foreach (config('rewards.tiers') as $limit => $points) {
            if ($total <= $limit) {
                return $points;
            }
        }
    
        return config('rewards.default', 600);
    }
    
}
