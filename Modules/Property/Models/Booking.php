<?php

namespace Modules\Property\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Auth\Models\User;
use Modules\Shared\Models\Reminder;
use Modules\Shared\Helpers\CommonHelper;


class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'property_id',
        'check_in',
        'check_out',
        'duration',
        'bedrooms',
        'adults',
        'children',
        'coupon_code',
        'discount',
        'subtotal',
        'tax',
        'total',
        'status',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reminder()
    {
        return $this->hasOne(Reminder::class);
    }

    public function scopeVisibleTo($query, $user)
    {
        // User role: Only see their own bookings
        if ($user->hasRole('user')) {
            $query->where('user_id', $user->id);
        }

        // Admin role: See their own bookings OR bookings for their properties
        if ($user->hasRole('admin')) {
            $query->where(function ($subQuery) use ($user) {
                $subQuery->where('user_id', $user->id)
                    ->orWhereHas('property', function ($propertyQuery) use ($user) {
                        $propertyQuery->where('user_id', $user->id);
                    });
            });
        }
    }

        // Accessors
        public function getRewardPointsAttribute($value)
        {
            $points = CommonHelper::calculateRewardPoints($this->total);
            return $points;
        }
    
        protected $appends = ['reward_points'];
}
