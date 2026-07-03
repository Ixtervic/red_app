<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Image\ImageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class UserResource extends JsonResource
{
    private function imageUrl(?string $url): ?string
    {
        if (!$url) return null;
        return Str::startsWith($url, 'http') ? $url : asset('storage/' . $url);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => $this->thumbnail_url
                ? $this->imageUrl($this->thumbnail_url)
                : ($this->relationLoaded('images') && $this->images->isNotEmpty()
                    ? $this->imageUrl($this->images->first()->url)
                    : null),
            'images' => ImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
