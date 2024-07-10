@extends('reviewer.layouts.app')
@section('title', translate('Update Review: :item_name', ['item_name' => $itemUpdate->name]))
@section('update_download', true)
@section('content')
    <div class="dashboard-tabs">
        @include('reviewer.items.updated.includes.tabs-nav')
        <div class="dashboard-tabs-content">
            <div class="row g-3">
                <div class="col-lg-7 order-2 order-sm-0">
                    <div class="accordion" id="accordion">
                        @if ($itemUpdate->preview_image)
                            <div class="accordion-item border-0 mb-4">
                                <h2 class="accordion-header">
                                    <button class="accordion-button p-4" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                                        <i class="fa-regular fa-image me-2"></i>
                                        <h5 class="mb-0">{{ translate('Preview Image') }}</h5>
                                    </button>
                                </h2>
                                <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#accordion">
                                    <div class="accordion-body p-4 pt-1">
                                        <img class="img-fluid rounded-2" src="{{ $itemUpdate->getPreviewImageLink() }}"
                                            alt="{{ $itemUpdate->name }}">
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($itemUpdate->screenshots)
                            <div class="accordion-item border-0 mb-4">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed p-4" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                        <i class="fa-solid fa-camera-retro me-2"></i>
                                        <h5 class="mb-0">{{ translate('Screenshots') }}</h5>
                                    </button>
                                </h2>
                                <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#accordion">
                                    <div class="accordion-body p-4 pt-1">
                                        <div id="carouselAutoplaying" class="carousel slide" data-bs-ride="carousel">
                                            <div class="carousel-inner">
                                                @foreach ($itemUpdate->getScreenshotLinks() as $screenshot)
                                                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                                        <img class="d-block w-100 rounded-2" src="{{ $screenshot }}">
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button class="carousel-control-prev" type="button"
                                                data-bs-target="#carouselAutoplaying" data-bs-slide="prev">
                                                <i class="fa-solid fa-chevron-left fa-rtl"></i>
                                            </button>
                                            <button class="carousel-control-next" type="button"
                                                data-bs-target="#carouselAutoplaying" data-bs-slide="next">
                                                <i class="fa-solid fa-chevron-right fa-rtl"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="accordion-item border-0 mb-4">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed p-4" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                    <i class="fa-solid fa-bars-staggered me-2"></i>
                                    <h5 class="mb-0">{{ translate('Description') }}</h5>
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#accordion">
                                <div class="accordion-body p-4 pt-1">
                                    {!! $itemUpdate->description !!}
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-4">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed p-4" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                    <i class="fa-solid fa-list-ol me-2"></i>
                                    <h5 class="mb-0">{{ translate('Category And Attributes') }}</h5>
                                </button>
                            </h2>
                            <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#accordion">
                                <div class="accordion-body p-4 pt-1">
                                    <div class="row g-4">
                                        @if ($itemUpdate->options)
                                            @foreach ($itemUpdate->options as $key => $option)
                                                <div class="col-lg-12">
                                                    <div class="p-3 bg-light border rounded-2">
                                                        <h6 class="mb-3">{{ $key }}</h6>
                                                        <div class="row row-cols-auto g-2">
                                                            @if (is_array($option))
                                                                @foreach ($option as $subOption)
                                                                    <div class="col">
                                                                        <div
                                                                            class="badge bg-primary rounded-2 fw-light fs-6 px-3 py-2">
                                                                            {{ $subOption }}
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                <div
                                                                    class="badge bg-primary rounded-2 fw-light fs-6 px-3 py-2">
                                                                    {{ $option }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                        @if ($itemUpdate->version)
                                            <div class="col-lg-12">
                                                <div class="p-3 bg-light border rounded-2">
                                                    <h6 class="mb-3">{{ translate('Version') }}</h6>
                                                    <input type="text" class="form-control form-control-md bg-white"
                                                        value="{{ $itemUpdate->version }}" readonly>
                                                </div>
                                            </div>
                                        @endif
                                        @if ($itemUpdate->demo_link)
                                            <div class="col-lg-12">
                                                <div class="p-3 bg-light border rounded-2">
                                                    <h6 class="mb-3">{{ translate('Demo Link') }}</h6>
                                                    <div class="input-group">
                                                        <input type="text"
                                                            class="form-control form-control-md bg-white"
                                                            value="{{ $itemUpdate->demo_link }}" readonly>
                                                        <button class="btn btn-outline-secondary"
                                                            onclick="window.open('{{ $itemUpdate->demo_link }}')"><i
                                                                class="fa-solid fa-up-right-from-square"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-lg-12">
                                            <div class="p-3 bg-light border rounded-2">
                                                <h6 class="mb-3">{{ translate('Tags') }}</h6>
                                                <div class="row row-cols-auto g-2">
                                                    @foreach ($itemUpdate->getTags() as $tag)
                                                        <div class="col">
                                                            <div
                                                                class="badge bg-primary rounded-2 fw-light fs-6 px-3 py-2">
                                                                {{ $tag }}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if ($itemUpdate->getRegularPrice() || $itemUpdate->getExtendedPrice())
                            <div class="accordion-item border-0 mb-4">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed p-4" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false"
                                        aria-controls="collapse5">
                                        <i class="fa-solid fa-dollar-sign me-2"></i>
                                        <h5 class="mb-0">{{ translate('Licenses Price') }}</h5>
                                    </button>
                                </h2>
                                <div id="collapse5" class="accordion-collapse collapse" data-bs-parent="#accordion">
                                    <div class="accordion-body p-4 pt-1">
                                        <div class="row g-3">
                                            @if ($itemUpdate->getRegularPrice())
                                                <div class="col-lg-6">
                                                    <div class="p-3 bg-light border rounded-2">
                                                        <h6 class="mb-3">
                                                            {{ translate('Regular License Price') }}
                                                        </h6>
                                                        <div class="custom-input-group input-group">
                                                            @if (@$settings->currency->position == 1)
                                                                <span
                                                                    class="input-group-text px-3 fs-5 bg-white">{{ @$settings->currency->symbol }}</span>
                                                            @endif
                                                            <input type="number"
                                                                class="form-control form-control-md bg-white"
                                                                value="{{ $itemUpdate->getRegularPrice() }}" disabled>
                                                            @if (@$settings->currency->position == 2)
                                                                <span
                                                                    class="input-group-text px-4 fs-5 bg-white">{{ @$settings->currency->symbol }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($itemUpdate->getExtendedPrice())
                                                <div class="col-lg-6">
                                                    <div class="p-3 bg-light border rounded-2">
                                                        <h6 class="mb-3">
                                                            {{ translate('Extended License Price') }}
                                                        </h6>
                                                        <div class="custom-input-group input-group">
                                                            @if (@$settings->currency->position == 1)
                                                                <span
                                                                    class="input-group-text px-3 fs-5 bg-white">{{ @$settings->currency->symbol }}</span>
                                                            @endif
                                                            <input type="number"
                                                                class="form-control form-control-md bg-white"
                                                                value="{{ $itemUpdate->getExtendedPrice() }}" disabled>
                                                            @if (@$settings->currency->position == 2)
                                                                <span
                                                                    class="input-group-text px-4 fs-5 bg-white">{{ @$settings->currency->symbol }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (@$settings->item->free_item_option)
                            <div class="accordion-item border-0">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed p-4" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="false"
                                        aria-controls="collapse6">
                                        <i class="fa-regular fa-heart me-2"></i>
                                        <h5 class="mb-0">{{ translate('Free Item') }}</h5>
                                    </button>
                                </h2>
                                <div id="collapse6" class="accordion-collapse collapse" data-bs-parent="#accordion">
                                    <div class="accordion-body p-4 pt-1">
                                        <div class="row g-4">
                                            <div class="col-12">
                                                <div class="p-3 bg-light border rounded-2">
                                                    @if ($itemUpdate->isFree())
                                                        <div class="badge bg-primary rounded-2 fw-light fs-6 px-3 py-2">
                                                            {{ translate('Yes') }}
                                                        </div>
                                                    @else
                                                        <div class="badge bg-danger rounded-2 fw-light fs-6 px-3 py-2">
                                                            {{ translate('No') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            @if ($itemUpdate->isFree())
                                                <div class="col-12">
                                                    <div class="p-3 bg-light border rounded-2">
                                                        @if ($itemUpdate->isPurchasingEnabled())
                                                            <div
                                                                class="badge bg-primary rounded-2 fw-light fs-6 px-3 py-2">
                                                                {{ translate('Purchasing Enabled') }}
                                                            </div>
                                                        @else
                                                            <div class="badge bg-danger rounded-2 fw-light fs-6 px-3 py-2">
                                                                {{ translate('Purchasing Disabled') }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-5">
                    @include('reviewer.items.updated.includes.sidebar')
                </div>
            </div>
        </div>
    </div>
@endsection
