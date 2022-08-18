import Vuex from 'vuex';
import firebaseUtil from '../../lib/firebase-utils';
import util from '../../lib/common';

let firebaseRef = {
    allConversationUnreadCount: (userId) => `chats_by_user/u${userId}/number_of_unseen_messages`,
};

const AdminVuexMixin = (Vue) => {
    Vue.use(Vuex);

    const store = new Vuex.Store({
        state: {
            debug: process.env.NODE_ENV !== 'production',
            authState:  {
                id: null,
                name: '',
                chatId: null,
            },
            filterFormState: {
                hasForm: false,
                form: []
            },
            filterValueState: {
                timestamp: null,
                value: {},
            },
            breadcrumbsState: [],
            sidebarState: {
                active: true,
                hovered: false,
            },
            notificationState: {
                unreadCount: 0,
            },
            chatState: {
                unreadCount: 0,
            },
            queryFilterState: {
                enable: true,
                placeholder: null,
            },
            loadingState: false,
        },
        mutations: {
            updateAuthState(state, newAuthState) {
                state.authState = newAuthState || {
                    id: null,
                    name: ''
                };
            },
            updateFilterFormState(state, newFilterFormState) {
                state.filterFormState = {
                    hasForm: newFilterFormState && Array.isArray(newFilterFormState) && newFilterFormState.length > 0,
                    form: newFilterFormState || [],
                };
            },
            updateFilterValueState(state, newFilterValueState) {
                state.filterValueState = {
                    timestamp: Date.now(),
                    value: newFilterValueState || {},
                };
            },
            updateBreadcrumbsState(state, newBreadcrumbsState) {
                state.breadcrumbsState = newBreadcrumbsState || [];
            },
            updateSidebarState(state, newSidebarState) {
                state.sidebarState = {
                    ...state.sidebarState,
                    ...newSidebarState,
                };
            },
            updateChatUnreadCount(state, newChatUnreadCountState) {
                state.chatState.unreadCount = newChatUnreadCountState;
            },
            updateQueryFilterState(state, newQueryFilterState) {
                state.queryFilterState = {
                    ...state.queryFilterState,
                    ...newQueryFilterState,
                };
            },
            updateLoadingState(state, newState) {
                state.loadingState = newState;
            },
        }
    });

    return {
        store,
        data() {
            return {
                numberOfUnreadChatMessage: 0,
            }
        },
        computed: {
            ...Vuex.mapState(['authState']),
        },
        watch: {
            authState(state) {
                if (!state.id) {
                    return;
                }

                /*firebaseUtil.checkFirebaseReady().then(() => {
                    let ref = firebase.database().ref(firebaseRef.allConversationUnreadCount(state.chatId));
                    ref.on('value', (snapshot) => {
                        let unreadCount = snapshot.val() || 0;
                        store.commit('updateChatUnreadCount', unreadCount);
                    });
                });*/
            },
        },
        methods: {
            getAuthInfo() {
                util.get({
                    url: `${this.appConfig.baseApiUrl}/auth`
                }).done((res) => {
                    store.commit('updateAuthState', res.data);
                });
            }
        },
        created() {
            this.getAuthInfo();
        }
    };
};
export default AdminVuexMixin;
