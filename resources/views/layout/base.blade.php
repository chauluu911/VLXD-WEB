<!doctype html>
{{--<html lang="{{ app()->getLocale() }}">--}}
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimal-ui, shrink-to-fit=no"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- for ios 7 style, multi-resolution icon of 152x152 --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-barstyle" content="black-translucent">
    <link rel="apple-touch-icon" href="/favicon.ico?v=190729">
    <meta name="apple-mobile-web-app-title" content="{{ __('Giáo dục giới tính - ERA') }}">
    {{-- for Chrome on Android, multi-resolution icon of 196x196 --}}
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="is-mobile" content="{{(int)\Agent::isMobile()}}">
    <meta name="is-tablet" content="{{(int)\Agent::isTablet()}}">
    <link rel="shortcut icon" sizes="256x256" href="/favicon.ico">
<!--     <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.css" integrity="sha256-46qynGAkLSFpVbEBog43gvNhfrOj+BmwXdxFgVK/Kvc=" crossorigin="anonymous" />

    @include('partial.base-css')

    @stack('pre-stylesheet')

    @stack('stylesheet')

    @stack('head-scripts')

    @hasSection('description')
        <meta name="description" content="@yield('description')">
        <meta property="og:description" content="@yield('description')" />
    @else
        <meta name="description" content="{{ __('common.era-description') }}">
        <meta property="og:description" content="{{ __('common.era-description') }}" />
    @endif
    <meta content="thi thử ielts online, test ielts online, ielts, english, test" name="keywords">
    <meta property="fb:app_id" content="{{ config('services.facebook.client_id') }}" />
    @hasSection('og:url')
        <meta property="og:url" content="@yield('og:url')" />
    @else
        <meta property="og:url" content="{{\Illuminate\Support\Str::replaceFirst('http://', 'https://', \Illuminate\Support\Facades\Request::url())}}" />
    @endif
    @hasSection('og:image')
        <meta property="og:image" content="@yield('og:image')"/>
    @else
        <meta property="og:image" content="https://static.era.edu.vn/sr/display?op=resize&w=300&path=topic/logo_path/20200221/xy1KUln4lGkYzUIc3Hq70Wi0JqoLVlyOz6bNQk3s.jpg"/>
    @endif
    <meta property="og:type" content="website"/>
    @section('meta-og')
    @show
    @hasSection ('author')
        <meta name="author" content="@yield('author')">
    @else
        <meta name="author" content="ERA">
    @endif

    @hasSection('title')
        <!-- <title data-default-title="@yield('title')">@yield('title')</title> -->
        <title data-default-title="@yield('title')">VLXD</title>
    @else
        <title data-default-title="@lang('Giáo dục giới tính - ERA')">@lang('Giáo dục giới tính - ERA')</title>
    @endif
</head>
<body>
    @section('body')
    @show
    @stack('modals')
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js" integrity="sha384-1CmrxMRARb6aLqgBO7yyAxTOQE2AKb9GfXnEo760AUcUmFx3ibVJJAzGytlQcNXd" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.4.0/bootbox.min.js" integrity="sha256-sfG8c9ILUB8EXQ5muswfjZsKICbRIJUG/kBogvvV5sY=" crossorigin="anonymous"></script>
    @stack('pre-scripts')
    <script src="{{ mix('/js/front/app.js') }}"></script>
    <script src="{{ mix('/js/front/common.js') }}"></script>
    @stack('scripts')

    @stack('stylesheet-bottom')
</body>
</html>
