@extends('front.layout.master')
@php
    $viewData = base64_encode(
        json_encode([
            'depositGuideAmountList' => $depositGuideAmountList,
            'appUrl' => config('app.url'),
            'shopId' => config('app.payoo.shopId'),
            'sellerName' => config('app.payoo.businessUsername'),
            'payooMethodListApi' => config('app.payoo.getMethodListApi'),
            'old' => [
                'subscriptionId' => request('subscriptionId') != 'null' ? request('subscriptionId') : null,
                'paymentMethod' => request('paymentMethod', null),
                'payooPaymentMethod' => request('payooPaymentMethod', null),
                'bankCode' => request('bankCode', null),
                'amount' => !empty(request('amount', null)) ? request('amount') : $depositGuideAmountList[0]->price,
                'amountStr' => !empty(request('amount', null)) ? number_format(request('amount'), 0, '.', '.') . 'đ' : number_format($depositGuideAmountList[0]->price, 0, '.', '.') . 'đ'
            ],
            'errMsg' => \Illuminate\Support\Facades\Session::get('errMsg'),
            'continue' => request('continue', null),
        ])
    );
@endphp
@section('main-id', 'payment-view')

@section('title')
    Nạp xu mua sắm
@endsection

@section('body-user')
	<div class="container-fluid bg-white p-1">
		<div class="container">
			<span class="font-weight-bold" style="font-size: 20px">NẠP XU</span>
		</div>
	</div>
	<div class="container" id="payment">
		<div class="row my-3" v-cloak>
			<div class="col-md-32">
				<div class="bg-white p-3">
                    <div class="mb-2">
                        <span class="font-weight-bold font-size-16px">Mệnh giá</span>
                        <span class="font-size-16px" style="color: #00000066">1xu = 1.000đ</span>
                    </div>
                    <div class="row p-3">
                        @foreach ($depositGuideAmountList as $key)
                            <button type="button" class="btn border-square mr-2 mb-2"
                                style="background: #0000001A; max-width: 150px"
                                :class="[formData.amount == {{ $key->price }} ? 'btn-outline-primary text-primary bg-white' : '']"
                                @click="formData.amount = {{ $key->price }}, formData.subscriptionPriceId = {{ $key->id }}">
                                <span class="font-size-16px">{{ number_format($key->price, 0, '.', '.') }} đ</span>
                            </button>
                        @endforeach
                         <button type="button" class="btn border-square mr-2 mb-2"
                            style="background: #0000001A; max-width: 150px"
                            :class="[formData.amount == otherPrice ? 'btn-outline-primary text-primary bg-white' : '']"
                            @click="showModalOtherPrice">
                            <span v-if="otherPrice > 0" class="font-size-16px">@{{otherPrice | money(0, '', '.', 'đ')}}</span>
                            <span v-else class="font-size-16px">Khác</span>
                        </button>
                    </div>
                    <div class="font-weight-bold mb-2 font-size-16px">
                        Hình thức thanh toán
                    </div>
                    <div class="row">
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
                        <!-- <div class="col-md-48">
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
                        </div> -->
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
                            <a href="javascript:void(0)" class="btn btn-primary" @click="saveDepositInfo">Thanh toán</a>
                            <a href="javascript:void(0)" class="btn btn-outline-primary" @click="goBack">Trở lại</a>
                        </div>
                    </div>
                </div>
			</div>
            <div class="col-md-16 p-0 my-auto payment-contact">
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
        <div class="modal fade p-0 bd-example-modal-sm" id="paymentInfo" tabindex="-1" role="dialog" aria-labelledby="paymentInfo" aria-hidden="true" data-backdrop="static" data-keyboard="false">
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
                                @{{formData.amountStr}}
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
        <div class="modal fade p-0 bd-example-modal-sm" id="modalNotify" tabindex="-1" role="dialog" aria-labelledby="modalNotify" aria-hidden="true">
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
        <div class="modal fade p-0 bd-example-modal-sm" id="modal-other-price" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="modal-other-price" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body mt-3 pt-0 pb-2 px-3">
                        <p class="text-center font-size-16px font-weight-bold mb-2">Nhập mệnh giá</p>
                        <money class="form-control" v-model="otherPrice" v-bind="money">
                        </money>
                    </div>
                    <div class="d-block modal-footer p-0 border-0">
                        <div class="row mb-3 mx-0">
                            <div class="col-md-24">
                                <button type="button" data-dismiss="modal" class="btn btn-primary w-100" @click="otherPrice = 0">Hủy</button>
                            </div>
                            <div class="col-md-24">
                                <button type="button" data-dismiss="modal" class="btn btn-primary w-100" @click="acceptNewPrice">Đồng ý</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<input id="view-data" type="hidden" value="{{ $viewData }}">
	</div>
@endsection

@push('app-scripts')
	<script src="{{ mix('/js/front/payment/deposit.js') }}"></script>
@endpush
