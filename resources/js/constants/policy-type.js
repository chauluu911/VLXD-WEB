export default class EPolicyType {
    static get TERMS_AND_CONDITIONS() {
        return 1;
    }
    static get USER_GUIDE() {
        return 2;
    }
    static get PAYMENT_POLICY() {
        return 3;
    }
    static get TRANSPORTATION_POLICY() {
        return 4;
    }
    static get PRIVACY_POLICY() {
        return 5;
    }
    static get BUY_POLICY() {
        return 6;
    }
    static get ABOUT_US() {
        return 7;
    }

    static valueToName(val, langStr = '') {
        switch (val) {
            case this.TERMS_AND_CONDITIONS:
                return 'terms.and.conditions';
            case this.USER_GUIDE:
                return 'guide_user';
            case this.PAYMENT_POLICY:
                return 'payment_policy';
            case this.TRANSPORTATION_POLICY:
                return 'transportation_policy';
            case this.PRIVACY_POLICY:
                return 'privacy_policy';
            case this.BUY_POLICY:
                return 'buy_policy';
            case this.ABOUT_US:
                return 'about_us';
        }
        return null;
    }
}
