import ELoginStage from "../constants/login-stage";
import EErrorCode from "../constants/error-code";
import ECustomerType from "../constants/customer-type";
import EOtpType from "../constants/otp-type";
import authMessage from '../locales/front/auth';
import validationMessage from '../locales/front/validation';
import OtpInput from "@bachdgvn/vue-otp-input";

const app = new Vue({
    el: '#verify-modal',
    name: 'VerifyForm',
    components: { OtpInput },
    data() {
        // let loggedUserInfo = $('#verify-view-info').data('userInfo');
        // if (loggedUserInfo) {
        //     loggedUserInfo = window.atob(loggedUserInfo);
        //     loggedUserInfo = JSON.parse(loggedUserInfo);
        // } else {
        //     loggedUserInfo = {};
        // }

        return {
            stage: $('#verify-view-info').data('stage') || ELoginStage.NOT_LOGGED_IN,
            //customerType: $('#login-view-info').data('customerType') || ECustomerType.BUYER,
            ELoginStage,
            formData: {
                otp: {
                    type: 'tel',
                    number: true,
                    focus: true,
                    numInputs: 6,
                    separator: '',
                    value: null,
                    error: null,
                },
                acceptTerm: {
                    value: false,
                    error: null,
                }
            },
            //loggedUserInfo,
            infoMessage: null,
            time: null,
        };
    },
    created() {
        this.timeOut();
        Object.keys(authMessage).forEach((locale) => {
            this.$i18n.mergeLocaleMessage(locale, {auth: authMessage[locale]});
        });
        Object.keys(validationMessage).forEach((locale) => {
            this.$i18n.mergeLocaleMessage(locale, {validation: validationMessage[locale]});
        });
        $('.button-login').click(() => {
            if (!$('#login-modal:visible').length) {
                $('#login-modal').modal('show');
                return;
            }
            if (this.stage === ELoginStage.NOT_LOGGED_IN) {
                this.stage = ELoginStage.NOT_REGISTERED;
            } else {
                this.stage = ELoginStage.NOT_LOGGED_IN;
            }
        });
    },
    mounted() {
        $('#verify-modal')
            .on('show.bs.modal', () => {
                $('body').addClass('modal-open--fixed-header');
                //this.formData.password.value = null;
                //this.formData.password_confirmation.value = null;
            })
            .on('hide.bs.modal', () => {
                $('body').removeClass('modal-open--fixed-header');
                this.stage = ELoginStage.NOT_LOGGED_IN;
            });
        if (this.stage === ELoginStage.VERIFY_SMS) {
            $('#verify-modal').modal({
                backdrop: 'static',
                keyboard: false,
                show: true,
            });
        } else if (this.stage === ELoginStage.RESET_PASSWORD) {
            $('#verify-modal').modal('show');
        }
    },
    watch: {
        stage(val) {
            this.infoMessage = null;
            Object.keys(this.formData).forEach((key) => {
                this.formData[key].error = null;
            });
        },
        "formData.acceptTerm.value"() {
            this.formData.acceptTerm.error = false;
        },
        "formData.acceptTerm.error"(val) {
            if (val) {
                $('#term-and-policy-checkbox').tooltip({
                    boundary: 'window',
                    title: this.$t('auth.auth-area.please-accept-term-and-policy'),
                    placement: 'top',
                    trigger: 'focus',
                }).focus();
            } else {
                $('#term-and-policy-checkbox').tooltip('dispose')
            }
        }
    },
    computed: {
        neededInputs() {
            let result = [];
            switch (this.stage) {
                case ELoginStage.VERIFY_SMS:
                    result.push('otp');
                    break;
            }
            return result;
        },
        formFields() {
            return this.neededInputs.map((field) => {
                return Object.assign({
                    name: field,
                }, this.formData[field]);
            });
        },
        formInfo() {
            switch (this.stage) {
                case ELoginStage.VERIFY_SMS:
                    return {
                        title: this.$t('auth.auth-area.verify-email-title'),
                        mainButton: this.$t('auth.auth-area.submit'),
                        process: this.verify,
                    };
                    break;
                default:
                    return {
                        title: null,
                        mainButton: null,
                        process: this.verify,
                    };
                    break;
            }
        },
    },
    methods: {
        timeOut() {
            let time = 180;
            let curentMinutes = 3;
            let interval = setInterval(() => {
                time--;
                let minutes = Math.floor(time / (60));
                time -= minutes * (60);
                if (time == 59) {
                    curentMinutes--;
                }
                if (curentMinutes == -1) {
                    this.time = 0;
                    clearInterval(interval);
                    return;
                }
                this.time = '0' + curentMinutes + ':' + (time < 10 ? '0' + time : time);
            }, 1000);
        },
        setStage(stage) {
            this.stage = stage;
        },
        validate() {
            let isValid = true;
            this.neededInputs.forEach((field) => {
                switch (field) {
                    case 'otp':
                        if (!this.formData.otp.value) {
                            this.formData.otp.error = 'Otp không thể để trống';
                            isValid = false;
                        } else {
                            this.formData.otp.error = null;
                        }
                        break;
                    default:
                        if (!this.formData[field].value) {
                            isValid = false;
                            this.formData[field].error = this.$t('validation.required', {
                                attribute: this.$t(`validation.attributes.${field}`),
                                Attribute: this.$t(`validation.attributes.${field}`),
                            });
                        } else {
                            this.formData[field].error = null;
                        }
                        break;
                }
            });
            return isValid;
        },
        verify() {
            let data = this.$_validateAndGetFormData();
            if (!data) {
                return;
            }

            switch (this.stage) {
                case ELoginStage.VERIFY_SMS:
                    data.type = EOtpType.VERIFY_EMAIL_WHEN_REGISTER;
                    break;
            }

            common.loadingScreen.show('body');
            $.ajax({
                url: '/auth/otp/verify',
                data,
                method: 'POST',
            }).done((res) => {
                if (res.error !== EErrorCode.NO_ERROR) {
                    this.formData.otp.error = res.msg;
                    return;
                }
                window.location.reload();
            }).fail((err) => {
                this.$_processErrorMessage(err);
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        async resendVerify() {
            let route = null,
                data = {};
            switch (this.stage) {
                case ELoginStage.VERIFY_SMS:
                    // let confirm = await new Promise((resolve) => {
                    //     common.confirm(this.$t('auth.auth-area.resend-otp-confirm'), resolve);
                    // });
                    // if (!confirm) {
                    //     return;
                    // }
                    data.type = EOtpType.VERIFY_EMAIL_WHEN_REGISTER;
                    route = '/auth/otp/resend';
                    break;
            }

            if (!route) {
                return;
            }

            this.infoMessage = null;
            common.loadingScreen.show('body');
            $.ajax({
                url: route,
                data,
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                },
            }).done((data, textStatus, jqXHR) => {
                if (jqXHR.status === 204) {
                    window.location.reload();
                    return;
                }
                if (jqXHR.status === 202) {
                    common.alert('Mã xác nhận đã được gửi tới số điện thoại của bạn');
                    this.timeOut();
                    return;
                }
                if (data.error !== EErrorCode.NO_ERROR) {
                    common.alert(data.msg);
                    return;
                }
            }).fail((err) => {
                this.$_processErrorMessage(err);
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        $_validateAndGetFormData() {
            if (!this.validate()) {
                return null;
            }

            let data = {};
            this.neededInputs.forEach((field) => {
                data[field] = this.formData[field].value;
            });
            return data;
        },
        $_processErrorMessage(errResponse) {
            if (!errResponse.responseJSON || !errResponse.responseJSON.errors) {
                common.alert(errResponse.responseJSON.msg);
                return;
            }
            let errors = errResponse.responseJSON.errors;
            Object.keys(errors).forEach((key) => {
                if (!errors[key] || (Array.isArray(errors[key]) && !errors[key].length)) {
                    return;
                }

                let formDataKey = key === 'login_id' ? 'phone' : key;
                this.formData[formDataKey].error = errors[key][0];
            });
        },
        logout() {
            common.post({
                url: '/logout',
            }).always( () => {
                window.location.reload();
            })
        }
    }
});
