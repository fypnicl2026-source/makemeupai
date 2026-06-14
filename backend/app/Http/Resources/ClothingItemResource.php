<?php

namespace App\Http\Resources;

use App\Support\PublicStorageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClothingItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'colors' => $this->colors,
            'season' => $this->season,
            'occasion' => $this->occasion,
            'notes' => $this->notes,
            'image_url' => PublicStorageUrl::fromPath($this->image_path),
            'created_at' => $this->created_at,
        ];
    }
}
