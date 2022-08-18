export default class EConnectStatus {
    static get WAITING() {
        return 0;
    }
    static get CONNECTED() {
        return 1;
    }
    static get CANCELED() {
        return 2;
    }

    static getVariant(status) {
        switch (status) {
            case this.CANCELED:
                return 'danger';
            case this.WAITING:
                return 'warning';
            case this.CONNECTED:
                return 'primary';
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
