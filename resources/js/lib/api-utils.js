'use strict';

import ErrorCode from '../constants/error-code.js';
import commonUtil from './common';

class ApiUtil {
    constructor() {

    }

    getCategory(type, get = ['id', 'type', 'code', 'name', 'name_search'], options = {}) {
        return new Promise((resolve, reject) => {
            let requestData = {
                type,
                get,
                options
            };
            if (options.page) {
                requestData.page = options.page;
                delete options.page;
            }
            commonUtil.get({
                url: '/api/back/category/all',
                data: requestData
            }).done((res) => {
                if (res.error === ErrorCode.NO_ERROR) {
                    resolve(res.data);
                    return;
                }
                reject(res);
            }).fail((err) => {
                reject(err);
            });
        });
    }

    getMembershipCardLevel() {
        return new Promise((resolve, reject) => {
            commonUtil.post({
                url: '/api/back/membership-card/level/all',
            }).done((res) => {
                if (res.error === ErrorCode.NO_ERROR) {
                    resolve(res.data);
                    return;
                }
                reject(res.message);
            }).fail((err) => {
                reject(err);
            });
        });
    }

    getBranch() {
        return new Promise((resolve, reject) => {
            commonUtil.get({
                url: '/api/back/config/branch/search',
            }).done((res) => {
                if (res.error === ErrorCode.NO_ERROR) {
                    resolve(res.data);
                    return;
                }
                reject(res.msg);
            }).fail((err) => {
                reject(err);
            });
        });
    }
}

const util = new ApiUtil();

export default util;
