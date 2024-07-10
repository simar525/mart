@extends('themes.basic.layouts.single')
@section('title', $category->name)
@section('header_v4', true)
@section('breadcrumbs', Breadcrumbs::render('categories.category', $category))
@section('container', 'container-custom')
@section('content')
    <x-ad alias="category_page_top" @class('mb-5') />
    <div class="row g-3 align-items-center mb-4">
        <div class="col">
            <h5 class="mb-0">
                @if (request()->query->count() > 0)
                    {{ translate('Your search results for the ":name" category', [
                        'name' => strtolower($category->name),
                    ]) }}
                @else
                    {{ translate('All results for the ":name" category', [
                        'name' => strtolower($category->name),
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
            <div class="card-v card-bg border p-4 mb-4">
                <h5 class="mb-4">{{ $category->name }}</h5>
                @foreach ($category->subCategories as $subCategory)
                    <div class="filter-item {{ !$loop->last ? 'mb-3' : '' }}">
                        <a href="{{ route('categories.sub-category', [$category->slug, $subCategory->slug] + request()->all()) }}"
                            class="text-dark">
                            <div class="row align-items-center g-3">
                                <div class="col">
                                    {{ $subCategory->name }}
                                </div>
                                <div class="col-auto">
                                    <i class="fa-solid fa-tag fa-rtl"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
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
