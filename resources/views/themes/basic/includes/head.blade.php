@include('themes.basic.includes.meta-tags')
<title>{{ pageTitle($__env) }}</title>
<link rel="icon" href="{{ asset($themeSettings->general->favicon) }}">
@include('themes.basic.includes.styles')
<script type="application/ld+json">
    {!! schema($__env) !!}
</script>
@stack('schema')
