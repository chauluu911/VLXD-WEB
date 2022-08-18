//window._ = require('lodash');
'use strict';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': token.content,
        }
    });
} else {
    process.env.NODE_ENV === 'development' && console.error('CSRF token not found');
}

$.ajaxSetup({
    headers: {
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        // get offset minutes from js then convert to hours
        timezone_offset: (- new Date().getTimezoneOffset()) / 60,
    }
});

const Vue = require('vue');
window.Vue = Vue;

const lodash = require('../lib/lodash.custom.min');
window._ = lodash;

window.VIEW_BP = {
    sm: 576,
    md: 768,
    lg: 992,
    xl: 1200
};