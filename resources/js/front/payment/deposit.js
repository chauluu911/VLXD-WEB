'use strict';

import EErrorCode from "../../constants/error-code";
import EPaymentMethod from '../../constants/payment-method';
import EConfigTableName from '../../constants/config-table-name';
import payooMixin from "../../front/mixins/payoo-mixin";

let viewData = $('#view-data').val();
viewData = atob(viewData) || '{}';
viewData = JSON.parse(viewData);
const app = new Vue({
	el:'#payment',
    components: {
    },
    mixins: [ payooMixin ],
    data() {
        return {
            EConfigTableName,

            orderCode: '',
            formData: {
                orderId: viewData.old.orderId,
                subscriptionPriceId: viewData.subscriptionPriceId ? viewData.subscriptionPriceId : null,
                paymentMethod: parseInt(viewData.old.paymentMethod) || (viewData.isPayooEnabled ? EPaymentMethod.PAYMENT_GATEWAY : EPaymentMethod.BANK_TRANSFER),
                payooPaymentMethod: viewData.old.payooPaymentMethod,
                bankCode: viewData.old.bankCode,
                amount: parseInt(viewData.old.amount),
                amountStr: viewData.old.amountStr
            },
            errors: {},
            step: 1,
            payooMethodList: {},
            subscriptionCode: null,
            otherPrice: 0,
            money: {
                thousands: '.',
                precision: 0,
            },
        }
    },

    mounted() {
        setTimeout(() => {
            this.initPayoo();
        }, 500);
        if (viewData.errMsg) {
            common.alert(viewData.errMsg);
        }
    },
    watch: {
        "formData.paymentMethod"(val) {
            if (val !== EPaymentMethod.PAYMENT_GATEWAY) {
                this.formData.payooPaymentMethod = null;
                this.formData.bankCode = null;
            }
        },
        "formData.payooPaymentMethod"(val) {
            this.formData.bankCode = null;
            if (val) {
                this.formData.paymentMethod = EPaymentMethod.PAYMENT_GATEWAY;
            }
        }
    },
    methods: {
        validateFormData() {
            this.errors = {};
            if (!this.formData.amount) {
                this.$set(this.errors, 'amount', 'Vui l??ng ch???n 1 m???nh gi??');
            }
            return Object.keys(this.errors).length <= 0;
        },
        saveDepositInfo() {
            if (!this.validateFormData()) {
                return;
            }

            bootbox.confirm({
                title: 'Th??ng b??o',
                message: 'B???n ch???c ch???n mu???n th???c hi???n thanh to??n',
                buttons: {
                    confirm: {
                        label: 'X??c nh???n',
                        className: 'btn-primary',
                    },
                    cancel: {
                        label: 'Kh??ng',
                        className: 'btn-default'
                    }
                },
                callback: (result) => {
                    if (result) {
                        common.loadingScreen.show();
                        common.post({
                            data: {
                                ...this.formData
                            },
                            url: '/payment/deposit',
                        }).done(response => {
                            if (response.error == EErrorCode.NO_ERROR) {
                                this.subscriptionCode = response.subscriptionCode;
                                if (this.formData.paymentMethod == EPaymentMethod.BANK_TRANSFER) {
                                    $('#modalNotify').modal('show');
                                } else {
                                    bootbox.alert('Thanh to??n th??nh c??ng');
                                    window.location.reload();
                                }
                            }
                        })
                        .always(() => {
                            common.loadingScreen.hide();
                        });
                    }
                }
            });
        },
        redirectTo() {
            window.location.reload();
        },
        goBack() {
            history.back();
        },
        showModalPaymentInfo() {
            $('#paymentInfo').modal('show');
        },
        showModalOtherPrice() {
            $('#modal-other-price').modal('show');   
        },
        acceptNewPrice() {
            if (this.otherPrice > 100000000000) {
                bootbox.alert('S??? ti???n qu?? l???n, vui l??ng nh???p th???p h??n 100 t???');
                this.otherPrice = 0;
                this.formData.amount = viewData.depositGuideAmountList[0].price;
                this.formData.subscriptionPriceId = viewData.depositGuideAmountList[0].id;
                return;
            } else if (this.otherPrice < viewData.depositGuideAmountList[0].price) {
                bootbox.alert('Gi?? ph???i l???n h??n ' + window.stringUtil.formatMoney(
                    viewData.depositGuideAmountList[0].price, 0, '.', '.', '??'));
                this.formData.amount = viewData.depositGuideAmountList[0].price;
                this.formData.subscriptionPriceId = viewData.depositGuideAmountList[0].id;
                this.otherPrice = 0;
                return;
            }

            this.formData.amount = this.otherPrice;
            this.formData.subscriptionPriceId = null;
        }
    }
});
