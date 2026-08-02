@extends('web.layouts.index')

@php
    view()->share('title', translate('thanks.title') . ' - ' . env('APP_NAME', 'Website'));
    view()->share('keywords', '');
    view()->share('description', '');
    view()->share('robots', 'noindex, nofollow');
    view()->share('canonical', url()->current());
    view()->share('og_image', asset('images/default-seo.jpg'));
    view()->share('og_url', url()->current());
@endphp

@section('content')
    <div class="grid-container" style="max-width: 800px; margin-top: 3rem; margin-bottom: 5rem; text-align: center;">
        <div aria-label="You are here:" role="navigation" style="margin-bottom: 2rem; text-align: left;">
            <ul class="breadcrumbs">
                <li><a href="{{ route('web_home') }}">{{ translate('breadcrumb.home') }}</a></li>
                <li><span class="show-for-sr">Current: </span> {{ translate('thanks.title') }}</li>
            </ul>
        </div>
        
        <div class="thanks-wrapper" style="background: #ffffff; padding: 3rem 2rem; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-top: 5px solid #10b981; text-align: center;">
            <div style="margin-bottom: 1.5rem; display: flex; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width: 80px; height: 80px; color: #10b981;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 style="font-size: 2.2rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem;">
                {{ translate('thanks.heading') }}
            </h1>
            
            <div class="thanks-content" style="color: #475569; line-height: 1.8; font-size: 1.1rem; margin-bottom: 2.5rem;">
                {!! $data !!}
            </div>

            <div style="margin-top: 1.5rem;">
                <a href="{{ route('web_home') }}" class="button" style="background: #2563eb; color: #ffffff; padding: 0.8rem 2.5rem; border-radius: 50px; font-weight: 700; transition: all 0.3s ease; text-transform: uppercase; font-size: 0.95rem;">
                    {{ translate('thanks.home_btn') }}
                </a>
            </div>
        </div>
    </div>
@endsection
