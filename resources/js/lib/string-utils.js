import DateUtil from './date-utils';

class StringUtil {
    constructor() {
    }

    nl2br(str, is_xhtml) {
        if (!str) return '';

        let breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    }

    sp2nbsp(val) {
        if (!val) return '';
        return val.replace(' ', '&#160;');
    }

    replaceAll(search, replace, subject) {
        if (!Array.isArray(search)) {
            search = [search];
        }

        let replaceString = (search, replace, subject) => {
            return subject.replace(new RegExp(search, 'g'), replace);
        };

        search.forEach((searchItem, index) => {
            if (!Array.isArray(search)) {
                subject = replaceString(searchItem, replace, subject);
            } else {
                subject = replaceString(searchItem, replace.hasOwnProperty(index) ? replace[index] : '', subject);
            }
        });

        return subject;
    }

    formatMoney(amount, decimalCount = 0, decimal = ".", thousands = ",", currency = '') {
        try {
            decimalCount = Math.abs(decimalCount);
            decimalCount = isNaN(decimalCount) ? 2 : decimalCount;

            const negativeSign = amount < 0 ? "-" : "";

            let i = parseInt(amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)).toString();
            let j = (i.length > 3) ? i.length % 3 : 0;

            return negativeSign + (j ? i.substr(0, j) + thousands : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands) + (decimalCount ? decimal + Math.abs(amount - i).toFixed(decimalCount).slice(2) : "") + ` ${currency}`;
        } catch (e) {
            console.error(e)
        }
    }

    compileLangArr(lang, mergeWith = {}) {
        let result = {
            en: {
                ...mergeWith.en
            },
            vi: {
                ...mergeWith.vi
            },
        };
        Object.keys(lang).forEach((key) => {
            result.en[key] = lang[key].en;
            result.vi[key] = lang[key].vi;
        });
        return result;
    }

    ucfirst(str) {
        return str[0].toUpperCase() + str.slice(1);
    }

    standardizedVietnamPhoneFormat(number) {
        let allowSpecialCharacterRegex = /[()\-\s]/g;
        return number.replace(allowSpecialCharacterRegex, '').replace(/\+84/g, '0');
    }

    validateVietnamPhone(number) {
        number = this.standardizedVietnamPhoneFormat(number);
        let numberRegex = /(\+84|0)([35789]\d{8}|2\d{9})$/g;
        return numberRegex.test(number);
    }

    validateString(str) {
        let lengthRegex = /.{1,}/g;
        let specialCharacterRegex = /[$&+,:;=?@#|<>.\-^*()%!]+/g;
        return !specialCharacterRegex.test(str) && lengthRegex.test(str);
    }

    validateEmail(email) {
        let emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return emailRegex.test(email);
    }

    switchCase(str, toCase) {
        let arr = str.split(/(?=[A-Z])|[_\s-](?=[A-Za-z])/);
        switch (toCase) {
            case 'snake':
                return arr.join('_').toLowerCase();
            case 'kebab':
                return arr.join('-').toLowerCase();
            case 'camel':
                return arr.map((part, index) => (!index ? part.charAt(0) : part.charAt(0).toUpperCase()) + part.slice(1).toLowerCase()).join('');
        }
        return str;
    }

    getYoutubeVideoId(url) {
        let regExp = new RegExp('^[a-zA-Z0-9_-]{11}$');
        if (regExp.test(url)) {
            return url;
        }
        regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
        let match = url.match(regExp);
        return (match && match[7].length === 11) ? match[7] : false;
    }

    getTikTokVideoId(url) {
        let lastIndex = url.lastIndexOf('video/');
        return url.slice(lastIndex + 'video/'.length);
    }
    getYoutubeThumbnail(youtubeId) {
        return '//i3.ytimg.com/vi/:youtubeId/hqdefault.jpg'.replace(':youtubeId', youtubeId);
    }

    formatCode(code) {
        return code || '-';
    }

    formatAddress(address) {
        return this.nl2br(address);
    }

    formatDescription(description) {
        let descText = $(description).text();
        if (!descText) {
            descText = description;
        }
        return descText;
    }

    formatPercent(percent) {
        if (!percent) {
            percent = 0;
        }
        return `${percent}%`;
    }

    formatDate(date) {
        if (date) {
            date = new Date(date);
            date = DateUtil.getDateString(date);
        }
        return date;
    }

    formatDateTime(date) {
        if (date) {
            date = new Date(date);
            date = DateUtil.getDateTimeString(date);
        }
        return date;
    }

    shortenText(text, limit = 150) {
        if (!text) {
            return '';
        }
        return text.substr(0, limit);
    }

    getUrlQueries(url, key = null) {
        let tmp = url.split('?');
        if (!tmp.length || tmp.length < 2) {
            return key ? null : [];
        }
        tmp = tmp[1];
        let queries = tmp.split('&');
        let result = {};
        for (let i = 0; i < queries.length; i++) {
            let query = queries[i];
            query = query.split('=');
            if (key && query[0] === key) {
                return typeof query[1] !== 'undefined' ? unescape(query[1]) : null;
            }
            result[query[0]] = query.length !== 2 ? null : unescape(query[1]);
        }
        return result;
    }

    getUrlWithQueries(pathname, queries = {}) {
        let queriesString = Object.keys(queries)
            .filter((key) => {
                return queries[key] !== null && queries[key] !== undefined && queries[key] !== '';
            })
            .map((key) => {
                return `${key}=${queries[key]}`;
            })
            .join('&');
        return `${pathname.split('?')[0]}?${queriesString}`;
    }

    stringToSlug(str) {
        str = str.replace(/^\s+|\s+$/g, ''); // trim
        str = str.toLowerCase();

        // remove accents, swap ñ for n, etc
        let langMap = {
            'a': 'àáạảãâầấậẩẫăằắặẳẵäÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÄ',
            'c': 'çÇ',
            'e': 'èéẹẻẽêềếệểễëÈÉẸẺẼÊỀẾỆỂỄË',
            'i': 'ìíịỉĩïîÌÍỊỈĨÏÎ',
            'n': 'ñÑ',
            'o': 'òóọỏõôồốộổỗơờớợởỡöÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÖ',
            'u': 'ùúụủũưừứựửữüûÙÚỤỦŨƯỪỨỰỬỮÜÛ',
            'y': 'ỳýỵỷỹỲÝỴỶỸ',
            'd': 'đĐ',
            '-': '·/_,:;',
        };
        Object.keys(langMap).forEach((replacement) => {
            str = str.replace(new RegExp(`[${langMap[replacement]}]`, 'gu'), replacement);
        });

        str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
            .replace(/\s+/g, '-') // collapse whitespace and replace by -
            .replace(/-+/g, '-'); // collapse dashes

        return str;
    }

    randomStr(length = 5) {
        let string = '';
        while (string.length < length) {
            string += Math.random().toString(36).substring(0, length - string.length);
        }
        return string;
    }

    getAvatarPath(avatarPath) {
        return avatarPath || '/images/default-user-avatar.png';
    }
}

const util = new StringUtil();

export default util;
