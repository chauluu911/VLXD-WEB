export default class EDeliveryStatus {
    static get WAITING_FOR_APPROVAL() {
        return 0;
    }

    static get ON_THE_WAY() {
        return 1;
    }

    static get DELIVERY_SUCCESS() {
        return 2;
    }

    static get CUSTOMER_REFUSE() {
        return 3;
    }

}
