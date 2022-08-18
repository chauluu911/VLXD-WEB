export default class EProductStatus {
    static get REJECTED() {
        return -2;
    }
    static get DELETED() {
        return -1;
    }
    static get WAITING() {
        return 0;
    }
    static get ACTIVE() {
        return 1;
    }
    static get WAITING_AND_ACTIVE() {
        return 2;
    }
}
