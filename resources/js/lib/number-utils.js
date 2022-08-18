class NumberUtil {
    constructor() {

    }

    twoDigitNumber(number) {
        return number < 10 ? '0' + number : number;
    }
}

const util = new NumberUtil();

export default util;
