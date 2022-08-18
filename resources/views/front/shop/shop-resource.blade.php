@extends('front.layout.master')

@push('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/shop.css') }}" type="text/css"/>
@endpush

@section('title')
    Hình ảnh cửa hàng
@endsection

@section('main-id', 'shop-resource')

@section('body-user')
    <div class="container px-0" id="create-shop">
        <div class="row no-gutters shop-resource my-5" v-cloak>
            <div class="col-md-16">
                <shop-tool-bar :shop-id="shopId"
                               current-route-name="{{request()->route()->getName()}}"
                               class="mx-1"></shop-tool-bar>
            </div>
            <div class="col-md-32">
                <div class="card mb-3 shop-resource-body">
                    <div class="card-header p-3 bg-white">
                        <div class="card-title mb-0 font-weight-bold"
                             style="color: #000000DE; font-size: 20px">
                            Hình ảnh cửa hàng
                        </div>
                    </div>
                    <div class="px-2 py-3">
                        <div>
                            <div style="color: #000000DE; padding-left: 6px"
                                 class="py-2 font-weight-bold"
                            >
                                Video
                            </div>
                            <div class="row m-0 p-1">
                                <div class="col-lg-9 col-md-15 col-24 p-0 ">
                                    <input type="file" id="video-upload" name="video-upload"
                                           accept="video/*" @change="onSelectVideo"
                                           style="display: none;"
                                    >
                                    <label for="video-upload" class="px-3 py-2 d-flex m-0 justify-content-center"
                                           style="background: #00000014;
                                            border-radius: 4px; cursor: pointer"
                                    >
                                        <img src="/images/icon/upload.svg" alt="upload" style="width: 20px">
                                        <span class="px-2">Tải lên</span>
                                    </label>
                                </div>
                                <div class="upload-video-with-link d-flex justify-content-between col-lg-39 col-md-33 col-48 p-0">
                                    <input type="text"
                                           class="w-100 px-3 ml-5"
                                           v-model="newVideoLink"
                                           placeholder="Nhập link(youtube, tiktok)"
                                           style="border:1px solid #0000001A;border-radius: 4px;"
                                    >
                                    <div class="px-3 py-2"
                                         style="background: #00000014;border-radius: 4px;"
                                         @click="uploadVideoLink"
                                    >
                                        Thêm
                                    </div>
                                </div>

                            </div>
                            <div v-if="permission.videoIntroduce" class="row m-0 p-1" style="font-size: 12px; color: #00000061">
                                Bạn có thể đăng tối đa @{{permission.videoIntroduce.num_video}} video
                                .Hỗ trợ file video mp4. Thời lượng tối đa @{{permission.videoIntroduce.upload_time}}s
                            </div>
                            <div class="w-100 row border-0 card-deck m-0 video-list">
                                <div v-if="videoList.value.length > 0"
                                    v-for="(item,index) in videoList.value"
                                     class="video-item"
                                     >
                                    <template v-if="item.typeVideo === EVideoType.INTERNAL_VIDEO">
                                        <div class="video-frame" >
                                            <video
                                                :src="item.src"
                                            >
                                            </video>
                                            <div class="position-absolute div-faded video-blur"
                                                 @click="showVideoModal(index)"
                                            >
                                            </div>
                                            <span class="position-absolute"
                                                  style="z-index: 2;top:1px; right: 1px; font-size: 12px;cursor: pointer"
                                                  @click="deleteResource(item)"
                                            >
                                                <img class="rounded-pill bg-white" alt="cancel" src="/images/icon/cancel-24px.svg">
                                            </span>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div class="video-frame" >
                                            <img class="img-thumbnail" :src="item.thumbnail" alt="Takamart">
                                            <div class="position-absolute div-faded video-blur"
                                                 @click="showVideoModal(index)"
                                            >
                                            </div>
                                            <span class="position-absolute"
                                                  style="z-index: 2;top:1px; right: 1px; font-size: 12px; cursor: pointer"
                                                  @click="deleteResource(item)"
                                            >
                                                <img class="rounded-pill bg-white" alt="cancel" src="/images/icon/cancel-24px.svg">
                                            </span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-2 py-3">
                        <div class="image-list">
                            <div style="color: #000000DE; padding-left: 6px"
                                 class="py-2 font-weight-bold"
                            >
                                Hình ảnh
                            </div>
                            <div class="row m-0 p-1">
                                <div class="col-lg-9 col-md-15 col-24 p-0 ">
                                    <input type="file" id="image-upload" name="image-upload"
                                           accept="image/*" @change="onSelectImage"
                                           style="display: none;"
                                    >
                                    <label for="image-upload" class="px-3 py-2 d-flex m-0 justify-content-center"
                                           style="background: #00000014;
                                            border-radius: 4px; cursor: pointer"
                                    >
                                        <img src="/images/icon/upload.svg" alt="upload" style="width: 20px">
                                        <span class="px-2">Tải lên</span>
                                    </label>
                                </div>

                            </div>
                            <div v-if="permission.imageIntroduce" class="row m-0 p-1" style="font-size: 12px; color: #00000061">
                               Bạn có thể đăng tối đa @{{permission.imageIntroduce.num_image}} ảnh. Hỗ trợ file ảnh .JPG .JPEG .PNG
                            </div>
                            <div class="w-100 row border-0 card-deck m-0">
                                <div
                                    v-for="(item,index) in imageList.value"
                                    class="image-item"
                                >
                                    <div class="image-frame"
                                        @click="showImgModal(index)"
                                         :style="{'background-image': `url(${item.src})`}"
                                    >
                                    </div>
                                    <span class="image-delete-button"
                                          @click="deleteResource(item)"
                                    >
                                        <img class="rounded-pill bg-white" alt="cancel" src="/images/icon/cancel-24px.svg">
                                    </span>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="modal div-faded" id="video-modal" data-backdrop="static"
                     role="dialog" aria-hidden="true"
                >
                    <div class="modal-dialog"
                         style="max-width: 80%; margin: auto;"
                         role="document"
                    >
                        <div class="modal-content bg-black mt-2" v-if="videoList.value.length > 0 ">
                            <div class="modal-header border-0">
                                <button type="button" class="close" @click="closeVideoModal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <div v-if="isShowVideoModal">
                                <template v-if="videoList.value[videoList.index].typeVideo === EVideoType.INTERNAL_VIDEO">
                                    <video
                                        id="video" controls
                                        style="width: 100%;max-height: 520px"
                                        :src="videoList.value.length > 0 ?
                                        videoList.value[videoList.index].src : ''">
                                    </video>
                                </template>
                                <template v-else-if="videoList.value[videoList.index].typeVideo === EVideoType.YOUTUBE_VIDEO">
                                    <iframe
                                        id="video-yt" width="100%" height="520" :src="videoList.value[videoList.index].src"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write;
                                    encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen>
                                    </iframe>
                                </template>
                                <template v-else>
                                    <iframe
                                        style="height: 900px"
                                        width="100%"
                                        id="video-tiktok" :src="videoList.value[videoList.index].src">
                                    </iframe>
                                </template>
                            </div>


                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="modal" id="image-modal" data-keyboard="true"
                     role="dialog" aria-hidden="true"
                >
                    <div class="modal-dialog image-modal-dialog"
                         role="document"
                    >
                        <div class="modal-content w-100 bg-white  mt-2"
                             style="display: flex;justify-content: center;flex-direction: row;border: 0;height: 100%; align-items: center"
                        >
                            <div class="row w-100" style="height: 95%;" >
                                <div class="col-md-30 main-image-modal">
{{--                                    <div id="carouselExampleIndicators" style="height: 100%"--}}
{{--                                         class="carousel slide"--}}
{{--                                         data-ride="carousel"--}}
{{--                                         data-interval="false">--}}
{{--                                        <div class="carousel-inner" style="height: 100%">--}}
{{--                                            <div--}}
{{--                                                class="carousel-item" style="height: 100%"--}}
{{--                                                :class="{'active': imageList.index == index}"--}}
{{--                                                v-for="(item, index) in imageList.value"--}}
{{--                                            >--}}
{{--                                                <div--}}
{{--                                                     :style="{'background-image': `url(${item.src})`}"--}}
{{--                                                     style="height: 100%;--}}
{{--                                                     background-repeat: no-repeat;--}}
{{--                                                     background-position: center;--}}
{{--                                                     background-size: contain;"--}}
{{--                                                >--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                    <div v-if="imageList.value.length > 0"
                                         :style="{'background-image': `url(${imageList.value[imageList.index].src})`}"
                                         style="height: 100%;
                                         background-repeat: no-repeat;
                                         background-position: center;
                                         background-size: contain;"
                                    >
                                    </div>
                                    <button class="btn position-absolute p-0 btn-pre-img"
                                            v-if="imageList.index > 0" @click="nextOrPreImg(-1)">
                                        <i class="fas fa-chevron-left" style="font-size: 14px; color: white;"></i>
                                    </button>
                                    <button class="btn position-absolute p-0 btn-next-img"
                                            style="z-index: 999999"
                                            v-if="imageList.index < imageList.value.length - 1" @click="nextOrPreImg(1)">
                                        <i class="fas fa-chevron-right" style="font-size: 14px; color: white;"></i>
                                    </button>
                                </div>
                                <div class="col-md-18" style="max-height: 100%;">
                                    <div class="w-100 border-0 m-0 image-list-modal"
                                         style="margin-top: -4px !important;">
                                        <div
                                            v-for="(item,index) in imageList.value"
                                            class="image-item-modal"
                                        >
                                            <div class="image-frame-modal"
                                                 :class="{'image-frame-modal-active': index === imageList.index}"
                                                 @click="imageList.index = index"
                                                 :style="{'background-image': `url(${item.src})`}"
                                            >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="modal fade p-0"
             id="no-permission-warning"
             tabindex="-1"
             role="dialog"
             aria-labelledby="exampleModalCenterTitle"
             aria-hidden="true"
             data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content p-3" style="max-width: 415px">
                    <div class="modal-body p-2">
                        <div class="row">
                            <div class="col-48 text-center">
                                <p class="mb-0 font-size-16px font-weight-bold">
                                    Vui lòng nâng cấp để thực hiện chức năng này !
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row no-gutters">
                        <div class="col-md-24 p-2">
                            <a data-dismiss="modal" class="btn btn-outline-primary w-100">Bỏ qua</a>
                        </div>
                        <div class="col-md-24 p-2">
                            <a :href="'/shop/' + shopId + '/upgrade'" class="btn btn-primary w-100">Nâng cấp shop</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade p-0"
         id="uploadLimit"
         tabindex="-1"
         role="dialog"
         aria-labelledby="exampleModalCenterTitle"
         aria-hidden="true"
         data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content p-3" style="max-width: 415px">
                <div class="modal-body p-2">
                    <div class="row">
                        <div class="col-48 text-center">
                            <p v-if="typeOfResourceUploading === 'image'" class="mb-0 font-size-16px font-weight-bold">
                                Số lượng hình ảnh cho phép cửa hàng tải lên đã hết, vui lòng nâng cấp để tải thêm !
                            </p>
                            <p v-else class="mb-0 font-size-16px font-weight-bold">
                                Số lượng video cho phép cửa hàng tải lên đã hết, vui lòng nâng cấp để tải thêm !
                            </p>
                        </div>
                    </div>
                </div>
                <div class="row no-gutters">
                    <div class="col-md-24 p-2">
                        <a data-dismiss="modal" class="btn btn-outline-primary w-100">Bỏ qua</a>
                    </div>
                    <div class="col-md-24 p-2">
                        <a :href="'/shop/' + shopId + '/upgrade'" class="btn btn-primary w-100">Nâng cấp shop</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <input id="shopData" hidden="" type="text" data-id="{{isset($shopId) ? $shopId : null}}">
    </div>
@endsection

@push('app-scripts')
    <script src="{{ mix('/js/front/shop/shop-resource.js') }}"></script>
    <script async src="https://www.tiktok.com/embed.js"></script>
    <!-- <script async defer src="https://maps.googleapis.com/maps/api/js?key={{config('app.maps_api_key')}}&callback=initGoogleMapSuccess"></script> -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAE7zocVnayiUt3EeLFosK0OaL9EYV46l8&callback=initGoogleMapSuccess&libraries=places"></script>
    <script type="text/javascript">
        function initGoogleMapSuccess() {
            $(document).trigger('initGoogleMapSuccess');
            $(document).data('initGoogleMapSuccess', true);
        }
    </script>
@endpush
