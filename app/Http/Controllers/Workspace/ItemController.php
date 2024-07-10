<?php

namespace App\Http\Controllers\Workspace;

use App\Events\ItemResubmitted;
use App\Events\ItemSubmitted;
use App\Events\ItemUpdated;
use App\Http\Controllers\Controller;
use App\Jobs\SendBuyersItemUpdateNotification;
use App\Jobs\SendFollowersNewItemNotification;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemChangeLog;
use App\Models\ItemDiscount;
use App\Models\ItemHistory;
use App\Models\ItemUpdate;
use App\Models\ItemView;
use App\Models\Sale;
use App\Models\SubCategory;
use App\Models\UploadedFile;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Jenssegers\Date\Date;
use Mews\Purifier\Facades\Purifier;
use Str;

class ItemController extends Controller
{
    private $imageTypes = ['image/png', 'image/jpg', 'image/jpeg'];
    private $fileTypes;
    private $author;

    public function __construct()
    {
        $this->author = authUser();
        $this->fileTypes = explode(',', @settings('item')->main_file_types);
    }

    public function index()
    {
        return theme_view('workspace.items.index', [
            'categories' => Category::all(),
            'items' => $this->getItems(),
        ]);
    }

    protected function getItems()
    {
        $items = Item::where('author_id', $this->author->id)
            ->whereNotIn('status', [Item::STATUS_HARD_REJECTED, Item::STATUS_DELETED]);

        if (request()->filled('search')) {
            $searchTerm = '%' . request('search') . '%';
            $items->where('id', 'like', $searchTerm)
                ->OrWhere('name', 'like', $searchTerm)
                ->OrWhere('slug', 'like', $searchTerm)
                ->OrWhere('description', 'like', $searchTerm)
                ->OrWhere('options', 'like', $searchTerm)
                ->OrWhere('demo_link', 'like', $searchTerm)
                ->OrWhere('tags', 'like', $searchTerm)
                ->OrWhere('regular_price', 'like', $searchTerm)
                ->OrWhere('extended_price', 'like', $searchTerm);
        }

        if (request()->filled('category')) {
            $category = Category::where('slug', request('category'))->first();
            if ($category) {
                $items->where('category_id', $category->id);
            }
        }

        $items = $items->orderbyDesc('id')->paginate(20);
        $items->appends(request()->only(['search', 'category']));

        return $items;
    }

    public function create()
    {
        $categories = Category::all();

        $category = Category::where('slug', request('category'))
            ->with(['subCategories', 'categoryOptions'])
            ->firstOrFail();

        return theme_view('workspace.items.create', [
            'categories' => $categories,
            'category' => $category,
        ]);
    }

    public function loadFiles()
    {
        $uploadedFiles = UploadedFile::where('author_id', $this->author->id)
            ->notExpired()->select(['id', 'name'])
            ->get();

        $result = [];
        foreach ($uploadedFiles as $uploadedFile) {
            $result[hash_encode($uploadedFile->id)] = $uploadedFile->getShortName();
        }

        return response()->json($result);
    }

    public function deleteFile(Request $request, $id)
    {
        $uploadedFile = UploadedFile::where('id', hash_decode($id))
            ->where('author_id', $this->author->id)
            ->notExpired()
            ->first();

        if ($uploadedFile) {
            try {
                $uploadedFile->deleteFile();
                $uploadedFile->delete();
            } catch (Exception $e) {
                response()->json(['error' => $e->getMessage()]);
                return back();
            }
        }

        return response()->json([
            'success' => translate('File has been deleted successfully'),
        ]);
    }

    public function store(Request $request)
    {
        $itemSettings = settings('item');

        $rules = [
            'name' => ['required', 'string', 'block_patterns', 'unique:items', 'max:100'],
            'description' => ['required'],
            'category' => ['required', 'string', 'exists:categories,slug'],
            'sub_category' => ['nullable', 'string', 'exists:sub_categories,slug'],
            'version' => ['nullable', 'string', 'max:100'],
            'demo_link' => ['nullable', 'url', 'block_patterns'],
            'tags' => ['required', 'block_patterns'],
            'regular_license_price' => ['required', 'integer', 'min:' . @$itemSettings->minimum_price, 'max:' . @$itemSettings->maximum_price],
            'extended_license_price' => ['required', 'integer', 'min:' . @$itemSettings->minimum_price, 'max:' . @$itemSettings->maximum_price],
            'free_item' => ['nullable', 'boolean'],
            'purchasing_status' => ['nullable', 'boolean'],
            'message' => ['nullable', 'string', 'max:1500'],
        ];

        if ($itemSettings->external_file_link_option) {
            $rules['main_file_source'] = ['required', 'boolean'];
            if ($request->main_file_source == Item::MAIN_FILE_EXTERNAL) {
                $rules['main_file'] = ['required', 'url'];
            }
        } else {
            $request->main_file_source = false;
        }

        try {

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    toastr()->error($error);
                }
                return back()->withInput();
            }

            $free = Item::NOT_FREE;
            $purchasing = Item::PURCHASING_STATUS_ENABLED;
            if (@$itemSettings->free_item_option) {
                if ($request->free_item) {
                    $free = Item::FREE;
                    $purchasing = ($request->purchasing_status == Item::PURCHASING_STATUS_ENABLED) ?
                    Item::PURCHASING_STATUS_ENABLED : Item::PURCHASING_STATUS_DISABLED;
                }
            }

            $description = $this->handleItemDescription($request->description);

            $category = Category::where('slug', $request->category)->with('categoryOptions')->firstOrFail();
            $options = $this->handleItemOptions($request, $category);

            $subCategory = null;
            if ($request->has('sub_category') && !is_null($request->sub_category)) {
                $subCategory = SubCategory::where('slug', $request->sub_category)->firstOrFail();
            }

            $itemFiles = $this->handleItemFiles($request);
            $previewImage = $itemFiles->preview_image;
            $mainFile = $itemFiles->main_file;
            $screenshots = $itemFiles->screenshots;

            $regularPrice = $request->regular_license_price;
            $extendedPrice = $request->extended_license_price;

            $status = @$itemSettings->adding_require_review ? Item::STATUS_PENDING : Item::STATUS_APPROVED;
            $itemHistoryTitle = @$itemSettings->adding_require_review ? ItemHistory::TITLE_SUBMISSION : ItemHistory::TITLE_TRUST_SUBMISSION;

            $item = new Item();
            $item->author_id = $this->author->id;
            $item->name = $request->name;
            $item->slug = Str::slug($request->name);
            $item->description = $description;
            $item->category_id = $category->id;
            $item->sub_category_id = $subCategory ? $subCategory->id : null;
            $item->options = $options;
            $item->version = $request->version;
            $item->demo_link = $request->demo_link;
            $item->tags = $request->tags;
            $item->preview_image = $previewImage;
            $item->main_file = $mainFile;
            $item->is_main_file_external = $request->main_file_source;
            $item->screenshots = $screenshots;
            $item->regular_price = $regularPrice;
            $item->extended_price = $extendedPrice;
            $item->purchasing_status = $purchasing;
            $item->status = $status;
            $item->is_free = $free;
            $item->price_updated_at = Carbon::now();
            $item->save();

            $this->createItemHistory($item->id, $itemHistoryTitle, $request->message);
            $this->handleFileDeletionAfterInsert($request);

            event(new ItemSubmitted($item));

            if (@$itemSettings->adding_require_review) {
                toastr()->success(translate('Your item has been submitted successfully, we will review it as soon as possible.'));
            } else {
                dispatch(new SendFollowersNewItemNotification($item));
                toastr()->success(translate('Your item has been added successfully.'));
            }
            return redirect()->route('workspace.items.index');

        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return back()->withInput();
        }
    }

    public function edit($id)
    {
        $item = Item::where('id', $id)
            ->where('author_id', $this->author->id)
            ->whereIn('status', [Item::STATUS_SOFT_REJECTED, Item::STATUS_APPROVED, Item::STATUS_RESUBMITTED])
            ->firstOrFail();

        $categories = Category::all();
        $category = $item->category->load(['subCategories', 'categoryOptions']);

        return theme_view('workspace.items.edit', [
            'item' => $item,
            'categories' => $categories,
            'category' => $category,
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = Item::where('id', $id)
            ->where('author_id', $this->author->id)
            ->whereIn('status', [Item::STATUS_SOFT_REJECTED, Item::STATUS_APPROVED, Item::STATUS_RESUBMITTED])
            ->firstOrFail();

        $itemSettings = settings('item');

        $rules = [
            'name' => ['required', 'string', 'block_patterns', 'max:100', 'unique:items,name,' . $item->id],
            'description' => ['required'],
            'version' => ['nullable', 'string', 'max:100'],
            'demo_link' => ['nullable', 'url', 'block_patterns'],
            'tags' => ['required', 'block_patterns'],
            'free_item' => ['nullable', 'boolean'],
            'purchasing_status' => ['nullable', 'boolean'],
            'message' => ['nullable', 'string', 'max:1500'],
        ];

        if ($itemSettings->external_file_link_option) {
            $rules['main_file_source'] = ['required', 'boolean'];
            if ($request->main_file_source == Item::MAIN_FILE_EXTERNAL) {
                $rules['main_file'] = ['nullable', 'url'];
            }
        } else {
            $request->main_file_source = false;
        }

        if (!$item->hasDiscount()) {
            $rules['regular_license_price'] = ['required', 'integer', 'min:' . @$itemSettings->minimum_price, 'max:' . @$itemSettings->maximum_price];
            $rules['extended_license_price'] = ['required', 'integer', 'min:' . @$itemSettings->minimum_price, 'max:' . @$itemSettings->maximum_price];
        }

        try {
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    toastr()->error($error);
                }
                return back()->withInput();
            }

            $free = Item::NOT_FREE;
            $purchasing = Item::PURCHASING_STATUS_ENABLED;
            if (@$itemSettings->free_item_option) {
                if ($request->free_item) {
                    $free = Item::FREE;
                    $purchasing = ($request->purchasing_status == Item::PURCHASING_STATUS_ENABLED) ?
                    Item::PURCHASING_STATUS_ENABLED : Item::PURCHASING_STATUS_DISABLED;
                }
            }

            $description = $this->handleItemDescription($request->description);

            $category = $item->category->load('categoryOptions');
            $options = $this->handleItemOptions($request, $category);

            $itemFiles = $this->handleItemFiles($request, false);
            $previewImage = $itemFiles->preview_image;
            $mainFile = $itemFiles->main_file;
            $screenshots = $itemFiles->screenshots;

            if (!$item->hasDiscount()) {
                $regularPrice = $request->regular_license_price;
                $extendedPrice = $request->extended_license_price;

                if ($regularPrice != $item->regular_price || $extendedPrice != $item->extended_price) {
                    $priceUpdatedAt = Carbon::now();
                } else {
                    $priceUpdatedAt = $item->price_updated_at;
                }
            } else {
                $regularPrice = $item->regular_price;
                $extendedPrice = $item->extended_price;
                $priceUpdatedAt = $item->price_updated_at;
            }

            if (@$itemSettings->updating_require_review) {
                if (!$item->isApproved()) {
                    $slug = Str::slug($request->name);
                    $status = Item::STATUS_RESUBMITTED;
                    $itemHistoryTitle = ItemHistory::TITLE_RESUBMISSION;
                    $toastrMessage = translate('Your item has been resubmitted successfully');
                } else {
                    if ($item->hasUpdate()) {
                        toastr()->warning(translate('You have a pending update please wait until we processed.'));
                        return back();
                    }

                    $regularPrice = $regularPrice == $item->regular_price ? null : $regularPrice;
                    $extendedPrice = $extendedPrice == $item->extended_price ? null : $extendedPrice;

                    $itemUpdate = new ItemUpdate();
                    $itemUpdate->author_id = $item->author_id;
                    $itemUpdate->item_id = $item->id;
                    $itemUpdate->name = $request->name;
                    $itemUpdate->description = $description;
                    $itemUpdate->category_id = $item->category_id;
                    $itemUpdate->sub_category_id = $item->sub_category_id;
                    $itemUpdate->options = $options;
                    $itemUpdate->version = $request->version;
                    $itemUpdate->demo_link = $request->demo_link;
                    $itemUpdate->tags = $request->tags;
                    $itemUpdate->preview_image = $previewImage;
                    if ($mainFile) {
                        $itemUpdate->main_file = $mainFile;
                        $itemUpdate->is_main_file_external = $request->main_file_source;
                    }
                    $itemUpdate->screenshots = $screenshots;
                    $itemUpdate->regular_price = $regularPrice;
                    $itemUpdate->extended_price = $extendedPrice;
                    $itemUpdate->purchasing_status = $purchasing;
                    $itemUpdate->is_free = $free;
                    $itemUpdate->save();

                    $this->createItemHistory($item->id, ItemHistory::TITLE_UPDATE_SUBMISSION, $request->message);
                    $this->handleFileDeletionAfterInsert($request);

                    event(new ItemUpdated($itemUpdate));
                    toastr()->success(translate('Your update has been submitted successfully, we will review it as soon as possible.'));
                    return back();
                }
            } else {
                $slug = $item->slug;
                $status = Item::STATUS_APPROVED;
                $itemHistoryTitle = ItemHistory::TITLE_TRUST_UPDATE;
                $toastrMessage = translate('Your item has been updated successfully');
            }

            $updated = false;
            $itemClone = clone $item;

            $item->name = $request->name;
            $item->slug = $slug;
            $item->description = $description;
            $item->options = $options;
            $item->version = $request->version;
            $item->demo_link = $request->demo_link;
            $item->tags = $request->tags;
            if ($previewImage) {
                $item->preview_image = $previewImage;
            }
            if ($mainFile) {
                $item->main_file = $mainFile;
                $item->is_main_file_external = $request->main_file_source;
                if ($status == Item::STATUS_APPROVED) {
                    $item->last_update_at = Carbon::now();
                    $updated = true;
                }
            }
            if ($screenshots) {
                $item->screenshots = $screenshots;
            }
            $item->regular_price = $regularPrice;
            $item->extended_price = $extendedPrice;
            $item->status = $status;
            $item->purchasing_status = $purchasing;
            $item->is_free = $free;
            $item->price_updated_at = $priceUpdatedAt;
            $item->update();

            if ($updated) {
                dispatch(new SendBuyersItemUpdateNotification($item));
            }

            $this->createItemHistory($item->id, $itemHistoryTitle, $request->message);
            $this->handleFileDeletionAfterInsert($request, $itemClone);

            if (!$itemClone->isResubmitted()) {
                event(new ItemResubmitted($item));
            }

            toastr()->success($toastrMessage);
            return back();
        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return back()->withInput();
        }
    }

    private function handleItemDescription($description)
    {
        $description = Purifier::clean($description);
        if (empty($description)) {
            throw new Exception(translate('Description cannot be empty'));
        }
        return $description;
    }

    private function handleItemOptions($request, $category)
    {
        $options = null;
        if ($category->categoryOptions->count() > 0) {
            $options = [];
            foreach ($category->categoryOptions as $categoryOption) {
                $option = isset($request->options[$categoryOption->id]) ? $request->options[$categoryOption->id] : null;
                if ($categoryOption->isMultiple()) {
                    $requestOptions = $option ? $option : [];
                    if ($categoryOption->isRequired() && count($requestOptions) < 1) {
                        throw new Exception(translate(':field Cannot be empty', ['field' => $categoryOption->name]));
                    }
                    foreach ($requestOptions as $requestOption) {
                        if ($requestOption && !in_array($requestOption, $categoryOption->options)) {
                            throw new Exception(translate('Something went wrong, please refresh the page and try again.'));
                        }
                    }
                } else {
                    $requestOption = $option ? $option : null;
                    if ($categoryOption->isRequired() && empty($requestOption)) {
                        throw new Exception(translate(':field Cannot be empty', ['field' => $categoryOption->name]));
                    }
                    if ($requestOption && !in_array($requestOption, $categoryOption->options)) {
                        throw new Exception(translate('Something went wrong, please refresh the page and try again.'));
                    }
                }
                if ($option) {
                    $options[$categoryOption->name] = $option;
                }
            }
        }
        return $options;
    }

    private function handleItemFiles($request, $required = true)
    {
        $response['preview_image'] = null;
        $response['main_file'] = null;
        $response['screenshots'] = null;

        $itemSettings = settings('item');

        if ($request->filled('preview_image')) {
            $previewImage = UploadedFile::where('author_id', $this->author->id)
                ->where('id', hash_decode($request->preview_image))->notExpired()->first();
            if (!$previewImage) {
                throw new Exception(translate('One or more of the selected files are expired or not exist'));
            }
            if (!in_array($previewImage->mime_type, $this->imageTypes)) {
                throw new Exception(translate('Preview image must be the type of JPG or PNG'));
            }
            $image = Image::make($previewImage->getFileLink());
            $previewImageMaxWidth = @$itemSettings->preview_image_width;
            $previewImageMaxHeight = @$itemSettings->preview_image_height;
            if ($image->width() != $previewImageMaxWidth || $image->height() != $previewImageMaxHeight) {
                throw new Exception(translate('The dimensions of the preview image must be :dimensions', [
                    'dimensions' => $previewImageMaxWidth . 'x' . $previewImageMaxHeight,
                ]));
            }
            $response['preview_image'] = $previewImage->path;
        } else {
            if ($required) {
                throw new Exception(translate(':field Cannot be empty', ['field' => 'Preview image']));
            }
        }

        if ($request->filled('main_file')) {
            if ($request->main_file_source == Item::MAIN_FILE_NOT_EXTERNAL) {
                $mainFile = UploadedFile::where('author_id', $this->author->id)
                    ->where('id', hash_decode($request->main_file))->notExpired()->first();
                if (!$mainFile) {
                    throw new Exception(translate('One or more of the selected files are expired or not exist'));
                }
                if (!in_array($mainFile->extension, $this->fileTypes)) {
                    throw new Exception(translate('Main files must be the type of :types', ['types' => @settings('item')->main_file_types]));
                }
                $response['main_file'] = $mainFile->path;
            } else {
                $response['main_file'] = $request->main_file;
            }
        } else {
            if ($required) {
                throw new Exception(translate(':field Cannot be empty', ['field' => 'Main file']));
            }
        }

        if ($request->filled('screenshots')) {
            if ($required && count($request->screenshots) < 0) {
                throw new Exception(translate(':field Cannot be empty', ['field' => 'Screenshots']));
            }
            $screenshots = [];
            foreach ($request->screenshots as $screenshot) {
                $screenshot = UploadedFile::where('author_id', $this->author->id)
                    ->where('id', hash_decode($screenshot))->notExpired()->first();
                if (!$screenshot) {
                    throw new Exception(translate('One or more of the selected files are expired or not exist'));
                }
                if (!in_array($screenshot->mime_type, $this->imageTypes)) {
                    throw new Exception(translate('Screenshots must be the type of JPG or PNG'));
                }
                $screenshots[] = $screenshot->path;
            }
            if (count($screenshots) > @$itemSettings->maximum_screenshots) {
                throw new Exception(translate('Maximum screenshots is :maximum', ['maximum' => @$itemSettings->maximum_screenshots]));
            }
            $response['screenshots'] = $screenshots;
        }

        return (object) $response;
    }

    private function handleFileDeletionAfterInsert($request, $item = null)
    {
        if ($request->filled('preview_image')) {
            if ($item) {
                $item->deletePreviewImage();
            }
            $this->deleteUploadedFile($request->preview_image);
        }

        if ($request->filled('main_file') && $request->main_file_source == Item::MAIN_FILE_NOT_EXTERNAL) {
            if ($item) {
                $item->deleteMainFile();
            }
            $this->deleteUploadedFile($request->main_file);
        }

        if ($request->filled('screenshots')) {
            if ($item) {
                $item->deleteScreenshots();
            }
            foreach ($request->screenshots as $screenshot) {
                $this->deleteUploadedFile($screenshot);
            }
        }
    }

    private function deleteUploadedFile($fileId)
    {
        $uploadedFile = UploadedFile::where('id', hash_decode($fileId))
            ->notExpired()->first();
        if ($uploadedFile) {
            $uploadedFile->delete();
        }
    }

    private function createItemHistory($itemId, $title, $message = null)
    {
        $itemHistory = new ItemHistory();
        $itemHistory->author_id = $this->author->id;
        $itemHistory->item_id = $itemId;
        $itemHistory->title = $title;
        $itemHistory->body = $message;
        $itemHistory->save();
    }

    public function changelogs($id)
    {
        $item = Item::where('id', $id)
            ->where('author_id', $this->author->id)
            ->whereIn('status', [Item::STATUS_SOFT_REJECTED, Item::STATUS_APPROVED, Item::STATUS_RESUBMITTED])
            ->firstOrFail();

        $changelogs = ItemChangeLog::where('item_id', $item->id)
            ->orderbyDesc('id')->paginate(10);

        return theme_view('workspace.items.changelogs', [
            'item' => $item,
            'changelogs' => $changelogs,
        ]);
    }

    public function changelogsStore(Request $request, $id)
    {
        $item = Item::where('id', $id)->where('author_id', $this->author->id)
            ->approved()
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'version' => ['required', 'string', 'max:100'],
            'body' => ['required', 'string', 'max:3000'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back()->withInput();
        }

        $changelogExists = ItemChangeLog::where('item_id', $item->id)
            ->where('version', $request->version)
            ->first();
        if ($changelogExists) {
            toastr()->error(translate('The changelog version already exists'));
            return back();
        }

        $changelog = new ItemChangeLog();
        $changelog->item_id = $item->id;
        $changelog->version = $request->version;
        $changelog->body = $request->body;
        $changelog->save();

        toastr()->success(translate('The changelog has been created successfully'));
        return back();
    }

    public function changelogsDelete($id, $changelog_id)
    {
        $item = Item::where('id', $id)->where('author_id', $this->author->id)
            ->approved()
            ->firstOrFail();

        $changelog = ItemChangeLog::where('id', $changelog_id)
            ->where('item_id', $item->id)
            ->firstOrFail();

        $changelog->delete();
        toastr()->success(translate('The changelog has been deleted successfully'));
        return back();
    }

    public function history($id)
    {
        $item = Item::where('id', $id)
            ->where('author_id', $this->author->id)
            ->whereIn('status', [Item::STATUS_SOFT_REJECTED, Item::STATUS_APPROVED, Item::STATUS_RESUBMITTED])
            ->firstOrFail();

        $itemHistories = ItemHistory::where('item_id', $item->id)
            ->orderbyDesc('id')->paginate(10);

        return theme_view('workspace.items.history', [
            'item' => $item,
            'itemHistories' => $itemHistories,
        ]);
    }

    public function discount($id)
    {
        $item = Item::where('id', $id)
            ->where('author_id', $this->author->id)
            ->whereIn('status', [Item::STATUS_SOFT_REJECTED, Item::STATUS_APPROVED, Item::STATUS_RESUBMITTED])
            ->firstOrFail();

        return theme_view('workspace.items.discount', ['item' => $item]);
    }

    public function discountCreate(Request $request, $id)
    {
        $item = Item::where('id', $id)->where('author_id', $this->author->id)
            ->approved()
            ->firstOrFail();

        abort_if($item->hasDiscount(), 403);

        $itemSettings = settings('item');

        $validator = Validator::make($request->all(), [
            'regular_percentage' => ['required', 'integer', 'min:1', 'max:' . @$itemSettings->discount_max_percentage],
            'extended_percentage' => ['nullable', 'integer', 'min:1', 'max:' . @$itemSettings->discount_max_percentage],
            'starting_date' => ['required', 'date'],
            'ending_date' => ['required', 'date'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back()->withInput();
        }

        $startingDate = Carbon::parse($request->starting_date)->format('Y-m-d');
        if ($startingDate < Carbon::now()->format('Y-m-d')) {
            toastr()->error(translate('The starting date cannot be in the past'));
            return back()->withInput();
        }

        $endingDate = Carbon::parse($request->ending_date)->format('Y-m-d');
        if (@$itemSettings->discount_max_days > 0) {
            if ($endingDate > Carbon::now()->addDays(@$itemSettings->discount_max_days)->format('Y-m-d')) {
                toastr()->error(translate('The discount maximum days should be less or equal :days days', [
                    'days' => @$itemSettings->discount_max_days,
                ]));
                return back()->withInput();
            }
        }

        if ($startingDate == $endingDate) {
            toastr()->error(translate('The discount cannot start and end at same day'));
            return back()->withInput();
        }

        if (@$itemSettings->discount_tb > 0 && $item->last_discount_at) {
            $discountTb = Carbon::now()->subDays(@$itemSettings->discount_tb);
            if ($item->last_discount_at > $discountTb) {
                toastr()->error(translate('You can only create a discount every :days days', [
                    'days' => @$itemSettings->discount_tb,
                ]));
                return back()->withInput();
            }
        }

        if (@$itemSettings->discount_tb_pch > 0 && $item->price_updated_at) {
            $discountTbPch = Carbon::now()->subDays(@$itemSettings->discount_tb_pch);
            if ($item->price_updated_at > $discountTbPch) {
                toastr()->error(translate('The discount cannot be created until :days days after the price change', [
                    'days' => @$itemSettings->discount_tb_pch,
                ]));
                return back()->withInput();
            }
        }

        $regularDiscountAmount = ($item->regular_price * $request->regular_percentage) / 100;
        $regularPrice = intval(ceil(($item->regular_price - $regularDiscountAmount)), 0);

        $extendedPrice = null;
        if ($request->filled('extended_percentage')) {
            $extendedDiscountAmount = ($item->extended_price * $request->extended_percentage) / 100;
            $extendedPrice = intval(ceil(($item->extended_price - $extendedDiscountAmount)), 0);
        }

        $discount = new ItemDiscount();
        $discount->item_id = $item->id;
        $discount->regular_percentage = $request->regular_percentage;
        $discount->regular_price = $regularPrice;
        $discount->extended_percentage = $request->extended_percentage ?? null;
        $discount->extended_price = $extendedPrice;
        $discount->starting_at = $startingDate;
        $discount->ending_at = $endingDate;
        $discount->save();

        toastr()->success(translate('The discount has been created successfully'));
        return back();
    }

    public function discountDelete(Request $request, $id)
    {
        $item = Item::where('id', $id)->where('author_id', $this->author->id)
            ->approved()
            ->firstOrFail();

        if ($item->hasDiscount() && $item->discount->isInactive()) {
            $item->discount->delete();
            toastr()->success(translate('The discount has been deleted successfully'));
        }

        return back();
    }

    public function statistics($id)
    {
        $item = Item::where('id', $id)
            ->where('author_id', $this->author->id)
            ->whereIn('status', [Item::STATUS_SOFT_REJECTED, Item::STATUS_APPROVED, Item::STATUS_RESUBMITTED])
            ->firstOrFail();

        if (request()->filled('period')) {
            $period = request()->input('period');
            $startDate = Date::parse($period)->startOfMonth();
            $endDate = Date::parse($period)->endOfMonth();
        } else {
            $startDate = Date::now()->startOfMonth();
            $endDate = Date::now()->endOfMonth();
        }

        $counters = $this->generateCounters($item, $startDate, $endDate);
        $charts['sales'] = $this->generateSalesChartData($item, $startDate, $endDate);
        $topPurchasingCountries = $this->getTopPurchasingCountries($item, $startDate, $endDate);
        $geoCountries = $this->getGeoCountries($item, $startDate, $endDate);
        $charts['views'] = $this->generateViewsChartData($item, $startDate, $endDate);
        $referrals = $this->generateReferralsData($item, $startDate, $endDate);

        return theme_view('workspace.items.statistics', [
            'item' => $item,
            'counters' => $counters,
            'charts' => $charts,
            'topPurchasingCountries' => $topPurchasingCountries,
            'geoCountries' => $geoCountries,
            'referrals' => $referrals,
        ]);
    }

    private function generateCounters($item, $startDate, $endDate)
    {
        $sales = Sale::active()
            ->where('item_id', $item->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        $counters['total_sales'] = $sales->count();
        $counters['total_sales_amount'] = $sales->sum('price');
        $counters['total_earnings'] = $sales->sum('author_earning');

        $counters['total_views'] = ItemView::where('item_id', $item->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->count();

        return $counters;
    }

    private function generateSalesChartData($item, $startDate, $endDate)
    {
        $chart['title'] = translate('Sales');
        $dates = chartDates($startDate, $endDate);

        $sales = Sale::active()
            ->where('item_id', $item->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $salesData = $dates->merge($sales);

        $chart['labels'] = [];
        $chart['data'] = [];
        foreach ($salesData as $date => $count) {
            $label = Date::parse($date)->format('d M');
            $chart['labels'][] = $label;
            $chart['data'][] = $count;
        }

        $chart['max'] = (max($chart['data']) > 9) ? max($chart['data']) + 2 : 10;

        return $chart;
    }

    private function getGeoCountries($item, $startDate, $endDate)
    {
        return Sale::active()
            ->where('item_id', $item->id)
            ->whereNotNull('country')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->select('country', DB::raw('COUNT(*) as total_sales'))
            ->groupBy('country')
            ->orderbyDesc('total_sales')
            ->get();
    }

    private function getTopPurchasingCountries($item, $startDate, $endDate)
    {
        return Sale::active()
            ->where('item_id', $item->id)
            ->whereNotNull('country')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->select('country', DB::raw('SUM(author_earning) as total_earnings'))
            ->groupBy('country')
            ->orderbyDesc('total_earnings')
            ->limit(6)
            ->get();
    }

    private function generateViewsChartData($item, $startDate, $endDate)
    {
        $chart['title'] = translate('Views');
        $dates = chartDates($startDate, $endDate);

        $sales = ItemView::where('item_id', $item->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $salesData = $dates->merge($sales);

        $chart['labels'] = [];
        $chart['data'] = [];
        foreach ($salesData as $date => $count) {
            $label = Date::parse($date)->format('d M');
            $chart['labels'][] = $label;
            $chart['data'][] = $count;
        }

        $chart['max'] = (max($chart['data']) > 9) ? max($chart['data']) + 2 : 10;

        return $chart;
    }

    private function generateReferralsData($item, $startDate, $endDate)
    {
        return ItemView::where('item_id', $item->id)
            ->whereNotNull('referrer')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->select('referrer', DB::raw('COUNT(*) as total_views'))
            ->groupBy('referrer')
            ->orderbyDesc('total_views')
            ->limit(10)
            ->get();
    }

    public function download(Request $request, $id)
    {
        $item = Item::where('id', $id)->where('author_id', $this->author->id)
            ->approved()->firstOrFail();
        try {
            $response = $item->download();
            if (isset($response->type) && $response->type == "error") {
                throw new Exception($response->message);
            }
            return $response;
        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return back();
        }
    }

    public function destroy($id)
    {
        $item = Item::where('id', $id)->where('author_id', $this->author->id)
            ->whereNotIn('status', [Item::STATUS_HARD_REJECTED, Item::STATUS_DELETED])
            ->firstOrFail();
        try {
            if ($item->isApproved()) {
                $item->softDelete();
            } else {
                $item->delete();
            }
            toastr()->success(translate('The item has been deleted successfully'));
            return back();
        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return back();
        }
    }
}
