'use strict';

import EErrorCode from "../../constants/error-code";

let getNotificationTimeout = null;
const app = new Vue({
	el:'#order-list',
    components: {
       
    },
    data() {
        return {
            cart: null,
            quantity: 0,
            total: 0,
            accumulation: 0,
            score: null,
            step: 1,
            receiverInfo: {
                name: null,
                phone: null,
                address: null,
                paymentMethod: 3,
            },
            errors: {
                name: null,
                phone: null,
                address: null,
            },
        }
    },
    mounted() {
        this.getCart();
    },
    methods: {
        getCart() {
            common.loadingScreen.show('body');
            common.get({
                url: '/cart/get',
            }).done(response => {
                if (response.error == EErrorCode.ERROR) {
                    return;
                }
                this.cart = response.cart;
                this.score = response.score;
                this.receiverInfo.name = response.info.name;
                this.receiverInfo.phone = response.info.phone;
                this.receiverInfo.address = response.info.address;
                this.quantity = response.quantity;
                if (this.quantity > 100) {
                    this.quantity = '99+';
                }
                this.totalCart();
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        totalCart() {
            this.total = 0;
            this.accumulation = 0;
            this.cart.forEach((item) => {
                item.orderDetail.forEach((detail) => {
                    this.total += detail.price * detail.quantity;
                });
            });
            if (this.total > 0) {
                this.accumulation = Math.ceil((this.total * this.score.point) / this.score.cost) + ' điểm';
                this.total = window.stringUtil.formatMoney(this.total, 0, '.', '.', '') + 'đ';
            }
            $('#cart-quantity').text(this.quantity);
            $('#mobile-cart-quantity').text(this.quantity);
            $('#cart-total').text(this.total);
        },
        changeQuantity(cartIndex, detailIndex, quantity) {
            if (this.cart[cartIndex].orderDetail[detailIndex].quantity == 1 && quantity == -1) {
                return;
            }
            //common.loadingScreen.show('body');
            common.get({
                url: '/product/add-to-cart',
                data: {
                    productId: this.cart[cartIndex].orderDetail[detailIndex].product_id,
                    shopId: this.cart[cartIndex].shop_id,
                    price: this.cart[cartIndex].orderDetail[detailIndex].price,
                    quantity: quantity,
                }
            }).done(response => {
                if (response.error == EErrorCode.ERROR) {
                    if (response.redirectTo) {
                        window.location.assign(response.redirectTo);
                    }
                    return;
                }
                this.getCart();
            }).always(() => {
                //common.loadingScreen.hide('body');
            });
        },
        deleteDetail(data) {
            bootbox.confirm({
                title: 'Thông báo',
                message: data.shop_id ? 'Bạn có muốn xóa những sản phẩm của shop này không?' :
                    'Bạn có muốn xóa sản phẩm này không?',
                buttons: {
                    confirm: {
                        label: 'Xác nhận',
                        className: 'btn-primary',
                    },
                    cancel: {
                        label: 'Không',
                        className: 'btn-default'
                    }
                },
                callback: (result) => {
                    if (result) {
                        common.loadingScreen.show();
                        common.post({
                            data: {
                                data: data
                            },
                            url: 'cart/delete-order-detail',
                        }).done(response => {
                            if (response.error == EErrorCode.NO_ERROR) {
                                 bootbox.alert(response.msg);
                                 this.getCart();
                            }else {
                                bootbox.alert(response.msg);
                            }
                        })
                        .always(() => {
                            common.loadingScreen.hide();
                        });
                    }
                }
            });
        },
        createOrder() {
            if (this.total == 0) {
                return false;
            }
            this.cart.forEach((item) => {
                if (item.note && item.note.length > 150) {
                    item.errorNote = 'Ghi chú không quá 150 ký tự';
                    return;
                } else {
                    item.errorNote = null;
                }
            })
            common.loadingScreen.show();
            common.post({
                data: {
                    cart: this.cart,
                    name: this.receiverInfo.name,
                    address: this.receiverInfo.address,
                    phone: this.receiverInfo.phone,
                    paymentMethod: this.receiverInfo.paymentMethod,
                },
                url: 'cart/create-order',
                errorModel: this.errors,
            }).done(response => {
                if (response.error == EErrorCode.NO_ERROR) {
                    $('#exampleModalCenter').modal('show');
                    this.cart = null;
                }else {
                    bootbox.alert(response.msg);
                }
            })
            .always(() => {
                common.loadingScreen.hide();
            });
        },
    }
});
