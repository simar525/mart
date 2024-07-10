@extends('admin.layouts.form')
@section('section', translate('Main Categories'))
@section('title', translate('New Category'))
@section('back', route('admin.categories.index'))
@section('container', 'container-max-lg')
@section('content')
    <form id="vironeer-submited-form" action="{{ route('admin.categories.store') }}" method="POST">
        @csrf
        <div class="card p-2 pb-3">
            <div class="card-body">
                <div class="row g-3 row-cols-1">
                    <div class="col">
                        <label class="form-label">{{ translate('Name') }} </label>
                        <input type="text" name="name" id="create_slug" class="form-control" value="{{ old('name') }}"
                            required autofocus />
                    </div>
                    <div class="col">
                        <label class="form-label">{{ translate('Slug') }} </label>
                        <input type="text" name="slug" id="show_slug" class="form-control" value="{{ old('slug') }}"
                            required />
                    </div>
                    <div class="col">
                        @include('admin.partials.input-price', [
                            'label' => translate('Regular License Buyer fee'),
                            'name' => 'regular_buyer_fee',
                            'required' => true,
                        ])
                    </div>
                    <div class="col">
                        @include('admin.partials.input-price', [
                            'label' => translate('Extended License Buyer fee'),
                            'name' => 'extended_buyer_fee',
                            'required' => true,
                        ])
                    </div>
                </div>
            </div>
        </div>
    </form>
    @push('top_scripts')
        <script>
            "use strict";
            let GET_SLUG_URL = "{{ route('admin.categories.slug') }}";
        </script>
    @endpush
@endsection
