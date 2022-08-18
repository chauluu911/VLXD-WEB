export default class EHomeConfigType {
    static get HOME() {
        return 1;
    }
    static get HOME_BUYER() {
        return 2;
    }
    static get HOME_SELLER() {
        return 3;
    }
    static get HOME_ADS() {
        return 4;
    }
    static get ABOUT_US() {
        return 5;
    }

    static valueToName(val, langStr = '') {
        switch (val) {
            case this.HOME:
                return 'home.introduce' + langStr;
            case this.HOME_BUYER:
                return 'home.introduce.buyer' + langStr;
            case this.HOME_SELLER:
                return 'home.introduce.seller' + langStr;
            case this.HOME_ADS:
                return 'home.introduce.advertiser' + langStr;
            case this.ABOUT_US:
                return 'about-us' + langStr;
        }
        return null;
    }

    static isValid(val) {
        return [
            this.HOME,
            this.HOME_BUYER,
            this.HOME_SELLER,
            this.HOME_ADS,
            this.ABOUT_US,
        ].indexOf(val) > -1;
    }
}
