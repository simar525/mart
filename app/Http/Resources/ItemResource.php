<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category->name,
            'sub_category' => $this->subCategory->name,
            'options' => $this->options,
            'demo_link' => $this->demo_link,
            'tags' => $this->tags,
            'media' => [
                'preview_image' => $this->getPreviewImageLink(),
                'screenshots' => $this->getScreenshotLinks(),
            ],
            'price' => [
                'regular' => $this->price->regular,
                'extended' => $this->price->extended,
            ],
            'currency' => @settings('currency')->code,
            'published_at' => $this->created_at,
        ];
    }
}
