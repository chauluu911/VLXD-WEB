@extends('front.layout.master')

@prepend('stylesheet')
	<link rel="stylesheet" href="{{ mix('/css/front/login.css') }}" type="text/css"/>
@endprepend

@section('title')
	Đăng nhập
@endsection

@section('main-id', 'home')

@section('body-user')
	<div class="container" id="login-modal">
		<div class="row m-0 justify-content-center" v-cloak>
			<div class="col-lg-16 col-md-24">
				{{-- <div id="login-view-info" class="d-none" data-stage="{{ $login_stage }}" data-customer-type="{{ $customerType }}" @if (isset($userData)) data-user-info="{{ $userData }}" @endif></div> --}}
				<div class="row form-login bg-white my-4 pt-4" style="box-shadow: 1px 1px 5px 1px #E0E0E0">
				    <div class="col-48 text-center">
				    	<h3 class="text-primary mb-4">
							@{{ formInfo.title }}
				    	</h3>
				    </div>
				    <div class="col-48 py-3">
				        <div class="row">
				            <div class="col-md-48 mb-3" v-if="infoMessage">
								<div class="alert alert-primary p-2">
									@{{ infoMessage }}
								</div>
							</div>
							<div class="col-md-48 mb-4" v-if="stage === ELoginStage.FORGOT_PASSWORD_EMAIL">
								@lang('front/auth.auth-area.forgot-password-guide')
							</div>
							<div class="col-md-48 mb-4" v-if="stage === ELoginStage.VERIFY_OTP_FORGOT_PASSWORD">
								@lang('front/auth.auth-area.verify-content-1') <br/>
								<span class="font-weight-bold">@{{ formData.phone.value }}</span> <br/>
								@lang('front/auth.auth-area.verify-content-2')
							</div>
							<div class="col-md-48 mb-4" v-if="stage === ELoginStage.OAUTH_ADDITION_INFO">
								@lang('front/auth.auth-area.oauth-addition-info-guide')
							</div>
				            <div class="col-md-48" v-for="field in formFields">
				                <div class="row mb-4" v-if="field.name === 'otp'">
				                    <div class="col-48 text-center d-flex otp-input-group">
				                        <otp-input
				                            ref="otpInput"
				                            :input-classes="['form-control text-center', field.error ? 'is-invalid' : ''].join(' ')"
				                            :separator="field.separator"
				                            :num-inputs="field.numInputs"
				                            :should-auto-focus="field.true"
				                            :is-input-num="field.number"
				                            :input-type="field.type"
				                            @on-change="formData.otp.value = $event"
				                            @on-complete="formData.otp.value = $event, formInfo.process"
				                        />
				                    </div>
				                    <input class="d-none form-control" :class="{'is-invalid': field.error}" type="hidden">
				                    <div class="col-48 invalid-feedback">@{{ field.error }}</div>
				                </div>
				                <div v-else class="form-group position-relative" style="border: 1px solid #0000000d; border-radius: 5px">
				                    <label class="px-2 text-black-50 mb-0" style="font-size: 12px">
				                        @{{ $t(`validation.attributes.${field.name}`) }}
				                    </label>
				                    <input
				                    	:id="field.name"
				                    	class="form-control px-2"
				                        :class="{'is-invalid': field.error}" :type="field.type"
				                        :placeholder="`@lang('common/common.input') ${$t(`validation.attributes.${field.name}`)}`"
				                        v-model.trim="formData[field.name].value"
										:disabled="field.disabled"
				                        @keyup.enter="formInfo.process"
				                        style="border: 1px solid white; font-weight: 500"
				                        autocomplete="off"
				                    >
				                    <span v-if="field.name == 'password'" class="position-absolute" style="top: 32px; right: 10px">
                                        <i id="show-pass" class="fas fa-eye-slash text-black-50 cursor-pointer"></i>
                                    </span>
                                    <div class="invalid-feedback d-block mb-2 ml-2 font-medium">@{{ formData[field.name].error }}</div>
				                </div>
				            </div>
				            <div v-if="availableIn" class="invalid-feedback d-block mb-2 ml-3 font-medium">@{{availableIn}}</div>
				            <div v-if="formData.email.error" class="invalid-feedback d-block mb-2 ml-3 font-medium">Số điện thoại hoặc mật khẩu không đúng</div>
				            <div
				            	v-else class="invalid-feedback d-block mb-2 ml-3 font-medium">@if(Session::has('msg'))
				            		{{Session::get('msg')}}
				            	@endif
				            </div>
				            <div v-if="stage === ELoginStage.UPDATE_PASSWORD" class="col-md-48">
				                <i>@lang('front/auth.auth-area.note')</i>
				            </div>
				            <div v-else-if="stage === ELoginStage.NOT_REGISTERED" class="col-md-48 mb-3">
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
				            </div>
				            <div class="col-md-48 mb-3 text-center form-group">
				                <button class="btn btn-primary w-100 bold text-white" @click="formInfo.process">
				                    @{{ formInfo.mainButton }}
				                </button>
				            </div>
				            <template v-if="stage === ELoginStage.NOT_LOGGED_IN">
				                <div class="col-48 text-right mb-3">
				                    <a
				                    	class="text-primary"
				                        href="{{ route('forgot-password', [], false) }}"
				                    >
				                        @lang('front/auth.auth-area.forgot-password-nav')
				                    </a>
				                </div>
				                <div class="col-48 mb-3">
				                    <a href="{{ route('auth.provider', ['provider' => 'facebook'], false) }}" class="btn btn-light border w-100">
			                            <i class="fab fa-facebook-f icon-padding mr-2" style="color: #0267e2"></i>
			                            <span>Đăng nhập bằng Facebook</span>
			                        </a>
				                </div>
				                <div class="col-48">
				                    <a href="{{ route('auth.provider', ['provider' => 'google'], false) }}" class="btn btn-light border w-100">
				                        <img src="/images/icon/google.svg" alt="Google icon" style="width: 20px;" class="mr-2">
				                        <span>Đăng nhập bằng Google</span>
				                    </a>
				                </div>
				                <div class="col-48 text-center mt-5 mb-2">
				                    <span>Bạn chưa có tài khoản?</span>
				                    <a class="ml-3 text-primary" href="{{ route('register', [], false) }}">Đăng ký</a>
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
		<input id="login-fb-google" hidden="" data-error="{{ !empty($msg) ? $msg : null}}">
	</div>
@endsection

@push('app-scripts')
	<script src="{{ mix('/js/front/login.js') }}"></script>
	<script>
        $(document).ready(function() {
            $('#show-pass').on('click', function() {
                if ($('#show-pass').hasClass('fa-eye-slash')) {
                    $('#password').removeAttr('type', 'password');
                    $('#password').attr('type', 'text');
                    $('#show-pass').removeClass('fa-eye-slash');
                    $('#show-pass').addClass('fa-eye');
                }else {

                    $('#password').removeAttr('type', 'text');
                    $('#password').attr('type', 'password');
                    $('#show-pass').removeClass('fa-eye');
                    $('#show-pass').addClass('fa-eye-slash');
                }
            });
        });
    </script>
@endpush
