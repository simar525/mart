@extends('admin.layouts.form')
@section('section', translate('Main Categories'))
@section('title', $category->name)
@section('back', route('admin.categories.index'))
@section('container', 'container-max-lg')
@section('content')
    <div class="mb-3">
        <a class="btn btn-outline-secondary" href="{{ route('categories.category', $category->slug) }}" target="_blank"><i
                class="fa fa-eye me-2"></i>{{ translate('View') }}</a>
    </div>
    <form id="vironeer-submited-form" action="{{ route('admin.categories.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card p-2 pb-3">
            <div class="card-body">
                <div class="row g-3 row-cols-1">
                    <div class="col">
                        <label class="form-label">{{ translate('Name') }} </label>
                        <input type="text" name="name" class="form-control" value="{{ $category->name }}" required />
                    </div>
                    <div class="col">
                        <label class="form-label">{{ translate('Slug') }} </label>
                        <input type="text" name="slug" id="show_slug" class="form-control"
                            value="{{ $category->slug }}" required />
                    </div>
                    <div class="col">
                        @include('admin.partials.input-price', [
                            'label' => translate('Regular License Buyer fee'),
                            'name' => 'regular_buyer_fee',
                            'value' => $category->regular_buyer_fee,
                            'required' => true,
                        ])
                    </div>
                    <div class="col">
                        @include('admin.partials.input-price', [
                            'label' => translate('Extended License Buyer fee'),
                            'name' => 'extended_buyer_fee',
                            'value' => $category->extended_buyer_fee,
                            'required' => true,
                        ])
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
