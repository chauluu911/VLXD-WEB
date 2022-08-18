export default class EAdvertiseStatus {
    static get DELETED() {
        return -1;
    }
    static get WAITING() {
        return 0;
    }
    static get APPROVED() {
        return 1;
    }
    static get EXPIRED() {
        return -2;
    }
}
