<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ItemController;
use App\Models\Category;
use App\Models\Item;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('subCategories')->paginate(5);
        return theme_view('categories.index', ['categories' => $categories]);
    }

    public function category($category_slug)
    {
        $category = Category::where('slug', $category_slug)
            ->with('subCategories')->firstOrFail();

        $items = $this->getItems($category);

        incrementViews($category, 'categories');

        return theme_view('categories.category', [
            'category' => $category,
            'items' => $items,
        ]);
    }

    public function subCategory($category_slug, $sub_category_slug)
    {
        $category = Category::where('slug', $category_slug)
            ->firstOrFail();

        $subCategory = SubCategory::where('category_id', $category->id)
            ->where('slug', $sub_category_slug)
            ->firstOrFail();

        $items = $this->getItems($category, $subCategory);

        incrementViews($subCategory, 'sub_categories');

        return theme_view('categories.sub-category', [
            'category' => $category,
            'subCategory' => $subCategory,
            'items' => $items,
        ]);
    }

    public function getItems($category, $subCategory = null)
    {
        $items = Item::where('category_id', $category->id)
            ->approved();

        if ($subCategory) {
            $items->where('sub_category_id', $subCategory->id);
        }

        $items = ItemController::getResultByParams($items);

        $items = $items->orderbyDesc('items.id')
            ->paginate(30);

        $items->appends(request()->only(['search', 'min_price', 'max_price',
            'trending', 'best_selling', 'on_sale', 'stars', 'date']));

        return $items;
    }
}
