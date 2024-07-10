@extends('themes.basic.layouts.single')
@section('title', translate('Items'))
@section('breadcrumbs', Breadcrumbs::render('items'))
@section('container', 'container-custom')
@section('header_v4', true)
@section('content')
    <x-ad alias="search_page_top" @class('mb-5') />
    <div class="row g-3 align-items-center mb-4">
        <div class="col">
            <h5 class="mb-0">
                @if (request()->query->count() > 0)
                    {{ translate('Your search results') }}
                @else
                    {{ translate('All Items') }}
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
    <x-ad alias="search_page_bottom" @class('mt-5') />
@endsection
