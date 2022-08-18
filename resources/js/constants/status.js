export default class EStatus {
    static get DELETED() {
        return -1;
    }
    static get WAITING() {
        return 0;
    }
    static get ACTIVE() {
        return 1;
    }
    static get SUSPENDED() {
        return 2;
    }
    static get EXCEPT_DELETED() {
        return 3;
    }

    static getVariant(status) {
        switch (status) {
            case this.DELETED:
                return 'danger';
            case this.WAITING:
                return 'warning';
            case this.ACTIVE:
                return 'primary';
            case this.SUSPENDED:
                return 'warning';
        }
        return '';
    }

    static getTextVariant(status) {
        let variant = this.getVariant(status);
        if (!variant) {
            return '';
        }
        return `text-${variant}`;
    }
}
