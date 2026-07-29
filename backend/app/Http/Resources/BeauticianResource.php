<?php

namespace App\Http\Resources;

use App\Support\PublicStorageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeauticianResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'salon_name' => $this->salon_name,
            'bio' => $this->bio,
            'city' => $this->city,
            'area' => $this->area,
            'gender_focus' => $this->gender_focus,
            'specializations' => $this->specializations,
            'hourly_rate' => $this->hourly_rate,
            'skill_badge' => $this->skill_badge,
            'profile_photo_url' => PublicStorageUrl::fromPath($this->profile_photo),
            'is_available' => $this->is_available,
            'avg_rating' => $this->avg_rating,
            'allowed_services' => $this->allowedServiceTypes(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
