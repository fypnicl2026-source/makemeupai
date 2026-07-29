<?php

namespace App\Services;

class LookRecommendationService
{
    public function recommend(array $traits, string $eventType, string $styleMood, string $gender): array
    {
        $faceShape = $traits['faceShape'] ?? 'oval';
        $skinTone = $traits['skinTone'] ?? 'warm-medium';
        $hairLength = $traits['hairLength'] ?? 'medium';

        if ($gender === 'male') {
            return [
                'gender' => 'male',
                'hairstyle' => $this->maleHairstyleSuggestions($faceShape, $hairLength, $eventType, $styleMood),
                'beard_grooming' => $this->beardGroomingSuggestions($faceShape, $eventType, $styleMood),
                'styling' => $this->maleStylingSuggestions($eventType, $styleMood),
            ];
        }

        return [
            'gender' => 'female',
            'makeup' => $this->makeupSuggestions($faceShape, $skinTone, $eventType, $styleMood),
            'hairstyle' => $this->femaleHairstyleSuggestions($faceShape, $hairLength, $eventType, $styleMood),
            'mehndi' => $this->mehndiSuggestions($eventType, $styleMood),
        ];
    }

    private function makeupSuggestions(
        string $faceShape,
        string $skinTone,
        string $eventType,
        string $styleMood
    ): array {
        $warm = str_contains($skinTone, 'warm');
        $base = $warm
            ? ['Soft Glam Warm Base', 'Peach Glow Finish', 'Golden Hour Highlight']
            : ['Cool Rose Base', 'Porcelain Soft Matte', 'Berry Soft Contour'];

        if ($eventType === 'wedding' || $eventType === 'formal') {
            $base = array_merge(['Classic Bridal Glow', 'Elegant Dewy Finish'], $base);
        }

        if ($eventType === 'party') {
            $base[] = 'Party Spot Glow';
        }

        if ($styleMood === 'bold') {
            $base[] = 'Defined Wing Liner';
        }

        if ($styleMood === 'natural') {
            $base[] = 'Barely-There Skin Tint';
        }

        if ($faceShape === 'round') {
            $base[] = 'Vertical Contour Lift';
        }

        return array_values(array_unique(array_slice($base, 0, 3)));
    }

    private function femaleHairstyleSuggestions(
        string $faceShape,
        string $hairLength,
        string $eventType,
        string $styleMood
    ): array {
        $options = match ($hairLength) {
            'short' => ['Textured Pixie', 'Sleek Side Part', 'Soft Curls Crop'],
            'long' => ['Loose Waves', 'Half-Up Curls', 'Braided Low Bun'],
            default => ['Textured Low Bun', 'Soft Blowout', 'Half-Up Twist'],
        };

        if ($faceShape === 'heart') {
            $options[] = 'Face-Framing Layers';
        }

        if ($eventType === 'party') {
            $options[] = 'Voluminous Party Curls';
        }

        if ($eventType === 'wedding' || $eventType === 'formal') {
            $options[] = 'Polished Low Chignon';
        }

        if ($styleMood === 'natural') {
            $options[] = 'Effortless Air-Dried Look';
        }

        return array_values(array_unique(array_slice($options, 0, 3)));
    }

    private function mehndiSuggestions(string $eventType, string $styleMood): array
    {
        if ($eventType === 'wedding' || $eventType === 'formal') {
            return $styleMood === 'bold'
                ? ['Traditional Bridal Full', 'Intricate Paisley', 'Mandala Palm Design']
                : ['Minimal Arabic', 'Floral Traditional', 'Delicate Finger Trails'];
        }

        if ($eventType === 'party') {
            return ['Glitter Accent Mehndi', 'Modern Geometric', 'Minimal Arabic'];
        }

        return ['Single Line Accent', 'Tiny Floral Motif', 'Skip or Light Henna Stain'];
    }

    private function maleHairstyleSuggestions(
        string $faceShape,
        string $hairLength,
        string $eventType,
        string $styleMood
    ): array {
        $options = match ($hairLength) {
            'short' => ['Clean Fade Crop', 'Textured Fringe', 'Slick Side Part'],
            'long' => ['Tidy Mid-Length Layers', 'Soft Push-Back', 'Controlled Wave Finish'],
            default => ['Modern Quiff', 'Taper Fade', 'Natural Texture Crop'],
        };

        if ($faceShape === 'square') {
            $options[] = 'Soft Fringe to Balance Jaw';
        }

        if ($faceShape === 'round') {
            $options[] = 'Height on Top, Tight Sides';
        }

        if ($eventType === 'wedding' || $eventType === 'formal') {
            $options[] = 'Groom-Ready Sleek Finish';
        }

        if ($eventType === 'party') {
            $options[] = 'Styled Volume with Matte Clay';
        }

        if ($styleMood === 'natural') {
            $options[] = 'Low-Maintenance Textured Crop';
        }

        if ($styleMood === 'bold') {
            $options[] = 'High Contrast Fade';
        }

        return array_values(array_unique(array_slice($options, 0, 3)));
    }

    private function beardGroomingSuggestions(string $faceShape, string $eventType, string $styleMood): array
    {
        $options = ['Clean Neckline Shape-Up', 'Even Cheek Line Trim', 'Conditioned Soft Finish'];

        if ($faceShape === 'oval' || $faceShape === 'heart') {
            $options[] = 'Short Stubble for Soft Contour';
        }

        if ($faceShape === 'square') {
            $options[] = 'Fuller Beard with Rounded Edges';
        }

        if ($eventType === 'wedding' || $eventType === 'formal') {
            $options[] = 'Precision Groom Package';
        }

        if ($eventType === 'casual' || $styleMood === 'natural') {
            $options[] = 'Natural 3-Day Stubble';
        }

        if ($styleMood === 'bold') {
            $options[] = 'Sharp Designer Beard Lines';
        }

        return array_values(array_unique(array_slice($options, 0, 3)));
    }

    private function maleStylingSuggestions(string $eventType, string $styleMood): array
    {
        $options = match ($eventType) {
            'wedding', 'formal' => [
                'Crisp Collar and Pocket Square',
                'Polished Leather Shoes',
                'Subtle Cologne Finish',
            ],
            'party' => [
                'Smart-Casual Layered Look',
                'Statement Watch Accent',
                'Matte Hair + Clean Beard Combo',
            ],
            'work' => [
                'Neat Business Casual Fit',
                'Minimal Accessories',
                'Fresh Groomed Day Look',
            ],
            default => [
                'Relaxed Tailored Fit',
                'Clean Sneakers or Loafers',
                'Light Moisturizer + SPF',
            ],
        };

        if ($styleMood === 'elegant') {
            $options[] = 'Monochrome Elevated Palette';
        }

        if ($styleMood === 'bold') {
            $options[] = 'One Strong Color Accent';
        }

        if ($styleMood === 'soft' || $styleMood === 'natural') {
            $options[] = 'Soft Neutrals and Breathable Fabrics';
        }

        return array_values(array_unique(array_slice($options, 0, 3)));
    }
}
