@php($faviconVersion = is_file(public_path('favicon-master.png')) ? filemtime(public_path('favicon-master.png')) : null)
@php($faviconQuery = $faviconVersion ? '?v='.$faviconVersion : '')
<link rel="icon" href="{{ asset('favicon.svg').$faviconQuery }}" type="image/svg+xml">
<link rel="icon" href="{{ asset('favicon-32x32.png').$faviconQuery }}" sizes="32x32" type="image/png">
<link rel="icon" href="{{ asset('favicon-16x16.png').$faviconQuery }}" sizes="16x16" type="image/png">
<link rel="shortcut icon" href="{{ asset('favicon.ico').$faviconQuery }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png').$faviconQuery }}" sizes="180x180">
<link rel="manifest" href="{{ asset('site.webmanifest').$faviconQuery }}">
