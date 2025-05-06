<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description ?? null,
            'price' => $this->price,
            'stock' => $this->stock,
            'category_id' => $this->category_id,
            'category_name' => $this->category->name,
            'image_url' => $this->image_url ? asset($this->image_url) : null,
            'status' => $this->status,
        ];
    }
}
