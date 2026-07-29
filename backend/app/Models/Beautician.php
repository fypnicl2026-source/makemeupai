<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Beautician extends Model
{
    public const GENDER_FOCUS = ['male', 'female', 'unisex'];

    public const FEMALE_SERVICES = [
        'Makeup Session',
        'Hairstyling',
        'Mehndi',
        'Bridal Package',
    ];

    public const MALE_SERVICES = [
        'Haircut & Styling',
        'Beard Grooming',
        'Groom Package',
        'Party Styling',
    ];

    protected $fillable = [
        'name',
        'salon_name',
        'bio',
        'city',
        'area',
        'gender_focus',
        'specializations',
        'hourly_rate',
        'skill_badge',
        'profile_photo',
        'is_available',
        'avg_rating',
    ];

    protected function casts(): array
    {
        return [
            'specializations' => 'array',
            'is_available' => 'boolean',
            'hourly_rate' => 'decimal:2',
            'avg_rating' => 'decimal:2',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function allowedServiceTypes(): array
    {
        return match ($this->gender_focus) {
            'male' => self::MALE_SERVICES,
            'female' => self::FEMALE_SERVICES,
            default => array_values(array_unique(array_merge(self::FEMALE_SERVICES, self::MALE_SERVICES))),
        };
    }
}
