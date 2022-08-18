<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <div>
                        <div style="color: #000000DE; padding-left: 6px" class="py-2">
                            Hình ảnh
                        </div>
                        <div v-if="imageList.value.length>0" class="w-100 border-0 card-deck m-0">
                            <div
                                v-for="(item,index) in imageList.value"
                                @click="showImgModal(index)"
                                :img="item.src"
                                :style="{'background-image': `url(${item.src})`}"
                                style="max-width: calc(100% / 6  - 12px);
                                border-radius: 4px;
                                background-position: center;
                                background-size:cover;
                                background-repeat: no-repeat;
                                min-width: calc(100% / 6  - 12px);
                                height: 0px;
                                padding-bottom: calc(100% / 6  - 12px);
                                margin: 6px;
                                "
                            >
                            </div>
                        </div>
                        <div v-else class="px-2" style="opacity: 0.5">
                            Chưa có hình ảnh nào để hiển thị
                        </div>
                    </div>
                    <div class="pt-4">
                        <div style="color: #000000DE; padding-left: 6px" class="py-2">
                            Video
                        </div>
                        <div v-if="videoList.value.length > 0" class="w-100 border-0 card-deck m-0 mb-5">
                            <div v-for="(item,index) in videoList.value"
                                 style="max-width: calc(100% / 4  - 12px);
                                 min-width: calc(100% / 4  - 12px);
                                 margin: 6px;">
                                <template v-if="item.typeVideo === EVideoType.INTERNAL_VIDEO">
                                    <div style="width: 100%; display: block;position: relative;
                                    padding-top: 56.25%;"
                                    >
                                        <video
                                            :src="item.src"
                                            style="object-fit: cover;display: block;
                                         position: absolute;width: 100%;
                                         border-radius: 4px;
                                         height: 100%;top: 0;left: 0;">
                                        </video>
                                        <div class="position-absolute div-faded"
                                             @click="showVideoModal(index)"
                                             style="z-index: 1;width:100%;height: 100%;
                                         border-radius: 4px;
                                         background: #0000004D; top:0%"
                                        >
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <div style="width: 100%; display: block;position: relative;
                                                padding-top: 56.25%;"
                                    >
                                        <img style="display: block;position: absolute;width: 100%;border-radius: 4px;
                                                    height: 100%;top: 0;left: 0;padding: 0;"
                                             :src="item.thumbnail"
                                        >
                                        <div class="position-absolute div-faded"
                                             style=" z-index: 1;width:100%;height: 100%;border-radius: 4px;
                                             background: #0000004D;top:0; width: 100%;cursor: pointer"
                                             @click="showVideoModal(index)"
                                        >
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div v-else class="px-2" style="opacity: 0.5">
                            Chưa có video nào để hiển thị
                        </div>
                    </div>
                </div>
            </b-col>
        </b-row>
        <b-modal
            size="lg"
            v-model="isShowImgModal"
            hide-footer
            hide-header
            dialog-class="resource-shop-modal-dialog"
            content-class="w-100 resource-shop-modal-content-image"
            body-class="resource-shop-modal-content-image p-0"
        >
<!--            <template v-slot:default>-->
<!--                <div style="width: fit-content">-->
<!--                    <div class="row">-->
<!--                        <div class="col-md-48">-->
<!--                            <div id="carouselExampleIndicators"-->
<!--                                 class="carousel slide"-->
<!--                                 data-ride="carousel"-->
<!--                                 data-interval="false">-->
<!--                                <div class="carousel-inner">-->
<!--                                    <div-->
<!--                                        class="carousel-item"-->
<!--                                        :class="{'active': imageList.index == index}"-->
<!--                                        v-for="(item, index) in imageList.value"-->
<!--                                    >-->
<!--                                        <img  style="width: auto; max-height: 530px; height: auto ; max-width: 100%"-->
<!--                                              :src="item.src">-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <button class="btn rounded-pill border position-absolute bg-white p-0"-->
<!--                        style="top: 48%;left: 0;height: 24px;width: 24px;z-index: 1"-->
<!--                        v-if="imageList.index > 0" @click="nextOrPreImg(-1)">-->
<!--                    <i class="fas fa-chevron-left" style="font-size: 12px"></i>-->
<!--                </button>-->
<!--                <button class="btn rounded-pill border position-absolute bg-white p-0 btn-next-video"-->
<!--                        style="top: 48%;right: 0;height: 24px;width: 24px;z-index: 1"-->
<!--                        v-if="imageList.index < imageList.value.length - 1" @click="nextOrPreImg(1)">-->
<!--                    <i class="fas fa-chevron-right" style="font-size: 12px"></i>-->
<!--                </button>-->
<!--            </template>-->
            <template v-slot:default>
                <div class="row w-100" style="height: 95%;" >
                    <div class="col-md-30 main-image-modal">
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
            </template>
        </b-modal>
        <b-modal
            v-model="isShowVideoModal"
            hide-footer
            hide-header
            dialog-class="resource-shop-modal-dialog"
            body-class="d-flex flex-column p-0"
            content-class="bg-black"
        >
            <template v-slot:default>
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
                            id="video-tiktok" :src="videoList.value[videoList.index].src">
                        </iframe>
                    </template>
                </div>
            </template>
        </b-modal>
    </div>
</template>

<script>
import {mapState} from "vuex";
import shopResourceManage from "../../locales/back/shop/shop-resource";
import EShopResourceType from "../../constants/shop-resource-type";
import EErrorCode from "../../constants/error-code";
import EOrderStatus from '../../constants/order-status';
import ImageCropper from "../../components/ImageCropper";
import EVideoType from "../../constants/video-type";

export default {
    inject: ['Util', 'StringUtil', 'DateUtil'],
    i18n: {
        messages: shopResourceManage
    },
    components :{
        ImageCropper,
    },
    props: {
        customerType: {
            type: Number,
            default: null
        },
        userType: {
            type: Number,
            default: null
        },
    },
    data() {
        return {
            imageList:{
                index : 0,
                value: []
            },
            videoList: {
                index: 0,
                value: []
            },
            isShowImgModal: false,
            isShowVideoModal: false,
            imgModal: null,
            videoModal: null,
            filter: {
                q: null,
            },
            pagination: {
                page: 1,
                size: null,
            },
            loading: false,
            processing: false,
            deleting: {},
            EErrorCode,
            EOrderStatus,
            EVideoType,
        }
    },
    computed: {
        ...mapState(['filterValueState', 'queryFilterState']),
    },
    created() {
        this.breadCrumb();
        this.resetFilter(this.$route);
        this.filterState();
        this.searchResource({page: 1, size: this.sz});
    },
    methods: {
        resetFilter(route) {
            this.$store.commit('updateQueryFilterState', {
                enable: false,
            });
            this.$store.commit('updateFilterValueState', {
                q: route.query.q,
            });
        },
        filterState() {
            this.$store.commit('updateFilterFormState', []);
        },
        breadCrumb() {
            this.$store.commit('updateBreadcrumbsState', [
                {
                    text: this.$t('object_name'),
                    to: {name: 'shop.image', params:{shopId: this.$route.params.shopId}}
                },
            ]);
        },
        showImgModal(index) {
            this.imageList.index = index;
            this.isShowImgModal = true;
        },
        showVideoModal(index) {
            this.videoList.index = index;
            this.isShowVideoModal = true;
        },
        nextOrPreImg(value){
            this.imageList.index += value;
        },
        async searchResource(paging) {
            // for(let i = 0 ; i< 4; i++) {
            //     let image = {
            //         src : 'https://vlxdst.bootech.vn//product/2010/EJ6x4Fk3kPrrFQRbmMyVxmuMxRYfSJ.jpg'
            //     }
            //     let image2 = {
            //         src: 'https://vlxdst.bootech.vn/product/2011/pCTMCXfPG36kGHekgSsXatQC2wm8RM.jpg'
            //     }
            //     let image3 = {
            //         src: 'https://vlxdst.bootech.vn/product/2011/qBHXaDp2xcHvxsfz2aCmKbZxBvjaZW.jpg'
            //     }
            //     let image4 = {
            //         src: 'https://vlxdst.bootech.vn/product/2011/TQpzerbhNDxj4NeewbXFEHjyG8cq4Q.jpg'
            //     }
            //     let image5 = {
            //         src: 'https://vlxdst.bootech.vn/product/2011/Ykkg9tgJpU4XEtuDv5znwCy2yZtCGc.jpg'
            //     }
            //     let video = {
            //         src: 'https://vlxdst.bootech.vn/shop/126/avatar/2010/DuCdJ6XcBr3fhTV5neWVKZseKykxDd.mp4'
            //     }
            //     let video2 = {
            //         src: '/images/video1.mp4'
            //     }
            //     this.imageList.value.push(image);
            //     this.imageList.value.push(image2);
            //     this.imageList.value.push(image3);
            //     this.imageList.value.push(image4);
            //     this.imageList.value.push(image5);
            //     this.videoList.value.push(video);
            //     this.videoList.value.push(video2);
            // }
            this.loading = true;
            this.Util.post({
                url: `${this.$route.meta.baseUrl}/${this.$route.params.shopId}/resource`,
            }).done(response => {
                if (response.error == EErrorCode.ERROR) {
                    this.Util.showMsg2(response);
                    return false;
                }
                response.resources.forEach(async (item) => {
                    if(item.type === EShopResourceType.IMAGE) {
                        this.imageList.value.push(item);
                    } else {
                        this.videoList.value.push(item);
                        if(item.typeVideo === EVideoType.YOUTUBE_VIDEO) {
                            item.src = this.getYoutubeEmbedLink(item.src);
                            item.thumbnail = this.getYoutubeThumbnail(item.src)
                        }
                        if(item.typeVideo === EVideoType.TIKTOK_VIDEO) {
                            let thumbnail = await this.getTikTokThumbnail(item.src);
                            this.$set(item, 'thumbnail', thumbnail);
                            item.src = this.getTikTokEmbedLink(item.src);
                        }
                    }
                });
            }).always(() => {
                this.loading = false;
                this.Util.loadingScreen.hide();
            });
        },
        getYoutubeEmbedLink(src) {
            if (src != null && src != '') {
                return 'https://youtube.com/embed/' + this.StringUtil.getYoutubeVideoId(src) + '?enablejsapi=1';
            }
            return null;
        },
        getYoutubeThumbnail(src) {
            if (src != null && src != '') {
                return 'https://img.youtube.com/vi/' + this.StringUtil.getYoutubeVideoId(src) + '/0.jpg';
            }
            return null;
        },
        getTikTokEmbedLink(src) {
            if (src != null && src != '') {
                return 'https://www.tiktok.com/embed/v2/' + this.StringUtil.getTikTokVideoId(src);
            }
            return null;
        },
        async getTikTokThumbnail(src) {
            let a = Object.assign({}, $.ajaxSettings.headers);
            delete $.ajaxSettings.headers;
            let result = await $.ajax({
                url: `https://www.tiktok.com/oembed?url=${src}`,
                method: 'GET',
            }).done((result) => {
                return result;
            })
            $.ajaxSettings.headers = a;
            return result.thumbnail_url;

        }
    }
}
</script>

<style scoped>

</style>
