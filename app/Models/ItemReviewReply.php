<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemReviewReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_review_id',
        'user_id',
        'body',
    ];

    protected $with = [
        'user',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function review()
    {
        return $this->belongsTo(ItemReview::class, 'item_review_id');
    }
}