@extends('front.layout.master')

@prepend('stylesheet')
	<link rel="stylesheet" href="{{ mix('/css/front/home.css') }}" type="text/css"/>
    <link rel="stylesheet" href="{{ mix('/css/front/product.css') }}" type="text/css"/>
@endprepend

@section('main-id', 'home')

@section('body-user')

{{--    Banners--}}
    <div class="container banner-group mb-5 bg-white p-0" style="margin-top: 20px;">
        <div id="banner-carousel"
             style="max-width: 1200px!important;"
             class="carousel slide top-banner-carousel"
             data-ride="carousel"
        >
            <ol class="carousel-indicators">
                {{--Nếu ds banner mobile có data mới in ra thẻ template--}}
                @if (!empty($bannerList['mainMobile']))
                    <template v-if="windowWidth >= 768">@endif
                        @foreach ($bannerList['mainDesktop'] as $banner)
                            <li
                                data-target="#banner-carousel"
                                data-slide-to="{{ $loop->index }}"
                                @if ($loop->first) class="active" @endif
                            ></li>
                        @endforeach
                        @if (!empty($bannerList['mainMobile']))
                    </template>
                    <template v-else>
                        @foreach ($bannerList['mainMobile'] as $banner)
                            <li
                                data-target="#banner-carousel"
                                data-slide-to="{{ $loop->index }}"
                                @if ($loop->first) class="active" @endif
                            ></li>
                        @endforeach
                    </template>
                @endif
            </ol>
            <div class="carousel-inner">
                {{--Nếu ds banner mobile có data mới in ra thẻ template--}}
                @if (!empty($bannerList['mainMobile']))
                    <template v-if="windowWidth >= 768">@endif
                        @foreach ($bannerList['mainDesktop'] as $banner)
                            <div
                                class="carousel-item  @if ($loop->first) active @endif"
                            >
                                <img
                                    data-src="{{ $banner['image'] }}"
                                    class=" w-100 lazyload"
                                    alt="Takamart"
                                >
                                @if ($banner['action'] == \App\Enums\Banner\EBannerActionType::OPEN_WEBSITE)
                                    <a
                                        href="{{ $banner['actionDetail']->url }}"
                                        target="_blank"
                                        aria-label="banner"
                                        rel="noreferrer"
                                        class="block-mask"
                                    ></a>
                                @endif
                            </div>
                        @endforeach
                        @if (!empty($bannerList['mainMobile']))
                    </template>
                    <template v-else>
                        @foreach ($bannerList['mainMobile'] as $banner)
                            <div
                                class="carousel-item  @if ($loop->first) active @endif"
                            >
                                <img
                                    data-src="{{ $banner['image'] }}"
                                    class=" w-100 lazyload"
                                    alt="Takamart"
                                >
                                @if ($banner['action'] == \App\Enums\Banner\EBannerActionType::OPEN_WEBSITE)
                                    <a
                                        href="{{ $banner['actionDetail']->url }}"
                                        target="_blank"
                                        class="block-mask"
                                        aria-label="banner"
                                        rel="noreferrer"
                                    ></a>
                                @endif
                            </div>
                        @endforeach
                    </template>
                @endif

                <a class="carousel-control-prev"
                   aria-label="prev-button"
                   href="#banner-carousel"
                   role="button"
                   data-slide="prev">
                    <button
                        aria-label="prev-button"
                        style="width: 40px; height: 40px;"
                        class="btn rounded-pill border position-absolute bg-white p-0" >
                        <i class="fas fa-chevron-left" style="font-size: 12px"></i>
                    </button>
                </a>
                <a class="carousel-control-next"
                   aria-label="next-button"
                   href="#banner-carousel"
                   role="button"
                   data-slide="next">
                    <button
                        aria-label="next-button"
                        style="width: 40px; height: 40px;"
                        class="btn rounded-pill border position-absolute bg-white p-0">
                        <i class="fas fa-chevron-right" style="font-size: 12px"></i>
                    </button>
                </a>
            </div>
        </div>
        <div class="pt-3 row m-0 home-top-nav justify-content-center">
            <!-- <div class="col-12 col-md-24 col-lg-12 mb-3">
                <a class="row no-decoration" href="{{ route('payment.deposit.view', [], false) }}">
                    <div class="col-48 col-md p-0 img-wrapper">
                        <img class="col-48 col-md p-0" alt="Nạp xu Takamart" src="/images/icon/napxu-fixed.png">
                    </div>
                    <div class="col d-flex-center-y">Nạp xu</div>
                </a>
            </div> -->
            <div class="col-12 col-md-24 col-lg-12 mb-3">
                <a class="row no-decoration" href="/profile/saved-product">
                    <div class="col-48 col-md p-0 img-wrapper">
                        <img class="col-48 col-md p-0" alt="Takamart" src="/images/icon/sanphamdaluu-fixed.png">
                    </div>
                    <div class="col d-flex-center-y">Sản phẩm đã lưu</div>
                </a>
            </div>
            <div class="col-12 col-md-24 col-lg-12 mb-3">
                <a class="row no-decoration" href="/news">
                    <div class="col-48 col-md p-0 img-wrapper">
                        <img class="col-48 col-md p-0" alt="Tin tức vật liệu xây dựng Takamart" src="/images/icon/news-fixed.png">
                    </div>
                    <div class="col d-flex-center-y">Tin tức xây dựng</div>
                </a>
            </div>
            <div class="col-12 col-md-24 col-lg-12 mb-3">
                <a class="row no-decoration" href="#">
                    <div class="col-48 col-md p-0 img-wrapper">
                        <img class="col-48 col-md p-0" alt="Takamart" src="/images/icon/vongquay-fixed.png">
                    </div>
                    <div class="col d-flex-center-y">Vòng quay may mắn</div>
                </a>
            </div>
        </div>
    </div>


{{--    Categorys--}}
    <div class="container mb-4 category-group web-category" v-cloak>
        <div class="row p-3" id="category-title">DANH MỤC SẢN PHẨM </div>
        <div
            class="card-group"
            id="category-list"
            v-show="@if (count($categoryList) > 12) true
                @elseif (count($categoryList) >= 10) windowWidth <= 1200
                @elseif (count($categoryList) >= 8) windowWidth <= 1000
                @elseif (count($categoryList) >= 6) windowWidth <= 767
                @elseif (count($categoryList) >= 4) windowWidth <= 400 @endif"
        >
            @foreach ($categoryList as $item => $category)
                <a class="no-decoration no-focus card category-item" @click="redirecToProduct({{$category['id']}})" href="javascript:void(0)">
                    <div>
                        <div class="position-relative" style="width: 100%; height: 0; padding-bottom: 100%">
                            <img class="card-img position-absolute h-100 lazyload"
                                 alt="{{$category['name']}}"
                                 data-src="{{ $category['logo_path'] }}">
                        </div>
                        <p id="category-name{{$item}}" class="card-text pt-2 card-text-category-carousel mb-0" title="{{ $category['name'] }}" v-shave="{'height': 52}" :style="height">
                            {{ $category['name'] }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
        <div
            class="card-group row"
            v-show="@if (count($categoryList) < 4) true
                @elseif (count($categoryList) < 6) windowWidth > 400
                @elseif (count($categoryList) < 8) windowWidth > 767
                @elseif (count($categoryList) < 10) windowWidth > 1000
                @elseif (count($categoryList) < 12) windowWidth > 1200 @endif"
        >
            @foreach ($categoryList as $item => $category)
                <a
                    class="no-decoration no-focus card category-item card category-item col-xl-8 col-lg-8 col-md-12 col-sm-18 col-24"
                    @click="redirecToProduct({{$category['id']}})" href="javascript:void(0)"
                >
                    <div>
                        <div>
                            <img class="card-img lazyload"
                                 alt="Takamart"
                                 data-src="{{ $category['logo_path'] }}">
                        </div>
                        <p id="category-name{{$item}}" class="card-text pt-2 card-text-category-carousel mb-0" title="{{ $category['name'] }}" v-shave="{'height': 52}" :style="height">
                            {{ $category['name'] }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
        <input id="product-category" hidden="" data-category="{{base64_encode(
        json_encode($categoryList))}}">
        @if(auth()->id())
        <input id="shop-info" hidden="" type="text"
               data-shopId="{{ auth()->user()->getShop ? auth()->user()->getShop->id : null}}"
        >
        @endif

    </div>

    <div class="container mb-4 category-group mobile-category" v-cloak>
        <div class="row p-3" id="category-title">DANH MỤC SẢN PHẨM </div>
        <div class="grid-category">
            @foreach ($categoryList as $item => $category)
                <a class="no-decoration no-focus card category-item mr-3 mb-3 border-0" href="{{ route('product-list', ['categoryId' => $category['id'], 'page' => 1]) }}" style="width: 150px">
                    <div>
                        <div class="position-relative" style="width: 100%; height: 0; padding-bottom: 100%">
                            <img class="card-img position-absolute h-100 lazyload"
                                 alt="Takamart"
                                 data-src="{{ $category['logo_path'] }}" style="border-radius: 10px">
                        </div>
                        <p id="category-name{{$item}}" class="card-text pt-2 card-text-category-carousel mb-0 text-center" title="{{ $category['name'] }}" v-shave="{'height': 52}" :style="height">
                            {{ $category['name'] }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
        <input id="product-category" hidden="" data-category="{{base64_encode(
        json_encode($categoryList))}}">
        @if(auth()->id())
        <input id="shop-info" hidden="" type="text"
               data-shopId="{{ auth()->user()->getShop ? auth()->user()->getShop->id : null}}"
        >
        @endif

    </div>

{{--Banner Trademark--}}
    @if(!empty($bannerList['trademarkMobile']) && count($bannerList['trademarkMobile']) > 0)
        <div class="container px-0 py-2 mb-2 bg-transparent product-group">
            <h5 class="mb-0 px-2 pt-2">TOP 10 THƯƠNG HIỆU</h5>
        </div>
    @endif
    <div class="container banner-trademark-group mb-4 pt-2 p-0">
        <div id="top-10-trademark-indicators"
             style="max-height:360px;max-width: 1200px!important;"
             class="carousel slide"
             data-ride="carousel"
        >
            <ol class="carousel-indicators">
                @if (!empty($bannerList['trademarkWeb']) && !empty($bannerList['trademarkMobile']))
                    <template v-if="windowWidth >= 768">@endif
                        @if (!empty($bannerList['trademarkWeb']) && count($bannerList['trademarkWeb']) > 0)
                            @foreach ($bannerList['trademarkWeb'] as $banner)
                                <li data-target="#top-10-trademark-indicators"
                                    data-slide-to="{{ $loop->index }}"
                                    @if ($loop->index === 0) class="active" @endif
                                ></li>
                            @endforeach
                        @endif
                        @if (!empty($bannerList['trademarkMobile']) && count($bannerList['trademarkMobile']) > 0)
                    </template>
                    <template v-else>
                        @foreach ($bannerList['trademarkMobile'] as $banner)
                            <li data-target="#top-10-trademark-indicators"
                                data-slide-to="{{ $loop->index }}"
                                @if ($loop->index === 0) class="active" @endif
                            ></li>
                        @endforeach
                    </template>
                @endif
            </ol>
            <div class="carousel-inner">
                @if (!empty($bannerList['trademarkMobile']))
                    <template v-if="windowWidth >= 768">@endif
                        @if (!empty($bannerList['trademarkWeb']) && count($bannerList['trademarkWeb']) > 0)
                            @foreach ($bannerList['trademarkWeb'] as $banner)
                                <div
                                    class="carousel-item  @if ($loop->first) active @endif"
                                >
                                    <img
                                        data-src="{{ $banner['image'] }}"
                                        class=" w-100 lazyload"
                                        alt="Takamart"
                                    >
                                    @if ($banner['action'] == \App\Enums\Banner\EBannerActionType::OPEN_WEBSITE)
                                        <a
                                            href="{{ $banner['actionDetail']->url }}"
                                            target="_blank"
                                            rel="noreferrer"
                                            aria-label="banner"
                                            class="block-mask"
                                        ></a>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                        @if (!empty($bannerList['trademarkMobile']))
                    </template>
                    <template v-else>
                        @foreach ($bannerList['trademarkMobile'] as $banner)
                            <div
                                class="carousel-item  @if ($loop->first) active @endif"
                            >
                                <img
                                    src="{{ $banner['image'] }}"
                                    class=" w-100"
                                    alt="Takamart"
                                >
                                @if ($banner['action'] == \App\Enums\Banner\EBannerActionType::OPEN_WEBSITE)
                                    <a
                                        href="{{ $banner['actionDetail']->url }}"
                                        target="_blank"
                                        rel="noreferrer"
                                        aria-label="banner"
                                        class="block-mask"
                                    ></a>
                                @endif
                            </div>
                        @endforeach
                    </template>
                @endif
            </div>
        </div>
    </div>

{{--    Products--}}
    <div class="container px-0 py-2 mb-2 bg-transparent product-group">
        <h5 class="mb-0 px-2 pt-2">SẢN PHẨM DÀNH CHO BẠN </h5>
    </div>
    <div class="container position-relative" id="product-list" style="min-height: 260px" v-cloak>
        <div class="row no-gutters card-deck mb-3">
            <product-item
                v-for="item in productList"
                :class-item="'product-in-home'"
                :item-data="item"
                :display-btn-like="shopId!==item.shop_id"
            >
            </product-item>
        </div>
    </div>


{{--    Button load more product--}}
    <div v-if="dataPaginate.to < dataPaginate.total"
         class="container" style="display: flex; justify-content: center;">
        <button class="btn btn-outline-primary mb-5 btn-loadmore "
                @click="getProductList"
        >
            Xem thêm
        </button>
    </div>

    @if (!empty($bannerList['promotion']))
        <div v-if="displayPromotionBanner" class="banner-promotion" v-cloak>
            <div class="position-relative"
                 style="width: 100%;height: 0; padding-bottom: 56.25%;"
            >
                @php $promotion = $bannerList['promotion'][0]; @endphp
                <img src="{{ $promotion['image'] }}" alt="Takamart"
                     class="w-100">
                @if ($promotion['action'] == \App\Enums\Banner\EBannerActionType::OPEN_WEBSITE)
                    <a
                        href="{{ $promotion['actionDetail']->url }}"
                        target="_blank"
                        class="block-mask"
                    ></a>
                @endif
                <span class="position-absolute"
                      @click="hidePromotionBanner"
                      style="z-index: 2;top:1px; right: 1px; line-height: 16px;cursor: pointer"
                >
                    <img style="width: 16px;height: 16px" alt="Takamart" src="/images/icon/cancel_primary_color.svg">
                </span>
            </div>
        </div>
    @endif
@endsection

@push('app-scripts')
	<script src="{{ mix('/js/front/home/home.js') }}"></script>
	<script type="application/javascript">
		$(document).ready(function() {
			$('#category-list')
				// .on('init', function() {
				// 	$('.block-3__list').removeClass('row');
				// 	$('.block-3__list__item').removeClass('col-16').removeClass('d-none');
				// })
				.slick({
                    "infinite": false,
                    "speed": 500,
                    "slidesToShow": 6,
                    "rows": 2,
                    "slidesToScroll": 12,
                    "swipeToSlide": true,
                    "draggable": false,
                    "responsive": [
                        {
                            "breakpoint": 1200,
                            "settings": {
                                "slidesToShow": 5,
                                "slidesToScroll": 10,
                                "rows": 2,
                                "infinite": false,
                            }
                        },
                        {
                            "breakpoint": 1000,
                            "settings": {
                                "slidesToShow": 4,
                                "slidesToScroll": 8,
                                "rows": 2,
                                "infinite": false,
                            }
                        },
                        {
                            "breakpoint": 767,
                            "settings": {
                                "slidesToShow": 3,
                                "slidesToScroll": 6,
                                "rows": 2,
                                "infinite": false,
                            }
                        },
                        {
                            "breakpoint": 400,
                            "settings": {
                                "slidesToShow": 2,
                                "slidesToScroll": 4,
                                "rows": 2,
                                "infinite": false,
                            }
                        }
                    ],
                });
		});
	</script>
@endpush
