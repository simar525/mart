@extends('admin.layouts.grid')
@section('section', translate('Settings'))
@section('title', translate('Buyer Taxes'))
@section('create', route('admin.settings.buyer-taxes.create'))
@section('container', 'container-max-xl')
@section('content')
    <div class="card">
        <table class="table datatable w-100">
            <thead>
                <tr>
                    <th class="tb-w-2x">#</th>
                    <th class="tb-w-7x">{{ translate('Name') }}</th>
                    <th class="tb-w-7x">{{ translate('Rate') }}</th>
                    <th class="tb-w-7x">{{ translate('Countries') }}</th>
                    <th class="tb-w-7x">{{ translate('Created date') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($buyerTaxes as $tax)
                    <tr>
                        <td>{{ $tax->id }}</td>
                        <td>
                            <a href="{{ route('admin.settings.buyer-taxes.edit', $tax->id) }}" class="text-dark">
                                {{ $tax->name }}
                            </a>
                        </td>
                        <td>{{ $tax->rate }}%</td>
                        <td>
                            @if (count($tax->countries) > 3)
                                {{ translate(':count Countries', ['count' => count($tax->countries)]) }}
                            @else
                                {{ implode(
                                    ', ',
                                    array_map(function ($country) {
                                        return countries($country);
                                    }, $tax->countries),
                                ) }}
                            @endif
                        </td>
                        <td>{{ dateFormat($tax->created_at) }}</td>
                        <td>
                            <div class="text-end">
                                <button type="button" class="btn btn-sm rounded-3" data-bs-toggle="dropdown"
                                    aria-expanded="true">
                                    <i class="fa fa-ellipsis-v fa-sm text-muted"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-sm-end" data-popper-placement="bottom-end">
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('admin.settings.buyer-taxes.edit', $tax->id) }}"><i
                                                class="fa-regular fa-pen-to-square me-2"></i>{{ translate('Edit') }}</a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider" />
                                    </li>
                                    <li>
                                        <form action="{{ route('admin.settings.buyer-taxes.destroy', $tax->id) }}"
                                            method="POST">
                                            @csrf @method('DELETE')
                                            <button class="action-confirm dropdown-item text-danger"><i
                                                    class="far fa-trash-alt me-2"></i>{{ translate('Delete') }}</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
