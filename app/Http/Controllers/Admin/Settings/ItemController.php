<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Models\StorageProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class ItemController extends Controller
{
    public function index()
    {
        $storageProviders = StorageProvider::all();
        return view('admin.settings.item', ['storageProviders' => $storageProviders]);
    }

    public function update(Request $request)
    {
        $rules = [
            'item.maximum_tags' => ['required', 'integer', 'min:1', 'max:100'],
            'item.minimum_price' => ['required', 'integer', 'min:1'],
            'item.maximum_price' => ['required', 'integer', 'min:1'],
            'item.trending_number' => ['required', 'integer', 'min:1'],
            'item.best_selling_number' => ['required', 'integer', 'min:1'],
            'item.max_files' => ['required', 'integer', 'min:1'],
            'item.max_file_size' => ['required', 'integer', 'min:1'],
            'item.preview_image_width' => ['required', 'integer', 'min:1'],
            'item.preview_image_height' => ['required', 'integer', 'min:1'],
            'item.maximum_screenshots' => ['required', 'integer', 'min:1', 'max:100'],
            'item.convert_images_webp' => ['required', 'boolean'],
            'item.file_duration' => ['required', 'integer', 'min:1'],
            'item.main_file_types' => ['required', 'string'],
        ];

        if ($request->has('item.discount_status')) {
            $rules['item.discount_max_percentage'] = ['required', 'integer', 'min:1', 'max:90'];
            $rules['item.discount_max_days'] = ['required', 'integer', 'min:0', 'max:365'];
            $rules['item.discount_tb'] = ['required', 'integer', 'min:0', 'max:365'];
            $rules['item.discount_tb_pch'] = ['required', 'integer', 'min:0', 'max:365'];
        } else {
            $rules['item.discount_max_percentage'] = ['nullable', 'integer', 'min:1', 'max:90'];
            $rules['item.discount_max_days'] = ['nullable', 'integer', 'min:0', 'max:365'];
            $rules['item.discount_tb'] = ['nullable', 'integer', 'min:0', 'max:365'];
            $rules['item.discount_tb_pch'] = ['nullable', 'integer', 'min:0', 'max:365'];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back();
        }

        $requestData = $request->except('_token');

        $itemSettings = $requestData['item'];

        if (($itemSettings['maximum_screenshots'] + 2) > $itemSettings['max_files']) {
            toastr()->error(translate('Max files cannot be less than total screenshots + item preview image + item main files'));
            return back();
        }

        $itemSettings['max_file_size'] = ($itemSettings['max_file_size'] * 1048576);

        $storageProvider = StorageProvider::where('alias', $request->storage_provider)->firstOrFail();

        if (!$storageProvider->isLocal()) {
            $credentials = $request->credentials[$storageProvider->alias];
            foreach ($credentials as $key => $value) {
                if (!array_key_exists($key, (array) $storageProvider->credentials)) {
                    toastr()->error(translate('Mismatch credentials'));
                    return back();
                }
            }
        }

        $itemSettings['buy_now_button'] = $request->has('item.buy_now_button') ? 1 : 0;
        $itemSettings['adding_require_review'] = $request->has('item.adding_require_review') ? 1 : 0;
        $itemSettings['updating_require_review'] = $request->has('item.updating_require_review') ? 1 : 0;
        $itemSettings['discount_status'] = ($request->has('item.discount_status')) ? 1 : 0;
        $itemSettings['free_item_option'] = ($request->has('item.free_item_option')) ? 1 : 0;
        $itemSettings['free_item_total_downloads'] = ($request->has('item.free_item_total_downloads')) ? 1 : 0;
        $itemSettings['reviews_status'] = ($request->has('item.reviews_status')) ? 1 : 0;
        $itemSettings['comments_status'] = ($request->has('item.comments_status')) ? 1 : 0;
        $itemSettings['changelogs_status'] = ($request->has('item.changelogs_status')) ? 1 : 0;
        $itemSettings['external_file_link_option'] = ($request->has('item.external_file_link_option')) ? 1 : 0;

        $update = Settings::updateSettings('item', $itemSettings);
        if (!$update) {
            toastr()->error(translate('Updated Error'));
            return back();
        }

        if (!$storageProvider->isLocal()) {
            $storageProvider->credentials = $credentials;
            $storageProvider->update();
            $storageProvider->processor::setCredentials($storageProvider->credentials);
        }

        setEnv('FILESYSTEM_DRIVER', $storageProvider->alias);
        toastr()->success(translate('Updated Successfully'));
        return back();

    }

    public function storageTest(Request $request)
    {
        $defaultStorage = config('filesystems.default');
        if ($defaultStorage != "local") {
            try {
                $disk = Storage::disk($defaultStorage);
                $upload = $disk->put('test.txt', 'public');
                if (!$upload) {
                    toastr()->error(translate('Connection Failed'));
                    return back();
                }
                $disk->delete('test.txt');
                toastr()->success(translate('Connected successfully'));
                return back();
            } catch (\Exception $e) {
                toastr()->error(translate('Connection Failed'));
                return back();
            }
        }
    }
}
