@php
    $faviconUrl = \App\Support\Settings::faviconUrl();
    $themeColor = \App\Support\Settings::themeColor();
@endphp
@if($faviconUrl)
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
@endif
<meta name="theme-color" content="{{ $themeColor }}">
