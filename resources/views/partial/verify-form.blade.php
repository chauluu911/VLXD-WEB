<div id="verify-view-info" class="d-none" data-stage="{{ $login_stage }}" @if (isset($userData)) data-user-info="{{ $userData }}" @endif></div>
<div class="row bg-white my-5">
    <div class="col-48 pb-3 pt-0">
        <div class="row">
            <div class="col-md-48 mb-2 p-2 alert">
                <div>
                    <div class="col-48 text-right px-1">
                        <a href="javascript:void(0)" class="text-black text-decoration-none" data-dismiss="modal" @click="logout">
                            <i class="fas fa-times font-size-16px"></i>
                        </a>
                    </div>
                </div>
                <div class="col-48 text-center">
                    <h3 class="text-primary mt-4">
                        Xác nhận OTP
                    </h3>
                </div>
            </div>
            <div class="col-md-48 mb-3 p-2" v-if="infoMessage">@{{ infoMessage }}</div>
            <div class="col-md-48 mb-4 text-center" v-if="stage === ELoginStage.VERIFY_SMS">
                @lang('front/auth.auth-area.verify-content-1') {{auth()->id() ?  auth()->user()->phone : null}}<br/>
                @lang('front/auth.auth-area.verify-content-2')
            </div>
            <div class="col-md-48 mb-1" v-for="field in formFields">
                <div class="row mb-4" v-if="field.name === 'otp'">
                    <div class="col-48 text-center d-flex otp-input-group">
                        <otp-input
                            ref="otpInput"
                            :input-classes="['form-control text-center w-75 m-auto', field.error ? 'is-invalid' : ''].join(' ')"
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
                    <div class="col-48 invalid-feedback font-weight-bold">@{{ field.error }}</div>
                </div>
            </div>
            <template v-if="time != 0">
                <div class="col-48 text-center mb-3">
                    Thời gian chờ xác thực: <span class="text-primary">@{{time}}</span>
                </div>
            </template>
            <template v-else>
                <div if="stage === ELoginStage.VERIFY_SMS" class="col-48 text-center mb-3">
                    <a
                        href="javascript:void(0)"
                        @click="resendVerify"
                    >
                        @lang('front/auth.auth-area.resend-otp')
                    </a>
                </div>
            </template>
            <div class="col-md-48 mb-3 text-center form-group">
                <button class="btn btn-primary w-100 bold" @click="formInfo.process">
                    Xác nhận
                </button>
            </div>
        </div>
    </div>
</div>

@push('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/login.css') }}" type="text/css"/>
@endpush

@push('app-scripts')
    <script src="{{ mix('/js/front/verify.js') }}"></script>
@endpush
