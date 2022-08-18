<template>
    <b-modal
        size="lg"
        v-model="isShow"
        no-close-on-esc no-close-on-backdrop
        busy:hide-header-close="processing"
        :title="$t('Đơn hàng')"
        header-class=""
        @hidden=""
    >
        <template v-slot:default>
            <b-form>
                <b-row class="mb-2">
                    <b-col cols="48">
                        <table v-if="orderInfo" class="w-100 table-striped">
                            <tbody>
                            <tr>
                                <td class="text-left w-25 p-1">
                                    Mã đơn hàng
                                </td>
                                <td class="text-left w-75 p-1">
                                    {{orderInfo ? orderInfo.code : null}}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-left w-25 p-1">
                                    Người mua
                                </td>
                                <td class="text-left w-75 p-1">
                                    <div v-if="orderInfo">
                                        {{orderInfo.buyerName}} {{orderInfo.buyerPhone ? `- ${orderInfo.buyerPhone}` : ''}}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-left w-25 p-1">
                                    Người nhận
                                </td>
                                <td class="text-left w-75 p-1">
                                    <div v-if="orderInfo">
                                        {{orderInfo.receiverName }} {{orderInfo.receiverPhone ? `- ${orderInfo.receiverPhone}` : ''}}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-left w-25 p-1">
                                    Tên cửa hàng
                                </td>
                                <td class="text-left w-75 p-1">
                                    {{ orderInfo ? orderInfo.shopName :null}}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-left w-25 p-1">
                                    Sản phẩm
                                </td>
                                <td class="text-left w-75 p-1">
                                    <div v-for="product in orderInfo.productsOfOrder" class="row">
                                        <div class="col-24">
                                            {{product.productName}}
                                        </div>
                                        <div class="col-12">
                                            Số lượng : {{product.quantity}}
                                        </div>
                                        <div class="col-12">
                                            {{product.price}}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-left w-25 p-1">
                                    Phí giao hàng
                                </td>
                                <td class="text-left w-75 p-1">
                                    {{ orderInfo.shippingFee}}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-left w-25 p-1">
                                    Tổng tiền
                                </td>
                                <td class="text-left w-75 p-1">
                                    {{ orderInfo.totalPrice}}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-left w-25 p-1">
                                    Trạng thái
                                </td>
                                <td class="text-left w-75 p-1">
                                    {{ orderInfo.statusStr}}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-left w-25 p-1">
                                    Giao hàng
                                </td>
                                <td class="text-left w-75 p-1">
                                    {{ orderInfo.deliveryStatusStr}}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-left w-25 p-1">
                                    Thanh toán
                                </td>
                                <td class="text-left w-75 p-1">
                                    {{ orderInfo.paymentStatusStr}}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-left w-25 p-1">
                                    Lý do hủy
                                </td>
                                <td class="text-left w-75 p-1">
                                    {{ orderInfo.cancelReason}}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </b-col>
                </b-row>
            </b-form>
        </template>
        <template v-slot:modal-footer="{ hide }">
            <b-button variant="outline-primary" @click="hide('forget')">{{ $t('close') }}</b-button>
        </template>
    </b-modal>
</template>

<script>

import Util from '../../lib/common'
import EErrorCode from "../../constants/error-code";

export default {
    name: "OrderDetailModal",
    props: ['infoFromParent'],
    data() {
        return {
            orderInfo: null,
            isShow: false,
        }
    },

    computed: {
        routes() {
            return {
                get: `/api/back/shop/${this.infoFromParent.shopId}/order/detail/${this.infoFromParent.orderId}`
            }
        }
    },

    created() {
        // this.getOrderInfo();
    },

    watch: {
        infoFromParent(val) {
            if(!this.infoFromParent.shopId ||
                !this.infoFromParent.orderId ||
                !this.infoFromParent.isShowOrderDetailModal) {
                return false;
            }
            this.isShow = this.infoFromParent.isShowOrderDetailModal;
            if(this.orderInfo) {
                if(this.infoFromParent.shopId == this.orderInfo.shopId &&
                    this.infoFromParent.orderId == this.orderInfo.orderId) {
                    return;
                }
            }
            this.getOrderInfo();
        },
        isShow(val) {
            this.$emit('isShowChanged', val)
        }
    },

    methods: {
        getOrderInfo() {
            Util.loadingScreen.show();
            Util.get({
                url: this.routes.get,
            }).done(response => {
                if (response.error !== EErrorCode.NO_ERROR) {
                    this.Util.showMsg2(response);
                    return false;
                }
                this.orderInfo = response.order;
            }).always(() => {
                Util.loadingScreen.hide();
            });
        },
    }
}
</script>

<style scoped>

</style>
