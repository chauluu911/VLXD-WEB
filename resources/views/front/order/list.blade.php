@extends('front.layout.master')

@php
    $queries = request()->all();
    $filterData = base64_encode(
        json_encode([
            'filter' => $queries,
        ])
    );
@endphp

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

@section('main-id', 'order-list')

@section('body-user')

    <div class="container">
        <div class="order row my-4" v-cloak>
            @if($isOrderOfShop)
                <div class="col-md-16 p-0 shop-tool-bar-order"
                >
                    <shop-tool-bar
                        :shop-id="shopId"
                        current-route-name="{{request()->route()->getName()}}"
                    >
                    </shop-tool-bar>
                </div>
            @else
                <profile class="col-md-16 mb-3" :profile-info="{
                    name: '{{auth()->user()->name}}',
                    code: '{{auth()->user()->code}}',
                    currentTab: 'order',
                    linkToOrder: '{{ route('order.list', [], false) }}',
                    linkToSavedProduct: '{{ route('saved-product-list', [], false) }}',
                    linkToPaymentHistory: '{{ route('payment.history', [], false) }}',
                    linkToChangePassword: '{{ route('profile.change-password', [], false) }}',
                    linkToDeposit: '{{ route('payment.deposit.view', [], false) }}',
                    linkToLogout: '{{ route('logout', [], false) }}',
                    linkToProfile: '{{ route('profile', [], false) }}',
                    avatarPath: '{{ App\Helpers\FileUtility::getFileResourcePath(auth()->user()->avatar_path, App\Constant\DefaultConfig::FALLBACK_USER_AVATAR_PATH)}}'
                    }"
                >
                </profile>
            @endif

            <div class="orders-info col-md-32">
                <div class="bg-white">
                    @if($isOrderOfShop)
                        <div class="ml-3"
                             style="font-size:16px;font-weight: 500;line-height: 30px"
                        >
                            ĐƠN BÁN
                        </div>
                    @else
                        <div class="ml-3"
                             style="font-size:16px;font-weight: 500;line-height: 30px"
                        >
                            ĐƠN MUA
                        </div>
                    @endif
                    <div class="row no-gutters order-filter align-items-center">
                        <div class="col order-filter__status">
                            <div class="cursor-pointer mr-2" statusValue ="0"
                                 :class="{ 'active': customOrderStatusForUser == 0 }"
                                 @click="changeStatusFilter">
                                Chờ xác nhận
                            </div>
                            <div class="cursor-pointer mx-2" statusValue ="1"
                                 :class="{ 'active': customOrderStatusForUser == 1 }"
                                 @click="changeStatusFilter">
                                Chờ giao hàng
                            </div>
                            <div class="cursor-pointer mx-2" statusValue ="2"
                                 :class="{ 'active': customOrderStatusForUser == 2 }"
                                 @click="changeStatusFilter">
                                Đang giao
                            </div>
                            <div class="cursor-pointer mx-2" statusValue ="3"
                                 :class="{ 'active': customOrderStatusForUser == 3 }"
                                 @click="changeStatusFilter">
                                Giao thành công
                            </div>
                            <div class="cursor-pointer mx-2" statusValue ="-1"
                                 :class="{ 'active': customOrderStatusForUser == -1 }"
                                 @click="changeStatusFilter">
                                Đã hủy
                            </div>
                        </div>
                        <div class="col position-relative order-filter__query py-2 py-lg-0" >
                            <input type="text"
                                   style="background-color: #F2F3F7"
                                   class="form-control pr-5"
                                   id="order-search-input"
                                   @keypress.enter="searchOrder"
                                   placeholder="Nhập SĐT/Mã đơn hàng"
                                   v-model="qOrder">
                            <span class="position-absolute">
                                <i class="fas fa-search text-black-50"
                                   style="cursor: pointer"
                                   @click="searchOrder">

                                </i>
                            </span>
                        </div>
                    </div>
                </div>
                <div id="orders" class=" row no-gutters border-0 card-deck"
                     style="margin: 6px -6px;">
                    <order-item
                        v-for="item in orderList"
                        :item-data="item"
                        :key="item.code"
                    >
                    </order-item>
                </div>
                <div v-if="dataPaginate.total > pageSize" class="row no-gutters bg-transparent mx-1 my-3">
                    <div class="col-48 d-flex justify-content-center py-2">
                        <pagination class="mb-0" :data="dataPaginate" @pagination-change-page="getOrderList" ></pagination>
                    </div>
                </div>
                <div v-if="dataPaginate.total == 0" v-cloak>
                    @if($isOrderOfShop)
                        <div class="d-flex justify-content-center mt-3" >Bạn chưa có đơn bán nào</div>
                    @else
                        <div class="d-flex justify-content-center mt-3">Bạn chưa có đơn mua nào</div>
                    @endif
                </div>
            </div>
        </div>

    </div>


    <input id="orderListData" hidden="" type="text" data-shopId="{{isset($shopId) ? $shopId : null}}"
           data-filter="{{isset($filterData) ? $filterData : null}}">

@endsection

@push('app-scripts')
    <script src="{{ mix('/js/front/order/list.js') }}"></script>
@endpush
