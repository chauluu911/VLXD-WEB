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
                subscriptionId: viewData.old.subscriptionId,
                subscriptionPriceId: viewData.subscriptionPriceId,
                paymentMethod: parseInt(viewData.old.paymentMethod) || (viewData.isPayooEnabled ? EPaymentMethod.PAYMENT_GATEWAY : EPaymentMethod.COIN),
                payooPaymentMethod: viewData.old.payooPaymentMethod,
                bankCode: viewData.old.bankCode,
                amount: parseInt(viewData.old.amount),
                paymentPrice: viewData.old.paymentPrice,
                refferalCode: null,
                type: viewData.type,
                subscriptionPriceName: viewData.subscriptionPriceName,
                paymentPriceStr: viewData.paymentPriceStr,
            },
            errors: {},
            step: 1,
            payooMethodList: {},
            subscriptionCode: null,
            link: null,
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
        },
        "formData.refferalCode"(val) {
            this.formData.refferalCode = val.toUpperCase();
        }
    },
    methods: {
        validateFormData() {
            this.errors = {};
            return Object.keys(this.errors).length <= 0;
        },
        goBack() {
            history.back();
        },
        savePaymentInfo() {
            if (!this.validateFormData()) {
                return;
            }

            bootbox.confirm({
                title: 'Thông báo',
                message: this.formData.type == 'shop' ? 
                    'Xác nhận thanh toán "' + this.formData.subscriptionPriceName + 
                    '" với giá <span class="font-weight-bold">' + this.formData.paymentPriceStr + '<span>'
                    : 'Bạn chắc chắn muốn thực hiện thanh toán',
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
                                ...this.formData
                            },
                            url: `/payment/${viewData.type}/${viewData.tableId}/${viewData.subscriptionPriceId}`,
                        }).done(response => {
                            if (response.error == EErrorCode.NO_ERROR) {
                                this.subscriptionCode = response.subscriptionCode;
                                this.link = response.redirectTo;
                                if (this.formData.paymentMethod == EPaymentMethod.BANK_TRANSFER) {
                                    $('#modalNotify').modal('show');
                                } else {
                                    bootbox.alert('Thanh toán thành công', () => {
                                        window.location.assign(response.redirectTo);
                                    });
                                }
                            } else {
                                if (response.redirectTo) {
                                    window.location(response.redirectTo);
                                }
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
        redirectTo() {
            window.location.assign(this.link);
        },
        showModalPaymentInfo() {
            $('#paymentInfo').modal('show');
        }
    }
});
