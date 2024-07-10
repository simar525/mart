@extends('themes.basic.blog.layout')
@section('title', translate('Categories'))
@section('breadcrumbs', Breadcrumbs::render('blog_categories'))
@section('header', true)
@section('content')
    @if ($blogCategories->count() > 0)
        <div class="list-group">
            @foreach ($blogCategories as $blogCategory)
                <a href="{{ $blogCategory->getLink() }}"
                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-4">
                    <span>{{ $blogCategory->name }} ({{ $blogCategory->articles_count }})</span>
                    <i class="fa-solid fa-tag"></i>
                </a>
            @endforeach
        </div>
        {{ $blogCategories->links() }}
    @else
        <div class="card-v border p-5 text-center">
            <span class="text-muted">{{ translate('No categories found') }}</span>
        </div>
    @endif
@endsection
