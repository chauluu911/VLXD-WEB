'use strict';

import * as Pagination from 'laravel-vue-pagination';
import Profile from '../components/Profile'
import DateUtil from "../../lib/date-utils"

const app = new Vue({
    el:'#payment-history',
    name: 'paymentHistory',
    components: {
        Pagination,
        Profile
    },
    data() {
        return {
            paymentHistoryList : [],
            pageSize:12,
            dataPaginate: {},
        }
    },
    created() {
       this.getPaymentHistoryList();
    },
    computed: {
        route() {
            return {
                payment: '/profile/payment/history/get',
            }
        }
    },
    methods: {
        getPaymentHistoryList(paging) {
            window.history.pushState(null, null, stringUtil.getUrlWithQueries(
                window.location.pathname, {...this.filter, page: paging ? paging : 1})
            );
            common.loadingScreen.show();
            common.get({
                url: this.route.payment,
                data: {
                    pageSize: this.pageSize,
                    page: paging ? paging : 1,
                }
            }).done(response => {
                this.paymentHistoryList = [];
                let tempPayments = response.payments.data;
                let tempList = {};
                // partion list payment by date created
                // tempList {
                // dd/mm/yyyy : [array of payment has date created is dd/mm/yyyy]
                // }
                this.dataPaginate = response.payments;
                tempPayments.forEach((payment,index) => {
                    let date = new Date(payment.createdAt);
                    let dateCreated = DateUtil.getDateString(date, '/', false);
                    let timeCreated = DateUtil.getTimeString(date,':',false);
                    tempPayments[index].dateCreated = dateCreated;
                    tempPayments[index].timeCreated = timeCreated;
                    if (Object.keys(tempList).indexOf(dateCreated) === -1 ) {
                        tempList[dateCreated] = [];
                        this.paymentHistoryList.push(tempList[dateCreated])
                    }
                    tempList[dateCreated].push(payment);
                })
            }).always(() => {
                common.loadingScreen.hide();
            });
        },

    }
});
