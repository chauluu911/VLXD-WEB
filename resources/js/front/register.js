import ELoginStage from "../constants/login-stage";
import EErrorCode from "../constants/error-code";
import ECustomerType from "../constants/customer-type";
import EOtpType from "../constants/otp-type";
import authMessage from '../locales/front/auth';
import validationMessage from '../locales/front/validation';
import OtpInput from "@bachdgvn/vue-otp-input";

const app = new Vue({
    el: '#register-modal',
    name: 'RegisterForm',
    components: { OtpInput },
    data() {
        let loggedUserInfo = $('#login-view-info').data('userInfo');
        if (loggedUserInfo) {
            loggedUserInfo = window.atob(loggedUserInfo);
            loggedUserInfo = JSON.parse(loggedUserInfo);
        } else {
            loggedUserInfo = {};
        }

        return {
            stage: $('#register-view-info').data('stage') || ELoginStage.NOT_REGISTERED,
            //customerType: $('#login-view-info').data('customerType') || ECustomerType.BUYER,
            ELoginStage,
            formData: {
                email: {
                    type: 'text',
                    value: null,
                    error: null,
                    cooldown: null,
                },
                phone: {
                    type: 'text',
                    value: null,
                    error: null,
                },
                password: {
                    type: 'password',
                    value: null,
                    error: null,
                },
                name: {
                    type: 'text',
                    value: null,
                    error: null,
                },
                password_confirmation: {
                    type: 'password',
                    value: null,
                    error: null,
                },
                identification: {
                    type: 'text',
                    value: null,
                    error: null,
                },
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
            loggedUserInfo,
            infoMessage: null,
        };
    },
    created() {
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
        $('#login-modal')
            .on('show.bs.modal', () => {
                $('body').addClass('modal-open--fixed-header');
                this.formData.password.value = null;
                this.formData.password_confirmation.value = null;
            })
            .on('hide.bs.modal', () => {
                $('body').removeClass('modal-open--fixed-header');
                this.stage = ELoginStage.NOT_LOGGED_IN;
            });
        if (this.stage === ELoginStage.VERIFY_EMAIL) {
            $('#login-modal').modal({
                backdrop: 'static',
                keyboard: false,
                show: true,
            });
        } else if (this.stage === ELoginStage.RESET_PASSWORD) {
            $('#login-modal').modal('show');
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
            console.log(this.stage)
            switch (this.stage) {
                case ELoginStage.NOT_REGISTERED:
                    result.push('phone', 'name', 'identification', 'password', 'password_confirmation');
                    break;
                case ELoginStage.NOT_LOGGED_IN:
                    result.push('phone', 'password');
                    break;
                case ELoginStage.VERIFY_EMAIL:
                    result.push('otp');
                    break;
                case ELoginStage.FORGOT_PASSWORD_EMAIL:
                    result.push('email');
                    break;
                case ELoginStage.RESET_PASSWORD:
                    result.push('email', 'password', 'password_confirmation');
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
                case ELoginStage.NOT_LOGGED_IN:
                    return {
                        title: this.$t('auth.auth-area.login-title'),
                        mainButton: this.$t('auth.auth-area.login-btn-name'),
                        process: this.login,
                    };
                case ELoginStage.NOT_REGISTERED:
                    return {
                        title: this.$t('auth.auth-area.register-title'),
                        mainButton: this.$t('auth.auth-area.register-btn-name'),
                        process: this.register,
                    };
                case ELoginStage.FORGOT_PASSWORD_EMAIL:
                    return {
                        title: this.$t('auth.auth-area.forgot-password-title'),
                        mainButton: this.$t('auth.auth-area.request-forgot-password-btn'),
                        process: this.requestResetPasswordEmail,
                    };
                case ELoginStage.RESET_PASSWORD:
                    return {
                        title: this.$t('auth.auth-area.reset-password-title'),
                        mainButton: this.$t('auth.auth-area.reset-password-btn'),
                        process: this.resetPassword,
                    };
                case ELoginStage.VERIFY_EMAIL:
                    return {
                        title: this.$t('auth.auth-area.verify-email-title'),
                        mainButton: this.$t('auth.auth-area.submit'),
                        process: this.verifyEmail,
                    };
            }
        },
    },
    methods: {
        setStage(stage) {
            this.stage = stage;
        },
        validate() {
            let isValid = true;
            this.neededInputs.forEach((field) => {
                switch (field) {
                    case 'password_confirmation':
                        if (!this.formData.password_confirmation.value) {
                            isValid = false;
                            this.formData.password_confirmation.error = 'Nhập lại mật khẩu là bắt buộc';
                        } else if (this.formData.password_confirmation.value !== this.formData.password.value) {
                            isValid = false;
                            this.formData.password_confirmation.error = this.$t('validation.confirmed', {
                                attribute: this.$t(`validation.attributes.password`),
                            });
                        } else {
                            this.formData.password_confirmation.error = null;
                        }
                        break;
                    case 'otp':
                        if (!this.formData.otp.value) {
                            this.formData.otp.error = this.$t('validation.custom.not_empty', {
                                Attribute: this.$t(`validation.attributes.otp`)
                            });
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
        login() {
            if (!this.validate()) {
                return;
            }
            common.loadingScreen.show('body');
            $.ajax({
                url: '/login',
                data: {
                    email: this.formData.email.value,
                    password: this.formData.password.value,
                    remember: this.remember_me,
                },
                method: 'POST',
            }).done((res) => {
                if (res.error === EErrorCode.NO_ERROR) {
                    window.location.reload();
                    return;
                }
                common.loadingScreen.hide('body');
            }).fail((err) => {
                common.loadingScreen.hide('body');
                this.$_processErrorMessage(err);
            });
        },
        register() {
            let data = this.$_validateAndGetFormData();
            if (!data) {
                return;
            }
            // if (!this.formData.acceptTerm.value) {
            //     this.formData.acceptTerm.error = true;
            //     return;
            // }

            //data.customerType = this.customerType;

            common.loadingScreen.show('body');
            $.ajax({
                url: '/register',
                data,
                method: 'POST',
            }).done((res) => {
                if (res.error === EErrorCode.NO_ERROR) {
                    window.location.reload();
                }
            }).fail((err) => {
                this.$_processErrorMessage(err);
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        verifyEmail() {
            let data = this.$_validateAndGetFormData();
            if (!data) {
                return;
            }

            switch (this.stage) {
                case ELoginStage.VERIFY_EMAIL:
                    data.type = EOtpType.VERIFY_EMAIL_WHEN_REGISTER;
                    break;
            }

            common.loadingScreen.show('body');
            $.ajax({
                url: '/auth/verify-email',
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
        async resendVerifyEmail() {
            let route = null,
                data = {};
            switch (this.stage) {
                case ELoginStage.VERIFY_EMAIL:
                    let confirm = await new Promise((resolve) => {
                        common.confirm(this.$t('auth.auth-area.resend-otp-confirm'), resolve);
                    });
                    if (!confirm) {
                        return;
                    }
                    data.type = EOtpType.VERIFY_EMAIL_WHEN_REGISTER;
                    route = '/auth/resend-verify-email';
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
                    common.alert(this.$t('auth.auth-area.verify-content-1'));
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
        requestResetPasswordEmail() {
            if (this.formData.email.cooldown && Date.now() < this.formData.email.cooldown.getTime()) {
                this.formData.email.error = this.$t('auth.waiting', {
                    seconds: Math.round((this.formData.email.cooldown.getTime() - Date.now()) / 1000),
                });
                return;
            }

            this.infoMessage = null;
            let data = this.$_validateAndGetFormData();
            if (!data) {
                return;
            }

            common.loadingScreen.show('body');
            $.ajax({
                url: '/password/email',
                data,
                method: 'POST',
            }).done((res) => {
                if (res.error === EErrorCode.NO_ERROR) {
                    this.infoMessage = res.message;
                    this.formData.email.value = null;
                    this.formData.email.cooldown = new Date();
                } else {
                    this.formData.email.error = res.message;
                }
            }).fail((err) => {
                this.$_processErrorMessage(err);
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        resetPassword() {
            this.infoMessage = null;
            let data = this.$_validateAndGetFormData();
            if (!data) {
                return;
            }

            data.token = common.getUrlQueries(window.location.href, 'rs_pw_token');

            common.loadingScreen.show('body');
            $.ajax({
                url: '/password/reset',
                data,
                method: 'POST',
            }).done((res) => {
                if (res.error === EErrorCode.NO_ERROR) {
                    this.stage = ELoginStage.NOT_LOGGED_IN;
                    common.alert(res.message, () => {
                        window.location.reload();
                    });
                } else {
                    this.infoMessage = res.message;
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
        }
    }
});
