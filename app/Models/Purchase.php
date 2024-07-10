<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    const LICENSE_TYPE_REGULAR = 1;
    const LICENSE_TYPE_EXTENDED = 2;

    const NOT_DOWNLOADED = 0;
    const DOWNLOADED = 1;

    const STATUS_ACTIVE = 1;
    const STATUS_REFUNDED = 2;
    const STATUS_CANCELLED = 3;

    public function isLicenseTypeRegular()
    {
        return $this->license_type == self::LICENSE_TYPE_REGULAR;
    }

    public function isLicenseTypeExtended()
    {
        return $this->license_type == self::LICENSE_TYPE_EXTENDED;
    }

    public function isDownloaded()
    {
        return $this->is_downloaded == self::DOWNLOADED;
    }

    public function scopeActive($query)
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    public function scopeRefunded($query)
    {
        $query->where('status', self::STATUS_REFUNDED);
    }

    public function isRefunded()
    {
        return $this->status == self::STATUS_REFUNDED;
    }

    public function scopeCancelled($query)
    {
        $query->where('status', self::STATUS_CANCELLED);
    }

    public function isCancelled()
    {
        return $this->status == self::STATUS_CANCELLED;
    }

    public function getReviewAttribute()
    {
        $review = $this->item->reviews()
            ->where('user_id', $this->user_id)->first();
        return $review ? $review : null;
    }

    protected $fillable = [
        'user_id',
        'author_id',
        'sale_id',
        'item_id',
        'license_type',
        'code',
        'is_downloaded',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
