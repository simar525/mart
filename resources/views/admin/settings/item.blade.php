@extends('admin.layouts.form')
@section('section', translate('Settings'))
@section('title', translate('Item Settings'))
@section('container', 'container-max-xl')
@section('content')
    <form id="vironeer-submited-form" action="{{ route('admin.settings.item.update') }}" method="POST">
        @csrf
        <div class="card mb-4">
            <div class="card-header">{{ translate('General') }}</div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-4">
                        <label class="form-label">{{ translate('Maximum Tags') }}</label>
                        <input type="number" name="item[maximum_tags]" class="form-control" min="1" max="100"
                            step="any" value="{{ @$settings->item->maximum_tags }}" required>
                        <div class="form-text">{{ translate('Maximum tags that the author can set for item') }}</div>
                    </div>
                    <div class="col-lg-4">
                        @include('admin.partials.input-price', [
                            'label' => translate('Minimum Price'),
                            'name' => 'item[minimum_price]',
                            'value' => @$settings->item->minimum_price,
                            'min' => 1,
                            'required' => true,
                        ])
                    </div>
                    <div class="col-lg-4">
                        @include('admin.partials.input-price', [
                            'label' => translate('Maximum Price'),
                            'name' => 'item[maximum_price]',
                            'value' => @$settings->item->maximum_price,
                            'min' => 1,
                            'required' => true,
                        ])
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">{{ translate('Actions') }}</div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-lg-4">
                        <label class="form-label">{{ translate('Adding the items require review') }}</label>
                        <input type="checkbox" name="item[adding_require_review]" data-toggle="toggle" data-height="38px"
                            data-on="{{ translate('Yes') }}" data-off="{{ translate('No') }}"
                            {{ @$settings->item->adding_require_review ? 'checked' : '' }}>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">{{ translate('Updating the items require review') }}</label>
                        <input type="checkbox" name="item[updating_require_review]" data-toggle="toggle" data-height="38px"
                            data-on="{{ translate('Yes') }}" data-off="{{ translate('No') }}"
                            {{ @$settings->item->updating_require_review ? 'checked' : '' }}>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">{{ translate('Free item option') }}</label>
                        <input type="checkbox" name="item[free_item_option]" data-toggle="toggle" data-height="38px"
                            data-on="{{ translate('Yes') }}" data-off="{{ translate('No') }}"
                            {{ @$settings->item->free_item_option ? 'checked' : '' }}>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">{{ translate('Show free item total downloads') }}</label>
                        <input type="checkbox" name="item[free_item_total_downloads]" data-toggle="toggle"
                            data-height="38px" data-on="{{ translate('Yes') }}" data-off="{{ translate('No') }}"
                            {{ @$settings->item->free_item_total_downloads ? 'checked' : '' }}>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">{{ translate('Item Changelogs') }}</label>
                        <input type="checkbox" name="item[changelogs_status]" data-toggle="toggle" data-height="38px"
                            data-on="{{ translate('Enable') }}" data-off="{{ translate('Disable') }}"
                            {{ @$settings->item->changelogs_status ? 'checked' : '' }}>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">{{ translate('Item Reviews') }}</label>
                        <input type="checkbox" name="item[reviews_status]" data-toggle="toggle" data-height="38px"
                            data-on="{{ translate('Enable') }}" data-off="{{ translate('Disable') }}"
                            {{ @$settings->item->reviews_status ? 'checked' : '' }}>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">{{ translate('Item Comments') }}</label>
                        <input type="checkbox" name="item[comments_status]" data-toggle="toggle" data-height="38px"
                            data-on="{{ translate('Enable') }}" data-off="{{ translate('Disable') }}"
                            {{ @$settings->item->comments_status ? 'checked' : '' }}>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">{{ translate('External file link option') }}</label>
                        <input type="checkbox" name="item[external_file_link_option]" data-toggle="toggle"
                            data-height="38px" data-on="{{ translate('Yes') }}" data-off="{{ translate('No') }}"
                            {{ @$settings->item->external_file_link_option ? 'checked' : '' }}>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">{{ translate('Buy Now Button') }}</label>
                        <input type="checkbox" name="item[buy_now_button]" data-toggle="toggle" data-height="38px"
                            data-on="{{ translate('Enable') }}" data-off="{{ translate('Disable') }}"
                            {{ @$settings->item->buy_now_button ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">{{ translate('Discount') }}</div>
            <div class="card-body p-4">
                <div class="col-lg-4 mb-3">
                    <label class="form-label">{{ translate('Status') }}</label>
                    <input type="checkbox" id="discountStatus" name="item[discount_status]" data-toggle="toggle"
                        data-height="38px" @checked(@$settings->item->discount_status)>
                </div>
                <div id="discountDetails" class="row g-3 {{ !@$settings->item->discount_status ? 'd-none' : '' }}">
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Discount maximum percentage') }}</label>
                        <div class="input-group">
                            <input type="number" name="item[discount_max_percentage]" class="form-control"
                                placeholder="0" min="1" max="90"
                                value="{{ @$settings->item->discount_max_percentage }}">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">{{ translate('The maximum should be less than 90%') }}</div>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Discount maximum days') }}</label>
                        <div class="input-group">
                            <input type="number" name="item[discount_max_days]" class="form-control" placeholder="0"
                                min="0" max="365" value="{{ @$settings->item->discount_max_days }}">
                            <span class="input-group-text">{{ translate('Days') }}</span>
                        </div>
                        <div class="form-text">{{ translate('Enter 0 to disable it') }}</div>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Time between discounts') }}</label>
                        <div class="input-group">
                            <input type="number" name="item[discount_tb]" class="form-control" placeholder="0"
                                min="0" max="365" value="{{ @$settings->item->discount_tb }}">
                            <span class="input-group-text">{{ translate('Days') }}</span>
                        </div>
                        <div class="form-text">{{ translate('Enter 0 to disable it') }}</div>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Time between changing price and discount') }}</label>
                        <div class="input-group">
                            <input type="number" name="item[discount_tb_pch]" class="form-control" placeholder="0"
                                min="0" max="365" value="{{ @$settings->item->discount_tb_pch }}">
                            <span class="input-group-text">{{ translate('Days') }}</span>
                        </div>
                        <div class="form-text">{{ translate('Enter 0 to disable it') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">{{ translate('Trending And Best selling') }}</div>
            <div class="card-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Trending Items Number') }}</label>
                        <input type="number" class="form-control" name="item[trending_number]" min="1"
                            value="{{ @$settings->item->trending_number }}" required>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Best Selling Items Number') }}</label>
                        <input type="number" class="form-control" name="item[best_selling_number]" min="1"
                            value="{{ @$settings->item->best_selling_number }}" required>
                    </div>
                </div>
                <div class="alert alert-warning mb-0">
                    <i class="fa-regular fa-circle-question me-1"></i>
                    <span>{{ translate('You must setup the cron job to refresh the Items every 24 hours.') }}</span>
                    <a href="{{ route('admin.system.cronjob.index') }}">{{ translate('Setup Cron Job') }}<i
                            class="fa-solid fa-arrow-right fa-rtl ms-2"></i></a>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">
                {{ translate('Files') }}
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">{{ translate('Maximum Upload Files') }}</label>
                        <input type="number" name="item[max_files]" class="form-control"
                            min="{{ @$settings->item->maximum_screenshots + 2 }}" step="any"
                            value="{{ @$settings->item->max_files }}" required>
                        <div class="form-text">
                            {{ translate('Maximum files that can author upload every time, total screenshots + item preview image + item main files') }}
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ translate('Maximum File Size') }}</label>
                        <div class="input-group">
                            <input type="number" name="item[max_file_size]" class="form-control" min="1"
                                step="any" value="{{ @$settings->item->max_file_size / 1048576 }}" required>
                            <span class="input-group-text">{{ translate('MB') }}</span>
                        </div>
                        <div class="form-text">{{ translate('The size of each file the author can upload') }}</div>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Preview Image Width') }}</label>
                        <div class="input-group">
                            <input type="number" name="item[preview_image_width]" class="form-control" min="1"
                                step="any" value="{{ @$settings->item->preview_image_width }}" required>
                            <span class="input-group-text">{{ translate('px') }}</span>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Preview Image Height') }}</label>
                        <div class="input-group">
                            <input type="number" name="item[preview_image_height]" class="form-control" min="1"
                                step="any" value="{{ @$settings->item->preview_image_height }}" required>
                            <span class="input-group-text">{{ translate('px') }}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ translate('Maximum Screenshots') }}</label>
                        <input type="number" name="item[maximum_screenshots]" class="form-control" min="1"
                            step="any" max="100" value="{{ @$settings->item->maximum_screenshots }}" required>
                        <div class="form-text">{{ translate('Number between 1 to 100') }}</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ translate('Convert Images To WEBP') }}</label>
                        <select name="item[convert_images_webp]" class="form-select form-select-md">
                            <option value="0" @selected(@$settings->item->convert_images_webp == 0)>
                                {{ translate('No') }}</option>
                            <option value="1" @selected(@$settings->item->convert_images_webp == 1)>
                                {{ translate('Yes') }}</option>
                        </select>
                        <div class="form-text">
                            {{ translate('Convert uploaded images to WEBP (Preview Image and Screenshots)') }}</div>
                    </div>
                    <div class="col-lg-12">
                        <label class="form-label">{{ translate('File duration') }}</label>
                        <div class="input-group">
                            <input type="number" name="item[file_duration]" class="form-control" min="1"
                                step="any" value="{{ @$settings->item->file_duration }}" required>
                            <span class="input-group-text"><i
                                    class="fa-regular fa-clock me-1"></i>{{ translate('Hours') }}</span>
                        </div>
                        <div class="form-text">
                            {{ translate('Uploaded files will expire after this time if the author does not use them.') }}
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ translate('Main File Types') }}</label>
                        <input type="text" name="item[main_file_types]"
                            class="form-control form-control-md tags-input"
                            value="{{ @$settings->item->main_file_types }}" required>
                        <div class="form-text">
                            {{ translate('The allowed files to be uploaded as main file, like (ZIP, RAR, PDF, etc...)') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">
                {{ translate('Files Storage') }}
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label">{{ translate('Storage Provider') }}</label>
                        <select id="storage-provider" name="storage_provider" class="form-select form-select-md">
                            @foreach ($storageProviders as $storageProvider)
                                <option value="{{ $storageProvider->alias }}"
                                    {{ $storageProvider->isDefault() ? 'selected' : '' }}>{{ $storageProvider->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @foreach ($storageProviders as $storageProvider)
                        @if (!$storageProvider->isLocal())
                            @if ($storageProvider->credentials)
                                @foreach ($storageProvider->credentials as $key => $value)
                                    <div
                                        class="col-12 credentials credential-{{ $storageProvider->alias }} {{ !$storageProvider->isDefault() ? 'd-none' : '' }}">
                                        <label class="form-label capitalize">
                                            {{ str_replace('_', ' ', $key) }} :
                                        </label>
                                        <input type="text"
                                            name="credentials[{{ $storageProvider->alias }}][{{ $key }}]"
                                            value="{{ demo($value) }}" class="form-control remove-spaces">
                                    </div>
                                @endforeach
                            @endif
                        @endif
                    @endforeach
                </div>
                <div class="alert alert-primary mb-0">
                    <i class="fa-regular fa-circle-question me-1"></i>
                    {{ translate('When you change the storage provider, you must move all files form those paths to new storage provider.') }}
                    <div class="mt-2">
                        <h6>{{ translate('Local') }}</h6>
                        <ul>
                            <li><strong>public/images/items/</strong></li>
                            <li class="mb-0"><strong>storage/app/files/</strong></li>
                        </ul>
                        <h6>{{ translate('s3 and others') }}</h6>
                        <ul class="mb-0">
                            <li><strong>images/items/</strong></li>
                            <li class="mb-0"><strong>files/</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @if (config('filesystems.default') != 'local')
        <div class="card">
            <div class="card-header">
                {{ translate('Test Storage Connection') }}
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.settings.item.storage-test') }}" method="POST">
                    @csrf
                    <button class="btn btn-dark btn-lg w-100">{{ translate('Test Connection') }}</button>
                </form>
            </div>
        </div>
    @endif
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('vendor/libs/tags-input/bootstrap-tagsinput.css') }}">
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('vendor/libs/tags-input/bootstrap-tagsinput.min.js') }}"></script>
    @endpush
    @push('scripts')
        <script>
            "use strict";
            let storageProvider = $('#storage-provider');
            storageProvider.on('change', function() {
                let storageProviderValue = $(this).val();
                $('.credentials').addClass('d-none');
                $('.credential-' + storageProviderValue).removeClass('d-none');
            });

            let discountStatus = $('#discountStatus');
            discountStatus.on('change', function() {
                let discountDetails = $('#discountDetails');
                if ($(this).is(':checked')) {
                    discountDetails.removeClass('d-none');
                } else {
                    discountDetails.addClass('d-none');
                }
            })
        </script>
    @endpush
@endsection
