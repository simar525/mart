@extends('themes.basic.layouts.single')
@section('title', translate('Categories'))
@section('breadcrumbs', Breadcrumbs::render('categories'))
@section('header_v2', true)
@section('container', 'container-custom')
@section('content')
    @if ($categories->count() > 0)
        <ul class="list-group">
            @foreach ($categories as $category)
                <a href="{{ route('categories.category', $category->slug) }}"
                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center bg-light p-4">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-tags fa-rtl me-2"></i>
                        <strong>{{ $category->name }}</strong>
                    </h5>
                </a>
                @foreach ($category->subCategories as $subCategory)
                    <a href="{{ route('categories.sub-category', [$category->slug, $subCategory->slug]) }}"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-4">
                        <span class="ms-5">
                            <i class="fa-solid fa-tag fa-rtl me-2"></i>
                            <span>{{ $subCategory->name }}</span>
                        </span>
                    </a>
                @endforeach
            @endforeach
        </ul>
        {{ $categories->links() }}
    @else
        <div class="card-v border p-5 text-center">
            <span class="text-muted">{{ translate('No Categories found') }}</span>
        </div>
    @endif
@endsection
