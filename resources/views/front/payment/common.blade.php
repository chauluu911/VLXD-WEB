@extends('front.layout.master')
@php
    $viewData = base64_encode(
        json_encode([
            'subscriptionPriceId' => $paymentInfo['subscriptionPriceId'],
            'tableId' => $paymentInfo['tableId'],
            'type' => $paymentInfo['type'],
            'paymentPriceStr' => $paymentInfo['paymentPriceStr'],
            'subscriptionPriceName' => $paymentInfo['subscriptionPriceName'],
            'appUrl' => config('app.url'),
            'shopId' => config('app.payoo.shopId'),
            'sellerName' => config('app.payoo.businessUsername'),
            'payooMethodListApi' => config('app.payoo.getMethodListApi'),
            'old' => [
                'subscriptionId' => request('subscriptionId') != 'null' ? request('subscriptionId') : null,
                'paymentMethod' => request('paymentMethod', null),
                'payooPaymentMethod' => request('payooPaymentMethod', null),
                'bankCode' => request('bankCode', null),
                'amount' => request('amount', null),
                'paymentPrice' => $paymentInfo['paymentPrice']
            ],
            'errMsg' => \Illuminate\Support\Facades\Session::get('errMsg'),
            'continue' => request('continue', null),
        ])
    );
@endphp
@section('main-id', 'payment-view')

@section('body-user')
    <div class="container-fluid bg-white p-1">
        <div class="container">
            <span class="font-weight-bold" style="font-size: 20px">{{$paymentInfo['title']}}</span>
        </div>
    </div>
    <div class="container" id="payment">
        <div class="row my-3" v-cloak>
            <div class="col-md-32">
                @if ($paymentInfo['type'] == 'product')
                    <div>
                        <div class="bg-white p-3">
                            <p class="mb-0 font-weight-bold font-size-16px mb-2">Thông tin thanh toán</p>
                            <p class="mb-0 font-medium" style="color: #00000066">Tên dịch vụ</p>
                            <p class="mb-0 font-medium font-size-16px mb-1">{{$paymentInfo['subscriptionPriceName']}}</p>
                            <!-- <p class="mb-0 font-medium" style="color: #00000066">Chi tiết dịch vụ</p> -->
                            <p class="mb-0 font-medium font-size-16px mb-1">{{$paymentInfo['detail']}}</p>
                            <p class="mb-0 font-medium" style="color: #00000066">Giá dịch vụ</p>
                            <p class="mb-0 font-medium font-size-16px">{{$paymentInfo['priceStr']}}</p>
                            <p class="mb-0 font-medium" style="color: #00000066">Thời hạn</p>
                            <p class="mb-0 font-medium font-size-16px">{{$paymentInfo['numDay']}} ngày</p>
                            <p class="mb-0 font-medium" style="color: #00000066">Đối tượng áp dụng</p>
                            <p class="mb-0 font-medium font-size-16px">{{$paymentInfo['product']}}</p>
                        </div>
                    </div>
                @elseif ($paymentInfo['type'] == 'shop')
                    <div>
                        <div class="bg-white p-3">
                            <p class="font-weight-bold">Thông tin đơn hàng</p>
                            <p class="font-medium mb-1">
                                <span style="color: #00000066">Tên gói: </span>
                                <span>{{$paymentInfo['subscriptionPriceName']}}</span>
                            </p>
                            <p class="font-medium mb-1">
                                <span style="color: #00000066">Giá gói: </span>
                                <span>{{$paymentInfo['priceStr']}}</span>
                            </p>
                            <p class="font-medium">
                                <span style="color: #00000066">Giá thanh toán: </span>
                                <span>{{$paymentInfo['paymentPriceStr']}}</span>
                            </p>
                            <div class="row no-gutters">
                                <div class="col-md-7">
                                    <p class="font-medium mb-1">
                                        <span style="color: #00000066; line-height: 2">Mã giới thiệu: </span>
                                    </p>
                                </div>
                                <div class="col-md-41">
                                    <input type="text" class="form-control" v-model="formData.refferalCode" style="width: 125px" placeholder="Mã giới thiệu">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="bg-white p-3">
                    <div class="font-weight-bold mb-2 font-size-16px">
                        Hình thức thanh toán
                    </div>
                    <div class="row">
                        <div class="col-md-48">
                            <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input"
                                       :value="{{ \App\Enums\EPaymentMethod::COIN }}"
                                       v-model="formData.paymentMethod"
                                       ref="paymentMethodCoinRadioEl">
                                <label class="custom-control-label"
                                       @click="$refs.paymentMethodCoinRadioEl.click()">
                                    Xu hiện tại
                                </label>
                            </div>
                        </div>
                        <div class="col-md-48">
                            <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input"
                                       :value="{{ \App\Enums\EPaymentMethod::BANK_TRANSFER }}"
                                       v-model="formData.paymentMethod"
                                       ref="paymentMethodTransferRadioEl">
                                <label class="custom-control-label font-size-16px"
                                       @click="$refs.paymentMethodTransferRadioEl.click()">
                                    Chuyển khoản
                                </label>
                            </div>
                        </div>
                        <!-- @if (config('app.payoo.enable'))
                            @foreach (\App\Enums\Payoo\EPayooPaymentMethod::getAll() as $method)
                                @php $methodKebabName = strtolower(\App\Enums\Payoo\EPayooPaymentMethod::valueToName($method, \App\Enums\EValueToNameType::KEBAB_CASE)) @endphp
                                <div class="col-md-48">
                                    <div v-if="payooMethodList['{{ $methodKebabName }}']">
                                        <div class="custom-control custom-radio mr-3">
                                            <input type="radio" class="custom-control-input"
                                                   value="{{ $methodKebabName }}"
                                                   v-model="formData.payooPaymentMethod"
                                                   ref="paymentMethodGateway{{ $loop->index }}RadioEl">
                                            <label class="custom-control-label font-size-16px"
                                                   @click="$refs.paymentMethodGateway{{ $loop->index }}RadioEl.click()">
                                                @lang("front/payment.method.payoo.$methodKebabName")
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif -->
                    </div>
                    <!-- <div class="row">
                        <div class="col-md-48">
                            @if (config('app.payoo.enable'))
                                @foreach (\App\Enums\Payoo\EPayooPaymentMethod::getAll() as $method)
                                    @php $methodKebabName = strtolower(\App\Enums\Payoo\EPayooPaymentMethod::valueToName($method, \App\Enums\EValueToNameType::KEBAB_CASE)) @endphp
                                    <div v-show="formData.payooPaymentMethod === '{{ $methodKebabName }}'">
                                        @if ($method === \App\Enums\Payoo\EPayooPaymentMethod::E_WALLET)
                                            <img src="/images/bank-logo/payoo.png" class="d-none d-md-block mb-3" style="height: 100px; margin-left: 10px;">
                                            <img src="/images/bank-logo/payoo-sm.png" class="d-md-none mb-3" style="height: 180px; margin-left: 10px;">
                                        @endif
                                        <div v-if="payooMethodList['{{ $methodKebabName }}'] && payooMethodList['{{ $methodKebabName }}'].length" class="payoo-icons-list">
                                            <a v-for="icon in payooMethodList['{{ $methodKebabName }}']" @click="formData.payooPaymentMethod = '{{ $methodKebabName }}', formData.bankCode = icon.code"
                                               class="payoo-icon" :class="[`payoo-icon-${icon.code}`, (formData.bankCode === icon.code ? 'active' : '')]"></a>
                                        </div>
                                        <div class="text-danger">@{{ errors.bankCode }}</div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div> -->
                    <div class="row mt-2">
                        <div class="col-md-48">
                            <a href="javascript:void(0)" class="btn btn-primary" @click="savePaymentInfo">Thanh toán</a>
                            <a href="javascript:void(0)" class="btn btn-outline-primary" @click="goBack">Trở lại</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-16 p-0 payment-contact">
                <div class="bg-white p-3">
                    <p class="mb-0 font-weight-bold font-size-16px mb-2">Thông tin liên hệ</p>
                    <p class="mb-0" style="color: #00000066">Địa chỉ</p>
                    <p class="mb-0 font-size-16px mb-2">{{$contact['address']}}</p>
                    <p class="mb-0" style="color: #00000066">Hotline</p>
                    <p class="mb-0 font-size-16px mb-2">{{$contact['phone']}}</p>
                    <p class="mb-0" style="color: #00000066">Email</p>
                    <p class="mb-0 font-size-16px">{{$contact['email']}}</p>
                </div>
            </div>
        </div>
        <div class="modal fade p-0 bd-example-modal-sm" id="paymentInfo" tabindex="-1" role="dialog" aria-labelledby="paymentInfo" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header py-2 bg-primary">
                        <span class="font-medium font-size-16px w-100 text-white" style="line-height: 2">Thông tin chuyển khoản</span>
                        <button type="button" class="btn text-white" data-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body py-1">
                        <p class="font-weight-bold mb-2">Bạn vui lòng chuyển khoản theo thông tin dưới đây:</p>
                        <div class="row">
                            <div class="col-md-24 font-medium">
                                Số tiền
                            </div>
                            <div class="col-md-24 text-right text-primary">
                                {{$paymentInfo['paymentPriceStr']}}
                            </div>
                            <div class="col-md-24 font-medium">
                                Nội dung
                            </div>
                            <div class="col-md-24 text-right text-primary">
                                @{{subscriptionCode}}
                            </div>
                            <div class="col-md-24 font-medium">
                                Chủ tài khoản
                            </div>
                            <div class="col-md-24 text-right text-primary">
                                {{$bankTranferInfo->accountHolderName}}
                            </div>
                            <div class="col-md-24 font-medium">
                                Số tài khoản
                            </div>
                            <div class="col-md-24 text-right text-primary">
                                {{$bankTranferInfo->accountNumber}}
                            </div>
                            <div class="col-md-24 font-medium">
                                Ngân hàng
                            </div>
                            <div class="col-md-24 text-right text-primary">
                                {{$bankTranferInfo->bankName}}
                            </div>
                            <div class="col-md-24 font-medium">
                                Chi nhánh
                            </div>
                            <div class="col-md-24 text-right text-primary">
                                {{$bankTranferInfo->bankBranch}}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-1">
                        <button type="button" data-dismiss="modal" class="btn btn-primary" @click="redirectTo()">Đồng ý</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade p-0 bd-example-modal-sm" id="modalNotify" tabindex="-1" role="dialog" aria-labelledby="modalNotify" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body py-1">
                        <p class="font-medium m-2">Thông tin thanh toán của bạn đã được lưu. Vui lòng nhấn đồng ý để tiếp tục</p>
                    </div>
                    <div class="modal-footer p-0 border-0">
                        <button type="button" data-dismiss="modal" class="btn btn-primary w-100 mx-3 mb-2" @click="showModalPaymentInfo()">Đồng ý</button>
                    </div>
                </div>
            </div>
        </div>
        <input id="view-data" type="hidden" value="{{ $viewData }}">
    </div>
@endsection

@push('app-scripts')
    <script src="{{ mix('/js/front/payment/common.js') }}"></script>
@endpush