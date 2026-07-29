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
                'salon_name' => 'Noor Bridal Studio',
                'bio' => 'Bridal and party makeup specialist with 8 years of experience in Gulberg. Soft glam and traditional bridal looks.',
                'city' => 'Lahore',
                'area' => 'Gulberg',
                'gender_focus' => 'female',
                'specializations' => ['makeup', 'bridal', 'hairstyle'],
                'hourly_rate' => 3500.00,
                'skill_badge' => 'expert',
                'profile_photo' => null,
                'is_available' => true,
                'avg_rating' => 4.85,
            ],
            [
                'name' => 'Sana Malik',
                'salon_name' => 'Malik Mehandi & Makeup',
                'bio' => 'Full-service bridal packages with makeup, mehndi, and hairstyling for Lahore weddings.',
                'city' => 'Lahore',
                'area' => 'DHA Phase 5',
                'gender_focus' => 'female',
                'specializations' => ['bridal', 'mehndi', 'makeup'],
                'hourly_rate' => 4000.00,
                'skill_badge' => 'expert',
                'profile_photo' => null,
                'is_available' => true,
                'avg_rating' => 4.92,
            ],
            [
                'name' => 'Hira Sheikh',
                'salon_name' => 'Blush Lane Salon',
                'bio' => 'Everyday glam, party makeup, and blowouts with skincare-friendly products.',
                'city' => 'Lahore',
                'area' => 'Johar Town',
                'gender_focus' => 'female',
                'specializations' => ['makeup', 'hairstyle', 'skincare'],
                'hourly_rate' => 2800.00,
                'skill_badge' => 'intermediate',
                'profile_photo' => null,
                'is_available' => true,
                'avg_rating' => 4.68,
            ],
            [
                'name' => 'Zara Imran',
                'salon_name' => 'Luxe Locks Studio',
                'bio' => 'Hairstyling and soft bridal updos with a modern Lahore aesthetic.',
                'city' => 'Lahore',
                'area' => 'Model Town',
                'gender_focus' => 'female',
                'specializations' => ['hairstyle', 'bridal'],
                'hourly_rate' => 3200.00,
                'skill_badge' => 'expert',
                'profile_photo' => null,
                'is_available' => true,
                'avg_rating' => 4.74,
            ],
            [
                'name' => 'Fatima Raza',
                'salon_name' => 'Henna & Hue',
                'bio' => 'Arabic and traditional mehndi with natural dyes for mehndi nights and parties.',
                'city' => 'Lahore',
                'area' => 'Bahria Town',
                'gender_focus' => 'female',
                'specializations' => ['mehndi', 'bridal'],
                'hourly_rate' => 2200.00,
                'skill_badge' => 'intermediate',
                'profile_photo' => null,
                'is_available' => true,
                'avg_rating' => 4.61,
            ],
            [
                'name' => 'Ali Hassan',
                'salon_name' => 'The Gentleman Cut',
                'bio' => 'Precision fades, classic cuts, and groom-ready styling for Lahore gentlemen.',
                'city' => 'Lahore',
                'area' => 'Gulberg',
                'gender_focus' => 'male',
                'specializations' => ['haircut', 'styling', 'groom'],
                'hourly_rate' => 2500.00,
                'skill_badge' => 'expert',
                'profile_photo' => null,
                'is_available' => true,
                'avg_rating' => 4.88,
            ],
            [
                'name' => 'Usman Khan',
                'salon_name' => 'Beard Lab',
                'bio' => 'Beard sculpting, hot towel shaves, and grooming packages for everyday and events.',
                'city' => 'Lahore',
                'area' => 'DHA Phase 6',
                'gender_focus' => 'male',
                'specializations' => ['beard', 'grooming'],
                'hourly_rate' => 2000.00,
                'skill_badge' => 'expert',
                'profile_photo' => null,
                'is_available' => true,
                'avg_rating' => 4.79,
            ],
            [
                'name' => 'Hamza Farooq',
                'salon_name' => 'Studio Fade',
                'bio' => 'Modern party looks, textured crops, and matte styling for young professionals.',
                'city' => 'Lahore',
                'area' => 'Johar Town',
                'gender_focus' => 'male',
                'specializations' => ['haircut', 'styling', 'party'],
                'hourly_rate' => 2300.00,
                'skill_badge' => 'intermediate',
                'profile_photo' => null,
                'is_available' => true,
                'avg_rating' => 4.55,
            ],
            [
                'name' => 'Bilal Ahmed',
                'salon_name' => 'Groom Room Lahore',
                'bio' => 'Full groom packages: haircut, beard, and polish for wedding day confidence.',
                'city' => 'Lahore',
                'area' => 'Cantt',
                'gender_focus' => 'male',
                'specializations' => ['groom', 'beard', 'haircut'],
                'hourly_rate' => 3000.00,
                'skill_badge' => 'expert',
                'profile_photo' => null,
                'is_available' => true,
                'avg_rating' => 4.91,
            ],
            [
                'name' => 'Saad Malik',
                'salon_name' => 'North Barbers',
                'bio' => 'Clean barbering with classic and contemporary cuts in a relaxed Lahore setting.',
                'city' => 'Lahore',
                'area' => 'Faisal Town',
                'gender_focus' => 'male',
                'specializations' => ['haircut', 'beard'],
                'hourly_rate' => 1800.00,
                'skill_badge' => 'intermediate',
                'profile_photo' => null,
                'is_available' => true,
                'avg_rating' => 4.48,
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
