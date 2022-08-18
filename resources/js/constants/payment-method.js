export default class EPaymentMethod {
    static get BANK_TRANSFER() {
        return 1;
    }
    static get PAYMENT_GATEWAY() {
        return 2;
    }
    static get CODE() {
        return 3;
    }
    static get COIN() {
        return 4;
    }
    static get FREE() {
        return 100;
    }
}
