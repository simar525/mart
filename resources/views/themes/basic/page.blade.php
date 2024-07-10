@extends('themes.basic.layouts.single')
@section('title', $page->title)
@section('description', $page->short_description)
@section('breadcrumbs', Breadcrumbs::render('page', $page))
@section('header_v2', true)
@section('container', 'container-custom')
@section('content')
    {!! $page->body !!}
@endsection
