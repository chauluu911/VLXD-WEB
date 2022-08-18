@extends('layout.master')

@php
	$active_slick = in_array(\Route::currentRouteName(), [
		'home'
	]);
	$active_cropper = in_array(\Route::currentRouteName(), [
		'ads.create.form'
	]);
	$active_firebase = auth()->check();

	$active_chat = auth()->check();

    $active_toast = in_array(\Route::currentRouteName(), [
        'product.detail', 'shop.upgrade'
    ]);

@endphp
@prepend('meta')
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-221449267-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'UA-221449267-1');
    </script>
@endprepend
@prepend('pre-stylesheet')
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Outlined" rel="stylesheet">
    @if ($active_toast)
        <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet" type="text/css"/>
    @endif
	@if ($active_slick)
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css" integrity="sha256-UK1EiopXIL+KVhfbFa8xrmAWPeBjMVdvYMYkTAEv/HI=" crossorigin="anonymous" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css" integrity="sha256-4hqlsNP9KM6+2eA8VUT0kk4RsMRTeS7QGHIM+MZ5sLY=" crossorigin="anonymous"/>
	@endif
	@if ($active_cropper)
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.4.3/cropper.min.css" integrity="sha256-d2pK8EVd0fI3O9Y+/PYWrCfAZ9hyNvInLoUuD7qmWC8=" crossorigin="anonymous"/>
	@endif
    <link rel="stylesheet" href="{{ mix('/css/front/core.css') }}" type="text/css"/>
    <link rel="stylesheet" href="{{ mix('/css/front/app.css') }}" type="text/css"/>
    <link rel="stylesheet" href="{{ mix('/css/front/menu.css') }}" type="text/css"/>
@endprepend

@prepend('stylesheet')

@endprepend

@section('body')
    <header class="w-100 d-flex justify-content-center" id="header">
        <div class="container m-0 pl-0">

            <div class="navbar-expand-xl" id="login-notification"v-cloak>
                <div class="d-flex justify-content-start">
                    <!-- Logo -->
                    <div class="p-0">
                        <a href="/"><img class="image-logo" src="/images/logo.jpg" height="86px" alt="logo"></a>
                    </div>
                    <!-- Logo -->
                    <!-- Header right -->
                    <div class="menu-web w-100 p-0">
                        <div class="wrap-login-notification">
                            <nav>
                                <div class="navbar-toggler"
                                     data-toggle="collapse"
                                     aria-expanded="false"
                                     aria-label="Toggle navigation">
                                    <div class="w-100 d-flex justify-content-between">
                                        <div class="px-2" style=" width: 80%;">
                                            <div class="nav-text" style="display: flex; flex-wrap: wrap; align-content: center; padding-left: 0px">
                                                <div class="position-relative w-100 ">
                                                    <input id="input-search-mobile"
                                                           aria-label="search"
                                                           type="text"
                                                           class="rounded-pill border-0 pl-3 pr-5 w-100"
                                                           placeholder="Tìm kiếm sản phẩm"
                                                           autocomplete="off"
                                                           style="height: 32px; line-height: 32px"
                                                           v-on:keyup.13="searchProductMobile()">
                                                    <span class="position-absolute" style="top: 6px; right: 10px">
                                        <i class="fas fa-search text-black-50 cursor-pointer" @click="searchProductMobile()"></i>
                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <div class="px-1">
                                                <div class="d-flex flex-column justify-content-center"
                                                     style="height: 86px"
                                                >
                                                    <a class="btn btn-primary custom-nav-text p-0 "
                                                       href="{{ route('chat', [], false) }}">
                                                        <div class="d-flex justify-content-center pb-1 position-relative">
                                                            <img
                                                                src="/images/icon/chat_home.svg"
                                                                width="24px"
                                                                alt="chat"
                                                            >
                                                            @if(auth()->id())
                                                                <div v-if="message.numberMessageUnSeen" v-cloak
                                                                     class="rounded-pill bg-white text-primary position-absolute"
                                                                     style="width: 16px; height: 16px; right: -5px; top: -8px; font-size: 9px;">
                                                                    <span style="line-height: 1.8" v-if="message.numberMessageUnSeen < 10" v-cloak>@{{message.numberMessageUnSeen}}</span>
                                                                    <span v-else style="line-height: 1.8">9+</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="px-1">
                                                <div class="d-flex flex-column justify-content-center"
                                                     style="height: 86px"
                                                >
                                                    <a class="custom-nav-text btn btn-primary p-0"
                                                       href="{{ route('cart', [], false) }}">
                                                        <div class="d-flex justify-content-center pb-1 position-relative">
                                                            <img
                                                                src="/images/icon/shopping_cart.svg"
                                                                width="24px"
                                                                alt="shopping-cart"
                                                            >
                                                            <div class="rounded-pill bg-white text-primary position-absolute"
                                                                 style="width: 16px; height: 16px; right: -5px; top: -8px; font-size:9px;">
                                                                <span id="mobile-cart-quantity" style="line-height: 1.8">0</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
                                    {{--<ul class="navbar-nav ml-auto">--}}
                                    <div class="row w-100 m-0">
                                        <div class="col-23 px-4">
                                            <div class="nav-text" style="display: flex; flex-wrap: wrap; align-content: center;">
                                                <div class="position-relative w-100 ">
                                                    <input id="input-search"
                                                           type="text"
                                                           aria-label="search"
                                                           class="rounded-pill border-0 px-4 w-100"
                                                           placeholder="Tìm kiếm sản phẩm"
                                                           autocomplete="off"
                                                           style="height: 32px"
                                                           v-on:keyup.13="searchProduct()">
                                                    <span class="position-absolute" style="top: 5px; right: 10px">
                                        <i class="fas fa-search text-black-50 cursor-pointer" @click="searchProduct()"></i>
                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4 p-0">
                                            @if(empty(auth()->user()->getShop) || auth()->user()->getShop->status == App\Enums\EStatus::DELETED)
                                                <div class="custom-nav-item" style="border-left: 1px solid #ff8839">
                                                    <a class="btn btn-primary custom-nav-text p-0"
                                                       href="{{ route('shop.create', [], false) }}">
                                                        <div class="d-flex justify-content-center pb-1">
                                                            <img
                                                                src="/images/icon/store.svg"
                                                                width="24px"
                                                                alt="store"
                                                            >
                                                        </div>

                                                        <span>Bán hàng</span>
                                                    </a>
                                                </div>
                                            @else
                                                <div class="custom-nav-item" style="border-left: 1px solid #ff8839">
                                                    <a class="btn btn-primary custom-nav-text p-0"
                                                       href="{{ route('shop.edit', ['shopId' => auth()->user()->getShop->id], false) }}">
                                                        <div class="d-flex justify-content-center pb-1">
                                                            <img
                                                                src="/images/icon/store.svg"
                                                                width="24px"
                                                                alt="store"
                                                            >
                                                        </div>

                                                        <span>Bán hàng</span>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-4 p-0">
                                            <div class="custom-nav-item" >
                                                <a class="btn btn-primary custom-nav-text p-0 "
                                                   href="{{ route('chat', [], false) }}">
                                                    <div class="d-flex justify-content-center pb-1 position-relative">
                                                        <img
                                                            src="/images/icon/chat_home.svg"
                                                            width="24px"
                                                            alt="chat"
                                                        >
                                                        @if(auth()->id())
                                                            <div v-if="message.numberMessageUnSeen" v-cloak
                                                                 class="rounded-pill bg-white text-primary position-absolute"
                                                                 style="width: 16px; height: 16px; right: 25px; top: -5px; font-size: 9px;">
                                                                <span style="line-height: 1.8" v-if="message.numberMessageUnSeen < 100" v-cloak>@{{message.numberMessageUnSeen}}</span>
                                                                <span v-else style="line-height: 1.8">99+</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <span>Tin nhắn</span>
                                                    <input id="user-id-for-message-notify" hidden="" type="text" data-id="{{auth()->id() ? auth()->id() : null}}">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-4 p-0">
                                            <div id="notify-item">
                                                <div class="custom-nav-item">
                                                    @if(auth()->id())
                                                        <div class="btn btn-primary custom-nav-text p-0" onclick="myFunction()">
                                                            <div class="d-flex justify-content-center pb-1 position-relative">
                                                                <img
                                                                    src="/images/icon/notifications.svg"
                                                                    width="24px"
                                                                    alt="notifications"
                                                                >
                                                                <div v-if="notify.numberNotSeen > 0" class="rounded-pill bg-white text-primary position-absolute" style="width: 16px; height: 16px; right: 25px; top: -5px; font-size: 9px;">
                                                                    <span style="line-height: 1.8" v-if="notify.numberNotSeen < 100" v-cloak>@{{notify.numberNotSeen}}</span>
                                                                    <span style="line-height: 1.8" v-else>99+</span>
                                                                </div>
                                                            </div>
                                                            <span>Thông báo</span>
                                                        </div>
                                                    @else
                                                        <a class="btn btn-primary custom-nav-text p-0"
                                                           href="{{ route('login', [], false) }}">
                                                            <div class="d-flex justify-content-center pb-1 position-relative">
                                                                <img
                                                                    src="/images/icon/notifications.svg"
                                                                    width="24px"
                                                                    alt="notifications"
                                                                >
                                                            </div>
                                                            <span>Thông báo</span>
                                                        </a>
                                                    @endif
                                                </div>
                                                <div v-show="type == 'system'" id="myDropdown" class="dropdown-content">
                                                    <div class="row no-gutters">
                                                        <div
                                                            class="col-24 py-2 text-center"
                                                            :class="type == 'system' ? 'option-activated' : ''"
                                                            @click="changeOption('system')"
                                                        >
                                                            <div
                                                                class="dropdown-text text-decoration-none"
                                                                style="cursor: pointer"
                                                                :class="type == 'system' ? 'text-primary' : 'text-black'"
                                                            >
                                                                Hệ thống
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="col-24 py-2 text-center"
                                                            :class="type == 'news' ? 'option-activated' : ''"
                                                            @click="changeOption('news')"
                                                        >
                                                            <div
                                                                class="dropdown-text text-decoration-none"
                                                                style="cursor: pointer"
                                                                :class="type == 'news' ? 'text-primary' : 'text-black'"
                                                            >
                                                                Tin mới
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <template>
                                                        <div class="row no-gutters mt-2" ref="listNotifyEl" style="max-height: 320px; overflow: auto;">
                                                            <div
                                                                v-for="item in notify.data"
                                                                class="col-48 px-3 py-2"
                                                                :style="!item.is_seen ? 'background-color: #8080801f' : ''"
                                                                @click="seenNotification(item)"
                                                            >
                                                                <div>
                                                                    <div class="media">
                                                                        <img class="rounded-pill mr-12" alt="logo" src="/images/logo.jpg" width="48px" height="48px">
                                                                        <div class="media-body pl-3">
                                                                            <div class="font-medium" v-html="item.content"></div>
                                                                            <p  style="color: #0000004D; font-size: 12px">
                                                                                @{{item.created_at}}
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div style="text-align: center;" :class="{'d-none': notify.pageSize >= notify.numberNotification}">
                                                            <i v-if="iconLoadNotify" class="fas fa-spinner fa-spin"></i>
                                                        </div>
                                                    </template>
                                                </div>
                                                <div v-show="type == 'news'" id="myDropdown1" class="dropdown-content">
                                                    <div class="row no-gutters">
                                                        <div
                                                            class="col-24 py-2 text-center"
                                                            :class="type == 'system' ? 'option-activated' : ''"
                                                            @click="changeOption('system')"
                                                        >
                                                            <div
                                                                class="dropdown-text text-decoration-none"
                                                                style="cursor: pointer!important"
                                                                :class="type == 'system' ? 'text-primary' : 'text-black'"
                                                            >
                                                                Hệ thống
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="col-24 py-2 text-center"
                                                            :class="type == 'news' ? 'option-activated' : ''"
                                                            @click="changeOption('news')"
                                                        >
                                                            <div
                                                                class="dropdown-text text-decoration-none"
                                                                style="cursor: pointer!important"
                                                                :class="type == 'news' ? 'text-primary' : 'text-black'"
                                                            >
                                                                Tin mới
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <template>
                                                        <div class="row no-gutters mt-2" ref="listNewsEl" style="max-height: 320px; overflow: auto;">
                                                            <div
                                                                v-for="item in news.data"
                                                                class="col-48 px-3 py-2"
                                                                @click="redirectTo(item)"
                                                            >
                                                                <a :href="item.href">
                                                                    <div class="media">
                                                                        <div
                                                                            class="m-auto"
                                                                            style="width: 100px; height: 60px; background-size: cover; border-radius: 5px"
                                                                            :style="{'background-image': `url(${item.avatarPath})`}"
                                                                        >
                                                                        </div>
                                                                        <div class="media-body ml-2">
                                                                            <span class="font-medium">@{{item.title}}</span>
                                                                            <p  style="color: #0E98E8; font-size: 12px">
                                                                                @{{item.createdAt}}
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div style="text-align: center;" :class="{'d-none': news.pageSize >= news.numberNews}">
                                                            <i v-if="iconLoadNews" class="fas fa-spinner fa-spin"></i>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4 p-0">
                                            @if (auth()->id())
                                                <div class="custom-nav-item" style="display: flex !important">
                                                    <a href="{{ route('profile.edit', [], false) }}"
                                                       class="btn btn-primary dropdown custom-nav-text p-0">
                                                        <div class="d-flex justify-content-center pb-1">
                                                            @if (auth()->user() && auth()->user()->avatar_path)
                                                                <div class="m-auto rounded-pill" style="width: 24px; height: 24px; background-size: cover"
                                                                     :style="{'background-image': `url({{App\Helpers\FileUtility::getFileResourcePath(auth()->user()->avatar_path, App\Constant\DefaultConfig::FALLBACK_USER_AVATAR_PATH)}})`}">

                                                                </div>
                                                            @else
                                                                <img
                                                                    src="/images/default-user-avatar.png"
                                                                    width="24px"
                                                                    height="24px"
                                                                    class="rounded-pill"
                                                                    alt="avatar"
                                                                >
                                                            @endif
                                                        </div>
                                                        <span>
                                                            Tài khoản
                                                        </span>
                                                    </a>
                                                </div>
                                            @else
                                                <div class="dropdown custom-nav-item" style="display: flex !important">
                                                    <a href="{{ route('profile.edit', [], false) }}"
                                                       class="btn btn-primary dropdown custom-nav-text p-0"
                                                       aria-haspopup="true"
                                                       aria-expanded="false">
                                                        <div class="d-flex justify-content-center pb-1">
                                                            <img
                                                                src="/images/icon/person-white-24px.svg"
                                                                width="24px"
                                                                class="rounded-pill"
                                                                alt="avatar"
                                                            >
                                                        </div>
                                                        <span>
                                                            Tài khoản
                                                        </span>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-9 p-0">
                                            <div class="custom-nav-item">
                                                <a class="custom-nav-text btn btn-primary p-0"
                                                   href="{{ route('cart', [], false) }}">
                                                    <div class="d-flex justify-content-center pb-1">
                                                        <img
                                                            src="/images/icon/shopping_cart.svg"
                                                            width="24px"
                                                            alt="shopping-cart"
                                                        >
                                                        <div class="rounded-pill bg-white text-primary position-absolute" style="width: 16px; height: 16px; left: 65px; top: 15px; font-size: 9px;">
                                                            <span id="cart-quantity" style="line-height: 1.8">0</span>
                                                        </div>
                                                        <span class="pl-3">Giỏ hàng</span>
                                                    </div>
                                                    <div class="font-medium bg-white text-black d-inline-block px-2 mb-1 rounded" style="width: 90px; font-size: 12px">
                                                        <span id="cart-total">0 đ</span>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="navbar-toggler w-100 m-0 p-0 border-0"
                     data-toggle="collapse"
                     style="position: fixed;bottom: 0px; left:0px"
                     aria-expanded="false"
                     aria-label="Toggle navigation">
                    <div class="row m-0 bg-white">
                        <div class="col-12 p-0">
                            <div class="d-flex justify-content-center">
                                <div class="menu-mobile-item">
                                    <a class="btn menu-mobile-item-text p-0"
                                       href="/">
                                        <div class="d-flex justify-content-center pb-1">
                                            <img
                                                :src="getTabName() === 'home' ? '/images/icon/home-primary-24px.svg' : '/images/icon/home-gray-24px.svg'"
                                                width="24px"
                                                alt="store"
                                            >
                                        </div>

                                        <span :class="{'active': getTabName() === 'home' }">
                                            Trang chủ
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 p-0">
                            <div class="d-flex justify-content-center">
                                @if(empty(auth()->user()->getShop))
                                    <div class="menu-mobile-item">
                                        <a class="btn menu-mobile-item-text p-0"
                                           href="{{ route('shop.create', [], false) }}">
                                            <div class="d-flex justify-content-center pb-1">
                                                <img
                                                    :src="getTabName() === 'shop' ? '/images/icon/store-primary-24px.svg' : '/images/icon/store-gray-24px.svg'"
                                                    width="24px"
                                                    alt="store"
                                                >
                                            </div>

                                            <span :class="{'active': getTabName() === 'shop' }">
                                                Bán hàng
                                            </span>
                                        </a>
                                    </div>
                                @else
                                    <div class="menu-mobile-item" >
                                        <a class="btn menu-mobile-item-text p-0"
                                           href="{{ route('shop.edit', ['shopId' => auth()->user()->getShop->id], false) }}">
                                            <div class="d-flex justify-content-center pb-1">
                                                <img
                                                    :src="getTabName() === 'shop' ? '/images/icon/store-primary-24px.svg' : '/images/icon/store-gray-24px.svg'"
                                                    width="24px"
                                                    alt="store"
                                                >
                                            </div>

                                            <span :class="{'active': getTabName() === 'shop' }">
                                                Bán hàng
                                            </span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-12 p-0">
                            <div id="notify-item" class="d-flex justify-content-center">
                                <div class="menu-mobile-item">
                                    @if(auth()->id())
                                        <div class="btn menu-mobile-item-text p-0" @click="toggleShowMobileNotify()">
                                            <div class="d-flex justify-content-center pb-1 position-relative">
                                                <img
                                                    :src="isShowMobileDropdown ? '/images/icon/notifications-primary-24px.svg' : '/images/icon/notifications-gray-24px.svg' "
                                                    width="24px"
                                                    alt="notifications"
                                                >
                                                @if(auth()->id())
                                                    <div v-if="notify.numberNotSeen > 0" class="rounded-pill position-absolute"
                                                         style="width: 20px; height: 16px; color: white; right: 15px; top: -9px; font-size: 9px;background: red !important;">
                                                        <span
                                                            style="line-height: 1.8" v-if="notify.numberNotSeen < 100"
                                                            v-cloak>
                                                            @{{notify.numberNotSeen}}
                                                        </span>
                                                        <span style="line-height: 1.8" v-else>99</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <span :class="{'active': isShowMobileDropdown }">
                                                Thông báo
                                            </span>
                                        </div>
                                    @else
                                        <a class="btn menu-mobile-item-text p-0" href="{{ route('login', [], false) }}">
                                        <div class="d-flex justify-content-center pb-1 position-relative">
                                            <img
                                                src="/images/icon/notifications-gray-24px.svg"
                                                width="24px"
                                                alt="notifications"
                                            >
                                        </div>
                                        <span>Thông báo</span>
                                        </a>
                                    @endif
                                </div>
                                <div id="myMobileDropdown" v-show="isShowMobileDropdown && type == 'system'"
                                     class="dropdown-notify-mobile"
                                >
                                    <div class="row no-gutters">
                                        <div
                                            class="col-24 py-2 text-center"
                                            :class="type == 'system' ? 'option-activated' : ''"
                                            @click="changeOption('system')"
                                        >
                                            <div
                                                class="dropdown-text text-decoration-none font-size-16px"
                                                :class="type == 'system' ? 'text-primary' : 'text-black'"
                                            >
                                                Hệ thống
                                            </div>
                                        </div>
                                        <div
                                            class="col-24 py-2 text-center"
                                            :class="type == 'news' ? 'option-activated' : ''"
                                            @click="changeOption('news')"
                                        >
                                            <div
                                                class="dropdown-text text-decoration-none font-size-16px"
                                                :class="type == 'news' ? 'text-primary' : 'text-black'"
                                            >
                                                Tin mới
                                            </div>
                                        </div>
                                    </div>
                                    <template>
                                        <div class="row no-gutters mt-2" ref="listNotifyElMobile" style="max-height: 320px; overflow: auto;">
                                            <div
                                                v-for="item in notify.data"
                                                class="col-48 px-3 py-2"
                                                :style="!item.is_seen ? 'background-color: #8080801f' : ''"
                                                @click="seenNotification(item)"
                                            >
                                                <div>
                                                    <div class="media">
                                                        <img class="rounded-pill mr-12" alt="notifications" src="/images/logo.jpg" width="48px" height="48px">
                                                        <div class="media-body pl-3">
                                                            <div style="font-size: 14px !important;" class="font-medium" v-html="item.content"></div>
                                                            <p class="pt-1" style="color: #0000004D; font-size: 12px">
                                                                @{{item.created_at}}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="text-align: center;" :class="{'d-none': notify.pageSize >= notify.numberNotification}">
                                            <i v-if="iconLoadNotify" class="fas fa-spinner fa-spin"></i>
                                        </div>
                                    </template>
                                </div>
                                <div v-show="isShowMobileDropdown && type == 'news'"
                                     class="dropdown-notify-mobile"
                                >
                                    <div class="row no-gutters">
                                        <div
                                            class="col-24 py-2 text-center"
                                            :class="type == 'system' ? 'option-activated' : ''"
                                            @click="changeOption('system')"
                                        >
                                            <div
                                                class="dropdown-text text-decoration-none font-size-16px"
                                                :class="type == 'system' ? 'text-primary' : 'text-black'"
                                            >
                                                Hệ thống
                                            </div>
                                        </div>
                                        <div
                                            class="col-24 py-2 text-center"
                                            :class="type == 'news' ? 'option-activated' : ''"
                                            @click="changeOption('news')"
                                        >
                                            <div
                                                class="dropdown-text text-decoration-none font-size-16px"
                                                :class="type == 'news' ? 'text-primary' : 'text-black'"
                                            >
                                                Tin mới
                                            </div>
                                        </div>
                                    </div>
                                    <template>
                                        <div class="row no-gutters mt-2" ref="listNewsElMobile" style="max-height: 320px; overflow: auto;">
                                            <div
                                                v-for="item in news.data"
                                                class="col-48 px-3 py-2"
                                                @click="redirectTo(item)"
                                            >
                                                <a :href="item.href">
                                                    <div class="media">
                                                        <div
                                                            class="m-auto"
                                                            style="width: 100px; height: 60px; background-size: cover; border-radius: 5px"
                                                            :style="{'background-image': `url(${item.avatarPath})`}"
                                                        >
                                                        </div>
                                                        <div class="media-body ml-2">
                                                            <span style="font-size: 14px !important;" class="font-medium">@{{item.title}}</span>
                                                            <p class="pt-1"  style="color: #0E98E8; font-size: 12px">
                                                                @{{item.createdAt}}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                        <div style="text-align: center;" :class="{'d-none': news.pageSize >= news.numberNews}">
                                            <i v-if="iconLoadNews" class="fas fa-spinner fa-spin"></i>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 p-0">
                            <div class="d-flex justify-content-center">
                                @if (auth()->id())
                                    <div class="menu-mobile-item" style="display: flex !important">
                                        <a href="{{ route('profile.edit', [], false) }}"
                                           class="btn dropdown menu-mobile-item-text p-0">
                                            <div class="d-flex justify-content-center pb-1">
                                                @if (auth()->user() && auth()->user()->avatar_path)
                                                    <div class="m-auto rounded-pill" style="width: 24px; height: 24px; background-size: cover"
                                                         :style="{'background-image': `url({{App\Helpers\FileUtility::getFileResourcePath(auth()->user()->avatar_path, App\Constant\DefaultConfig::FALLBACK_USER_AVATAR_PATH)}})`}">

                                                    </div>
                                                @else
                                                    <img
                                                        src="/images/default-user-avatar.png"
                                                        width="24px"
                                                        height="24px"
                                                        class="rounded-pill"
                                                        alt="avatar"
                                                    >
                                                @endif
                                            </div>
                                            <span :class="{'active': getTabName() === 'profile' }">
                                                Tài khoản
                                            </span>
                                        </a>
                                    </div>
                                @else
                                    <div class="dropdown menu-mobile-item" style="display: flex !important">
                                        <a href="{{ route('login', [], false) }}"
                                           class="btn dropdown menu-mobile-item-text p-0"
                                           aria-haspopup="true"
                                           aria-expanded="false">
                                            <div class="d-flex justify-content-center pb-1">
                                                <img
                                                    src="/images/icon/person-gray-24px.svg"
                                                    width="24px"
                                                    class="rounded-pill"
                                                    alt="avatar"
                                                >
                                            </div>
                                            <span :class="{'active': getTabName() === 'profile' }">
                                                Tài khoản
                                            </span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>


            </div>

        </div>
    </header>


	<main class="main" style="min-height: 100%" @hasSection('main-id') id="@yield('main-id')" @endif>
		@section('body-user')
		@show
	</main>
	<footer class="container-fluid bg-white">
		<div class="container" style="border-bottom: solid 1px #f1f1f1">
			<div class="footer-content row">
				<div class="col-md-12 mb-3">
					<div class="footer-title" style="max-width: 160px">
						Tải ứng dụng VLXD số một Việt Nam
					</div>
                    <div class="my-3">
                        <a href="https://apps.apple.com/us/app/id1544713904">
                            <img alt="Takamart for IOS" class="lazyload" data-src="/images/appstore.png">
                        </a>
                    </div>
					<div>
                        <a href="https://play.google.com/store/apps/details?id=com.boot.vlxd">
                            <img alt="Takamart for Android" class="lazyload" data-src="/images/playstore.png">
                        </a>
					</div>
				</div>
				<div class="col-md-12">
					<div class="footer-title" >@lang('front/home.footer.policy')</div>
					<div class="my-2"><a class="text" href="{{route('policy.view', ['policyName' => 'buy-policy'], false)}}">@lang('front/home.footer.purchase-policy')</a></div>
                    <div class="my-2"><a class="text" href="{{route('policy.view', ['policyName' => 'resolve-complaints'], false)}}">Quy trình giải quyết khiếu nại</a></div>
                    <div class="my-2"><a class="text" href="{{route('policy.view', ['policyName' => 'payment-policy'], false)}}">@lang('front/home.footer.payment-policy')</a></div>
                    <div class="my-2"><a class="text" href="{{route('policy.view', ['policyName' => 'privacy-policy'], false)}}">@lang('front/home.footer.privacy-policy')</a></div>
                    <div class="my-2"><a class="text" href="{{route('policy.view', ['policyName' => 'terms-and-conditions'], false)}}">@lang('front/home.footer.terms-of-use')</a></div>
                    <div class="my-2"><a class="text" href="{{route('policy.view', ['policyName' => 'user-guide'], false)}}">Hướng dẫn</a></div>
                    <div class="my-2"><a class="text" href="/doc/quychehoatdongtakamart.vn.pdf">Quy chế hoạt động</a></div>
				</div>
                <div class="col-md-12">
                    <div class="footer-title">@lang('front/home.footer.about-us')</div>
                    <div class="my-2">
                        <a class="text" href="{{route('about-us.view', [], false)}}">
                            @lang('front/home.footer.introduce')
                        </a>
                    </div>
                    <div class="my-2">
                        <a class="text pr-3" href="{{route('news.list', [], false)}}">
                            @lang('front/home.footer.news')
                        </a>
                    </div>
                    <div class="position-relative" style="height: 60px">
                        <a class="position-absolute" href="http://online.gov.vn/Home/WebDetails/82649?AspxAutoDetectCookieSupport=1" style="left: -5px">
                            <img src="/images/logoCCDV.png" width="150px">
                        </a>
                    </div>
                </div>
				<div class="col-md-12">
					<div class="footer-title">@lang('front/home.footer.connect-with-us')</div>
					<a href="#" aria-label="facebook-contact" class="font-size-30px no-decoration" style="color: #4267b2">
						<i class="fab fa-facebook-square"></i>
					</a>
					<a href="#" aria-label="twitter-contact" class="font-size-30px  no-decoration" style="color: #1DA1F2">
						<i class="fab fa-twitter-square"></i>
					</a>
					<a href="#" aria-label="google-contact" class="font-size-30px no-decoration"style="color: #EA4335">
						<i class="fab fa-google-plus-square"></i>
					</a>
					<a href="#" aria-label="linkedin-contact" class="font-size-30px no-decoration" style="color: #0077B5">
						<i class="fab fa-linkedin"></i>
					</a>
				</div>
			</div>
		</div>
        <div class="container mt-3">
           <div class="row">
               <div class="col-md-36" style="color: black">
                   <p class="mb-1">CÔNG TY CỔ PHẦN GROUP VẬT LIỆU XÂY DỰNG MIỀN NAM</p>
                   <p class="mb-1">- Địa chỉ: 68 đường Tô Ký, Phường Trung Mỹ Tây, Quận 12, Thành phố Hồ Chí Minh, Việt Nam</p>
                   <p class="mb-1">- Giấy chứng nhận đăng ký doanh nghiệp số: 0316125291 do Sở Kế hoạch và Đầu tư Thành phố Hồ Chí Minh cấp ngày 03/02/2020</p>
                   <p class="mb-1">- Email: info.takamart@gmail.com</p>
                   <p class="mb-1">- Hotline: 08.3939.4343</p>
               </div>
               <!-- <div class="col-md-12">
                   <img src="/images/cerfiticate.png">
               </div> -->
           </div>
        </div>
	</footer>
	@php
		if (auth()->check()) {
            $user = auth()->user();
            if ($user->status != \App\Enums\EStatus::ACTIVE) {
                $login_stage = \App\Enums\ELoginStage::VERIFY_SMS;
            } else {
                $login_stage = \App\Enums\ELoginStage::LOGGED_IN;
            }
        } elseif (request()->has('rs_pw_token')) {
            $login_stage = \App\Enums\ELoginStage::RESET_PASSWORD;
        } else {
            $login_stage = \App\Enums\ELoginStage::NOT_LOGGED_IN;
        }
        $routeName = \Route::currentRouteName();
	@endphp
	@if($login_stage !== \App\Enums\ELoginStage::LOGGED_IN)
		<div class="modal fade" id="verify-modal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content container-fluid bg-transparent border-0">
					@include('partial.verify-form', ['login_stage' => $login_stage])
				</div>
			</div>
		</div>
	@endif

	@stack('modals')
@endsection

@prepend('body-scripts')
    <script type="text/javascript">
        $(window).on("popstate", function(e) {
            window.location.reload();
        });
    </script>
    <script src="/js/libs/lazysizes.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js" integrity="sha384-1CmrxMRARb6aLqgBO7yyAxTOQE2AKb9GfXnEo760AUcUmFx3ibVJJAzGytlQcNXd" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.4.0/bootbox.min.js" integrity="sha512-8vfyGnaOX2EeMypNMptU+MwwK206Jk1I/tMQV4NkhOz+W8glENoMhGyU6n/6VgQUhQcJH8NqQgHhMtZjJJBv3A==" crossorigin="anonymous"></script>
    <script src="{{ mix('/js/front/app.js') }}"></script>
    <script type="application/javascript">
        function toggleMobileMenu() {
            $('.menu-mobile').toggleClass('show');
        }
    </script>
    <script src="{{ mix('/js/front/notification/notification.js') }}"></script>
	@if ($active_slick)
		<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js" integrity="sha256-NXRS8qVcmZ3dOv3LziwznUHPegFhPZ1F/4inU7uC8h0=" crossorigin="anonymous"></script>
	@endif
	@if ($active_cropper)
		<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.min.js" integrity="sha256-EuV9YMxdV2Es4m9Q11L6t42ajVDj1x+6NZH4U1F+Jvw=" crossorigin="anonymous"></script>
	@endif

    @if ($active_firebase || $active_chat)
        @if ($active_chat)
            <script src="/js/libs/GoTime.min.js"></script>
            <script>
                GoTime.setOptions({
                    AjaxURL: "{{ route('time', [], false) }}",
                    SyncInitialTimeouts: [0, 500, 3000, 9000, 15000],
                    SyncInterval: 60 * 1000
                });
            </script>
        @endif
        <script src="https://www.gstatic.com/firebasejs/7.4.0/firebase-app.js"></script>
        <script src="https://www.gstatic.com/firebasejs/7.4.0/firebase-auth.js"></script>
        <script src="https://www.gstatic.com/firebasejs/7.4.0/firebase-database.js"></script>
        <script type="application/javascript">
            $(document).ready(function() {
                firebase.initializeApp(JSON.parse('{!! json_encode(config('app.firebase')) !!}'));
                firebase.auth().signInWithCustomToken('{{ auth()->user()->firebaseToken() }}');
            });
        </script>
    @endif

    @if(auth()->id())
        @if(request()->route()->getName() != 'cart')
            <script src="{{ mix('/js/front/cart/cart-menu.js') }}"></script>
        @endif
        <script type="application/javascript">
            function myFunction() {
                document.getElementById("myDropdown").classList.toggle("show");
                document.getElementById("myDropdown1").classList.toggle("show");
            }
            $(document).ready(function() {
                $('.main, .dropdown').click(function() {
                    var dropdowns = document.getElementsByClassName("dropdown-content");
                    var i;
                    for (i = 0; i < dropdowns.length; i++) {
                        var openDropdown = dropdowns[i];
                        if (openDropdown.classList.contains('show')) {
                            openDropdown.classList.remove('show');
                        }
                    }
                });
            });
        </script>
    @endif
    @if ($active_toast)
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    @endif
@endprepend
