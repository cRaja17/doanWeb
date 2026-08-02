@if(isset($items) && count($items) > 0)
<ul class="breadcrumb">
    <li><a href="{{ route('web_home') }}" class="breadcrumb__link"><i class="fa-solid fa-house" style="margin-right: 0.2rem;"></i> {{ translate('menu.home') }}</a></li>
    
    @foreach($items as $item)
        <li><i class="fa-solid fa-chevron-right breadcrumb__separator"></i></li>
        @if($loop->last)
            <li class="breadcrumb__item--active">{{ $item['label'] }}</li>
        @else
            <li><a href="{{ $item['url'] }}" class="breadcrumb__link">{{ $item['label'] }}</a></li>
        @endif
    @endforeach
</ul>
@endif
