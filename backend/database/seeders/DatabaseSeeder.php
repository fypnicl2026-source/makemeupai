<?php

namespace Database\Seeders;

use App\Models\Beautician;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $beauticians = [
            [
                'name' => 'Ayesha Noor',
                'bio' => 'Bridal and party makeup specialist with 8 years of experience in Lahore. Known for soft glam and traditional bridal looks.',
                'city' => 'Lahore',
                'specializations' => ['makeup', 'bridal', 'hairstyle'],
                'hourly_rate' => 3500.00,
                'skill_badge' => 'expert',
                'profile_photo' => null,
                'is_available' => true,
                'avg_rating' => 4.85,
            ],
            [
                'name' => 'Fatima Khan',
                'bio' => 'Karachi-based makeup artist focused on everyday glam and skincare-friendly routines for all skin tones.',
                'city' => 'Karachi',
                'specializations' => ['makeup', 'skincare'],
                'hourly_rate' => 2500.00,
                'skill_badge' => 'intermediate',
                'profile_photo' => null,
                'is_available' => true,
                'avg_rating' => 4.55,
            ],
            [
                'name' => 'Zainab Ali',
                'bio' => 'Up-and-coming stylist in Islamabad specializing in elegant updos and minimal Arabic mehndi designs.',
                'city' => 'Islamabad',
                'specializations' => ['hairstyle', 'mehndi'],
                'hourly_rate' => 1800.00,
                'skill_badge' => 'beginner',
                'profile_photo' => null,
                'is_available' => true,
                'avg_rating' => 4.30,
            ],
            [
                'name' => 'Sana Malik',
                'bio' => 'Award-winning bridal expert serving Lahore clients with full-service makeup, mehndi, and hairstyling packages.',
                'city' => 'Lahore',
                'specializations' => ['bridal', 'mehndi', 'makeup'],
                'hourly_rate' => 4000.00,
                'skill_badge' => 'expert',
                'profile_photo' => null,
                'is_available' => true,
                'avg_rating' => 4.92,
            ],
            [
                'name' => 'Hira Sheikh',
                'bio' => 'Versatile beautician in Karachi offering party makeup, blowouts, and personalized skincare consultations.',
                'city' => 'Karachi',
                'specializations' => ['makeup', 'hairstyle', 'skincare'],
                'hourly_rate' => 2800.00,
                'skill_badge' => 'intermediate',
                'profile_photo' => null,
                'is_available' => true,
                'avg_rating' => 4.68,
            ],
        ];

        foreach ($beauticians as $data) {
            Beautician::updateOrCreate(
                ['name' => $data['name'], 'city' => $data['city']],
                $data
            );
        }

        $this->call(ClientUserSeeder::class);
    }
}
