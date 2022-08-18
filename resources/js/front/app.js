require('./bootstrap');
import Vuex from "vuex";
import commonMessage from '../locales/common';
import VueI18n from 'vue-i18n';

const lang = document.documentElement.lang.substr(0, 2);

const i18n = new VueI18n({
    locale: lang,
    messages: _.merge(commonMessage),
    silentTranslationWarn: true,
});

import registerComponent from './vue-components';
registerComponent(Vue);

import registerDirective from './vue-directive';
registerDirective(Vue);

import registerFilter from './vue-filter';
registerFilter(Vue);

Vue.use(Vuex);

let isMobile = document.head.querySelector('meta[name="is-mobile"]');
let isTablet = document.head.querySelector('meta[name="is-tablet"]');

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

if (window.location.pathname.startsWith('/trial/speaking-demo-test') || window.location.pathname.startsWith('/trial/speaking-demo-teacher')) {
    Object.defineProperty(appConfig, 'isTester', {
        value: Number(document.head.querySelector('meta[name="is-tester"]').content),
        writable: false,
        enumerable: true,
        configurable: true
    });
}

import firebaseUtil from '../lib/firebase-utils';
let firebaseRef = {
    allConversationUnreadCount: (userId) => `chats_by_user/u${userId}/number_of_unseen_messages`,
};

const store = new Vuex.Store({
    state: {
        debug: process.env.NODE_ENV !== 'production',
        authState:  {
            code: $('#user-id').val(),
            name: '',
            phone: '',
            email: '',
            dateOfBirth: null,
            gender: null,
        },
        walletState: {
            money: null,
            point: null,
            star: null,
        },
    },
    mutations: {
        updateAuthState(state, newAuthState) {
            state.authState = {
                ...state.authState,
                ...newAuthState,
            };
        },
    }
});

firebaseUtil.checkFirebaseReady().then(() => {
    let chatId = $('#user-id').val();
    if (!chatId) {
        return;
    }
    let ref = firebase.database().ref(firebaseRef.allConversationUnreadCount(chatId));
    ref.on('value', (snapshot) => {
        let unreadCount = snapshot.val() || 0;
        store.commit('updateChatUnreadCount', unreadCount);
        console.log(unreadCount);
        $('.unseen-message-count span').text(unreadCount > 9 ? '9+' : (unreadCount || ''));
        if (unreadCount > 9) {
            $('.unseen-message-count').removeClass('d-none').addClass('unseen-message-count--plus');
        } else if (unreadCount) {
            $('.unseen-message-count').removeClass('d-none').removeClass('unseen-message-count--plus');
        } else {
            $('.unseen-message-count').addClass('d-none');
        }
    });
});

import StringUtil from '../lib/string-utils';
import DateUtil from '../lib/date-utils';
import NumberUtil from '../lib/number-utils';
import FileUtil from '../lib/file-utils';
import Util from '../lib/common';
import FirebaseUtil from '../lib/firebase-utils';
Util.withI18n(i18n);

window.common = Util;
window.dateUtil = DateUtil;
window.stringUtil = StringUtil;
window.numberUtil = NumberUtil;
window.fileUtil = FileUtil;
window.firebaseUtil = FirebaseUtil

Vue.mixin({
    store,
    i18n,
    data() {
        return {
            appConfig: appConfig,
            //shared: jsShare,
            pageSize: 7,
        }
    },
});
