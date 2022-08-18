export default class ELoginStage {
    static get NOT_REGISTERED() {
        return 1;
    }
    static get NOT_LOGGED_IN() {
        return 2;
    }
    static get VERIFY_EMAIL() {
        return 3;
    }
    static get LOGGED_IN() {
        return 4;
    }
    static get FORGOT_PASSWORD_EMAIL() {
        return 5;
    }
    static get VERIFY_OTP_FORGOT_PASSWORD() {
        return 6;
    }
    static get RESET_PASSWORD() {
        return 7;
    }
    static get VERIFY_SMS() {
        return 8;
    }
    static get OAUTH_ADDITION_INFO() {
        return 9;
    }
}
