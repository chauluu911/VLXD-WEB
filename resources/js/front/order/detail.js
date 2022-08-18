import ECustomOrderStatusForUser from "../../constants/custom-order-status-for-user";
import DateUtil from "../../lib/date-utils";
import EErrorCode from "../../constants/error-code";
import StarRating from "vue-star-rating";


const app = new Vue ({
    el:'#order-detail',
    name: 'orderDetail',
    components:{
        StarRating,
    },
    data() {
        return {
            orderDetail: {},
            rating: null,
            shippingFeeInput: null,
            cancelReasonInput: null,
            reviewInput: null,
            code: $('#orderDetailData').data('code'),
            isOrderOfShop : $('#orderDetailData').data('order-of-shop'),
            shopId : $('#orderDetailData').data('shop-id'),
            customOrderStatusForUser: ECustomOrderStatusForUser.WAITING,
            errors: {
                'shippingFee' : [],
                'cancelReason': [],
                'review': [],
                'rating': [],
            }
        }
    },
    created() {
        this.getInfoOrder();
    },
    mounted() {
        $('#cancel-reason').on('input', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    },
    computed: {
        route() {
            return {
                getOrderDetail: `/order/${this.code}/info`,
                approveAndUpdateShippingFeeOrder:
                    `/shop/${this.shopId}/order/${this.code}/approveAndUpdateShippingFee`,
                cancelOrder: `/order/${this.code}/cancel`,
                approveDeliveryOrder:
                    `/shop/${this.shopId}/order/${this.code}/approveDelivery`,
                completeOrder:
                    `/shop/${this.shopId}/order/${this.code}/complete`,
                reviewOrder:
                    `/order/${this.code}/review`,
            }
        }
    },
    methods:{
        getInfoOrder() {
            common.loadingScreen.show();
            return common.get({
                url: this.route.getOrderDetail,
                data: {
                    isOrderOfShop: this.isOrderOfShop ? this.isOrderOfShop : null
                }
            }).done(response => {
                if(response.error != EErrorCode.NO_ERROR) {
                    window.location.assign(response.redirectTo);
                }
                this.orderDetail = response.order;
                let date = new Date(this.orderDetail.createdAt);
                let dateCreated = DateUtil.getDateString(date, '/', false);
                let timeCreated = DateUtil.getTimeString(date,'h',false);
                this.orderDetail = {... this.orderDetail, dateCreated, timeCreated };
                let confirmedAtDate = this.orderDetail.confirmedAt ?
                    new Date(this.orderDetail.confirmedAt) : null;
                this.orderDetail.confirmedAt = confirmedAtDate ?
                    DateUtil.getDateTimeString(confirmedAtDate,'/',
                        'h',false,false,', ') : null
                let deliveredAtDate = this.orderDetail.deliveredAt ?
                    new Date(this.orderDetail.deliveredAt) : null;
                this.orderDetail.deliveredAt = deliveredAtDate ?
                    DateUtil.getDateTimeString(deliveredAtDate,'/',
                        'h',false,false,', ') : null
                let completedAtDate = this.orderDetail.completedAt ?
                    new Date(this.orderDetail.completedAt) : null;
                this.orderDetail.completedAt = completedAtDate ?
                    DateUtil.getDateTimeString(completedAtDate,'/',
                        'h',false,false,', ') : null

            }).always(() => {
                common.loadingScreen.hide();
            });
        },
        showModal(e) {
            $(`#${e.toElement.value}`).modal('show');
        },
        approveAndUpdateShippingFeeOrder() {
            let shippingFee = null;
            if(this.shippingFeeInput) {
                shippingFee = parseInt(this.shippingFeeInput) ;
            }
            common.loadingScreen.show();
            common.post({
                url: this.route.approveAndUpdateShippingFeeOrder,
                data: {
                    code: this.code,
                    shippingFee : shippingFee,
                }
            }).done( async (response) => {
                if(response.error != EErrorCode.NO_ERROR) {
                    common.showMsg2(response);
                    return;
                }
                $('#approve-order-modal').modal('hide');
                await this.getInfoOrder();
                common.showMsg2(response);
            }).fail((response) => {
                this.errors.shippingFee = response.responseJSON.errors.shippingFee
            }).always(() => {
                common.loadingScreen.hide();
            });
        },

        approveDeliveryOrder() {
            common.loadingScreen.show();
            common.post({
                url: this.route.approveDeliveryOrder,
                data: {
                    code: this.code,
                }
            }).done(response => {
                if(response.error != EErrorCode.NO_ERROR) {
                    common.showMsg2(response);
                    return;
                }
                this.getInfoOrder();
            })
            .always(() => {
                common.loadingScreen.hide();
            });
        },
        completeOrder() {
            common.loadingScreen.show();
            common.post({
                url: this.route.completeOrder,
                data: {
                    code: this.code,
                }
            }).done(response => {
                if(response.error != EErrorCode.NO_ERROR) {
                    common.showMsg2(response);
                    return;
                }
                this.getInfoOrder();
            })
                .always(() => {
                    common.loadingScreen.hide();
                });
        },
        reviewOrder() {
            common.loadingScreen.show();
            common.post({
                url: this.route.reviewOrder,
                data: {
                    code: this.code,
                    review: this.reviewInput,
                    rating: this.rating,
                }
            }).done(async (response) => {
                if(response.error != EErrorCode.NO_ERROR) {
                    common.showMsg2(response);
                    return;
                }
                this.errors.review = [];
                this.errors.rating = [];
                $('#review-order-modal').modal('hide');
                await this.getInfoOrder();
                common.showMsg2(response);
            }).fail((response) => {
                this.errors.review = [];
                this.errors.rating = [];
                this.errors.review = response.responseJSON.errors.review ?
                    response.responseJSON.errors.review : []
                this.errors.rating = response.responseJSON.errors.rating ?
                    response.responseJSON.errors.rating : []
            }).always(() => {
                common.loadingScreen.hide();
            });
        },
        setRating(rating){
            this.rating= rating;
        },

        cancelOrder() {
            common.loadingScreen.show();
            common.post({
                url: this.route.cancelOrder,
                data: {
                    code: this.code,
                    cancelReason: this.cancelReasonInput,
                    cancelBy: this.isOrderOfShop ? "shop" : "user",
                }
            }).done(async (response) => {
                if(response.error != EErrorCode.NO_ERROR) {
                    common.showMsg2(response);
                    return;
                }
                $('#cancel-order-modal').modal('hide');
                await this.getInfoOrder();
                common.showMsg2(response);
            }).fail((response) => {
                this.errors.cancelReason = response.responseJSON.errors.cancelReason
            }).always(() => {
                common.loadingScreen.hide();
            });
        },

    }

})
