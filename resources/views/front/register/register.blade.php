@extends('front.layout.master')

@prepend('stylesheet')
	<link rel="stylesheet" href="{{ mix('/css/front/login.css') }}" type="text/css"/>
@endprepend

@section('main-id', 'home')

@section('title')
    Đăng ký
@endsection

@section('body-user')
	<div class="container" id="register-modal">
		<div class="row justify-content-center" v-cloak>
			<div class="col-lg-16 col-md-24">
				<div id="register-view-info" class="d-none" data-stage="{{ $login_stage }}" @if (isset($userData)) data-user-info="{{ $userData }}" @endif></div>
				<div class="row bg-white my-4 mx-0 form-register" style="box-shadow: 1px 1px 5px 1px #E0E0E0">
				    <div class="col-48 text-center">
				    	<h3 class="text-primary mt-2 mb-3">
				    		Đăng ký
				    	</h3>
				    </div>
				    <div class="col-48 pb-3">
				        <div class="row">
				            <div class="col-md-48 mb-3 p-2 alert alert-primary" v-if="infoMessage">@{{ infoMessage }}</div>
				            <div class="col-md-48 mb-4 text-center" v-if="stage === ELoginStage.VERIFY_EMAIL">
				                @lang('front/auth.auth-area.verify-content-1') <br/>
				                @lang('front/auth.auth-area.verify-content-2')
				            </div>
				            <div class="col-md-48" v-for="field in formFields">
				                <div class="row mb-4" v-if="field.name === 'otp'">
				                    <div class="col-48 text-center d-flex otp-input-group">
				                        <otp-input
				                            ref="otpInput"
				                            :separator="field.separator"
				                            :num-inputs="field.numInputs"
				                            :should-auto-focus="field.true"
				                            :is-input-num="field.number"
				                            :input-type="field.type"
				                            @on-change="formData.otp.value = $event"
				                            @on-complete="formData.otp.value = $event, formInfo.process"
				                        />
				                    </div>
				                    <input class="d-none form-control" type="hidden">
				                    <div class="col-48 invalid-feedback d-block font-medium ml-2">@{{ field.error }}</div>
				                </div>
				                <div v-else class="form-group position-relative" style="border: 1px solid #0000000d; border-radius: 5px">
				                    <label class="px-2 text-black-50" style="font-size: 12px">
				                        @{{ $t(`validation.attributes.${field.name}`) }}
				                    </label>
				                    <input
				                    	:id="field.name"
				                    	class="form-control pl-2 pr-4 font-medium"
				                        :type="field.type"
				                        :placeholder="`@lang('common/common.input') ${$t(`validation.attributes.${field.name}`)}`"
				                        v-model.trim="formData[field.name].value"
				                        @keyup.enter="formInfo.process"
				                        style="border: 1px solid white"
				                    >
				                    <span v-if="field.name == 'password' || field.name == 'password_confirmation'" class="position-absolute" style="top: 31px; right: 9px">
                                        <i :id="`show-${field.name}`" class="fas fa-eye-slash text-black-50 cursor-pointer"></i>
                                    </span>
				                    <div class="invalid-feedback d-block font-medium ml-2">@{{ field.error }}</div>
				                </div>
				            </div>
				            <div v-if="stage === ELoginStage.UPDATE_PASSWORD" class="col-md-48">
				                <i>@lang('front/auth.auth-area.note')</i>
				            </div>
				            <!-- <div v-else-if="stage === ELoginStage.NOT_REGISTERED" class="col-md-48 mb-3">
				                <div class="custom-control custom-checkbox">
				                    <input
				                        id="term-and-policy-checkbox"
				                        v-model="formData.acceptTerm.value"
				                        type="checkbox"
				                        class="custom-control-input"
				                        :class="{'is-invalid': formData.acceptTerm.error}"
				                        required
				                    >
				                    <label class="custom-control-label" for="term-and-policy-checkbox">
				                        @lang('front/auth.auth-area.accept-policy')
				                        <a href="#">@lang('front/auth.auth-area.term-of-engagement')</a>
				                    </label>
				                </div>
				            </div> -->
				            <div class="col-md-48 mb-3 text-center form-group">
				                <button class="btn btn-primary w-100 bold" @click="formInfo.process">
				                    @{{ formInfo.mainButton }}
				                </button>
				            </div>

				            <template v-if="stage === ELoginStage.NOT_LOGGED_IN">
				                <div class="col-48 text-right mb-3">
				                    <a
				                    	class="text-primary"
				                        href="javascript:void(0)"
				                        @click="setStage(ELoginStage.FORGOT_PASSWORD_EMAIL)"
				                    >
				                        @lang('front/auth.auth-area.forgot-password-nav')
				                    </a>
				                </div>
				            </template>
				            <template v-if="stage === ELoginStage.NOT_REGISTERED">
				            	<div class="col-48 text-center mt-3">
				                    <span>Bạn đã có tài khoản?</span>
				                    <a class="ml-3 text-primary" href="{{ route('login', [], false) }}">Đăng nhập</a>
				                </div>
				            </template>
				            <template v-if="stage === ELoginStage.VERIFY_EMAIL">
				                <div class="col-48 text-right mb-3">
				                    <a
				                        href="javascript:void(0)"
				                        @click="resendVerifyEmail"
				                    >
				                        @lang('front/auth.auth-area.resend-otp')
				                    </a>
				                </div>
				            </template>
				        </div>
				    </div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('app-scripts')
	<script src="{{ mix('/js/front/register.js') }}"></script>
	<script>
        $(document).ready(function() {
            $('#show-password').on('click', function() {
                if ($('#show-password').hasClass('fa-eye-slash')) {
                    $('#password').removeAttr('type', 'password');
                    $('#password').attr('type', 'text');
                    $('#show-password').removeClass('fa-eye-slash');
                    $('#show-password').addClass('fa-eye');
                }else {

                    $('#password').removeAttr('type', 'text');
                    $('#password').attr('type', 'password');
                    $('#show-password').removeClass('fa-eye');
                    $('#show-password').addClass('fa-eye-slash');
                }
            });

            $('#show-password_confirmation').on('click', function() {
                if ($('#show-password_confirmation').hasClass('fa-eye-slash')) {
                    $('#password_confirmation').removeAttr('type', 'password');
                    $('#password_confirmation').attr('type', 'text');
                    $('#show-password_confirmation').removeClass('fa-eye-slash');
                    $('#show-password_confirmation').addClass('fa-eye');
                }else {

                    $('#password_confirmation').removeAttr('type', 'text');
                    $('#password_confirmation').attr('type', 'password');
                    $('#show-password_confirmation').removeClass('fa-eye');
                    $('#show-password_confirmation').addClass('fa-eye-slash');
                }
            });
        });
    </script>
@endpush
