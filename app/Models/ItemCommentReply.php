<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCommentReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_comment_id',
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

    public function comment()
    {
        return $this->belongsTo(ItemComment::class, 'item_comment_id');
    }
}
