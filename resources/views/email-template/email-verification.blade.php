@extends('email-template.base')

@section('stylesheet')

@endsection

@foreach([\App\Enums\ELanguage::EN, \App\Enums\ELanguage::VI] as $lang)
    @section("body-content-$lang")
        <div>
            <h1 style="font-weight: normal; font-size: 25px;">@lang('front/email.verify-email.title', [], $lang)</h1>
            <p>@lang('front/email.Hello', [], $lang),</p>
            <p>@lang('front/email.verify-email.mail-reason', [], $lang)</p>
            <div>
                <a href="{{ $action ?? '' }}" class="btn" style="text-decoration: none; {{ add_css_class_for_email('btn') }}" type="button">@lang('front/email.verify-email.action-name', [], $lang)</a>
            </div>
            <br>
            <p>@lang('front/email.verify-email.otp-intro', [], $lang) <b style="font-size: 24px;">{{ $otp_code }}</b></p>
            <p>@lang('front/email.verify-email.otp-expire-in', [], $lang)</p>
            <br>
            <p>@lang('front/email.verify-email.warning', [], $lang)</p>
            <p>@lang('front/email.Regards', [], $lang),</p>
            <p>{{ config('app.name') }}</p>
        </div>
        <div class="divider" style="margin: 20px 0; {{ add_css_class_for_email('divider') }}"></div>
        <div>
            @lang('front/email.direct-link-intro', ['actionName' => __('front/email.verify-email.action-name', [], $lang)], $lang): <span style="text-decoration: underline; color: #007bff; word-break: break-word;">{{ $action ?? '' }}</span>
        </div>
    @endsection
@endforeach