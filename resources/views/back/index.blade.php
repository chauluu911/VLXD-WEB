@extends('back.layout.master')

@push('meta')
    <meta name="api-token" content="{{ auth()->user()->api_token }}">
@endpush

@prepend('pre-stylesheet')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.4.3/cropper.min.css" integrity="sha256-d2pK8EVd0fI3O9Y+/PYWrCfAZ9hyNvInLoUuD7qmWC8=" crossorigin="anonymous"/>
@endprepend

@section('body')
    <div id="app">
        <div v-if="false" class="common-loading-body">
            <div class="blob-body"></div>
        </div>
        <div class="d-flex" v-cloak>
            <b-sidebar
                id="main-sidebar"
                class="main-sidebar"
                :class="{active: sidebarState.active}"
                :sidebar-class="['bg-white', sidebarState.active ? 'active' : '', sidebarState.hovered ? 'hovered' : '']"
                text-variant="primary"
                no-close-on-backdrop
                no-header
                no-header-close
                shadow
            >
                <form id="logout-form" action="#" method="GET" style="display: none;">
                    @csrf
                </form>
                <div class="main-sidebar-logo position-relative bg-primary">
                    <b-img src="/images/logo.jpg" class="app-logo ml-3" :height="150" alt="App Logo"></b-img>
                    <button
                        class="close text-white"
                        @click="$store.commit('updateSidebarState', {active: !sidebarState.active})"
                    >
                        <f-chevrons-left-icon v-if="sidebarState.active"></f-chevrons-left-icon>
                        <f-chevrons-right-icon v-else></f-chevrons-right-icon>
                    </button>
                </div>
                <div
                    class="main-sidebar-menu bg-primary text-white"
                    @mouseover="$store.commit('updateSidebarState', {hovered: true})"
                    @mouseleave="$store.commit('updateSidebarState', {hovered: false})"
                >
                    <router-link
                        :to="{name: 'analytic'}"
                        class="no-decoration"
                        :class="{'router-link-exact-active': metaStack.module === 'analytic'}"
                    >
                        <div class="pl-default py-2 px-3">
                            <div class="div--navbar">
                                <f-bell-icon></f-bell-icon>
                                Thống kê
                            </div>
                        </div>
                    </router-link>
                    @if(Gate::allows('use-module-customer-management'))
                        <router-link
                            :to="{name: 'customer'}"
                            class="no-decoration"
                            :class="{'router-link-exact-active': ~['customer', 'shop'].indexOf(metaStack.module)}"
                        >
                            <div class="pl-default py-2 px-3">
                                <div class="div--navbar">
                                    <f-users-icon></f-users-icon>
                                    @lang('back/nav.customer')
                                    <i
                                        v-if="sidebarState.active"
                                        class="fa fa-caret-down i--dropdown"
                                        v-b-toggle.main-sidebar-customer
                                    ></i>
                                </div>
                            </div>
                        </router-link>
                        <b-collapse
                            id="main-sidebar-customer"
                            class="sub-menu-collapse"
                            :visible="['customer', 'shop'].indexOf(metaStack.module) > -1 && (sidebarState.active || sidebarState.hovered)"
                        >
                            <ul class="bg-primary2">
                                <li class="mb-3">
                                    <router-link :to="{name: 'customer'}" class="no-decoration">
                                        Quản lý khách hàng
                                    </router-link>
                                </li>
                                <li class="mb-3">
                                    <router-link :to="{name: 'shop'}" class="no-decoration">
                                        Quản lý cửa hàng
                                    </router-link>
                                </li>
                            </ul>
                        </b-collapse>
                    @endif

                    @if(Gate::allows('use-module-payment-management'))
                        <router-link
                            :to="{name: 'payment'}"
                            class="no-decoration"
                            :class="{'router-link-exact-active': metaStack.module === 'payment'}"
                        >
                            <div class="pl-default py-2 px-3">
                                <div class="div--navbar">
                                    <f-credit-card-icon></f-credit-card-icon>
                                    @lang('back/nav.payment')
                                </div>
                            </div>
                        </router-link>
                    @endif

                    @if(Gate::allows('use-module-category-management'))
                        <router-link :to="{name: 'product-category', query: {parentCategoryId: '', level: 1}}"
                                     class="no-decoration"
                                     :class="{'router-link-exact-active': metaStack.module === 'category'}">
                            <div class="pl-default py-2 px-3">
                                <div class="div--navbar">
                                    <i class="fas fa-list-alt" style="font-size: 24px"></i>
                                    @lang('back/nav.category')
                                    <i v-if="sidebarState.active"
                                       class="fa fa-caret-down i--dropdown"
                                       v-b-toggle.main-sidebar-category></i>
                                </div>
                            </div>
                        </router-link>
                        <b-collapse
                            id="main-sidebar-category"
                            class="sub-menu-collapse"
                            :visible="metaStack.module === 'category' && (sidebarState.active || sidebarState.hovered)"
                        >
                            <ul class="bg-primary2">
                                <li class="mb-3">
                                    <router-link :to="{name: 'product-category', query: {parentCategoryId: '', level: 1}}" class="no-decoration">
                                        Danh mục sản phẩm
                                    </router-link>
                                </li>
                                <li class="mb-3">
                                    <router-link :to="{name: 'issue-report.list'}" class="no-decoration">
                                        Danh mục báo tin sai
                                    </router-link>
                                </li>
                            </ul>
                        </b-collapse>
                    @endif

                    @if(Gate::allows('use-module-product-management'))
                        <router-link
                            :to="{name: 'product'}"
                            class="no-decoration"
                            :class="{'router-link-exact-active': metaStack.module === 'product'}"
                        >
                            <div class="pl-default py-2 px-3">
                                <div class="div--navbar">
                                    <f-list-icon></f-list-icon>
                                    Sản phẩm
                                    <i
                                        v-if="sidebarState.active"
                                        class="fa fa-caret-down i--dropdown"
                                        v-b-toggle.main-sidebar-product
                                    ></i>
                                </div>
                            </div>
                        </router-link>
                        <b-collapse
                            id="main-sidebar-product"
                            class="sub-menu-collapse"
                            :visible="metaStack.module === 'product' && (sidebarState.active || sidebarState.hovered)"
                        >
                            <ul class="bg-primary2">
                                <li class="mb-3">
                                    <router-link :to="{name: 'product'}" class="no-decoration">
                                        Danh sách sản phẩm
                                    </router-link>
                                </li>
                                <li class="mb-3">
                                    <router-link :to="{name: 'product-report.list'}" class="no-decoration">
                                        Danh sách báo sai
                                    </router-link>
                                </li>
                            </ul>
                        </b-collapse>
                    @endif
                    <router-link
                            :to="{name: 'branch'}"
                            class="no-decoration"
                            :class="{'router-link-exact-active': metaStack.module === 'branch'}"
                        >
                            <div class="pl-default py-2 px-3">
                                <div class="div--navbar">
                                    <i class="fas fa-truck"></i>
                                    Chi nhánh
                                </div>
                            </div>
                        </router-link>
                    @if(Gate::allows('use-module-news-management'))
                        <router-link
                            :to="{name: 'news'}"
                            class="no-decoration"
                            :class="{'router-link-exact-active': metaStack.module === 'news'}"
                        >
                            <div class="pl-default py-2 px-3">
                                <div class="div--navbar">
                                    <i class="fas fa-newspaper"></i>
                                    Tin tức
                                </div>
                            </div>
                        </router-link>
                    @endif

                    @if(Gate::allows('use-module-notification-management'))
                        <router-link
                            :to="{name: 'notification'}"
                            class="no-decoration"
                            :class="{'router-link-exact-active': metaStack.module === 'notification'}"
                        >
                            <div class="pl-default py-2 px-3">
                                <div class="div--navbar">
                                    <f-bell-icon></f-bell-icon>
                                    @lang('back/nav.notification')
                                </div>
                            </div>
                        </router-link>
                    @endif

                    @if(Gate::allows('use-module-employee-management'))
                        <router-link
                            :to="{name: 'employee'}"
                            class="no-decoration"
                            :class="{'router-link-exact-active': metaStack.module === 'employee'}"
                        >
                            <div class="pl-default py-2 px-3">
                                <div class="div--navbar">
                                    <i class="far fa-address-card"></i>
                                    @lang('back/nav.staff')
                                </div>
                            </div>
                        </router-link>
                    @endif

                    @if(Gate::allows('use-module-config-management'))
                        <router-link
                            :to="{name: 'config'}"
                            class="no-decoration"
                            :class="{'router-link-exact-active': metaStack.module === 'config'}"
                        >
                            <div class="pl-default py-2 px-3">
                                <div class="div--navbar">
                                    <f-settings-icon></f-settings-icon>
                                    Cấu hình
                                    <i
                                        v-if="sidebarState.active"
                                        class="fa fa-caret-down i--dropdown"
                                        v-b-toggle.main-sidebar-config
                                    ></i>
                                </div>
                            </div>
                        </router-link>
                        <b-collapse
                            id="main-sidebar-config" class="sub-menu-collapse"
                            :visible="metaStack.module === 'config' && (sidebarState.active || sidebarState.hovered)"
                        >
                            <ul class="bg-primary2">
                                <li class="mb-3">
                                    <router-link :to="{name: 'config.general'}" class="no-decoration">
                                        Cấu hình chung
                                    </router-link>
                                </li>
                                <li class="mb-3">
                                    <router-link :to="{name: 'config.banner'}" class="no-decoration">
                                        Banner
                                    </router-link>
                                </li>
                                <li class="mb-3">
                                    <router-link :to="{name: 'config.package-push-product'}" class="no-decoration">
                                        Gói đẩy tin
                                    </router-link>
                                </li>
                                <li class="mb-3">
                                    <router-link :to="{name: 'config.package-upgrade-shop'}" class="no-decoration">
                                        Gói nâng cấp cửa hàng
                                    </router-link>
                                </li>
                                <li class="mb-3">
                                    <router-link :to="{name: 'config.shop-level'}" class="no-decoration">
                                        Cấp độ cửa hàng
                                    </router-link>
                                </li>
                                <li class="mb-3">
                                    <router-link :to="{name: 'config.forbidden_search'}" class="no-decoration">
                                        Từ khóa bị cấm tìm kiếm
                                    </router-link>
                                </li>
                                <li class="mb-3">
                                    <router-link
                                        :to="{name: 'config.policy', params: {'policyType': '1'}}" class="no-decoration"
                                    >
                                        <span title="Chính sách, hướng dẫn, giới thiệu">Chính sách, hướng dẫn...</span>
                                    </router-link>
                                </li>
                            </ul>
                        </b-collapse>
                    @endif
                </div>
            </b-sidebar>
            <div
                class="content-ctn flex-grow-1"
                :class="{'sidebar-deactive': !sidebarState.active, 'with-active-sidebar': sidebarState.active}"
            >
                <div class="content container-fluid">
                    <transition name="slide">
                        <router-view class="content__header" name="header"></router-view>
                        <router-view></router-view>
                        <router-view class="content__footer" name="footer"></router-view>
                    </transition>
                </div>
            </div>
        </div>
    </div>
@endsection

@prepend('body-scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.min.js" integrity="sha256-EuV9YMxdV2Es4m9Q11L6t42ajVDj1x+6NZH4U1F+Jvw=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js" integrity="sha256-NXRS8qVcmZ3dOv3LziwznUHPegFhPZ1F/4inU7uC8h0=" crossorigin="anonymous"></script>
@endprepend

@prepend('app-scripts')
    <script src="{{ mix('/js/back/app.js') }}"></script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAE7zocVnayiUt3EeLFosK0OaL9EYV46l8&callback=initGoogleSuccess&libraries=places"></script>
    <script type="text/javascript">
        function initGoogleSuccess() {
            $(document).trigger('initGoogleSuccess');
            $(document).data('initGoogleSuccess', true);
        }
    </script>
@endprepend
