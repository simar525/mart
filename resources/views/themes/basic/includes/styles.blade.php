@bootstrap
<link rel="stylesheet" href="{{ asset('vendor/libs/fontawesome/fontawesome.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/libs/vironeer/toastr/css/vironeer-toastr.min.css') }}">
@stack('styles_libs')
@themeColors
<link rel="stylesheet" href="{{ theme_assets_with_version('assets/css/app.css') }}">
@if (getDirection() == 'rtl')
<link rel="stylesheet" href="{{ theme_assets_with_version('assets/css/app.rtl.css') }}">
@endif
@stack('styles')
@themeCustomStyle
{!! $themeSettings->extra_codes->head_code !!}
<livewire:styles />