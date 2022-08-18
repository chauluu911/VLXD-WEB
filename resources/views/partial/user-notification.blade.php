@php
    $disableDefaultLogoutBtn = in_array(\Route::currentRouteName(), [
	    'do.test'
	]);
@endphp
{{--Ban đầu đã chia block kiểu kì lạ này (gom cả thông tin ví user, menu, notification vào 1 block
vì không có thời gian nên giữ nguyên--}}
<style type="text/css">
    #login-notification {
        .modal-backdrop {
            display: none;
        }
    }
</style>
<input type="hidden" id="user-id" value="{{ auth()->id() }}">
<div class="wrap-login-notifition" id="login-notification">
    <exercise-result ref="exerciseResultEl" style="background: #0000006e"></exercise-result>
    <div class="d-flex justify-content-end navbar-expand-md">
        <button class="navbar-toggler btn-menu-mobile mr-auto @if(!auth()->check()) pt-2 @endif" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" >
            <img src="/images/icon/hamburger-menu.png" width="24px">
        </button>
        @if(auth()->check())
            <div class="wrap-notf-user position-relative" style="top: 3px;" v-cloak>
                <div class="d-flex justify-content-end position-relative" style="right: 25px">
                    <div class="mr-1">
                        <button type="button" id="dropdownNotification" data-toggle="dropdown" style="background: transparent; border: none;" @click="getNotification">
                            <span class="span--title-notification" v-if="numberNotSeen > 0 && numberNotSeen < 100">@{{numberNotSeen}}</span>
                            <span class="span--title-notification" v-else-if="numberNotSeen > 99">99+</span>
                            <i class="fas fa-bell" style="font-size: 22px;"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu--custum-notification" aria-labelledby="dropdownNotification" ref="conversationListEl">
                            <div class="row m-2">
                                <div class="w-50 font-weight-bold">{{ __('front/home.header.notification') }}</div>
                                <div class="w-50 text-right">
                                    <a class="check--all-notification" href="javascript:void(0)" @click="markAllRead">{{ __('front/home.header.read-all-notification') }}</a>
                                </div>
                            </div>
                            <div v-if="results.length <= 0" class="div--no-notify">{{ __('front/notification.no_notification') }}</div>
                            <div class="dropdown-item" v-for="(item, index) in results" @click="seenNotification(item)" :style="item.is_seen == false ? {'background-color': '#166e5040'} : ''">
                                <div class="row">
                                    <div class="col-5">
                                        <img src="/images/icon/logo-notification.svg" width="24px">
                                    </div>
                                    <div class="col-40">
                                        <div class="row">
                                            <div class="col-48">
                                                <span class="font-weight-bold">@{{item.title}}</span>
                                            </div>
                                            <div class="col-48 pr-1 span--content" v-shave="{height: 34, character: '...'}">
                                                <div class="ck-edt-content" v-html="item.content"></div>
                                            </div>
                                            <div class="col-48">
                                                <span class="span--time">@{{item.createdAt}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="text-align: center;" :class="{'d-none': pageSize >= numberNotification}">
                                <i v-if="iconLoad" class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('profile', [], false) }}">
                        <img @if(auth()->user()->avatar_path == null) src="/images/default-avatar.png"
                             @else src="{{ config('app.resource_url_path') }}/{{ auth()->user()->avatar_path }}" @endif style="border-radius:50%; width:20px; height:20px;" onerror="this.src='{{ auth()->user()->avatar_path }}'">
                    </a>
                    <div>
                        <button type="button" id="dropdownMenuUser" data-toggle="dropdown" class="align-self-start user-toggle" style="background: transparent; border: none;">
                            <span v-if="authState.name">@{{ authState.name }}</span>
                            <span v-else>{{ auth()->user()->name }}</span>
                            <span><i class="fas fa-caret-down"></i></span>
                        </button>
                        <div class="dropdownMenuUser dropdown-menu" aria-labelledby="dropdownMenuUser" style="border-radius: 10px;">
                            <a class="dropdown-item" href="{{ route('profile', [], false) }}"><span class="mr-2"><i class="fas fa-user icon"></i></span>@lang('front/user-profile.personal-info')</a>
                            <a class="dropdown-item" href="{{ route('log-exam', [], false) }}"><span class="mr-2"><i class="fas fa-book-reader icon"></i></span>@lang('front/user-profile.manage-exercise')</a>
                            <div class="dropdown-divider mb-0"></div>
                            <a class="dropdown-item" href="{{ route('logout', [], false) }}"
                               @if (!$disableDefaultLogoutBtn) onclick="event.preventDefault(); sessionStorage && sessionStorage.clear(); document.getElementById('logout-form').submit();" @endif
                            >
                                <span class="mr-2"><i class="fas fa-sign-out-alt position-relative"></i></span>
                                @lang('front/user-profile.logout')
                            </a>
                            <form id="logout-form" action="{{ route('logout', [], false) }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div> <!-- wrap info -->
                <div class="d-flex justify-content-end wallet-user position-relative" v-cloak style="right: 25px">
                    <span><img src="/images/icon/point.svg" width="12px" height="12px" class="mr-1">@{{ walletState.point }}</span>
                    <span><img src="/images/icon/star-user.svg" width="12px" height="12px" class="mr-1">@{{ walletState.star }}</span>
                    <span><img src="/images/icon/coins.svg" width="12px" height="12px" class="mr-1">@{{ walletState.money }}</span>
                </div>
            </div><!-- wrap-notf-user -->
        @else
            <div class="mr-3 mt-3">
                <a href="javascript:void(0)" class="login-form text-form-login">
                    <img src="/images/icon/alarm.svg" height="20px">
                </a>
            </div>
            <div class="btn button-login form-login-register">
                <a href="javascript:void(0)" class="login-form text-form-login">
                    {{ __('front/auth.auth-area.login-btn-name') }}
                </a>
                <span class="text-white">|</span>
                <a href="javascript:void(0)" class="registered-form text-form-login">
                    {{ __('front/auth.auth-area.register-btn-name') }}
                </a>
            </div>
        @endif
        <div class="ml-3 @if(auth()->check()) pt-1 @else pt-3 @endif language">
            <a href="{{ request()->fullUrlWithQuery(['lang' => \App\Enums\ELanguage::VI]) }}" class="@if(app()->getLocale() === \App\Enums\ELanguage::VI) active @endif">VN</a>
            <span>/</span>
            <a style="pointer-events: none;" href="{{ request()->fullUrlWithQuery(['lang' => \App\Enums\ELanguage::EN]) }}" class="@if(app()->getLocale() === \App\Enums\ELanguage::EN) active @endif">EN</a>
        </div>
    </div>
    <nav class="navbar-expand-md">
        <div class="collapse navbar-collapse topNavWeb font-weight-bold" id="navbarSupportedContent">
            <ul class="navbar-nav ml-auto mt-md-3 mt-2">
                <li class="nav-item">
                    <a class=" @if(Route::currentRouteName() === 'home') active @endif" href="{{ route('home') }}">{{ __('front/home.Home') }}</a>
                </li>
                <li class="nav-item">
                    <a class="@if(in_array(\Route::currentRouteName(), [
										'exercise.list.view',
										'do.test',
										'payment-view',
									])) active @endif" href="{{ route('exercise.list.view') }}">{{ __('front/home.Exercise') }}</a>
                </li>
                {{-- <li class="nav-item">
                    <a href="javascript:void(0)">{{ __('front/home.Document') }}</a>
                </li> --}}
                <li class="nav-item">
                    <a class="@if(Route::currentRouteName() === 'view-news' || Route::currentRouteName() === 'news-view-detail' || Route::currentRouteName() === 'payment-view-post') active @endif" href="{{ route('view-news') }}">{{ __('front/home.Post') }}</a>
                </li>
                <li class="nav-item">
                    <a class="@if(Route::currentRouteName() === 'view.advisory') active @endif" href="{{ route('view.advisory') }}">{{ __('front/home.Advisory') }}</a>
                </li>
                <li class="nav-item">
                    <a class=" @if(Route::currentRouteName() === 'view-introduce' || Route::currentRouteName() === 'view-faqs') active @endif" href="{{ route('view-introduce') }}">{{ __('front/home.Introduce') }}
                    </a>
                </li>
                {{-- <div data-toggle="dropdown" class="dropdownMenuIntroduce dropdown-menu" aria-labelledby="dropdownMenuIntroduce">
                    <a class="dropdown-item font-weight-bold pl-3" href="{{ route('view-introduce') }}">{{ __('front/home.header.introduce-era') }}</a>
                    <a class="dropdown-item font-weight-bold pl-3" href="{{ route('policy.view', ['policyName' => 'user-guide'], false) }}">{{ __('front/user-manual.admin.type.user_guide') }}</a>
                </div> --}}
                <li class="nav-item">
                    <a class=" @if(Route::currentRouteName() === 'view-contact') active @endif" href="{{route('view-contact')}}">{{ __('front/home.Contact') }}</a>
                </li>
            </ul>
        </div>
    </nav>
</div>
