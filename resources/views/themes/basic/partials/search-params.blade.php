<div class="card-v card-bg border p-4 mb-4">
    <h5 class="mb-4">{{ translate('Options') }}</h5>
    <div class="filter-item">
        <div class="form-check">
            <input class="form-check-input search-param" type="checkbox" name="free" value="true" id="searchParam1">
            <label class="form-check-label" for="searchParam1">{{ translate('Free') }}</label>
        </div>
    </div>
    <div class="filter-item">
        <div class="form-check">
            <input class="form-check-input search-param" type="checkbox" name="on_sale" value="true"
                id="searchParam2">
            <label class="form-check-label" for="searchParam2">{{ translate('On Sale') }}</label>
        </div>
    </div>
    <div class="filter-item">
        <div class="form-check">
            <input class="form-check-input search-param" type="checkbox" name="best_selling" value="true"
                id="searchParam3">
            <label class="form-check-label" for="searchParam3">{{ translate('Best Selling') }}</label>
        </div>
    </div>
    <div class="filter-item">
        <div class="form-check">
            <input class="form-check-input search-param" type="checkbox" name="trending" value="true"
                id="searchParam4">
            <label class="form-check-label" for="searchParam4">{{ translate('Trending') }}</label>
        </div>
    </div>
    <div class="filter-item">
        <div class="form-check">
            <input class="form-check-input search-param" type="checkbox" name="featured" value="true"
                id="searchParam16">
            <label class="form-check-label" for="searchParam16">{{ translate('Featured') }}</label>
        </div>
    </div>
</div>
<div class="card-v card-bg border p-4 mb-4">
    <h5 class="mb-4">{{ translate('Price') }}</h5>
    <div class="filter-item">
        <div class="d-flex align-items-center gap-2">
            <input id="priceForm" type="text" name="min_price" class="form-control form-control-md"
                placeholder="{{ translate('min') }}" value="{{ request()->input('min_price') }}" />
            <span>-</span>
            <input id="priceTo" type="text" name="max_price" class="form-control form-control-md"
                placeholder="{{ translate('max') }}" value="{{ request()->input('max_price') }}" />
            <button id="searchByPrice" class="btn btn-primary btn-md btn-padding">
                <i class="fa fa-arrow-right fa-rtl"></i>
            </button>
        </div>
    </div>
</div>
@if (@$settings->item->reviews_status)
    <div class="card-v card-bg border p-4 mb-4">
        <h5 class="mb-4">{{ translate('Rating') }}</h5>
        <div class="filter-item">
            <div class="form-check">
                <input class="form-check-input search-param" type="radio" name="stars" value=""
                    id="searchParam5">
                <label class="form-check-label" for="searchParam5">{{ translate('Show All') }}</label>
            </div>
        </div>
        <div class="filter-item">
            <div class="form-check">
                <input class="form-check-input search-param" type="radio" name="stars" value="5"
                    id="searchParam6">
                <label class="form-check-label" for="searchParam6">
                    <div class="row g-2 row-cols-auto align-items-center">
                        <div class="col">
                            @include('themes.basic.partials.rating-stars', [
                                'stars' => 5,
                            ])
                        </div>
                        <div class="col">
                            {{ translate('5 stars') }}
                        </div>
                    </div>
                </label>
            </div>
        </div>
        <div class="filter-item">
            <div class="form-check">
                <input class="form-check-input search-param" type="radio" name="stars" value="4"
                    id="searchParam7">
                <label class="form-check-label" for="searchParam7">
                    <div class="row g-2 row-cols-auto align-items-center">
                        <div class="col">
                            @include('themes.basic.partials.rating-stars', [
                                'stars' => 4,
                            ])
                        </div>
                        <div class="col">
                            {{ translate('4 stars') }}
                        </div>
                    </div>
                </label>
            </div>
        </div>
        <div class="filter-item">
            <div class="form-check">
                <input class="form-check-input search-param" type="radio" name="stars" value="3"
                    id="searchParam8">
                <label class="form-check-label" for="searchParam8">
                    <div class="row g-2 row-cols-auto align-items-center">
                        <div class="col">
                            @include('themes.basic.partials.rating-stars', [
                                'stars' => 3,
                            ])
                        </div>
                        <div class="col">
                            {{ translate('3 stars') }}
                        </div>
                    </div>
                </label>
            </div>
        </div>
        <div class="filter-item">
            <div class="form-check">
                <input class="form-check-input search-param" type="radio" name="stars" value="2"
                    id="searchParam9">
                <label class="form-check-label" for="searchParam9">
                    <div class="row g-2 row-cols-auto align-items-center">
                        <div class="col">
                            @include('themes.basic.partials.rating-stars', [
                                'stars' => 2,
                            ])
                        </div>
                        <div class="col">
                            {{ translate('2 stars') }}
                        </div>
                    </div>
                </label>
            </div>
        </div>
        <div class="filter-item">
            <div class="form-check">
                <input class="form-check-input search-param" type="radio" name="stars" value="1"
                    id="searchParam10">
                <label class="form-check-label" for="searchParam10">
                    <div class="row g-2 row-cols-auto align-items-center">
                        <div class="col">
                            @include('themes.basic.partials.rating-stars', [
                                'stars' => 1,
                            ])
                        </div>
                        <div class="col">
                            {{ translate('1 star') }}
                        </div>
                    </div>
                </label>
            </div>
        </div>
    </div>
@endif
<div class="card-v card-bg border p-4 mb-4">
    <h5 class="mb-4">{{ translate('Date Added') }}</h5>
    <div class="filter-item">
        <div class="form-check">
            <input class="form-check-input search-param" type="radio" name="date" value=""
                id="searchParam11">
            <label class="form-check-label" for="searchParam11">{{ translate('Any time') }}</label>
        </div>
    </div>
    <div class="filter-item">
        <div class="form-check">
            <input class="form-check-input search-param" type="radio" name="date" value="this_month"
                id="searchParam12">
            <label class="form-check-label" for="searchParam12">{{ translate('This month') }}</label>
        </div>
    </div>
    <div class="filter-item">
        <div class="form-check">
            <input class="form-check-input search-param" type="radio" name="date" value="last_month"
                id="searchParam13">
            <label class="form-check-label" for="searchParam13">{{ translate('Last month') }}</label>
        </div>
    </div>
    <div class="filter-item">
        <div class="form-check">
            <input class="form-check-input search-param" type="radio" name="date" value="this_year"
                id="searchParam14">
            <label class="form-check-label" for="searchParam14">{{ translate('This year') }}</label>
        </div>
    </div>
    <div class="filter-item">
        <div class="form-check">
            <input class="form-check-input search-param" type="radio" name="date" value="last_year"
                id="searchParam15">
            <label class="form-check-label" for="searchParam15">{{ translate('Last year') }}</label>
        </div>
    </div>
</div>
