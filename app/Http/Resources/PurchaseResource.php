<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'purchase_code' => $this->code,
            'license_type' => $this->isLicenseTypeRegular() ? translate('Regular') : translate('Extended'),
            'price' => $this->sale->price,
            'currency' => @settings('currency')->code,
            'item' => [
                'id' => $this->item->id,
                'name' => $this->item->name,
                'url' => $this->item->getLink(),
                'media' => [
                    'preview_image' => $this->item->getPreviewImageLink(),
                ],
            ],
            'downloaded' => $this->isDownloaded() ? true : false,
            'date' => $this->created_at,
        ];
    }
}