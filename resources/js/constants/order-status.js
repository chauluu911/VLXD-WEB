export default class EOrderStatus {
    static get DELETED() {
        return -3;
    }
    static get CANCEL_BY_USER() {
        return -2;
    }
    static get CANCEL_BY_SHOP() {
        return -1;
    }
    static get WAITING() {
        return 0;
    }
    static get CONFIRMED() {
        return 1;
    }
    static get SHOPPING_CART() {
        return 2;
    }

    static getVariant(status) {
        switch (status) {
            case this.DELETED:
                return 'danger';
            case this.CANCEL_BY_USER:
                return 'danger';
            case this.CANCEL_BY_SHOP:
                return 'danger';
            case this.WAITING:
                return 'warning';
            case this.CONFIRMED:
                return 'primary';
            case this.SHOPPING_CART:
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
