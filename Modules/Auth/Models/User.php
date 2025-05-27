<?php

namespace Modules\Auth\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Laravel\Sanctum\HasApiTokens;
use Modules\Shared\Models\Reminder;
use Modules\Shared\Helpers\CommonHelper;

class User extends Authenticatable implements LaratrustUser
{
    use HasApiTokens, HasFactory, HasRolesAndPermissions, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function profile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function ownCoupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    // Accessors
    public function getPhoneAttribute($value)
    {
        return $this->profile->phone ?? null;
    }

    public function getGuestLevelAttribute($value)
    {
        $level = CommonHelper::calculateGuestLevel($this->profile->reward_points);
        return $level;
    }

    protected $appends = ['phone', 'guest_level'];
}
