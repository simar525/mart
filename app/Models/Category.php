<?php

namespace App\Models;

use App\Scopes\SortByIdScope;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, Sluggable;

    protected static function booted()
    {
        static::addGlobalScope(new SortByIdScope);
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    protected $fillable = [
        'name',
        'slug',
        'regular_buyer_fee',
        'extended_buyer_fee',
        'views',
    ];

    public function getLink()
    {
        return route('categories.category', $this->slug);
    }

    public function reviewers()
    {
        return $this->belongsToMany(Reviewer::class);
    }

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }

    public function categoryOptions()
    {
        return $this->hasMany(CategoryOption::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
