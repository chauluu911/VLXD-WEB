export default class EDisplayStatus {
    static get HIDDEN() {
        return 0;
    }
    static get SHOWING() {
        return 1;
    }

    static getVariant(status) {
        switch (status) {
            case this.SHOWING:
                return 'primary';
            case this.HIDDEN:
                return 'danger';
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
