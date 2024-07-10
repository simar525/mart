<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory;

    const STATUS_PENDING = 1;
    const STATUS_SOFT_REJECTED = 2;
    const STATUS_RESUBMITTED = 3;
    const STATUS_APPROVED = 4;
    const STATUS_HARD_REJECTED = 5;
    const STATUS_DELETED = 6;

    const MAIN_FILE_NOT_EXTERNAL = 0;
    const MAIN_FILE_EXTERNAL = 1;

    const NOT_FREE = 0;
    const FREE = 1;

    const PURCHASING_STATUS_DISABLED = 0;
    const PURCHASING_STATUS_ENABLED = 1;

    const NOT_TRENDING = 0;
    const TRENDING = 1;

    const NOT_BEST_SELLING = 0;
    const BEST_SELLING = 1;

    const DISCOUNT_OFF = 0;
    const DISCOUNT_ON = 1;

    const NOT_FEATURED = 0;
    const FEATURED = 1;

    protected static function boot()
    {
        parent::boot();
        static::deleted(function ($item) {
            $item->deleteFiles();
            if ($item->itemUpdate) {
                $item->itemUpdate->deleteFiles();
            }
        });
    }

    public function scopePending($query)
    {
        $query->where('status', self::STATUS_PENDING);
    }

    public function isPending()
    {
        return $this->status == self::STATUS_PENDING;
    }

    public function scopeSoftRejected($query)
    {
        $query->where('status', self::STATUS_SOFT_REJECTED);
    }

    public function isSoftRejected()
    {
        return $this->status == self::STATUS_SOFT_REJECTED;
    }

    public function scopeResubmitted($query)
    {
        $query->where('status', self::STATUS_RESUBMITTED);
    }

    public function isResubmitted()
    {
        return $this->status == self::STATUS_RESUBMITTED;
    }

    public function scopeApproved($query)
    {
        $query->where('status', self::STATUS_APPROVED);
    }

    public function isApproved()
    {
        return $this->status == self::STATUS_APPROVED;
    }

    public function scopeHardRejected($query)
    {
        $query->where('status', self::STATUS_HARD_REJECTED);
    }

    public function isHardRejected()
    {
        return $this->status == self::STATUS_HARD_REJECTED;
    }

    public function scopeDeleted($query)
    {
        $query->where('status', self::STATUS_DELETED);
    }

    public function scopeNotDeleted($query)
    {
        $query->where('status', '!=', self::STATUS_DELETED);
    }

    public function isDeleted()
    {
        return $this->status == self::STATUS_DELETED;
    }

    public function hasUpdate()
    {
        return $this->itemUpdate;
    }

    public function scopeOnDiscount($query)
    {
        $query->where('is_on_discount', self::DISCOUNT_ON);
    }

    public function isOnDiscount()
    {
        return $this->is_on_discount == self::DISCOUNT_ON;
    }

    public function hasDiscount()
    {
        return $this->discount;
    }

    public function isExtendedOnDiscount()
    {
        return $this->discount->extended_price != null;
    }

    public function scopeFree($query)
    {
        $query->where('is_free', self::FREE);
    }

    public function isFree()
    {
        return $this->is_free == self::FREE;
    }

    public function scopePurchasingEnabled($query)
    {
        if ($this->isFree()) {
            $query->free()
                ->where('purchasing_status', self::PURCHASING_STATUS_ENABLED);
        }
    }

    public function isPurchasingEnabled()
    {
        if ($this->isFree()) {
            return $this->purchasing_status == self::PURCHASING_STATUS_ENABLED;
        }
        return true;
    }

    public function scopeTrending($query)
    {
        $query->where('is_trending', self::TRENDING);
    }

    public function isTrending()
    {
        return $this->is_trending == self::TRENDING;
    }

    public function scopeBestSelling($query)
    {
        $query->where('is_best_selling', self::BEST_SELLING);
    }

    public function isBestSelling()
    {
        return $this->is_best_selling == self::BEST_SELLING;
    }

    public function scopeFeatured($query)
    {
        $query->where('is_featured', self::FEATURED);
    }

    public function isFeatured()
    {
        return $this->is_featured == self::FEATURED;
    }

    public function wasFeatured()
    {
        return $this->was_featured == self::FEATURED;
    }

    public function scopeWhereReviewerCategories($query, $reviewer)
    {
        $categoryIds = $reviewer->categories->pluck('id')->toArray();
        return $query->whereIn('category_id', $categoryIds);
    }

    public function hasSales()
    {
        return $this->total_sales > 0;
    }

    public function hasChangelogs()
    {
        return $this->changelogs->count() > 0;
    }

    public function hasReviews()
    {
        return $this->total_reviews > 0;
    }

    public function isRecentlyUpdated()
    {
        return $this->last_update_at >= Carbon::now()->subMonth();
    }

    public function isMainFileExternal()
    {
        return $this->is_main_file_external == self::MAIN_FILE_EXTERNAL;
    }

    protected $fillable = [
        'author_id',
        'name',
        'slug',
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
        'status',
        'total_sales',
        'total_sales_amount',
        'total_earnings',
        'total_reviews',
        'avg_reviews',
        'total_comments',
        'total_views',
        'current_month_views',
        'free_downloads',
        'purchasing_status',
        'is_trending',
        'is_best_trending',
        'is_on_discount',
        'is_featured',
        'was_featured',
        'last_update_at',
        'last_discount_at',
        'price_updated_at',
    ];

    protected $casts = [
        'options' => 'array',
        'screenshots' => 'object',
    ];

    protected $with = [
        'category',
        'subCategory',
    ];

    protected $dates = [
        'last_update_at',
        'last_discount_at',
        'price_updated_at',
        'deleted_at',
    ];

    public function getRegularPrice()
    {
        return ($this->regular_price + $this->category->regular_buyer_fee);
    }

    public function getExtendedPrice()
    {
        return ($this->extended_price + $this->category->extended_buyer_fee);
    }

    public function getPriceAttribute()
    {
        $data['regular'] = $this->getRegularPrice();
        $data['extended'] = $this->getExtendedPrice();

        if ($this->hasDiscount() && $this->discount->isActive()) {
            $discount = $this->discount;
            $data['regular'] = $discount->getRegularPrice();
            if ($discount->withExtended()) {
                $data['extended'] = $discount->getExtendedPrice();
            }
        }

        return (object) $data;
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_PENDING => translate('Pending'),
            self::STATUS_SOFT_REJECTED => translate('Soft Rejected'),
            self::STATUS_RESUBMITTED => translate('Resubmitted'),
            self::STATUS_APPROVED => translate('Approved'),
            self::STATUS_HARD_REJECTED => translate('Hard Rejected'),
            self::STATUS_DELETED => translate('Deleted'),
        ];
    }

    public function getStatusName()
    {
        return self::getStatusOptions()[$this->status];
    }

    public function getPreviewImageLink()
    {
        return getLinkFromStorageProvider($this->preview_image);
    }

    public function getScreenshotLinks()
    {
        if ($this->screenshots) {
            $screenshots = [];
            foreach ($this->screenshots as $screenshot) {
                $screenshots[] = getLinkFromStorageProvider($screenshot);
            }
            return (object) $screenshots;
        }
    }

    public function getTags()
    {
        $tags = explode(',', $this->tags);
        return (object) $tags;
    }

    public function getLink()
    {
        return route('items.view', [$this->slug, $this->id]);
    }

    public function getChangeLogsLink()
    {
        return route('items.changelogs', [$this->slug, $this->id]);
    }

    public function getReviewsLink()
    {
        return route('items.reviews', [$this->slug, $this->id]);
    }

    public function getCommentsLink()
    {
        return route('items.comments', [$this->slug, $this->id]);
    }

    public function getDemoLink()
    {
        if ($this->demo_link) {
            return route('items.preview', encrypt($this->id));
        }
    }

    public function download()
    {
        $storageProvider = storageProvider();
        $processor = new $storageProvider->processor;

        $siteName = Str::slug(@settings('general')->site_name);
        $filename = $siteName . '-' . time() . '-' . Str::slug($this->name) . '.' . File::extension($this->main_file);

        return $processor->download($this->main_file, $filename);
    }

    public function deletePreviewImage()
    {
        $storageProvider = storageProvider();
        $processor = new $storageProvider->processor;
        $processor->delete($this->preview_image);
    }

    public function deleteMainFile()
    {
        if (!$this->isMainFileExternal()) {
            $storageProvider = storageProvider();
            $processor = new $storageProvider->processor;
            $processor->delete($this->main_file);
        }
    }

    public function deleteScreenshots()
    {
        if ($this->screenshots) {
            $storageProvider = storageProvider();
            $processor = new $storageProvider->processor;
            foreach ($this->screenshots as $screenshot) {
                $processor->delete($screenshot);
            }
        }
    }

    public function deleteFiles()
    {
        $this->deletePreviewImage();
        $this->deleteMainFile();
        $this->deleteScreenshots();
    }

    public function softDelete()
    {
        $this->status = self::STATUS_DELETED;
        $this->update();

        if ($this->isDeleted()) {
            $this->deleteFiles();
            if ($this->itemUpdate) {
                $this->itemUpdate->delete();
                $this->itemUpdate->deleteFiles();
            }
            $this->discount()->delete();
            $this->cartItems()->delete();
            $this->favorites()->delete();
        }
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function itemUpdate()
    {
        return $this->hasOne(ItemUpdate::class);
    }

    public function histories()
    {
        return $this->hasMany(ItemHistory::class);
    }

    public function discount()
    {
        return $this->hasOne(ItemDiscount::class);
    }

    public function changelogs()
    {
        return $this->hasMany(ItemChangeLog::class);
    }

    public function reviews()
    {
        return $this->hasMany(ItemReview::class);
    }

    public function comments()
    {
        return $this->hasMany(ItemComment::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
