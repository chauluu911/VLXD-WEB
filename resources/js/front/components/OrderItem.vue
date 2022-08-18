<template>
    <div class="order-item">
        <div class="over border-bottom" @click="redirectTo()">
            <div class="left py-3 ml-3 pr-3 border-right">
                <div class="block-over">
                    <template v-if="itemData.userInfo">
                        <img
                            :src="itemData.userInfo.avatarPath"
                            style="width: 33px; height: 33px"
                            class=" rounded-pill"
                            alt="avatar"
                        >
                        <div class="ml-2">
                            <div style="line-height: 14px;">{{itemData.userInfo.name}}</div>
                            <div style="font-size: 12px ;color: #00000061">
                                {{itemData.userInfo.phone}}
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <img v-if="itemData.shopInfo.avatarType !== EAvatarType.VIDEO"
                            :src="itemData.shopInfo.avatarPath"
                            style="width: 33px; height: 33px"
                            class="rounded-pill"
                            alt="avatar"
                        >
                        <span v-else
                            class="rounded-circle"
                        >
                            <span class="shop-avatar">
                                <video autoplay muted loop
                                       :src="StringUtil.getAvatarPath(itemData.shopInfo.avatarPath)"
                                       style="width: 33px; height: 33px"></video>
                            </span>
                        </span>
                        <div class="ml-2">
                            <div style="line-height: 14px;">{{itemData.shopInfo.name}}</div>
                            <div style="font-size: 12px ;color: #00000061">
                                {{itemData.shopInfo.phone}}
                            </div>
                        </div>
                    </template>
                </div>
                <div class="my-2">
                    <span class="font-medium font-size-16px mr-2">
                        {{itemData.totalPrice}}
                    </span>
                    <span style="color: #00000061; font-size: 12px; line-height: 14px;">
                        {{itemData.totalProduct}} sản phẩm
                    </span>
                </div>
                <div style="font-size: 12px; line-height: 14px">
                    <span style="color: #00000061">
                        Phí vận chuyển:
                    </span>
                    <span>
                        {{itemData.shippingFee}}
                    </span>
                </div>
            </div>

            <div class="right my-3 pr-4">
                <div class="block-over mb-3">
                    <div class="text-break code">#{{itemData.code}}</div>
                </div>
                <div class="block-under mt-3" style="word-break: break-all">
                    <div class="text-break time-created float-right">{{itemData.timeCreated}}</div>
                    <div class="text-break date-created float-right">{{itemData.dateCreated}}</div>
                </div>
            </div>
        </div>
        <div class="under">
            <div v-if="itemData.isOrderOfShop" class="py-3 pl-3" style="color: #0E98E8">
                {{itemData.statusStr}}
                <span v-if="itemData.status === ECustomOrderStatusForUser.CANCELED">
                    bởi {{itemData.canceledBy === "shop" ? "bạn" : "khách hàng"}}
                </span>
            </div>
            <div v-else class="py-3 pl-3" style="color: #0E98E8">
                {{itemData.statusStr}}
                <span v-if="itemData.status === ECustomOrderStatusForUser.CANCELED">
                   bởi {{itemData.canceledBy === "shop" ? "cửa hàng" : "bạn"}}
                </span>
            </div>
            <div class="py-2 pr-3 d-flex flex-column justify-content-center"
                 v-if="itemData.isOrderOfShop"
            >
                <button class="btn btn-primary" v-if="itemData.status == 0"
                        @click="showModal"
                        style="color:white;"
                        value="approve-order-modal">
                    Xác nhận
                </button>
                <button class="btn btn-primary" v-else-if="itemData.status == 1"
                        @click="showModal"
                        style="color:white;"
                        value="approve-delivery-order-modal">
                    Giao hàng
                </button>
                <button class="btn btn-primary" v-else-if="itemData.status == 2"
                        @click="showModal"
                        style="color:white;"
                        value="complete-order-modal">
                    Giao thành công
                </button>
            </div>
            <div class="py-2 pr-3 d-flex flex-column justify-content-center"
                 v-else>
                <button class="btn btn-primary" v-if="itemData.status > 2 && !itemData.rated"
                        @click="showModal"
                        style="color:white;"
                        value="review-order-modal">
                    Đánh giá
                </button>
            </div>
        </div>
        <div class="modal fade" :id="'approve-order-modal'+itemData.code"
             role="dialog" aria-hidden="true"
        >
            <div class="modal-dialog width-30" role="document">
                <div class="modal-content">
                    <div class="py-2">
                        <h5 class="text-center">CẬP NHẬT ĐƠN HÀNG {{itemData.code}}</h5>
                    </div>
                    <div class="modal-body">
                        <form
                            style="border: solid 1px #f5f5f5; border-radius: 5px">
                            <label class="pl-3" style="color:#000000CC">Phí vận chuyển</label>
                            <input type="number"
                                   step="1"
                                   required
                                   pattern="\d+"
                                   v-model="shippingFeeInput"
                                   class="form-control border-0"
                                   placeholder="Nhập phí vận chuyển" >
                        </form>
                        <div class="invalid-feedback px-3" style="display: block;">
                            {{errors.shippingFee.length > 0 ? errors.shippingFee[0]:
                            ''}}
                        </div>
                    </div>
                    <div class="px-3 pt-1 pb-3" style="display: flex;justify-content: space-between">
                        <button type="button" style="width: 49%"
                                class="btn btn-outline-primary" data-dismiss="modal">
                            Trở lại
                        </button>
                        <button style="width: 49%;color:white"
                                class="btn btn-primary"
                                @click="approveAndUpdateShippingFeeOrder"
                        >
                            Cập nhật
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" :id="'approve-delivery-order-modal'+itemData.code"
             role="dialog" aria-hidden="true"
        >
            <div class="modal-dialog width-30" role="document">
                <div class="modal-content">
                    <div class="py-2">
                        <h5 class="text-center">Xác nhận đơn hàng</h5>
                    </div>
                    <div class="modal-body">
                        <div>Đơn hàng {{ itemData.code }} đang được vận chuyển</div>
                    </div>
                    <div class="p-3" style="display: flex;justify-content: space-between">
                        <button type="button" style="width: 49%"
                                class="btn btn-outline-primary" data-dismiss="modal">
                            Trở lại
                        </button>
                        <button type="button" style="width: 49%;color:white"
                                class="btn btn-primary"
                                @click="approveDeliveryOrder"
                        >
                            Xác nhận
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" :id="'complete-order-modal'+itemData.code"
             role="dialog" aria-hidden="true"
        >
            <div class="modal-dialog width-30" role="document">
                <div class="modal-content">
                    <div class="py-2">
                        <h5 class="text-center">Xác nhận đơn hàng</h5>
                    </div>
                    <div class="modal-body">
                        <div>Đơn hàng {{ itemData.code }} đã được nhận thành công</div>
                    </div>
                    <div class="p-3" style="display: flex;justify-content: space-between">
                        <button type="button" style="width: 49%"
                                class="btn btn-outline-primary" data-dismiss="modal">
                            Trở lại
                        </button>
                        <button type="button" style="width: 49%;color:white"
                                class="btn btn-primary"
                                @click="completeOrder"
                        >
                            Xác nhận
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" :id="'review-order-modal'+itemData.code"
             role="dialog" aria-hidden="true"
        >
            <div class="modal-dialog width-30" role="document">
                <div class="modal-content">
                    <div class="py-2">
                        <h5 class="text-center">Đánh giá đơn hàng</h5>
                        <div class="d-flex flex-column align-items-center">
                            <star-rating
                                :show-rating="false"
                                @rating-selected ="setRating"
                                active-color="#F76301"
                                :increment="1"
                                :star-size="16"
                                :padding = "5"
                                :read-only="false">
                            </star-rating>
                            <div v-if="errors.rating" class="invalid-feedback px-3"
                                 style="display: block;text-align: center"
                            >
                                {{errors.rating.length > 0 ? errors.rating[0]:
                                ''}}
                            </div>
                        </div>
                    </div>
                    <div class="modal-body py-1 ">
                        <div class="border px-2 py-1" >
                            <form>
                                <label>
                                    Nội dung
                                </label>
                                <textarea :id="'reviewInput'+itemData.code" rows="7" cols="2"
                                          class="w-100 border-0"
                                          v-model="reviewInput"
                                          name="reviewInput">
                                    </textarea>
                            </form>
                            <div v-if="errors.review" class="invalid-feedback px-3" style="display: block;">
                                {{errors.review.length > 0 ? errors.review[0]:
                                ''}}
                            </div>
                        </div>

                    </div>
                    <div class="p-3" style="display: flex;justify-content: space-between">
                        <button type="button" style="width: 49%"
                                class="btn btn-outline-primary" data-dismiss="modal">
                            Trở lại
                        </button>
                        <button type="button" style="width: 49%;color:white;"
                                class="btn btn-primary"
                                @click="reviewOrder"
                        >
                            Xác nhận
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>

import EErrorCode from "../../constants/error-code";
import EAvatarType from "../../constants/avatar-type";
import StarRating from "vue-star-rating";
import ECustomOrderStatusForUser from "../../constants/custom-order-status-for-user";

export default {
    props: {
        itemData: {
            default: null,
        },
    },
    components: {
        StarRating,
    },
    data() {
        return {
            shippingFeeInput: null,
            ECustomOrderStatusForUser,
            EAvatarType,
            reviewInput: null,
            rating: null,
            StringUtil: stringUtil,
            errors: {
                'shippingFee' : [],
                'review': [],
                'rating': [],
            }
        }
    },
    computed: {
        route() {
            return {
                approveAndUpdateShippingFeeOrder:
                    `/shop/${this.itemData.shopId}/order/${this.itemData.code}/approveAndUpdateShippingFee`,
                approveDeliveryOrder:
                    `/shop/${this.itemData.shopId}/order/${this.itemData.code}/approveDelivery`,
                completeOrder:
                    `/shop/${this.itemData.shopId}/order/${this.itemData.code}/complete`,
                reviewOrder:
                    `/order/${this.code}/review`,
            }
        }
    },
    methods: {
        redirectTo() {
            window.location.assign(this.itemData.redirectTo);
        },
        showModal(e) {
            $(`#${e.toElement.value}${this.itemData.code}`).modal('show');
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
                    code: this.itemData.code,
                        shippingFee : shippingFee
                }
            }).done( async (response) => {
                if(response.error != EErrorCode.NO_ERROR) {
                    common.showMsg2(response);
                    return;
                }
                $(`#approve-order-modal${this.itemData.code}`).modal('hide');
                await this.$parent.getOrderList();
                common.showMsg2(response);
            }).fail((response) => {
                this.errors = response.responseJSON.errors
            }).always(() => {
                common.loadingScreen.hide();
            });
        },

        setRating(rating){
            this.rating= rating;
        },

        approveDeliveryOrder() {
            console.log('approve delivery');
            common.loadingScreen.show();
            common.post({
                url: this.route.approveDeliveryOrder,
                data: {
                    code: this.itemData.code,
                }
            }).done(async (response) => {
                if(response.error != EErrorCode.NO_ERROR) {
                    common.showMsg2(response);
                    return;
                }
                $(`#approve-delivery-order-modal${this.itemData.code}`).modal('hide');
                await this.$parent.getOrderList();
                common.showMsg2(response);
            }).always(() => {
                common.loadingScreen.hide();
            });
        },

        completeOrder() {
            console.log('approve delivery');
            common.loadingScreen.show();
            common.post({
                url: this.route.completeOrder,
                data: {
                    code: this.itemData.code,
                }
            }).done(async (response) => {
                if(response.error != EErrorCode.NO_ERROR) {
                    common.showMsg2(response);
                    return;
                }
                $(`#complete-order-modal${this.itemData.code}`).modal('hide');
                await this.$parent.getOrderList();
                common.showMsg2(response);
            }).always(() => {
                common.loadingScreen.hide();
            });
        },

        reviewOrder() {
            console.log('review order-----', this.reviewInput);
            console.log('review order-----star', this.rating);
            common.loadingScreen.show();
            common.post({
                url: this.route.reviewOrder,
                data: {
                    code: this.itemData.code,
                    review: this.reviewInput,
                    rating: this.rating,
                }
            }).done(async (response) => {
                console.log('res--- review', response);
                if(response.error != EErrorCode.NO_ERROR) {
                    common.showMsg2(response);
                    return;
                }
                this.errors.review = [];
                this.errors.rating = [];
                $(`#review-order-modal${this.itemData.code}`).modal('hide');
                await this.$parent.getOrderList();
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
    }
}
</script>

<style scoped>

</style>
