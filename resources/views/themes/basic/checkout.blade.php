@extends('themes.basic.layouts.single')
@section('noindex', true)
@section('title', translate('Checkout'))
@section('breadcrumbs', Breadcrumbs::render('checkout', $trx))
@section('header_v3', true)
@section('content')
    @if ($trx->isUnpaid())
        <livewire:checkout :trx="$trx" />
    @else
        <div class="card-v border">
            <div class="col-lg-6 m-auto">
                <div class="py-3 text-center">
                    <div class="mb-4">
                        <i class="fa fa-check-circle text-primary fa-5x"></i>
                    </div>
                    <h2 class="mb-3">{{ translate('Payment completed') }}</h2>
                    <p>
                        {{ translate('Your payment has been completed successfully. You can now browse your purchases and download the items') }}
                    </p>
                    <a href="{{ route('workspace.purchases.index') }}" class="btn btn-outline-primary btn-md mt-2">
                        <i class="fa-solid fa-cart-shopping me-2"></i>
                        <span>{{ translate('View My Purchases') }}</span>
                    </a>
                </div>
            </div>
        </div>
    @endif
    @push('scripts')
        <script>
            "use strict";
            let checkoutButton = $('.checkout-button');
            checkoutButton.on('click', function(e) {
                let checkedPaymentMethod = $('.payment-method input:checked');
                if (checkedPaymentMethod.val() == "balance") {
                    if (!confirm(config.translates.actionConfirm)) {
                        e.preventDefault();
                    }
                }
            });
        </script>
    @endpush
@endsection
