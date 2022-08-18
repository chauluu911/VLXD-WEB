'use strict';

import EErrorCode from "../../constants/error-code";
import FCameraIcon from 'vue-feather-icons/icons/CameraIcon';
import FEdit3Icon from 'vue-feather-icons/icons/Edit3Icon';
import ENotificationType from "../../constants/notification-type";

let getNotificationTimeout = null;
const app = new Vue({
	el:'#notification',
    components: {
        FCameraIcon,
        FEdit3Icon
    },
    data() {
        return {
            iconLoad: false,
            numbernotificationNotSeen: 0,
            numberNotification: 0,
            pageSize: 6,
            results: {},

            ENotificationType,
        }
    },
    created() {
        this.getNotification();
    },
    mounted() {
        let el = this.$refs.conversationListEl;
        $(el).scroll(() => {
            if (el.scrollHeight - el.clientHeight === this.$refs.conversationListEl.scrollTop) {
                this.loadMore();
            }
        });
    },
    
    methods: {
        getNotification(tmp = false) {
            this.iconLoad = true;
            if (getNotificationTimeout) {
                clearTimeout(getNotificationTimeout);
                getNotificationTimeout = null;
            }
            let data = {
                pageSize: this.pageSize,
            };
            $.post('notification/get-notification', data)
            .done(response => {
                this.results = response.notification;
                // this.numberNotSeen = response.numberNotSeen;
                this.numberNotification = response.numberNotification;
                this.iconLoad = false;
            })
            .always(() => {
                this.iconLoad = false;
            });

            if (tmp) {
                getNotificationTimeout = setTimeout(() => {
                    this.getNotification(tmp);
                }, 30000);
            }
        },

        seenNotification(item) {
            this.type = item.type;
            let data = {
                notification: item.id,
                type: item.type,
            };
            $.post('notification/seen-notification', data)
            .done(response => {
                if(response.error !== EErrorCode.NO_ERROR) {
                    return;
                }
                if (item.href) {
                    window.location.assign(item.href);
                } else if(item.type == ENotificationType.POST_AVAILABLE) {
                    let data = {};
                    Object.keys(item.meta).map(async (key) => {
                        switch(key) {
                            case 'category_id':
                                data['categoryId'] = item.meta[key];
                                break;
                            case 'country_id':
                                if (item.meta[key]) {
                                    data['countryId'] = item.meta[key];
                                }
                                break;
                            case 'area_id':
                                if (item.meta[key]) {
                                    data['areaId'] = [];
                                    item.meta[key].forEach((item) => {
                                        data['areaId'].push(item);
                                    });
                                }
                                break;
                            default:
                                if (item.meta[key] != null) {
                                    data[key] = item.meta[key];
                                }
                                break;
                        }
                    });
                    window.history.pushState(null, null, window.stringUtil.getUrlWithQueries(
                        '/buyer', {...data, page: 1})
                    );
                    window.location.reload();
                }
                this.getNotification();
            })
            .always(() => {
            });
        },

        loadMore() {
            this.iconLoad = true;
            this.pageSize += 5;
            this.getNotification();
        },
    }
});
