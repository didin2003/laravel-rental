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

    public static function calculateGuestLevel(float|int $total): string
    {
        foreach (config('guest_level.tiers') as $limit => $level) {
            if ($total <= $limit) {
                return $level;
            }
        }
    
        return config('guest_level.default', 'Pro Explorer');
    }
    
}
