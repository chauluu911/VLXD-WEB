'use strict';

import EErrorCode from "../../constants/error-code";

let getNotificationTimeout = null;
const app = new Vue({
	el:'#cart-total',
    components: {

    },
    data() {
        return {
            total: 0,
        }
    },
    mounted() {
        this.getCart();
    },
    methods: {
        getCart() {
            common.get({
                url: '/cart/get',
            }).done(response => {
                if (response.error == EErrorCode.ERROR) {
                    return;
                }
                this.total = 0;
                response.cart.forEach((item) => {
                    item.orderDetail.forEach((detail) => {
                        this.total += detail.price * detail.quantity;
                    });
                });
                if (this.total > 0) {
                    this.total = window.stringUtil.formatMoney(this.total, 0, '.', '.', '') + 'đ';
                }
                setTimeout(() => {
                    this.getCart();
                }, 30000);
                if (response.quantity > 100) {
                    response.quantity = '99+';
                }
                $('#cart-quantity').text(response.quantity);
                $('#mobile-cart-quantity').text(response.quantity);
                $('#cart-total').text(this.total);
            }).always(() => {
            });
        },
    }
});
