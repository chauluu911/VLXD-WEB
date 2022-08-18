import EErrorCode from "../../constants/error-code";
import ENotificationType from "../../constants/notification-type";

let getNotificationTimeout = null;
let getNewsTimeout = null;
const app = new Vue ({
    el:'#login-notification',
    name: 'notify',
    data() {
        return {
            type: 'system',
            userId: $('#user-id-for-message-notify').data('id'),
            message: {
                numberMessageUnSeen: null,
                firebaseRef : null,
            },
            notify: {
                pageSize: 6,
                data: {},
                numberNotSeen: 0,
                numberNotification: 0,

            },
            isShowMobileDropdown: false,
            news: {
                pageSize: 6,
                data: {},
                numberNews: 0,
            },
            iconLoadNotify: false,
            iconLoadNews: false,
            filter: {
                q: null
            },
            FirebaseUtil: firebaseUtil,
            optionSearchProduct: {
                approvalStatus: 1,
                minPrice: 0,
                maxPrice: '100000000',
                orderBy: 'created_at',
                orderDirection: 'desc',
                page: 1,
            }
        }
    },
    created() {
        this.getNotification(true);
        this.getNews(true);
        this.getUnseenMessage();
    },
    mounted() {
        let listNotifyEl = this.$refs.listNotifyEl;
        let listNewsEl = this.$refs.listNewsEl;
        $(listNotifyEl).scroll(() => {
            if (listNotifyEl.scrollHeight - listNotifyEl.clientHeight === listNotifyEl.scrollTop) {
                this.loadMore();
            }
        });
        $(listNewsEl).scroll(() => {
            if (listNewsEl.scrollHeight - listNewsEl.clientHeight === listNewsEl.scrollTop) {
                this.loadMore();
            }
        });

        let listNotifyElMobile = this.$refs.listNotifyElMobile;
        $(listNotifyElMobile).scroll(() => {
            if (listNotifyElMobile.scrollHeight - listNotifyElMobile.clientHeight === listNotifyElMobile.scrollTop) {
                this.loadMore();
            }
        });

        let listNewsElMobile = this.$refs.listNewsElMobile;
        $(listNewsElMobile).scroll(() => {
            if (listNewsElMobile.scrollHeight - listNewsElMobile.clientHeight === listNewsElMobile.scrollTop) {
                this.loadMore();
            }
        });
    },
    beforeDestroy() {
        if (this.message.firebaseRef) {
            this.message.firebaseRef.off();
        }
    },
    methods:{
        searchProduct() {
            window.location.assign(
                stringUtil.getUrlWithQueries('/product', {q:  $('#input-search').val(), ...this.optionSearchProduct})
            );
        },
        searchProductMobile() {
            window.location.assign(
                stringUtil.getUrlWithQueries('/product', {q:  $('#input-search-mobile').val(), ...this.optionSearchProduct})
            );
        },
        changeOption(type) {
            this.type = type;
            if (type == 'system') {
                this.notify.pageSize += 6;
                this.getNotification(true);
            } else {
                this.news.pageSize += 6;
                this.getNews(true);
            }
        },
        getNotification(tmp = false) {
            if (getNotificationTimeout) {
                clearTimeout(getNotificationTimeout);
                getNotificationTimeout = null;
            }
            common.post({
                url: '/notification/get-notification',
                data: {
                    pageSize: this.notify.pageSize
                }
            }).done(response => {
                if(!this.userId) {
                    return false;
                }
                this.notify.data = response.notification;
                this.notify.numberNotSeen = response.numberNotSeen;
                this.notify.numberNotification = response.numberNotification;
                if (tmp) {
                    getNotificationTimeout = setTimeout(() => {
                        this.getNotification(tmp);
                    }, 30000);
                }
            }).always(() => {
                this.iconLoadNotify = false;
            });
        },
        getNews(tmp = false) {
            if(!this.userId) {
                return false;
            }
            if (getNewsTimeout) {
                clearTimeout(getNewsTimeout);
                getNewsTimeout = null;
            }
            common.post({
                url: '/news/get-news',
                data: {
                    pageSize: this.news.pageSize
                }
            }).done(response => {
                this.news.data = response.news;
                this.news.numberNews = response.numberOfNews;
                if (tmp) {
                    getNewsTimeout = setTimeout(() => {
                        this.getNews(tmp);
                    }, 30000);
                }
            }).always(() => {
                this.iconLoadNews = false;
            });
        },
        loadMore() {
            if (this.type == 'system') {
                this.iconLoadNotify = true;
            } else {
                this.iconLoadNews = true;
            }
            this.pageSize += 6;
            this.changeOption(this.type);
        },
        seenNotification(item) {
            common.post({
                url: '/notification/seen-notification',
                data: {
                    notificationId: item.id,
                    type: item.type,
                }
            }).done(response => {
                if(response.error !== EErrorCode.NO_ERROR) {
                    return;
                }
                if (item.href) {
                    if (item.meta.bannerId) {
                        window.location.assign(
                            stringUtil.getUrlWithQueries(item.href, {bannerId: item.meta.bannerId})
                        );
                    } else {
                        window.location.assign(
                            stringUtil.getUrlWithQueries(item.href)
                        );
                    }
                }else {
                    if (item.type == ENotificationType.APPROVED_PRODUCT || 
                        item.type == ENotificationType.APPROVED_SHOP) {
                        bootbox.alert('Cửa hàng không còn tồn tại');
                    }
                }
            }).always(() => {

            });
        },
        redirectTo(item) {
            if (item.href) {
                window.location.assign(item.href);
            }
        },
        async getUnseenMessage() {
            await this.FirebaseUtil.checkFirebaseReady();
            if(this.userId) {
                let ref = firebase.database().ref(`chats_by_user/u${this.userId}/number_of_unseen_messages`);
                this.firebaseRef = ref;
                ref.on('value', snapshot => {
                    this.$set(this.message, 'numberMessageUnSeen', snapshot.val());
                })
            }
        },
        getTabName() {
            let path =  window.location.pathname;
            if(path === '/'){
                return 'home';
            } else if (path.indexOf('/shop') === 0) {
                return 'shop';
            } else if (path.indexOf('/profile') === 0) {
                return 'profile';
            }
        },
        toggleShowMobileNotify() {
            this.isShowMobileDropdown = !this.isShowMobileDropdown;
        }
    }
})
