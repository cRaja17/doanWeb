<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{{asset('favicon.ico')}}" type="image/x-icon" rel="shortcut icon" />
<title>{{$title}}</title>
<meta name="keywords" content="{{$keywords}}" />
<meta name="description" content="{{ $description }}" />
<meta name="author" content="{{app('request')->getRequestUri()}}">
<meta name="robots" content="{{$robots}}" />
<link rel="canonical" href="{{$canonical}}" />


<meta property="og:image" content="{{$og_image}}" />
<meta property="og:url" content="{{$og_url}}">
<meta property="og:title" content="{{ $title }}" />
<meta property="og:description" content="{{ $description }}" />
<meta property="og:type" content="website" />
<meta property="og:locale" content="en_US" />
<meta property="og:site_name" content="" />