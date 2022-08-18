'use strict';

import EErrorCode from "../../constants/error-code";
import Profile from '../components/Profile';
import ShopToolBar from '../components/ShopToolBar';
import OrderItem from "../components/OrderItem";
import EApprovalStatus from "../../constants/approval-status";
import * as Pagination from 'laravel-vue-pagination';
import ECustomOrderStatusForUser from "../../constants/custom-order-status-for-user";
import DateUtil from "../../lib/date-utils"

let viewData = $('#orderListData').data('filter');
viewData = atob(viewData) || '{}';
viewData = JSON.parse(viewData);
let getNotificationTimeout = null;
const app = new Vue({
    el:'#order-list',
    name: 'orderList',
    components: {
        Pagination,
        OrderItem,
        Profile,
        ShopToolBar,
    },
    data() {
        return {
            EApprovalStatus,

            orderList: [],
            customOrderStatusForUser: parseInt(viewData.filter.orderStatus) || ECustomOrderStatusForUser.WAITING,
            pageSize:10,
            dataPaginate: {},
            isNeedToPushState: true,
            qOrder: '',
            query: viewData,
            shopId: $('#orderListData').data('shopid'),
        }
    },
    created() {
        this.getOrderList();

    },
    // mounted() {
    //     $(window).on("popstate", () => {
    //         let query = stringUtil.getUrlQueries(window.location.href);
    //         console.log(query);
    //         this.isNeedToPushState = false;
    //         if(this.query.filter.page != parseInt(query.page) || this.customOrderStatusForUser != query.orderStatus) {
    //             this.query.filter.page = parseInt(query.page);
    //             this.customOrderStatusForUser = query.orderStatus;
    //         } else {
    //             this.getOrderList();
    //         }
    //     });
    // },
    watch : {
        customOrderStatusForUser: function () {
            this.getOrderList(1);
        },
        // "query.filter.page"() {
        //     this.getOrderList();
        // }
    },
    computed: {
        route() {
            return {
                order: '/order/get',
            }
        }
    },
    methods: {
        getOrderList(paging) {
            if(this.isNeedToPushState) {
                window.history.pushState(null, null, stringUtil.getUrlWithQueries(
                    window.location.pathname, {
                        orderStatus: this.customOrderStatusForUser,
                        page: paging ? paging : this.query.filter.page || 1,
                    })
                );
            }
            common.loadingScreen.show();
            return common.get({
                    url: this.route.order,
                    data: {
                        pageSize: this.pageSize,
                        page: paging ? paging : this.query.filter.page || 1,
                        customOrderStatusForUser: this.customOrderStatusForUser,
                        q: this.qOrder,
                        shopId: this.shopId ? this.shopId : null,
                        getForOrderListOfShop : this.shopId ? true : null ,
                        getForOrderListOfUser:  this.shopId ? null : true,
                    }
                }).done(response => {
                    this.orderList = response.orders.data;
                    this.dataPaginate = response.orders;
                    this.orderList.forEach((order,index) => {
                        let date = new Date(order.createdAt);
                        let dateCreated = DateUtil.getDateString(date, '/', false);
                        let timeCreated = DateUtil.getTimeString(date,'h',false);
                        this.orderList[index].dateCreated = dateCreated;
                        this.orderList[index].timeCreated = timeCreated;
                    })
                }).always(() => {
                    common.loadingScreen.hide();
                    this.isNeedToPushState = true;
                });
        },
        searchOrder() {
          this.getOrderList();
        },
        changeStatusFilter(e) {
            let status = e.toElement.attributes.statusValue.value;
            if(status != this.customOrderStatusForUser) {
                this.$set(this,'customOrderStatusForUser',status);
            }
        }
    }
});
