<div id="login-view-info" class="d-none" data-stage="{{ $login_stage }}" data-customer-type="{{ $customerType }}" @if (isset($userData)) data-user-info="{{ $userData }}" @endif></div>

<div class="row bg-white">
    <template v-if="stage !== ELoginStage.UPDATE_PASSWORD">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" v-if="stage !== ELoginStage.MAKE_ADDITIONAL_INFORMATION">
            <i class="fas fa-times"></i>
        </button>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" v-else @click="logout">
            <i class="fas fa-times"></i>
        </button>
    </template>
    <template v-else>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="common.loading.show('body'); window.location.reload();">
            <i class="fas fa-times"></i>
        </button>
    </template>
    <div class="col-48 bg-primary d-flex-center-x py-2">
        <img class="w-100" src="/images/logo.jpg" alt="B4S logo" style="max-width: 170px;">
    </div>
    <div class="col-48" v-if="stage !== ELoginStage.VERIFY_EMAIL">
        <ul class="nav nav-tabs row">
            <li class="nav-item col-24 p-0 text-center">
                <a
                    class="nav-link cursor-pointer"
                    :class="{active: stage === ELoginStage.NOT_LOGGED_IN}"
                    @click="setStage(ELoginStage.NOT_LOGGED_IN)"
                >
                    LOGIN
                </a>
            </li>
            <li class="nav-item col-24 p-0 text-center">
                <a
                    class="nav-link cursor-pointer"
                    :class="{active: stage === ELoginStage.NOT_REGISTERED}"
                    @click="setStage(ELoginStage.NOT_REGISTERED)"
                >
                    REGISTER
                </a>
            </li>
        </ul>
    </div>
    <div class="col-48 py-3">
        <div class="row">
            <div class="col-md-48 mb-3 p-2 alert alert-primary" v-if="infoMessage">@{{ infoMessage }}</div>
            <div class="col-md-48 mb-4 text-center" v-if="stage === ELoginStage.VERIFY_EMAIL">
                @lang('front/auth.auth-area.verify-content-1') <br/>
                @lang('front/auth.auth-area.verify-content-2')
            </div>
            <div class="col-md-48 mb-1" v-for="field in formFields">
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
                <div class="form-group" v-else>
                    <label>
                        @{{ $t(`validation.attributes.${field.name}`) }}
                    </label>
                    <input class="form-control"
                           :class="{'is-invalid': field.error}" :type="field.type"
                           :placeholder="`@lang('common/common.input') ${$t(`validation.attributes.${field.name}`)}`"
                           v-model.trim="formData[field.name].value"
                           @keyup.enter="formInfo.process">
                    <div class="invalid-feedback">@{{ field.error }}</div>
                </div>
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
                <button class="btn btn-primary2 w-100 rounded-pill bold" @click="formInfo.process">
                    @{{ formInfo.mainButton }}
                </button>
            </div>
            <template v-if="stage === ELoginStage.NOT_LOGGED_IN">
                <div class="col-48 text-center mb-3">
                    <a
                        href="javascript:void(0)"
                        @click="setStage(ELoginStage.FORGOT_PASSWORD_EMAIL)"
                    >
                        @lang('front/auth.auth-area.forgot-password-nav')
                    </a>
                </div>
                <div class="col-48">
                    <a href="{{ route('auth.provider', ['provider' => 'google'], false) }}" class="btn btn-light border rounded-pill w-100">
                        <img src="/images/icon/google.svg" alt="Google icon" style="width: 20px;" class="mr-2">
                        <span>Google</span>
                    </a>
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

@push('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/login.css') }}" type="text/css"/>
@endpush

@push('app-scripts')
    <script src="{{ mix('/js/front/login.js') }}"></script>
@endpush
