export default class ECustomOrderStatusForUser {

    static get CANCELED() {
        return -1;
    }
    static get WAITING() {
        return 0;
    }
    static get WAITING_FOR_DELIVERY() {
        return 1;
    }
    static get ON_THE_WAY() {
        return 2;
    }
    static get DELIVERED_SUCCESS() {
        return 3;
    }

}
