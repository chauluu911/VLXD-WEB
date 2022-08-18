'use strict';

import EStatus from "../../constants/status";
import StarRating from 'vue-star-rating';

import EErrorCode from "../../constants/error-code";
import * as Pagination from "laravel-vue-pagination";
import DateUtil from "../../lib/date-utils";
import EResourceType from "../../constants/resource-type";


let getNotificationTimeout = null;
const app = new Vue({
    el:'#shop-review',
    components: {
        StarRating,
        Pagination,
    },
    data() {
        return {
            shop: {},
            EStatus,
            EResourceType,
            shopId: $('#shopData').data('id'),
            dataPaginate: {},
            reviewList: [],
            starFilter: -1,
            pageSize: 10,
        }
    },
    watch : {
        starFilter: function () {
            this.getReviewList();
        }
    },
    created() {
        this.getInfoShop();
        this.getReviewList();
    },
    computed: {
        route() {
            return {
                list: '/shop/' + this.shopId + '/review/get',
                shopInfo: '/shop/' + this.shopId + '/info'
            }
        }
    },

    mounted() {

    },
    methods: {
        getInfoShop() {
            common.loadingScreen.show('body');
            common.get({
                url: this.route.shopInfo,
            }).done(response => {
                if (response.error != EErrorCode.NO_ERROR) {
                    return;
                }
                this.shop = response.shop;
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        getReviewList(paging) {
            common.loadingScreen.show('body');
            common.get({
                url: this.route.list,
                data: {
                    pageSize: this.pageSize,
                    page: paging ? paging : 1,
                    starFilter: this.starFilter != -1 ? this.starFilter : null ,
                }
            }).done(response => {
                if (response.error != EErrorCode.NO_ERROR) {
                    return;
                }
                this.reviewList = response.reviews.data;
                this.dataPaginate = response.reviews;
                this.reviewList.forEach((review,index) => {
                    let date = new Date(review.createdAt);
                    let dateCreated = DateUtil.getDateString(date, '/', false);
                    this.reviewList[index].dateCreated = dateCreated;
                })
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        changeStarFilter(value) {
            this.starFilter = value;
        },
        back() {
            history.back();
        },
    }
});
