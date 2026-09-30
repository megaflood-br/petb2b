@php
    $faviconUrl = \App\Support\Settings::faviconUrl();
    $themeColor = \App\Support\Settings::themeColor();
    $googleVerification = \App\Support\Settings::googleVerification();
    $bingVerification = \App\Support\Settings::bingVerification();
    $gtmId = \App\Support\Settings::gtmId();
    $ga4Id = \App\Support\Settings::ga4Id();
@endphp

@if($faviconUrl)
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
@endif
<meta name="theme-color" content="{{ $themeColor }}">
@if($googleVerification)
    <meta name="google-site-verification" content="{{ $googleVerification }}">
@endif
@if($bingVerification)
    <meta name="msvalidate.01" content="{{ $bingVerification }}">
@endif

@if($gtmId)
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ $gtmId }}');</script>
@elseif($ga4Id)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $ga4Id }}');
    </script>
@endif

<script type="application/ld+json">{!! json_encode(\App\Support\Seo::jsonLdGraph(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
