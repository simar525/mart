@extends('themes.basic.items.layout')
@section('title', $item->name)
@section('breadcrumbs', Breadcrumbs::render('items.comments', $item))
@section('og_image', $item->getPreviewImageLink())
@section('description', shorterText(strip_tags($item->description), 155))
@section('keywords', $item->tags)
@section('content')
    <livewire:item.comments :item="$item" />
@endsection
