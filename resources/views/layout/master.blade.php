<!doctype html>
{{--<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">--}}
<html lang="vi">
@php
    $active_ckfinder = in_array(\Route::currentRouteName(), [
		'back.index', 'back.any'
	]);
@endphp
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, minimal-ui, shrink-to-fit=no"/>
    <meta name="description" content="Một trang web chuyên nghiệp, nơi bạn có thể mua bán vật liệu xây dựng">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- for ios 7 style, multi-resolution icon of 152x152 --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-barstyle" content="black-translucent">
    <link rel="apple-touch-icon" href="/favicon.ico?v=190729">
    <meta name="apple-mobile-web-app-title" content="{{ __('common/site.title') }}">
    {{-- for Chrome on Android, multi-resolution icon of 196x196 --}}
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="is-mobile" content="{{(int)\Agent::isMobile()}}">
    <meta name="is-tablet" content="{{(int)\Agent::isTablet()}}">
    <link rel="shortcut icon" sizes="256x256" href="/favicon.ico">

    <meta property="og:url" content="@yield('og:url')" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="@yield('og:title')" />
    <meta property="og:description" content="@yield('og:description')" />
    <meta property="og:image" content="@yield('og:image')"/>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css" integrity="sha512-1PKOgIY59xJ8Co8+NE6FZ+LOAZKjy+KY8iq0G4B3CyeY6wYHN3yt9PW0XpSriVlkMXe40PTKnXrLnZ9+fkDaog==" crossorigin="anonymous" />
    <link rel="stylesheet" href="{{ mix('/css/common/core.css') }}" type="text/css"/>

    @stack('pre-stylesheet')

    <link rel="stylesheet" href="{{ mix('/css/common/custom.css') }}" type="text/css"/>

    @stack('stylesheet')

    @stack('head-scripts')

    @stack('meta')

    @hasSection('title')
        <title data-default-title="@yield('title')">@yield('title')</title>
    @else
        <title data-default-title="@lang('common/site.title')">@lang('common/site.title')</title>
    @endif
</head>
<body class="d-flex flex-column @hasSection('body-class')@yield('body-class')@endif" @hasSection('body-style') style="@yield('body-style')" @endif>
@section('body')
@show

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
@if ($active_ckfinder)
    <script type="text/javascript" src="/js/ckfinder/ckfinder.js"></script>
    <script>CKFinder.config( { connectorPath: '/ckfinder/connector' } );</script>
@endif


@stack('body-scripts')

@stack('app-scripts')

@stack('stylesheet-bottom')
</body>
</html>
