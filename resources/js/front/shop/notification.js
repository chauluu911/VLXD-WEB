'use strict';

import EErrorCode from "../../constants/error-code";
import EStatus from "../../constants/status";
import EShopPaymentStatus from "../../constants/shop-payment-status";
import ShopToolBar from '../components/ShopToolBar.vue';
import FDateTimePicker from '../components/FDateTimePicker.vue';
import ENotificationScheduleTargetType from "../../constants/notification-target-type";

let getNotificationTimeout = null;
const app = new Vue({
	el:'#notification',
    components: {
        ShopToolBar,
        FDateTimePicker,
    },
    data() {
        return {
            shopId: $('#shopData').data('id'),
            formData: this.$_formData(),
            date: window.dateUtil.today(),
            dateString: window.dateUtil.getDateString(window.dateUtil.today()),

            ENotificationScheduleTargetType
        }
    },
    created() {
        
    },
    mounted() {
        
    },
    methods: {
        $_formData() {
            return {
                date: null,
                title: null,
                content: null,
                time: null,
                targetType: ENotificationScheduleTargetType.ALL,
                errors: {
                    date: null,
                    time: null,
                    contentVi: null,
                    titleVi: null,
                }
            }
        },

        createNotification() {
            let formData = new FormData();
            Object.keys(this.formData).forEach((key) => {
                switch (key) {
                    case 'title':
                        if (this.formData[key]) {
                            formData.append('titleVi', this.formData[key]);
                        }
                        break;
                    case 'content':
                        if (this.formData[key]) {
                            formData.append('contentVi', this.formData[key]);
                        }
                        break;
                    default:
                        if (this.formData[key]) {
                            formData.append(key, this.formData[key]);
                        }
                        break;
                }
            });

            common.loadingScreen.show('body');

            common.post({
                url: '/shop/' + this.shopId + '/notification/save',
                data: formData,
                errorModel: this.formData.errors,
            }).done((res) => {
                if (res.error == EErrorCode.NO_ERROR) {
                    bootbox.alert(res.msg, () => {
                        window.location.reload();
                    });
                } else {
                    bootbox.alert(res.msg);
                }
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        }
    }
});
