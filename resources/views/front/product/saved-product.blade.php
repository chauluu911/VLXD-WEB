@extends('front.layout.master')

@push('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/product.css') }}" type="text/css"/>
    <link rel="stylesheet" href="{{ mix('/css/front/home.css') }}" type="text/css"/>
@endpush

@section('title')
    Sản phẩm đã lưu
@endsection

@section('main-id', 'saved-product')

@section('body-user')
   <div class="container div-interest" id="product-list" v-cloak>
       <div class="row my-4" v-cloak>
           <profile class="col-md-16 mb-3" :profile-info="{
               name: '{{auth()->user()->name}}',
               code: '{{auth()->user()->affiliateCode ? auth()->user()->affiliateCode->code : null}}',
               currentTab: 'savedProduct',
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
           <div class="saved-product-info col-md-32" class="orders-info col-md-32" style="padding: 0px 12px">
               <div class="bg-white p-3"
                    style="font-size: 16px;line-height: 24px; padding-bottom: 2.25rem !important"
               >
                   <div>SẢN PHẨM ĐÃ LƯU</div>
               </div>
               <div class="row no-gutters card-deck border-0"
               style="margin: 6px -6px;">
                   <product-item
                       v-for="item in productList"
                       :class-item="'saved-product-item'"
                       :item-data="item"
                       :display-btn-like="true"
                   >
                   </product-item>
               </div>
               <div v-if="dataPaginate.total == 0" v-cloak>
                   <div class="d-flex justify-content-center">Bạn chưa lưu sản phẩm nào</div>
               </div>
               <div v-if="dataPaginate.to < dataPaginate.total " v-cloak
                    style="display: flex; justify-content: center;"
               >
                   <button class="btn btn-outline-primary mb-5 btn-loadmore"
                           @click="getProductList"
                   >
                       Xem thêm
                   </button>
               </div>
           </div>

       </div>

   </div>


@endsection

@push('app-scripts')
    <script src="{{ mix('/js/front/product/saved-product.js') }}"></script>
@endpush
