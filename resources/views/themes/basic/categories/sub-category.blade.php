@extends('themes.basic.layouts.single')
@section('title', $subCategory->name)
@section('breadcrumbs', Breadcrumbs::render('categories.sub-category', $category, $subCategory))
@section('header_v4', true)
@section('container', 'container-custom')
@section('content')
    <x-ad alias="category_page_top" @class('mb-5') />
    <div class="row g-3 align-items-center mb-4">
        <div class="col">
            <h5 class="mb-0">
                @if (request()->query->count() > 0)
                    {{ translate('Your search results for the ":name" category', [
                        'name' => strtolower($subCategory->name),
                    ]) }}
                @else
                    {{ translate('All results for the ":name" category', [
                        'name' => strtolower($subCategory->name),
                    ]) }}
                @endif
            </h5>
        </div>
        @if ($items->count() > 0)
            <div class="col d-none d-md-inline">
                @include('themes.basic.partials.grid-buttons')
            </div>
        @endif
    </div>
    <div class="row g-4">
        <div class="col-12 col-xl-3">
            @include('themes.basic.partials.search-params')
        </div>
        <div class="col-12 col-xl-9">
            @include('themes.basic.partials.search-items', [
                'items' => $items,
            ])
        </div>
    </div>
    <x-ad alias="category_page_bottom" @class('mt-5') />
@endsection
