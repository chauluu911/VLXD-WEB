import {appConfig, Vue} from './bootstrap';

// Global components
import BootstrapVue from 'bootstrap-vue';
Vue.use(BootstrapVue, {
    BPopover: {
        boundary: "window",
    },
    BTooltip: {
        boundary: "window",
    }
});

import VueRouter from 'vue-router';
import VueI18n from 'vue-i18n';

Vue.use(VueRouter);
Vue.use(VueI18n);

/*
 * i18n
 */
import commonMessage from '../locales/common';
import appMessage from '../locales/back/app';
const lang = document.documentElement.lang.substr(0, 2);
const i18n = new VueI18n({
    locale: lang,
    messages: _.merge(commonMessage, appMessage)
});

import registerComponent from './vue-components';
registerComponent(Vue);

import registerDirective from './vue-directive';
registerDirective(Vue);

import registerFilter from './vue-filter';
registerFilter(Vue);

import Util from '../lib/common';
Util.withI18n(i18n);

import getRouter from './router';

const router = getRouter(i18n, appConfig);
router.beforeEach((to, from, next) => {
    Util.loadingScreen.show();
    Util.post({
        url: `${appConfig.baseApiUrl}/authorize-check`,
        data: {
            toUrl: to.path,
            fromUrl: from.path
        }
    }).done(response => {
        next();
    }).fail(() => {
        next(false);
        let msg = i18n.t('common.error.unknown');
        if (error.status === 401) {
            msg = i18n.t('common.error.notAuthorization');
        }
        Util.showMsg('error', null, msg);
    }).always(() => {
        Util.loadingScreen.hide();
    });
});

import StringUtil from '../lib/string-utils';
import DateUtil from '../lib/date-utils';
import NumberUtil from '../lib/number-utils';
import ValidationUtil from '../lib/validation-utils';
import ApiUtil from '../lib/api-utils';
import FirebaseUtil from '../lib/firebase-utils';

import MetaStackMixin from './mixins/meta-stack-mixin';
import MobileWebMixin from "./mixins/mobile-web-mixin";
import LayoutMixin from "./mixins/layout";
import AdminVuexMixin from "./mixins/admin-vuex";

const app = new Vue({
    el: '#app',
    mixins: [MetaStackMixin, MobileWebMixin, LayoutMixin, AdminVuexMixin(Vue)],
    i18n,
    router,
    provide: {
        Util,
        StringUtil,
        DateUtil,
        NumberUtil,
        ValidationUtil,
        ApiUtil,
        Vue,
        FirebaseUtil,
    },
});

Util.withBvModal(app.$bvModal);
