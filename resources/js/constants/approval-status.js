export default class EApprovalStatus {
    static get WAITING() {
        return 0;
    }

    static get APPROVED() {
        return 1;
    }

    static get DENY() {
        return -1;
    }
}
