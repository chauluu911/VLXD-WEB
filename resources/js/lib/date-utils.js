import NumberUtil from './number-utils';
// import * as timeago from 'timeago.js';
// import timeagoMessages from '../locales/back/timeago';

class DateUtil {
    constructor() {
        /*Object.keys(timeagoMessages).forEach((lang) => {
            timeago.register(lang, (number, index, totalSec) => {
                // number: the time ago / time in number;
                // index: the index of array below;
                // totalSec: total seconds between date to be formatted and today's date;
                return timeagoMessages[lang][index];
            });
        });*/
    }

    getDateString(date, separator = '/', getShortYear = false) {
        return NumberUtil.twoDigitNumber(date.getDate()) + separator + NumberUtil.twoDigitNumber(date.getMonth() + 1) + separator + (getShortYear ? date.getFullYear().toString().substr(2) : date.getFullYear());
    }

    getDateStringReverse(date, separator = '/', getShortYear = false) {
        return (getShortYear ? date.getFullYear().toString().substr(2) : date.getFullYear()) + separator + NumberUtil.twoDigitNumber(date.getMonth() + 1) + separator + NumberUtil.twoDigitNumber(date.getDate());
    }

    getTimeString(date, separator = ':', getSecond = false) {
        return NumberUtil.twoDigitNumber(date.getHours()) + separator + NumberUtil.twoDigitNumber(date.getMinutes()) + (getSecond ? separator + NumberUtil.twoDigitNumber(date.getSeconds()) : '');
    }

    getDateTimeString(date, dateSeparator = '/', timeSeparator = ':', getShortYear = false, getSecond = false, dateTimeSeparator = ' ') {
        let time = this.getTimeString(date, timeSeparator, getSecond);
        date = this.getDateString(date, dateSeparator, getShortYear);
        return `${time}${dateTimeSeparator}${date}`;
    }

    getDateTimeString2(date, dateSeparator = '/', timeSeparator = ':', getShortYear = false, getSecond = true, dateTimeSeparator = ' - ') {
        let time = this.getTimeString(date, timeSeparator, getSecond);
        date = this.getDateString(date, dateSeparator, getShortYear);
        return `${time}${dateTimeSeparator}${date}`;
    }

    // credit to https://g14n.info/2018/07/js-date-manipulation/
    addYears(num, t1 = new Date()) {
        const t2 = new Date(t1);
        t2.setFullYear(t2.getFullYear() + num);
        return t2;
    }

    addMonths(num, t1 = new Date()) {
        const t2 = new Date(t1);
        t2.setMonth(t2.getMonth() + num);
        return t2;
    }

    addDays(num, t1 = new Date()) {
        const t2 = new Date(t1);
        t2.setDate(t2.getDate() + num);
        return t2;
    }

    addHours(num, t1 = new Date()) {
        const t2 = new Date(t1);
        t2.setHours(t2.getHours() + num);
        return t2;
    }

    addMinutes(num, t1 = new Date()) {
        const t2 = new Date(t1);
        t2.setMinutes(t2.getMinutes() + num);
        return t2;
    }

    daysAgo(num, t1 = new Date()) {
        const t2 = new Date(t1);
        t2.setDate(t2.getDate() - num);
        return t2;
    }

    nextHour(t = new Date()) {
        return this.addHours(t, 1);
    }

    tomorrow(t = new Date()) {
        return this.addDays(t, 1);
    }

    ymd(t = new Date()) {
        return t.toISOString().slice(0, 10)
    }

    truncateDay(t = new Date()) {
        return new Date(`${this.ymd(t)} 00:00:00`);
    }

    today(t = new Date()) {
        return this.truncateDay(t);
    }

    checkGoTimeReady() {
        console.log('check go time');
        return new Promise((resolve) => {
            let goTimeReady = () => {
                console.log('check go time rd');
                if (GoTime && GoTime.getHistory() && GoTime.getHistory().length) {
                    console.log('gotime ready')
                    resolve();
                } else {
                    console.log('gotime timeout')
                    setTimeout(goTimeReady, 1000);
                }
            };
            goTimeReady();
        });
    }

    /*isValid(day) {
        try {
            var t = new Date(day)
            return t.toISOString().slice(0, 10) === day
        } catch (err) {
            return false
        }
    }*/

    /*timeago(t = new Date(), locale = 'vi') {
        return timeago.format(t, locale);
    }*/
}

const util = new DateUtil();

export default util;
