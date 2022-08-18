@extends('front.layout.master')

@prepend('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/order.css') }}" type="text/css"/>
@endprepend

@section('title')
    @if($isOrderOfShop)
        Đơn bán
    @else
        Đơn mua
    @endif
@endsection

@section('main-id', 'order-detail')


@section('body-user')
    <div v-cloak>
        <div style="background-color: white">
            <div class="container p-0">
                <div class="my-breadcrumb mb-3 pt-2">
                    <div class="my-breadcrumb-item mr-2">
                        @if($isOrderOfShop)
                            <a href="{{ route('orderShop.list', ['shopId'=>$shopId], false) }}">
                                <i class="fa fa-arrow-left" aria-hidden="true">
                                </i>
                            </a>
                        @else
                            <a href="{{ route('order.list', [], false) }}">
                                <i class="fa fa-arrow-left" aria-hidden="true">
                                </i>
                            </a>
                        @endif

                    </div>
                    @if($isOrderOfShop)
                        <a href="{{ route('orderShop.list', ['shopId'=>$shopId], false) }}"
                           class="my-breadcrumb-item text mr-2">
                            Quay lại cửa hàng
                        </a>
                    @else
                        <a href="{{ route('order.list', [], false) }}" class="my-breadcrumb-item text mr-2">
                            Quay lại đơn mua
                        </a>
                    @endif
                </div>
                @if($isOrderOfShop)
                    <div class="mt-2 mb-1" style="font-size:20px;color: #000000CC">
                        ĐƠN BÁN - #{{$code}}
                    </div>
                @else
                    <div class="mt-2 mb-1" style="font-size:20px;color: #000000CC">
                        ĐƠN MUA - #{{$code}}
                    </div>
                @endif
            </div>
        </div>

        <div class="container mt-2">
            <div class="row">
                <div class="col-md-32 px-0 contain-order-info">
                    <div class="order-info">
                        <div class="title">Thông tin đơn hàng</div>
                        <div class="content py-3">
                            <div class="field">
                                <span>Mã đơn hàng:</span>
                                <span>#@{{orderDetail.code}}</span>
                            </div>

                            <div class="field">
                                <span>Đặt hàng:</span>
                                <span>@{{ orderDetail.dateCreated }} -  @{{ orderDetail.timeCreated }}</span>
                            </div>

                            @if($isOrderOfShop)
                                <div class="field">
                                    <span>Khách hàng:</span>
                                    <span>@{{ orderDetail.buyerName }} - @{{ orderDetail.buyerPhone }}</span>
                                </div>
                            @else
                                <div class="field">
                                    <span>Cửa hàng:</span>
                                    <span>@{{ orderDetail.shopName }}</span>
                                </div>
                            @endif

                            <div class="field">
                                <span>Phương thức thanh toán:</span>
                                <span>@{{ orderDetail.paymentMethod }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="order-delivery-info">
                        <div class="title">
                            Thông tin giao nhận
                        </div>
                        <div class="content py-3">
                            <div class="field">
                                <div>Khách hàng</div>
                                <div>@{{ orderDetail.receiverName }}</div>
                            </div>
                            <div class="field">
                                <div>Số điện thoại</div>
                                <div>@{{ orderDetail.receiverPhone }}</div>
                            </div>
                            <div class="field">
                                <div>Địa chỉ</div>
                                <div>@{{ orderDetail.deliveryAddress }}</div>
                            </div>
                            <div class="field">
                                <div>Ghi chú</div>
                                <div style="word-break: break-all;">@{{ orderDetail.customerNote }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="order-product-list-info">
                        <div class="title">
                            <div>
                                <img alt="Takamart" src="/images/icon/store.svg" style="filter: invert(1)">
                            </div>
                           <div style="line-height: 24px; padding-left: 8px">
                               @{{ orderDetail.shopName }}
                           </div>
                        </div>
                        <div id="product-list">
                            <div class="product-of-order" v-for="product in orderDetail.productsOfOrder" >
                                <img :src="product.productImage" class="align-self-center mr-3" :alt="product.productName">
                                <div style="width:100%; display: flex;flex-direction: column;justify-content: space-between;">
                                    <div class="mt-0">@{{ product.productName }}</div>
                                    <div class="price_and_unit"style="display: flex;justify-content: space-between">
                                        <div>@{{ product.price }}</div>
                                        <div>x@{{ product.quantity }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="delivery-fee">
                            <div>Phí vận chuyển</div>
                            <div :class="{'text-color-light' : !orderDetail.shippingFee}">
                                @{{ orderDetail.shippingFee ? orderDetail.shippingFee : 'Cập nhật sau' }}
                            </div>
                        </div>
                        <div class="total_price">
                            <div>Tổng tiền</div>
                            <div>@{{ orderDetail.totalPrice }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-16">
                    <div class="order-status-info">
                        <div class="title">Trạng thái đơn hàng</div>
                        <div v-if="orderDetail.status == -1" class="content py-3">
                            <div class="cancel-order py-3">
                                <i class="fa fa-times-circle px-2"
                                   style="font-size: 20px"
                                   aria-hidden="true">
                                </i>
                                <div class="pr-2">
                                    <div>Đã hủy</div>
                                    <div class="cancel-reason" style="word-break: break-all;">Lý do: @{{ orderDetail.cancelReason }}</div>
                                </div>

                            </div>
                        </div>
                        <div v-else class="content py-3">
                            <div class="process">
                                <div class="circle-active" v-if="orderDetail.status == 0">1</div>
                                <template v-else>
                                    <i class="fa fa-check circle-active" aria-hidden="true"></i>
                                </template>
                                <div class="p-2 active">Chờ xác nhận</div>
                            </div>
                            <div class="under-waiting-confirm">
                                <div class="divider" style="color: transparent"
                                     :class="{'divider-active': orderDetail.status > 0 }"
                                >x
                                </div>

                                <div class="update-order ml-3">
                                    <div v-if="orderDetail.status == 0" class="px-2">
                                        @if($isOrderOfShop)
                                            <div>
                                                Sau khi cập nhật đơn hàng, vui lòng cập nhật chi phí vận chuyển để đến bước
                                                tiếp theo.
                                            </div>
                                            <button class="btn btn-primary"
                                                    style="color: white"
                                                    @click="showModal"
                                                    value="approve-order-modal">
                                                Xác nhận
                                            </button>
                                            <button class="btn btn-outline-primary"
                                                    value="cancel-order-modal"
                                                    @click="showModal">
                                                Từ chối
                                            </button>
                                        @else
                                            <div>
                                                Đơn hàng chờ xác nhận từ cửa hàng
                                            </div>
                                            <button class="btn btn-primary"
                                                    style="color: white"
                                                    value="cancel-order-modal"
                                                    @click="showModal">
                                                Hủy đơn
                                            </button>
                                        @endif
                                    </div>
                                    <div v-else class="px-2">
                                        <div>Đã xác nhận đơn hàng</div>
                                        <div>
                                            Chi phí vận chuyển @{{ orderDetail.shippingFee }}
                                        </div>
                                        <div style="font-size: 12px;color:#00000061;" >
                                            @{{ orderDetail.confirmedAt }}
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="process" >
                                <div v-if="orderDetail.status <= 1"
                                     :class="{'circle-active': orderDetail.status == 1}"
                                >2
                                </div>
                                <template v-else>
                                    <i class="fa fa-check circle-active" aria-hidden="true"></i>
                                </template>
                                <div class="p-2" :class="{'active': orderDetail.status > 0}">Đang giao</div>
                            </div>
                            <div class="under-delivering">
                                <div class="divider" style="color: transparent"
                                     :class="{'divider-active': orderDetail.status > 1 }"
                                >x
                                </div>

                                <div class="update-order ml-3">
                                    <div v-if="orderDetail.status == 1" class="px-2">
                                        @if($isOrderOfShop)
                                            <div>
                                                Đang chờ giao hàng
                                            </div>
                                            <button class="btn btn-primary"
                                                    style="color: white"
                                                    @click="showModal"
                                                    value="approve-delivery-order-modal"
                                            >
                                                Giao hàng
                                            </button>
                                            <button class="btn btn-outline-primary"
                                                    @click="showModal"
                                                    value="cancel-order-modal"
                                            >
                                                Từ chối
                                            </button>
                                        @else
                                            <div>
                                                Đang chờ giao hàng
                                            </div>
                                        @endif
                                    </div>
                                    <div v-else-if="orderDetail.status > 1" class="px-2">
                                        <div>Đã tiến hành vận chuyển</div>
                                        <div style="font-size: 12px;color:#00000061;" >
                                            @{{ orderDetail.deliveredAt }}
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="divider" :class="{'divider-active': orderDetail.status > 1}"></div>
                            <div class="process">
                                <div v-if="orderDetail.status <= 2"
                                     :class="{'circle-active': orderDetail.status == 2}"
                                >3
                                </div>
                                <template v-else>
                                    <i class="fa fa-check circle-active" aria-hidden="true"></i>
                                </template>
                                <div class="p-2" class="p-2" :class="{'active': orderDetail.status > 1}">
                                    Giao thành công
                                </div>
                            </div>
                            <div class="under-delivery-success">
                                <div class="divider " style="color: transparent; visibility: hidden"
                                >x
                                </div>

                                <div class="update-order ml-3">
                                    <div v-if="orderDetail.status == 2" class="px-2 pb-3">
                                        @if($isOrderOfShop)
                                            <div>
                                                Xác nhận đã giao hàng thành công
                                            </div>
                                            <button class="btn btn-primary"
                                                    style="color: white"
                                                    @click="showModal"
                                                    value="complete-order-modal"
                                            >
                                                Giao thành công
                                            </button>
                                            <button class="btn btn-outline-primary"
                                                    @click="showModal"
                                                    value="cancel-order-modal"
                                            >
                                                Từ chối
                                            </button>
                                        @else
                                            <div>
                                                Chờ nhận hàng
                                            </div>
                                        @endif
                                    </div>
                                    <div v-else-if="orderDetail.status > 2" class="px-2 pb-3">
                                        <div>Đơn hàng đã giao thành công</div>
                                        <div style="font-size: 12px;color:#00000061;" >
                                            @{{ orderDetail.completedAt }}
                                        </div>
                                        <div v-if="!orderDetail.isOrderOfShop && !orderDetail.rated">
                                            <button
                                                class="btn btn-primary"
                                                @click="showModal"
                                                value="review-order-modal"
                                            >
                                                Đánh giá
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade p-0" id="approve-order-modal"
                 role="dialog" aria-hidden="true"
            >
                <div class="modal-dialog width-30" role="document">
                    <div class="modal-content">
                        <div class="py-2">
                            <h5 class="text-center">CẬP NHẬT ĐƠN HÀNG</h5>
                        </div>
                        <div class="modal-body">
                            <form style="border: solid 1px #f5f5f5; border-radius: 5px">
                                <label class="pl-3" style="color:#000000CC">Phí vận chuyển</label>
                                <input type="number"
                                       step="1"
                                       pattern="\d+"
                                       v-model="shippingFeeInput"
                                       class="form-control border-0"
                                       id="shipping-fee" placeholder="Nhập phí vận chuyển" >
                            </form>
                            <div class="invalid-feedback px-3" style="display: block;">
                                @{{errors.shippingFee.length > 0 ? errors.shippingFee[0]:
                                ''}}
                            </div>
                        </div>
                        <div class="px-3 pt-1 pb-3" style="display: flex;justify-content: space-between">
                            <button type="button" style="width: 49%"
                                    class="btn btn-outline-primary" data-dismiss="modal">
                                Trở lại
                            </button>
                            <button type="button" style="width: 49%;color:white;"
                                    class="btn btn-primary"
                                    @click="approveAndUpdateShippingFeeOrder"
                            >
                                Cập nhật
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade p-0" id="cancel-order-modal"
                 role="dialog" aria-hidden="true"
            >
                <div class="modal-dialog width-30" role="document">
                    <div class="modal-content">
                        <div class="py-2">
                            <h5 class="text-center">HỦY ĐƠN HÀNG</h5>
                        </div>
                        <div class="modal-body">
                            <form style="border: solid 1px #f5f5f5; border-radius: 5px">
                                <label class="pl-3" style="color:#000000CC">Lí do hủy</label>
                                <textarea rows="3"
                                          class="px-3 w-100 border-0"
                                          v-model="cancelReasonInput"
                                          id="cancel-reason" placeholder="Nhập lí do hủy"
                                          >
                                </textarea>
                            </form>
                            <div class="invalid-feedback px-3" style="display: block;">
                                @{{errors.cancelReason.length > 0 ? errors.cancelReason[0]:
                                ''}}
                            </div>
                        </div>
                        <div class="p-3" style="display: flex;justify-content: space-between">
                            <button type="button" style="width: 49%"
                                    class="btn btn-outline-primary" data-dismiss="modal">
                                Trở lại
                            </button>
                            <button type="button" style="width: 49%;color:white;"
                                    class="btn btn-primary"
                                    @click="cancelOrder"
                            >
                                Hủy đơn hàng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade p-0" id="approve-delivery-order-modal"
                 role="dialog" aria-hidden="true"
            >
                <div class="modal-dialog width-30" role="document">
                    <div class="modal-content">
                        <div class="py-2">
                            <h5 class="text-center">Xác nhận đơn hàng</h5>
                        </div>
                        <div class="modal-body">
                            <div>Đơn hàng @{{ orderDetail.code }} đang được vận chuyển</div>
                        </div>
                        <div class="p-3" style="display: flex;justify-content: space-between">
                            <button type="button" style="width: 49%"
                                    class="btn btn-outline-primary" data-dismiss="modal">
                                Trở lại
                            </button>
                            <button type="button" style="width: 49%;color:white;"
                                    class="btn btn-primary"
                                    data-dismiss="modal"
                                    @click="approveDeliveryOrder"
                            >
                                Xác nhận
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade p-0" id="complete-order-modal"
                 role="dialog" aria-hidden="true"
            >
                <div class="modal-dialog width-30" role="document">
                    <div class="modal-content">
                        <div class="py-2">
                            <h5 class="text-center">Xác nhận đơn hàng</h5>
                        </div>
                        <div class="modal-body">
                            <div>Đơn hàng @{{ orderDetail.code }} đã được nhận thành công</div>
                        </div>
                        <div class="p-3" style="display: flex;justify-content: space-between">
                            <button type="button" style="width: 49%"
                                    class="btn btn-outline-primary" data-dismiss="modal">
                                Trở lại
                            </button>
                            <button type="button" style="width: 49%;color:white;"
                                    class="btn btn-primary"
                                    data-dismiss="modal"
                                    @click="completeOrder"
                            >
                                Xác nhận
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade p-0" id="review-order-modal"
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
                                <div class="invalid-feedback px-3"
                                     style="display: block;text-align: center"
                                >
                                    @{{errors.rating.length > 0 ? errors.rating[0]:
                                    ''}}
                                </div>
                            </div>
                        </div>
                        <div class="modal-body py-1 ">
                            <div class="border px-2 py-1" >
                                <form>
                                    <label for="reviewInput">
                                        Nội dung
                                    </label>
                                    <textarea id="reviewInput" rows="7" cols="2"
                                              class="w-100 border-0"
                                              v-model="reviewInput"
                                              name="reviewInput">
                                    </textarea>
                                </form>
                                <div class="invalid-feedback px-3" style="display: block;">
                                    @{{errors.review.length > 0 ? errors.review[0]:
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
        <input id="orderDetailData" hidden="" type="text"
               data-shop-id="{{isset($shopId) ? $shopId : null}}"
               data-code="{{isset($code) ? $code : null}}"
               data-order-of-shop="{{isset($isOrderOfShop) ? $isOrderOfShop : false}}">
    </div>

@endsection

@push('app-scripts')
    <script src="{{ mix('/js/front/order/detail.js') }}"></script>
@endpush
