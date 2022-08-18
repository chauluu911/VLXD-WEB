<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimal-ui, shrink-to-fit=no"/>
    <meta name="description" content="page description"/>

    <title>{{ config('app.name') }}</title>


    {{-- for ios 7 style, multi-resolution icon of 152x152 --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-barstyle" content="black-translucent">
    <link rel="apple-touch-icon" href="/favicon.ico?v=26.10.18">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
    {{-- for Chrome on Android, multi-resolution icon of 196x196 --}}
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="shortcut icon" sizes="256x256" href="/favicon.ico?v=26.10.18">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
</head>

<body style="{{ add_css_class_for_email('body') }}">
    @foreach([\App\Enums\ELanguage::EN, \App\Enums\ELanguage::FR, \App\Enums\ELanguage::VI] as $lang)
        <div class="container" style="{{ add_css_class_for_email('container') }}">
            <div class="header" style="{{ add_css_class_for_email('header') }}">
                <div>
                    <img src="{{ secure_url('/', []) . '/images/logo.jpg' }}" height="40px" alt="B4S">
                </div>
            </div>
            <div class="divider" style="{{ add_css_class_for_email('divider') }}"></div>
            <div class="content" style="{{ add_css_class_for_email('content') }}">
                <div>
                    @section("body-content-$lang")
                    @show
                </div>
            </div>
            <div class="footer" style="{{ add_css_class_for_email('footer') }}">
                <div>
                    @lang('front/email.contact-us-intro', [], $lang) <a href="#" style="{{ add_css_class_for_email('footer.a') }}">@lang('front/email.Contact us', [], $lang)</a>
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>
