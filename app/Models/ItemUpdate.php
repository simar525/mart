<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ItemUpdate extends Model
{
    use HasFactory;

    public function scopeWhereReviewerCategories($query, $reviewer)
    {
        $categoryIds = $reviewer->categories->pluck('id')->toArray();
        return $query->whereIn('category_id', $categoryIds);
    }

    public function isFree()
    {
        return $this->is_free == Item::FREE;
    }

    public function isPurchasingEnabled()
    {
        return $this->purchasing_status == Item::PURCHASING_STATUS_ENABLED;
    }

    public function isMainFileExternal()
    {
        return $this->is_main_file_external == Item::MAIN_FILE_EXTERNAL;
    }

    protected $fillable = [
        'author_id',
        'item_id',
        'name',
        'description',
        'category_id',
        'sub_category_id',
        'options',
        'version',
        'demo_link',
        'tags',
        'preview_image',
        'main_file',
        'is_main_file_external',
        'screenshots',
        'regular_price',
        'extended_price',
        'purchasing_status',
        'is_free',
    ];

    protected $casts = [
        'options' => 'array',
        'screenshots' => 'object',
    ];

    protected $with = [
        'category',
        'subCategory',
    ];

    public function getRegularPrice()
    {
        if ($this->regular_price) {
            return ($this->regular_price + $this->category->regular_buyer_fee);
        }
        return null;
    }

    public function getExtendedPrice()
    {
        if ($this->extended_price) {
            return ($this->extended_price + $this->category->extended_buyer_fee);
        }
        return null;
    }

    public function getPreviewImageLink()
    {
        $previewImage = $this->preview_image ?? $this->item->preview_image;
        return getLinkFromStorageProvider($previewImage);
    }

    public function getScreenshotLinks()
    {
        $screenshots = [];
        foreach ($this->screenshots as $screenshot) {
            $screenshots[] = getLinkFromStorageProvider($screenshot);
        }
        return (object) $screenshots;
    }

    public function getTags()
    {
        $tags = explode(',', $this->tags);
        return (object) $tags;
    }

    public function download()
    {
        $storageProvider = storageProvider();
        $processor = new $storageProvider->processor;

        $siteName = Str::slug(@settings('general')->site_name);
        $filename = $siteName . '-updated-' . time() . '-' . Str::slug($this->name) . '.' . File::extension($this->main_file);

        return $processor->download($this->main_file, $filename);
    }

    public function deleteFiles()
    {
        $storageProvider = storageProvider();
        $processor = new $storageProvider->processor;

        if ($this->preview_image) {
            $processor->delete($this->preview_image);
        }

        if ($this->main_file && !$this->isMainFileExternal()) {
            $processor->delete($this->main_file);
        }

        if ($this->screenshots) {
            foreach ($this->screenshots as $screenshot) {
                $processor->delete($screenshot);
            }
        }
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}
