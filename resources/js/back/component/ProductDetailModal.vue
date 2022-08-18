<template>
    <b-modal
        size="lg"
        v-model="isShow"
        no-close-on-esc no-close-on-backdrop
        busy:hide-header-close="processing"
        :title="$t('Thông tin sản phẩm')"
        header-class=""
        @hidden=""
    >
        <template v-slot:default>
            <b-form v-if="productInfo">
                <b-row class="mb-2">
                    <b-col cols="48">
                        <div align="center">
                            <template>
                                <div class="conditional-rendering">
                                    <div class="block-1" v-if="disableVideo == false">
                                        <b-carousel
                                            indicators
                                            controls
                                        >
                                            <b-carousel-slide v-for="(key, index) in productInfo.image" :key="index">
                                                <template #img>
                                                    <div style="width: 500px">
                                                        <img :src="key.path" width="100%">
                                                    </div>
                                                </template>
                                            </b-carousel-slide>
                                        </b-carousel>
                                    </div>
                                    <div class="block-2" v-else>
                                        <div v-if="!productInfo.video || productInfo.video.length == 0"
                                             class="col-48 d-flex justify-content-center"
                                        >
                                            <span style="font-size: 16px"> Sản phẩm này chưa có video </span>
                                        </div>
                                        <div v-else-if="productInfo.video[0].type == EVideoType.YOUTUBE_VIDEO" >
                                            <youtube-embed class="youtube-wrapper" :autoplay="false"
                                                           :youtube-id="getYoutubeId(productInfo.video[0].path_to_resource)" />
                                        </div>
                                        <div v-else-if="productInfo.video[0].type == EVideoType.INTERNAL_VIDEO">
                                            <video
                                                id="video" controls
                                                style="width: 100%;max-height: 520px"
                                                :src="productInfo.video[0].path">
                                            </video>
                                        </div>
                                        <div v-else>
                                            <iframe
                                                style="height: 900px"
                                                width="100%"
                                                id="video-tiktok" :src="productInfo.video[0].path">
                                            </iframe>
                                        </div>
                                    </div>
                                    <div class="col-48 d-flex justify-content-center" style="padding: 10px;">
                                        <button
                                            type="button"
                                            :class="{'bg-black': disableVideo}"
                                            class="btn btn-primary rounded-pill border-0 mx-1"
                                            @click="disableVideo = true"
                                        >
                                            <img class="mr-2 mb-1" src="/images/icon/play-arrow-white.svg" width="12px">
                                            <span class="text-white">Video</span>
                                        </button>
                                        <button
                                            type="button"
                                            :class="{'bg-black': !disableVideo}"
                                            class="btn btn-primary rounded-pill border-0 mx-1"
                                            @click="disableVideo = false"
                                        >
                                            <img class="mr-2 mb-1" src="/images/icon/insert-photo-white.svg" width="12px"><span class="text-white">Hình ảnh</span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <h4>Chi tiết sản phẩm</h4><hr>
                        <div>
                            <table text-align="left" style="width: 60%" v-if="productInfo">
                                <tr>
                                    <td>{{$t('Tên sản phẩm')}} </td>
                                    <td>{{ productInfo.name }}</td>
                                </tr>
                                <tr>
                                    <td>{{$t('Mã sản phẩm')}} </td>
                                    <td>{{ productInfo.code }}</td>
                                </tr>
                                <tr>
                                    <td>{{$t('Giá')}} </td>
                                    <td>{{ productInfo.priceStr }} đ/{{ productInfo.unit }}</td>
                                </tr>
                                <tr>
                                    <td>{{$t('Ngày tạo')}} </td>
                                    <td>{{ productInfo.createdAt }}</td>
                                </tr>
                                <tr v-for="item in productInfo.attribute">
                                    <td>
                                        {{item.attributeName}}
                                    </td>
                                    <td>
                                        <span v-for="(val, index) in item.value">
                                            {{val}}<span v-if="index != item.value.length - 1">, </span>
                                        </span>
                                    </td>
                                </tr>
                            </table><br>
                        </div>
                        <div>
                            <h4>{{$t('Mô tả')}}</h4><hr>
                            <div style="white-space: break-spaces; word-break: break-all;" v-html="productInfo.description"></div>
                        </div>
                    </b-col>
                </b-row>
            </b-form>
        </template>
        <template v-slot:modal-footer="{ hide }">
            <b-button variant="outline-primary" @click="hide('forget')">{{ $t('Đóng') }}</b-button>
        </template>
    </b-modal>
</template>

<script>

import Util from '../../lib/common'
import EErrorCode from "../../constants/error-code";
import DateUtil from "../../lib/date-utils";
import EVideoType from "../../constants/video-type";
import YoutubeEmbed from '../component/YoutubeEmbed.vue';

export default {
    name: "ProductDetailModal",
    components: {
        YoutubeEmbed,
    },
    props: ['infoFromParent'],
    inject: ['StringUtil'],
    data() {
        return {
            productInfo: null,
            isShow: false,
            disableVideo: false,
            EVideoType,
        }
    },

    computed: {
        routes() {
            return {
                get: `/api/back/product/${this.infoFromParent.productId}/detail`
            }
        }
    },

    created() {
        // this.getOrderInfo();
    },

    watch: {
        infoFromParent(val) {
            console.log('--------------val', val);
            if(!this.infoFromParent.productId ||
                !this.infoFromParent.isShowProductDetailModal) {
                return false;
            }
            this.isShow = this.infoFromParent.isShowProductDetailModal;
            if(this.productInfo) {
                if(this.infoFromParent.productId == this.productInfo.productId) {
                    return;
                }
            }
            this.getProductInfo();
        },
        isShow(val) {
            this.$emit('isShowChanged', val)
        }
    },

    methods: {
        getProductInfo() {
            Util.loadingScreen.show();
            Util.get({
                url: this.routes.get,
            }).done(response => {
                if (response.error !== EErrorCode.NO_ERROR) {
                    this.Util.showMsg2(response);
                    return false;
                }
                this.productInfo = response.product;
                let date = new Date(this.productInfo.createdAt);
                this.productInfo.createdAt = DateUtil.getDateString(date, '/', false);
                if(this.productInfo.video.length > 0 && this.productInfo.video[0].type == EVideoType.TIKTOK_VIDEO) {
                    this.productInfo.video[0].path = this.getTikTokEmbedLink(this.productInfo.video[0].path);
                }

            }).always(() => {
                Util.loadingScreen.hide();
            });
        },
        getYoutubeId(src) {
            return this.StringUtil.getYoutubeVideoId(src);
        },
        getTikTokEmbedLink(src) {
            if (src != null && src != '') {
                return 'https://www.tiktok.com/embed/v2/' + this.StringUtil.getTikTokVideoId(src);
            }
            return null;
        },
    },
}
</script>

<style scoped>

</style>
