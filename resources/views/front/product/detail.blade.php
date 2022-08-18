@extends('front.layout.master')

@push('stylesheet')
<link rel="stylesheet" href="{{ mix('/css/front/product.css') }}" type="text/css" />
@endpush

@section('title')
{{$shareData['title']}}
@endsection

@section('og:title', $shareData['title'])
@section('og:image', $shareData['image'])
@section('og:description', $shareData['description'])
@section('og:url', $shareData['url'])
@section('og:image:width', "600px")
@section('og:image:height', "600px")

@section('main-id', 'product-detail')

@section('body-user')
<div class="container mt-3" id="product-detail" v-cloak>
    <!-- <div class="w-100 position-relative bg-transparent d-flex justify-content-center">
            <div
                style=" background: #00000099;color: white;z-index: 1;top: 10px"
                id="toast-noti"
                class="alert alert-secondary position-absolute border-0 "
                :style="{display: displaySavedLinkNoti}"
                role="alert">
                Đã sao chép liên kết
            </div>
        </div> -->

    @if(request()->route()->getName() != 'shop.product.detail')
    <div class="my-breadcrumb mb-3 px-2 mt-1">
        <div class="my-breadcrumb-item mr-2">
            <a href="{{ route('home', [], false) }}">
                <i class="fa fa-home my-1" aria-hidden="true"></i>
            </a>
        </div>
        <template v-for="item in data.breadcrumb">
            <div class="my-breadcrumb-item mr-2"><i class="fa fa-angle-right my-1" aria-hidden="true"></i></div>
            <div class="my-breadcrumb-item mr-2">
                <a href="javascript:void(0)" class="font-medium" style="color: #000000DD;text-decoration: none;" @click="redirectToProductList(item.id, item.parent_category_id)">
                    @{{item.name}}
                </a>
            </div>
        </template>
        <div class="my-breadcrumb-item mr-2"><i class="fa fa-angle-right my-1" aria-hidden="true"></i></div>
        <div class="my-breadcrumb-item mr-2">
            <a href="" class="font-medium" style="color: #000000DD;text-decoration: none;">
                @{{data.name}}
            </a>
        </div>
    </div>
    @endif
    <div class="row no-gutters mt-2 position-relative">
        <div class="col-md-32 mb-2 px-1">
            <div class="bg-white h-100">
                <div class="row no-gutters p-2">
                    <img v-if="data.subscription" src="/images/icon/push-post.svg" width="30px" class="position-absolute mr-4" style="right: 0; top: 0; z-index: 1">
                    <div class="col-md-48 d-flex justify-content-center" style="height: 0px; padding-bottom: 56.25%" :class="{'paddingTiktokVideo': !disableVideo && data.video[0].typeVideo === 3 }">
                        <div v-if="disableVideo" id="carouselExampleIndicators" class="carousel slide position-relative carousel-image" data-ride="carousel" data-interval="false" style="height: 100%; width: 100%; padding-bottom: 56.25%">
                            <div v-if="data.video && data.video.length > 0" class="position-absolute w-100" style="bottom: 5px; z-index: 1">
                                <div class="row option-image-video">
                                    <div class="col-48 d-flex justify-content-center">
                                        <button class="btn btn-primary rounded-pill border-0 mx-1" :class="{'bg-black': disableVideo}" @click="disableVideo = false">
                                            <img class="mr-2 mb-1" src="/images/icon/play-arrow-white.svg" width="12px"><span>Video</span>
                                        </button>
                                        <button class="btn btn-primary rounded-pill border-0 mx-1 text-white" :class="{'bg-black': !disableVideo}" @click="disableVideo = true">
                                            <img class="mr-2 mb-1" src="/images/icon/insert-photo-white.svg" width="12px">
                                            <span>@{{ imageList.index + 1 }}/@{{imageList.value.length}}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="carousel-inner">
                                <div class="carousel-item" v-for="(item, index) in data.image" :class="{'active': index == 0}">
                                    <div class="cursor-pointer" style="background-repeat: no-repeat; background-size: contain; background-position: center; padding-bottom: 56.25%" :style="{'background-image': `url(${item.path})`}" @click="showModalImage">

                                    </div>
                                </div>
                            </div>
                            <template v-if="data.image && data.image.length > 1">
                                <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                                    <div class="image-indicator-btn">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    </div>
                                    <span class="sr-only">Previous</span>
                                </a>
                                <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                                    <div class="image-indicator-btn">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    </div>
                                    <span class="sr-only">Next</span>
                                </a>
                            </template>
                        </div>
                        <div v-else :class="{'paddingTiktokVideo': data.video[0].typeVideo === 3 }" style="height: 0px; width: 100%; padding-bottom: 56.25%">
                            <div class="position-relative video-product w-100" :class="{'paddingTiktokVideo': data.video[0].typeVideo === 3 }" style="height: 0px; padding-bottom: 56.25%">
                                <div class="position-absolute w-100" style="bottom: 5px; z-index: 1">
                                    <div class="row option-image-video">
                                        <div class="col-48 d-flex justify-content-center">
                                            <button class="btn btn-primary rounded-pill border-0 mx-1" :class="{'bg-black': disableVideo}" @click="disableVideo = false">
                                                <img class="mr-2 mb-1" src="/images/icon/play-arrow-white.svg" width="12px"><span>1/1</span>
                                            </button>
                                            <button class="btn btn-primary rounded-pill border-0 mx-1" :class="{'bg-black': !disableVideo}" @click="disableVideo = true, imageList.index = 0">
                                                <img class="mr-2 mb-1" src="/images/icon/insert-photo-white.svg" width="12px"><span>Hình ảnh</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <iframe class="border-0" v-for="item in data.video" style="position: absolute;" v-if="data.video.length > 0 && item.typeVideo != 1" width="100%" {{--                                            :height="item.typeVideo == 2 ? '350px' : '500px'"--}} height="100%" :src="item.path_to_resource" allowfullscreen="true">
                                </iframe>
                                <video controls="" style="position: absolute;" v-else-if="item.typeVideo == 1" :src="item.path" height="100%" width="100%">
                                </video>
                                <img v-else width="300px" height="355px" src="/images/icon/ondemand-video.svg">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row no-gutters">
                    <div class="col-md-48">
                        <div class="media px-3">
                            <div class="media-body">
                                <div class="pb-3">
                                    <p class="mb-0 mt-3" style="font-size: 24px">@{{data.name}}</p>
                                    <p v-if="data.type === EProductType.PRODUCT_FOR_SALE" class="mb-0 font-weight-bold" {{--                                           style="background: #00000008"--}}>
                                        <span style="font-size: 36px">@{{data.priceStr}}</span>
                                        <span style="font-size: 20px"> đ/@{{data.unit}}</span>
                                    </p>
                                    <p v-else class="mb-0 font-weight-bold" {{--                                           style="background: #00000008"--}}>
                                        <span class="px-3" style="font-size: 36px">Liên hệ</span>
                                    </p>
                                    <p class="mb-0 mt-2 font-size-16px cursor-pointer" v-if="data.area" @click="showMap"><img class="mb-1 mr-2" width="16px" src="/images/icon/location.svg">@{{data.area}}</p>
                                    <template v-if="!isProductOfShop">
                                        <div class="row">
                                            <div class="col-48">
                                                <button v-if="data.isAddToCart && data.type == EProductType.PRODUCT_FOR_SALE" class="btn btn-primary mt-2 mr-2 py-1" @click="addToCart">
                                                    <img src="/images/icon/add-shopping-cart-white.svg" class="mr-1">
                                                    <span>Thêm vào giỏ hàng</span>
                                                </button>
                                                <button class="btn btn-outline-primary mt-2 mr-2 py-1" @click="redirectToMessage">
                                                    <div class="float-left pt-1 mr-2" style="height: 20px">
                                                        <i class="material-icons-outlined icon-image-preview font-size-16px">chat</i>
                                                    </div>
                                                    <span>Nhắn tin</span>
                                                </button>
                                                <button class="btn btn-outline-primary mt-2 py-1" :class="isSaved ? 'btn-primary ': 'btn-outline-primary'" @click="interestProduct">
                                                    <i class="far fa-heart mr-1"></i>
                                                    <span>Lưu</span>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-48 mt-5 px-3" v-if="isProductOfShop">
                        <a href="javascript:void(0)" class="font-weight-bold btn btn-outline-secondary1 float-right mt-5" @click="deleteProduct">Xóa</a>
                        <a class="font-weight-bold btn btn-outline-secondary1 float-right mt-5 mr-2" @click="redirectToEdit">
                            <img class="mb-1 mr-1" src="/images/icon/edit.svg" width="16px">Chỉnh sửa
                        </a>
                    </div>
                </div>
                <div class="row no-gutters my-3">
                    <div class="col-md-48 p-3">
                        <p class="font-weight-bold font-size-16px p-2" style="background-color: #00000008">Chi tiết sản phẩm</p>
                        <div class="row mb-2 no-gutters">
                            <div class="col-10">
                                <span class="font-weight-bold" style="color: #00000066">Ngày tạo</span>
                            </div>
                            <div class="col-38">
                                <span class="mx-2">@{{data.createdAt}}</span>
                            </div>
                        </div>
                        <div class="row mb-2 no-gutters" v-for="item in data.attribute">
                            <div class="col-10">
                                <span class="font-weight-bold" style="color: #00000066">@{{item.attributeName}}</span>
                            </div>
                            <div class="col-38">
                                <span class="mx-2" v-for="(val, index) in item.value">
                                    @{{val}}<span v-if="index != item.value.length - 1">, </span>
                                </span>
                            </div>
                        </div>
                        <p class="font-weight-bold font-size-16px p-2 mt-3" style="background-color: #00000008">Mô tả</p>
                        <p style="white-space: break-spaces; word-break: break-all" v-html="data.description"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-16 px-1 mb-2">
            @if(request()->route()->getName() == 'product.detail')
            <div class="row no-gutters text-white d-flex justify-content-center">
                <div class="col-md-48 text-center p-3" style="background-size: cover" :style="`background-image: url('/images/store/level-${shop.level}.jpg')`">
                    <div class="m-auto rounded-pill" v-if="shop.avatarType == EResourceType.IMAGE" style="width: 72px; height: 72px; background-size: cover" :style="{'background-image': `url(${shop.avatar})`}"></div>
                    <span v-else-if="shop.avatarType == EResourceType.VIDEO" class="rounded-circle" style="width: 72px; height: 72px; display: inline-block;">
                        <span class="shop-avatar">
                            <video autoplay muted loop :src="shop.avatar" style="width: 130px"></video>
                        </span>
                    </span>
                    <div v-else class="mr-3 rounded-pill" style="width: 72px; height: 72px; background-size: cover;" :style="{'background-image': `url('/images/img_no_image.svg')`}"></div>
                    <p class="font-size-16px mb-0">@{{shop.name}}</p>
                    <div>
                        <span style="color: #FFFFFF99">
                            Lượt theo dõi:
                        </span>
                        <span>
                            @{{shop.follow}}
                        </span>
                    </div>
                    <div class="mt-1">
                        <a :href="shop.url" class="btn btn-primary text-white text-decoration-none px-0" style="width: 85px; height: 24px; padding-top: 2px; font-size: 12px">
                            Đến cửa hàng
                        </a>
                    </div>
                </div>
                <div class="col-md-48 bg-white border-bottom p-2">
                    <p class="mb-1 text-center font-medium text-black">Cấp cửa hàng</p>
                    <p class="text-center mb-0" style="padding-top: 2px">
                        <img :src="`/images/star/level-${shop.level}.png`" class="pb-1" width="20px">
                        <span class="font-medium text-black">@{{shop.levelName}}</span>
                    </p>
                </div>
                <div class="col-md-48 bg-white border-bottom p-2">
                    <p class="mb-0 text-center font-medium text-black">Đánh giá</p>
                    <p class="text-center mb-0" style="padding-top: 2px">
                        <a :href="'/shop/' + shop.id + '/review'" class="d-flex justify-content-center">
                            <star-rating v-if="shop.evaluate" :show-rating="false" :increment="0.1" :star-size="16" :read-only="true" :rating="shop.evaluate.average">
                            </star-rating>
                        </a>
                    </p>
                </div>
                <div class="col-md-48 bg-white p-2">
                    <p class="mb-1 text-center font-medium text-black">Phản hồi chat</p>
                    <p class="mb-0 text-center font-medium text-black">@{{shop.responseRate}}%</p>
                </div>

                <div class="col-md-48 bg-white p-3 mt-2">
                    <p class="mb-1 font-medium text-black text-justify text-center">Chi nhánh cửa hàng</p>

                    @foreach($branchShop as $branch)
                    <div class="d-flex">
                        <p class="mb-0 mt-2 font-size-16px cursor-pointer"><img class="mb-1 mr-2" width="16px" src="/images/icon/location.svg"><span class="address-shop" style="color: red;">{{$branch->name}} - {{$branch->address}}</span></p>
                    </div>
                    @endforeach

                </div>

                <div class="col-md-48 bg-white p-3 mt-2">
                    <p class="mb-1 font-medium text-black">Chia sẻ với bạn bè:</p>
                    <div class="d-flex pt-1">
                        <div class="px-1">
                            <div class="zalo-share-button" data-href="" data-oaid="{{config('app.zalo_app_id')}}" data-layout="4" data-color="white" data-customize=false>
                                <img class="cursor-pointer" src="/images/icon/zalo.png" width="35px" style="left: 0">
                            </div>
                        </div>
                        <div class="px-1">
                            <div id="fb-root">
                                <img id="shareBtn" class="cursor-pointer" src="/images/icon/facebook.png" width="35px">
                            </div>
                        </div>
                        <div class="px-1">
                            <div>
                                <img class="cursor-pointer" @click="copyLink" src="/images/icon/copy-link.png" width="35px">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-48 bg-white p-3 mt-2 text-black">
                    <p class="font-medium">Hỏi người bán</p>
                    @foreach($questions as $question)
                    <div class="btn border mb-2" @click="redirectToMessageWithQuestion({{"'".$question."'"}})" style="color: #0E98E8">
                        {{$question}}
                    </div>
                    @endforeach
                </div>
                <div class="col-md-48 bg-white p-3 mt-2 text-black">
                    <div class="row no-gutters mb-2">
                        <div class="col-md-8 d-flex justify-content-center mb-3">
                            <img class="mt-2 mr-2" src="/images/icon/warning-sign.svg" width="35px">
                        </div>
                        <div class="col-md-40">
                            <p class="mb-0 ml-1" style="font-size: 12px">
                                Sản phẩm(tin đăng) này đã được kiểm duyệt. Nếu gặp vấn đề, vui lòng báo cáo với chúng tôi
                            </p>
                        </div>
                    </div>
                    @foreach($reportlist as $item)
                    <a class="btn border mb-2" style="color: #0E98E8" @click="showModalReport({{$item}})">
                        {{$item['name']}}
                    </a>
                    @endforeach
                </div>
            </div>
            @else
            <div class="row no-gutters">
                <div class="col-md-48 bg-white p-3">
                    <p class="font-medium font-size-16px">Thông tin bài đăng</p>
                    <p class="mb-1 font-medium">
                        <span style="color: #00000061">Hình thức: </span>
                        <span>@{{data.typeStr}}</span>
                    </p>
                    <p class="mb-1 font-medium">
                        <span style="color: #00000061">Danh mục: </span>
                        <span v-if="data.category">@{{data.category.name}}</span>
                    </p>
                    <p class="mb-1 font-medium">
                        <span style="color: #00000061">Trạng thái: </span>
                        <span v-if="data.status != EStatus.DELETED" style="color: #0E98E8">@{{data.approvalStatusStr}}</span>
                        <span v-else class="text-danger">@{{data.statusStr}}</span>
                    </p>
                </div>
            </div>
            <div class="col-md-48 bg-white p-3 mt-2">
                <p class="mb-1 font-medium text-black">Chia sẻ với bạn bè:</p>
                <div class="d-flex pt-1">
                    <div class="px-1">
                        <div class="zalo-share-button" data-href="" data-oaid="{{config('app.zalo_app_id')}}" data-layout="4" data-color="white" data-customize=false>
                            <img class="cursor-pointer" src="/images/icon/zalo.png" width="35px" style="left: 0">
                        </div>
                    </div>
                    <div class="px-1">
                        <div id="fb-root">
                            <img id="shareBtn" class="cursor-pointer" src="/images/icon/facebook.png" width="35px">
                        </div>
                    </div>
                    <div class="px-1">
                        <div>
                            <img class="cursor-pointer" @click="copyLink" src="/images/icon/copy-link.png" width="35px">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row no-gutters mt-2" v-if="isProductOfShop && data.approvalStatus == EApprovalStatus.APPROVED">
                <div class="col-md-48 bg-white p-3">
                    <p class="mb-0 font-medium font-size-16px">Gói tin ưu tiên</p>
                    <template v-if="data.subscription">
                        <div class="row no-gutters mt-3 p-2" style="border: 1px solid #EFEFEF">
                            <div class="col-8">
                                <img src="/images/icon/push-post.svg" width="24px" class="mt-1">
                            </div>
                            <div class="col-40">
                                <p class="mb-0">@{{data.subscription.name}}</p>
                                <p class="mb-0 text-black font-weight-bold">
                                    @{{data.subscription.price}}
                                </p>
                                <p class="mb-0">@{{data.subscription.description}}</p>
                            </div>
                        </div>
                        <p class="mb-0 mt-2" v-if="data.subscription.paymentStatus == EApprovalStatus.APPROVED">
                            <template v-if="data.subscription.valid_date > 0">
                                <span class="font-medium" style="color: #0e97e8"> Thời hạn: </span><span style="color: #0e97e8">Còn @{{data.subscription.valid_date}} ngày</span>
                            </template>
                            <template v-else>
                                <span class="font-medium" style="color: #0e97e8">Thời hạn: </span><span style="color: #0e97e8">Hết hạn</span>
                            </template>
                        </p>
                        <p class="mb-0 mt-2" v-else>
                            <span style="color: #0e97e8">Chờ duyệt</span>
                        </p>
                    </template>
                    <template v-else>
                        <div class="row no-gutters card-deck mt-3 px-3">
                            <template v-if="data.packagePushProductWaiting">
                                <div v-for="(item, index) in data.subscriptionPrices" class="card subscription-item" style="border: 1px solid #EFEFEF" v-if="data.packagePushProductWaiting == item.id">
                                    <div class="row no-gutters p-2">
                                        <div class="col-7">
                                            <img src="/images/icon/push-post.svg" width="24px" class="mt-1">
                                        </div>
                                        <div class="col-34 pl-3">
                                            <p class="mb-0">@{{item.name}}</p>
                                            <p class="my-1 text-black font-weight-bold">@{{item.price}}</p>
                                            <p class="mb-1">@{{item.description}}</p>
                                            <p v-if="data.packagePushProductWaiting == item.id" class="mb-0 font-medium" style="color: #0E98E8">Đang chờ duyệt</p>
                                        </div>
                                        <div class="col-7 pl-2">
                                            <div class="custom-control custom-checkbox">
                                                <input type="radio" name="subscription" class="custom-control-input" :id="'subscription' + index" :checked="index == 0 ? true : false" @click="chooseSubscription(index)" :disabled="data.packagePushProductWaiting ? true : false">
                                                <label class="custom-control-label" :for="'subscription' + index"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <div v-for="(item, index) in data.subscriptionPrices" class="card subscription-item" style="border: 1px solid #EFEFEF">
                                    <div class="row no-gutters p-2">
                                        <div class="col-7">
                                            <img src="/images/icon/push-post.svg" width="24px" class="mt-1">
                                        </div>
                                        <div class="col-34 pl-3">
                                            <p class="mb-0">@{{item.name}}</p>
                                            <p class="my-1 text-black font-weight-bold">@{{item.price}}</p>
                                            <p class="mb-1">@{{item.description}}</p>
                                            <p v-if="data.packagePushProductWaiting == item.id" class="mb-0 font-medium" style="color: #0E98E8">Đang chờ duyệt</p>
                                        </div>
                                        <div class="col-7 pl-2">
                                            <div class="custom-control custom-checkbox">
                                                <input type="radio" name="subscription" class="custom-control-input" :id="'subscription' + index" :checked="index == 0 ? true : false" @click="chooseSubscription(index)" :disabled="data.packagePushProductWaiting ? true : false">
                                                <label class="custom-control-label" :for="'subscription' + index"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <div v-if="!data.subscription && !data.packagePushProductWaiting" class="row mb-2">
                        <div class="col-48 text-right">
                            <a v-if="permission.num_push_product_in_month_remain > 0" href="javascript:void(0)" class="btn btn-primary" @click="showModalNotify">Thanh toán</a>
                            <a v-else href="javascript:void(0)" class="btn btn-primary" @click="savePayment">Thanh toán</a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    <template v-if="!isProductOfShop && similarProduct && similarProduct.length > 0">
        <div class="row">
            <div class="col-48">
                <p class="font-weight-bold font-size-16px py-2 px-4 bg-white mx-1 mb-1 mt-3">Sản phẩm liên quan</p>
            </div>
        </div>
        <div id="product-list" class="row no-gutters slick-slide-product mb-5">
            <div class="col-md-48" v-if="similarProduct && similarProduct.length >= 6">
                <vue-slick-carousel class="card-deck m-auto" v-bind="settings" style="max-width: 1200px">
                    <div class="cursor-pointer" v-for="(item, index) in similarProduct">
                        <product-item class-item="height > 22 ? item2 ? item" :index="index" :item-data="item" :style="height"></product-item>
                    </div>
                </vue-slick-carousel>
            </div>
            <product-item v-else v-for="item in similarProduct" :class-item="'product-in-home'" :item-data="item"></product-item>
        </div>
    </template>
    <input id="productData" hidden="" type="text" data-code="{{isset($code) ? $code : null}}" data-product-of-shop="{{isset($productOfShop) ? $productOfShop : null}}" data-shop-id="{{isset($shopId) ? $shopId : null}}" data-user-id="{{auth()->user() ? auth()->user()->id : null}}">

    <div class="modal fade p-0" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header py-2 bg-primary d-block">
                    <span class="font-medium font-size-16px w-100 text-white" style="line-height: 2">Báo cáo</span>
                    <button type="button" class="float-right btn text-white" data-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div v-if="dataReport.value.childCategory && dataReport.value.childCategory.length > 0 && !dataReport.parentId">
                        <p class="mb-1 font-weight-bold">Danh mục con</p>
                        <button class="btn btn-outline-primary w-100 mb-3" v-for="item in dataReport.value.childCategory" @click="dataReport.parentId = dataReport.reportChoosed, dataReport.reportChoosed = item.id">
                            @{{item.name}}
                        </button>
                        <div v-if="dataReport.errors.parentId" class="invalid-feedback d-block font-weight-bold">
                            @{{dataReport.errors.parentId[0]}}
                        </div>
                    </div>
                    <div v-else>
                        <p class="mb-1 font-weight-bold">Nội dung</p>
                        <textarea v-model="dataReport.content" class="form-control" rows="4" :class="{'is-invalid': dataReport.errors.content}"></textarea>
                        <div v-if="dataReport.errors.content" class="invalid-feedback font-weight-bold">
                            @{{dataReport.errors.content[0]}}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary text-white" @click="report()">Báo cáo</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade bd-example-modal-lg p-0" id="modal-image" tabindex="-1" role="dialog" aria-labelledby="modal-image-Title" aria-hidden="true" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered image-modal-dialog" role="document">
            <div class="modal-content" style="display: flex;justify-content: center;flex-direction: row;
                     border: 0;height: 100%; align-items: center">
                <div class="row w-100" style="height: 95%;">
                    <div class="col-md-30 main-image-modal">
                        <div v-if="imageList.value.length > 0" :style="{'background-image': `url(${imageList.value[imageList.index].path})`}" style="height: 100%;
                                         background-repeat: no-repeat;
                                         background-position: center;
                                         background-size: contain;">
                        </div>
                        <button class="btn position-absolute p-0 btn-pre-img" v-if="imageList.index > 0" @click="nextOrPreImg(-1)">
                            <i class="fas fa-chevron-left" style="font-size: 14px; color: white;"></i>
                        </button>
                        <button class="btn position-absolute p-0 btn-next-img" style="z-index: 999999" v-if="imageList.index < imageList.value.length - 1" @click="nextOrPreImg(1)">
                            <i class="fas fa-chevron-right" style="font-size: 14px; color: white;"></i>
                        </button>
                    </div>
                    <div class="col-md-18" style="max-height: 100%;">
                        <div class="w-100 border-0 m-0 image-list-modal" style="margin-top: -4px !important;">
                            <div v-for="(item,index) in imageList.value" class="image-item-modal">
                                <div class="image-frame-modal" :class="{'image-frame-modal-active': index === imageList.index}" @click="imageList.index = index, $('#carouselExampleIndicators').carousel(imageList.index)" :style="{'background-image': `url(${item.path})`}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade p-0 bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="map-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div id="map" style="width: 100%;height: 500px"></div>
            </div>
        </div>
    </div>
    <div class="modal fade p-0" id="modal-notidy" tabindex="-1" role="dialog" aria-labelledby="modal-notidy" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <span class="font-medium font-size-16px">
                        Bạn còn @{{permission.num_push_product_in_month_remain}} lần đẩy tin ưu tiên miễn phí hằng tháng, bạn có muốn sử dụng không?
                    </span>
                </div>
                <div class="modal-footer d-block p-1">
                    <div class="row">
                        <div class="col-md-24">
                            <button type="button" data-dismiss="modal" class="btn btn-secondary text-white w-100" @click="savePayment">Thanh toán</button>
                        </div>
                        <div class="col-md-24">
                            <a data-dismiss="modal" href="javascript:void(0)" class="btn btn-primary text-white w-100" @click="pushProduct">Đồng ý</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('app-scripts')
<script src="{{ mix('/js/front/product/detail.js') }}"></script>
<script src="https://sp.zalo.me/plugins/sdk.js"></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAE7zocVnayiUt3EeLFosK0OaL9EYV46l8&callback=initGoogleMapSuccess&libraries=places"></script>
<script type="text/javascript">
    function initGoogleMapSuccess() {
        $(document).trigger('initGoogleMapSuccess');
        $(document).data('initGoogleMapSuccess', true);
    }
</script>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v8.0&appId={{config('app.facebook_app_id')}}&autoLogAppEvents=1" nonce="zle6cWuG"></script>
<script>
    document.getElementById('shareBtn').onclick = function() {
        FB.ui({
            display: 'popup',
            method: 'share',
            href: <?php echo ("'" . Request::url() . "'"); ?>,
        }, function(response) {});
    }
</script>
@endpush
