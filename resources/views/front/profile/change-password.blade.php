@php
    if(auth()->user()) {
        $userData = auth()->user();
        $userData->avatar_path = !empty($userData->avatar_path) ? get_image_url([
            'path' => $userData->avatar_path,
            'op' => 'thumbnail',
            'w' => 130,
            'h' => 130,
        ]) : App\Constant\DefaultConfig::FALLBACK_USER_AVATAR_PATH;
    }
@endphp
@prepend('stylesheet')
@endprepend
@extends('front.layout.master')

@section('title')
    Đổi mật khẩu
@endsection

@section('main-id', 'personal-info')

@section('body-user')
    <div class="container" id="change-password">
        <div class="row my-4" v-cloak>
            <profile class="col-md-16 mb-3" :profile-info="{
                name: '{{auth()->user()->name}}',
                code: '{{auth()->user()->affiliateCode ? auth()->user()->affiliateCode->code : null}}',
                currentTab: 'changePassword',
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
            <div class="col-md-32 change-password" style="padding: 0px 12px">
                <div class="card mb-3 border-0">
                    <div class="border-bottom bg-white p-3"
                    style="font-size:16px; line-height: 24px"
                    >
                        <div>ĐỔI MẬT KHẨU</div>
                    </div>
                    <div class="card-body px-4">
                        <div class="row">
                            <form class="w-100">
                                <div class="form-group row mt-3 mb-0">
                                    <label for="oldPassword"
                                           class="col-12 col-form-label text-right pr-0"
                                           style="color: #000000CC;
                                           font-size:14px; line-height: 17px"
                                    >
                                        Mật khẩu cũ
                                    </label>
                                    <div class="col-30">
                                        <input type="password"
                                               class="form-control"
                                               id="old-password"
                                               v-model="value.oldPassword">
                                    </div>
                                </div>
                                <div class="row mb-1" style="min-height: 16px">
                                    <div class="col-12"></div>
                                    <div v-if="errors.oldPassword" v-cloak
                                         class="d-flex align-items-center col-30">
                                        <div class="d-flex align-items-center text-danger"
                                             style="font-size: 12px">
                                            <i class="fa fa-exclamation-triangle"
                                               aria-hidden="true">

                                            </i>
                                            <div class="pl-1" style="font-size: 12px">
                                                @{{errors.oldPassword[0]}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label for="phone" class="col-12 col-form-label text-right pr-0"
                                           style="color: #000000CC;
                                           font-size:14px; line-height: 17px "
                                    >
                                        Mật khẩu mới
                                    </label>
                                    <div class="col-30">
                                        <input type="password"
                                               class="form-control"
                                               id="new-password"
                                               v-model="value.newPassword">
                                    </div>
                                </div>
                                <div class="row mb-1" style="min-height: 16px">
                                    <div class="col-12"></div>
                                    <div v-if="errors.newPassword" v-cloak
                                         class="col-30 d-flex align-items-center">
                                        <div class="d-flex align-items-center text-danger"
                                             style="font-size: 12px">
                                            <i class="fa fa-exclamation-triangle"
                                               aria-hidden="true">

                                            </i>
                                            <div class="pl-1" style="font-size: 12px">
                                                @{{errors.newPassword[0]}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label for="confirm-password"
                                           class="col-12 col-form-label text-right pr-0"
                                           style="color: #000000CC;
                                           font-size:14px; line-height: 17px"
                                    >
                                        Nhập lại mật khẩu
                                    </label>
                                    <div class="col-30">
                                        <input type="password"
                                               class="form-control"
                                               id="confirm-password"
                                               v-model="value.confirmPassword">
                                    </div>
                                </div>
                                <div class="row mb-1" style="min-height: 16px">
                                    <div class="col-12"></div>
                                    <div v-if="errors.confirmPassword" v-cloak
                                         class="col-30 d-flex align-items-center">
                                        <div class="d-flex align-items-center text-danger"
                                             style="font-size: 12px">
                                            <i class="fa fa-exclamation-triangle"
                                               aria-hidden="true">

                                            </i>
                                            <div class="pl-1" style="font-size: 12px">
                                                @{{errors.confirmPassword[0]}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12"></div>
                                    <a
                                        class="btn btn-primary ml-3"
                                        style="color: #fff !important;"
                                        href="javascript:void(0)"
                                        @click="changePassword"
                                    >
                                        Đổi mật khẩu
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('app-scripts')
    <script src="{{ mix('/js/front/profile/change-password.js') }}"></script>
@endpush
