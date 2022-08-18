@extends('front.layout.master')

@push('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/shop.css') }}" type="text/css"/>
@endpush

@section('main-id', 'shop-review')

@section('title')
    Đánh giá cửa hàng
@endsection

@section('body-user')
    <div class="container div-review">
        <div class="container p-0">
            <div class="my-breadcrumb my-3">
                <div class="my-breadcrumb-item mr-2">
                    <a href="{{ route('shop.product', ['shopId'=>$shopId], false) }}">
                        <i class="fa fa-arrow-left" aria-hidden="true">
                        </i>
                    </a>
                </div>
                <a @click="back"
                  href="javascript:void(0)"
                  class="my-breadcrumb-item text mr-2">
                    Quay lại
                </a>
            </div>
        </div>
        <div class="bg-white mb-5" v-cloak>
            <div class="border-bottom m-0 p-3 row">
                <div class="col-md-48 col-lg-24 shop-review-title-info" >
                    <div v-if="shop.avatarType != EResourceType.VIDEO" class="mx-2 rounded-pill"
                         style="width: 48px; height: 48px;
                         background-size: cover"
                         :style="{'background-image': `url(${shop.avatar})`}"
                         >
                    </div>
                    <span v-else="shop.avatarType == EResourceType.VIDEO"
                          class="mx-2 b-avatar badge-secondary rounded-circle"
                          style="width: 48px; height: 48px">
                            <span class="shop-avatar">
                                <video autoplay muted loop :src="shop.avatar" style="width: 125px"></video>
                            </span>
                    </span>
                    <div class="d-flex flex-column justify-content-around">
                        <div>
                            @{{ shop.name }}
                        </div>
                        <div>
                            <span style="color: #00000066;" >
                                ID cửa hàng:
                            </span>
                            <span>
                                @{{ shop.code }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-48 col-lg-24 shop-review-title-filter">
                    <div class="star-filter row">
                        <div class="item"
                             :class="{ 'active': starFilter == -1 }"
                             @click="changeStarFilter(-1)">
                            Tất cả
                        </div>
                        <div class="item"
                             :class="{ 'active': starFilter == 5 }"
                             @click="changeStarFilter(5)">
                            <span>5</span>
                            <span class="star-icon" :class="{ 'star-active': starFilter == 5 }">
                                <i class="fa fa-star"
                                   aria-hidden="true"
                                >
                                </i>
                            </span>
                        </div>
                        <div class="item"
                             :class="{ 'active': starFilter == 4 }"
                             @click="changeStarFilter(4)">
                            <span>4</span>
                            <span class="star-icon" :class="{ 'star-active': starFilter == 4 }">
                                <i class="fa fa-star"
                                   aria-hidden="true"
                                >
                                </i>
                            </span>
                        </div>
                        <div class="item"
                             :class="{ 'active': starFilter == 3 }"
                             @click="changeStarFilter(3)">
                            <span>3</span>
                            <span class="star-icon" :class="{ 'star-active': starFilter == 3 }">
                                <i class="fa fa-star"
                                   aria-hidden="true"
                                >
                                </i>
                            </span>
                        </div>
                        <div class="item"
                             :class="{ 'active': starFilter == 2 }"
                             @click="changeStarFilter(2)">
                            <span>2</span>
                            <span class="star-icon" :class="{ 'star-active': starFilter == 2 }">
                                <i class="fa fa-star"
                                   aria-hidden="true"
                                >
                                </i>
                            </span>
                        </div>
                        <div class="item"
                             :class="{ 'active': starFilter == 1 }"
                             @click="changeStarFilter(1)">
                            <span>1</span>
                            <span class="star-icon" :class="{ 'star-active': starFilter == 1 }">
                                <i class="fa fa-star"
                                   aria-hidden="true"
                                >
                                </i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-3 pb-2 review-list">
               <div v-for="review in reviewList">
                   <div class="py-3 m-2 border-bottom d-flex flex-column">
                       <div class="d-flex justify-content-start">
                           <div class="mr-2 rounded-pill"
                                style="width: 24px; height: 24px;
                         background-image: url('/images/default-user-avatar.png');
                         background-size: cover"
                           >
                           </div>
                           <div>
                               @{{ review.name }}
                           </div>
                       </div>
                       <div class="d-flex justify-content-start">
                           <div class="pr-2">
                               <star-rating
                                   :show-rating="false"
                                   active-color="#F76301"
                                   :increment="0.1"
                                   :star-size="16"
                                   :read-only="true"
                                   :rating="review.star">
                               </star-rating>
                           </div>
                           <div class="my-2" style="background: #dee2e6; width: 1px">
                           </div>
                           <div class="pl-2"
                               style="color: #00000099">
                               @{{ review.dateCreated }}
                           </div>
                       </div>
                       <div>
                           @{{ review.content }}
                       </div>
                   </div>

               </div>
            </div>

            <div v-if="dataPaginate.total > pageSize" class="row no-gutters bg-transparent mx-1 my-3">
                <div class="col-48 d-flex justify-content-center py-2">
                    <pagination class="mb-0" :data="dataPaginate" @pagination-change-page="getReviewList" ></pagination>
                </div>
            </div>
            <div v-if="dataPaginate.total == 0" v-cloak>
                    <div class="d-flex justify-content-center mt-3" >Cửa hàng chưa có đánh giá nào</div>
            </div>
        </div>
        <input id="shopData" hidden="" type="text" data-id="{{isset($shopId) ? $shopId : null}}">
    </div>
@endsection

@push('app-scripts')
    <script src="{{ mix('/js/front/shop/shop-review.js') }}"></script>
    <!-- <script async defer src="https://maps.googleapis.com/maps/api/js?key={{config('app.maps_api_key')}}&callback=initGoogleMapSuccess"></script> -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAE7zocVnayiUt3EeLFosK0OaL9EYV46l8&callback=initGoogleMapSuccess&libraries=places"></script>
    <script type="text/javascript">
        function initGoogleMapSuccess() {
            $(document).trigger('initGoogleMapSuccess');
            $(document).data('initGoogleMapSuccess', true);
        }
    </script>
@endpush
