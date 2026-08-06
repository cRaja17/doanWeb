@extends('web.layouts.index')

@php
    view()->share('title', isset($information) ? translate($information->name) : config('app.name'));
    view()->share('keywords', isset($information) ? translate($information->name) : '');
    view()->share('description', isset($information) ? strip_tags($information->description ?? '') : '');
    view()->share('robots', 'index, follow');
    view()->share('canonical', url()->current());
    view()->share('og_image', isset($information) && isset($information->image) ? get_image_url($information->image) : asset('images/default-seo.jpg'));
    view()->share('og_url', url()->current());
@endphp

@section('content')
    <!-- breadcrumbs  -->

    <div class="grid-x grid-container">
        <div aria-label="You are here:" role="navigation">
            <ul class="breadcrumbs">
                <li><a href="{{ route('web_home') }}">{{ translate('breadcrumb.home') }}</a></li>
                <li class="disabled">{{ $information->name }}</li>
            </ul>
        </div>
    </div>
    <div class="grid-container grid-x grid-margin-x align-center">
        <div class="title-4 title-col">
            <h1>{{ translate($information->name) }}</h1>
        </div>
        <div class="grid-y grid-margin-y" style="margin: 0 1rem;">
            {!! $information->description !!}
        </div>
    </div>
    <!-- Thank You Message -->
@endsection
