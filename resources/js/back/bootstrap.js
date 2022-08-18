'use strict';

let token = document.head.querySelector('meta[name="api-token"]').content;

if (token) {
    $.ajaxSetup({
        headers: {
            Authorization: `Bearer ${token}`
        }
    });
} else {
    process.env.NODE_ENV === 'development' && console.error('CSRF token not found');
}

$.ajaxSetup({
    headers: {
        'x-timezone': Intl.DateTimeFormat().resolvedOptions().timeZone,
        // get offset minutes from js then convert to hours
        'x-timezone-offset': (-new Date().getTimezoneOffset()) / 60,
    }
});

// some UI
setTimeout(() => {
    $('.nav-ctn .navbar__toggler').on('click', () => {
        $('.nav-ctn').toggleClass('nav-ctn--collapsed');
    });
});

const Vue = require('vue');
window._ = require('../lib/lodash.custom.min');

window.VIEW_BP = {
    sm: 576,
    md: 768,
    lg: 992,
    xl: 1200
};

let isMobile = document.head.querySelector('meta[name="is-mobile"]');
let isTablet = document.head.querySelector('meta[name="is-tablet"]');
let googleMapApiKey = document.head.querySelector('meta[name="google-map-api-key"]');
let locale = document.documentElement.getAttribute('lang');

let appConfig = {};
Object.defineProperty(appConfig, 'isMobile', {
    value: Number(isMobile.content),
    writable: false,
    enumerable: true,
    configurable: true
});
Object.defineProperty(appConfig, 'isTablet', {
    value: Number(isTablet.content),
    writable: false,
    enumerable: true,
    configurable: true
});
Object.defineProperty(appConfig, 'baseApiUrl', {
    value: '/api/back',
    writable: false,
    enumerable: true,
    configurable: true
});
Object.defineProperty(appConfig, 'locale', {
    value: locale,
    writable: false,
    enumerable: true,
    configurable: true
});
/*Object.defineProperty(appConfig, 'googleMapApiKey', {
    value: googleMapApiKey.content,
    writable: false,
    enumerable: true,
    configurable: true
});*/

import AppConstant from "../constants/app-constant";

Vue.mixin({
    data() {
        return {
            AppConstant,
            appConfig: appConfig,
            actionType: null,
            sz: AppConstant.DEFAULT_PAGE_SIZE
        }
    }
});

export { appConfig, Vue };
