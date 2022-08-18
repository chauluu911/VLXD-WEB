export default class ELevel {
    static get LEVEL_1() {
        return 1;
    }

    static get LEVEL_2() {
        return 2;
    }

    static get LEVEL_3() {
        return 3;
    }

    static get LEVEL_4() {
        return 4;
    }

    static get LEVEL_5() {
        return 5;
    }

    static valueToName(val) {
        switch (val) {
            case this.LEVEL_1:
                return 'Chưa xác thực';
            case this.LEVEL_2:
                return 'Xác thực';
            case this.LEVEL_3:
                return 'Đảm bảo';
            case this.LEVEL_4:
                return 'Chuyên nghiệp';
            case this.LEVEL_5:
                return 'VIP';
        }
        return null;
    }
}
