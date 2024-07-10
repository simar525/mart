<?php

namespace App\Http\Controllers\Admin\Categories;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;
use Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query();

        if (request()->filled('search')) {
            $searchTerm = '%' . request('search') . '%';
            $categories->where('name', 'like', $searchTerm)
                ->OrWhere('slug', 'like', $searchTerm);
        }

        $categories = $categories->get();

        return view('admin.categories.index', ['categories' => $categories]);
    }

    public function sortable(Request $request)
    {
        if (!$request->has('ids') || is_null($request->ids)) {
            return response()->json(['error' => translate('Failed to sort the table')]);
        }

        $ids = explode(',', $request->ids);
        foreach ($ids as $sortOrder => $id) {
            $category = Category::find($id);
            $category->sort_id = ($sortOrder + 1);
            $category->save();
        }

        return response()->json(['success' => true]);
    }

    public function slug(Request $request)
    {
        $slug = null;
        if ($request->content != null) {
            $slug = SlugService::createSlug(Category::class, 'slug', $request->content);
        }
        return response()->json(['slug' => $slug]);
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:categories'],
            'regular_buyer_fee' => ['required', 'integer', 'min:0'],
            'extended_buyer_fee' => ['required', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back()->withInput();
        }

        $category = new Category();
        $category->name = $request->name;
        $category->slug = SlugService::createSlug(Category::class, 'slug', $request->name);
        $category->regular_buyer_fee = $request->regular_buyer_fee;
        $category->extended_buyer_fee = $request->extended_buyer_fee;
        $category->save();

        toastr()->success(translate('Created Successfully'));
        return redirect()->route('admin.categories.index');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:categories,slug,' . $category->id],
            'regular_buyer_fee' => ['required', 'integer', 'min:0'],
            'extended_buyer_fee' => ['required', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back();
        }

        $category->name = $request->name;
        $category->slug = $request->slug;
        $category->regular_buyer_fee = $request->regular_buyer_fee;
        $category->extended_buyer_fee = $request->extended_buyer_fee;
        $category->update();

        toastr()->success(translate('Updated Successfully'));
        return back();
    }

    public function destroy(Category $category)
    {
        if ($category->items->count() > 0) {
            toastr()->error(translate('The selected category has items, it cannot be deleted'));
            return back();
        }

        if ($category->subCategories->count() > 0) {
            toastr()->error(translate('The selected category has subCategories, it cannot be deleted'));
            return back();
        }

        $category->delete();
        toastr()->success(translate('Deleted Successfully'));
        return back();
    }
}
