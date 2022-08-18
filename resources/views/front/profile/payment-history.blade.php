@extends('front.layout.master')

@prepend('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/payment-history.css') }}" type="text/css"/>
@endprepend

@section('title')
    Lịch sử giao dịch
@endsection

@section('main-id', 'payment-history')

@section('body-user')

    <div class="container" v-cloak>
        <div class="row my-4">
            <profile class="col-md-16" :profile-info="{
                name: '{{auth()->user()->name}}',
                code: '{{auth()->user()->affiliateCode ? auth()->user()->affiliateCode->code : null}}',
                currentTab: 'paymentHistory',
                linkToOrder: '{{ route('order.list', [], false) }}',
                linkToSavedProduct: '{{ route('saved-product-list', [], false) }}',
                linkToPaymentHistory: '{{ route('payment.history', [], false) }}',
                linkToChangePassword: '{{ route('profile.change-password', [], false) }}',
                linkToLogout: '{{ route('logout', [], false) }}',
                linkToDeposit: '{{ route('payment.deposit.view', [], false) }}',
                linkToProfile: '{{ route('profile', [], false) }}',
                avatarPath: '{{ App\Helpers\FileUtility::getFileResourcePath(auth()->user()->avatar_path, App\Constant\DefaultConfig::FALLBACK_USER_AVATAR_PATH)}}'
                }"
            >
            </profile>
            <div class="payment-info col-md-32" class="orders-info col-md-32" style="padding: 0px 12px">
                <div class="border-bottom bg-white p-3"
                    style="font-size: 16px; line-height: 24px;"
                >
                    <div>LỊCH SỬ GIAO DỊCH</div>
                </div>
                <div class="payment-history-content bg-white">
                    <div v-for="paymentsPerDay in paymentHistoryList" v-cloak>
                        <div class="payment-per-day py-2">
                            <div class="d-flex justify-content-center"
                                    style="color: #0E98E8"
                            >
                                @{{ paymentsPerDay[0].dateCreated }}
                            </div>
                            <div v-for="(payment,index) in paymentsPerDay">
                                <div class="payment-item"
                                     :class="{'border-0' : index == paymentsPerDay.length - 1 }"
                                >
                                    <div class="d-flex flex-column">
                                        <div>
                                            @{{ payment.name }}
                                        </div>
                                        <div class="d-flex" style="color : #9E9E9E">
                                            <div>
                                                <i class="fa fa-clock pr-2"
                                                   style="font-size: 15px; line-height: 24px;"
                                                   aria-hidden="true">
                                                </i>
                                            </div>
                                            <div style="font-size: 12px; line-height: 24px;">
                                                @{{ payment.timeCreated }}
                                                - @{{ payment.dateCreated }}
                                            </div>
                                        </div>

                                    </div>
                                    <div style="font: normal normal bold 14px/17px Roboto;
                                    padding-top: 0.2rem">
                                        @{{ payment.price }}
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div v-if="dataPaginate.total > pageSize" class="row no-gutters bg-transparent mx-1 my-3">
                    <div class="col-48 d-flex justify-content-center py-2">
                        <pagination class="mb-0"
                                    :data="dataPaginate"
                                    @pagination-change-page="getPaymentHistoryList" >
                        </pagination>
                    </div>
                </div>
                <div v-if="dataPaginate.total == 0" v-cloak>
                    <div class="col-48 d-flex justify-content-center mt-3" >
                        Bạn chưa có giao dịch nào
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('app-scripts')
    <script src="{{ mix('/js/front/profile/payment-history.js') }}"></script>
@endpush
