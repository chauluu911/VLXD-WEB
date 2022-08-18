import StringUtil from '../lib/string-utils';

export default function registerFilter(Vue) {
    Vue.filter('code', (val) => {
        return StringUtil.formatCode(val);
    });

    Vue.filter('address', (val) => {
        return StringUtil.formatAddress(val);
    });

    Vue.filter('description', (val) => {
        return StringUtil.formatDescription(val);
    });

    Vue.filter('money', (val, decimalCount = 2, decimal = ".", thousands = ",", currency = '') => {
        return StringUtil.formatMoney(val, decimalCount, decimal, thousands, currency);
    });

    Vue.filter('percent', (val) => {
        return StringUtil.formatPercent(val);
    });

    Vue.filter('date', (val) => {
        return StringUtil.formatDate(val);
    });

    Vue.filter('dateTime', (val) => {
        return StringUtil.formatDateTime(val);
    });

    Vue.filter('shorten', (val, limit = 150) => {
        return StringUtil.shortenText(val, limit);
    });

    Vue.filter('shortContent', (val, limit = 100) => {
        return StringUtil.shortenText(val, limit);
    });
}
