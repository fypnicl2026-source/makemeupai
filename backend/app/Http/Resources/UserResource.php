<?php

namespace App\Http\Resources;

use App\Support\PublicStorageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'gender' => $this->gender,
            'city' => $this->city,
            'profile_photo' => $this->profile_photo,
            'profile_photo_url' => PublicStorageUrl::fromPath($this->profile_photo),
            'face_traits' => $this->face_traits,
            'email_verified_at' => $this->email_verified_at,
            'email_verified' => $this->hasVerifiedEmail(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
