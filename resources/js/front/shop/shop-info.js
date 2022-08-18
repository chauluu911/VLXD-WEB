'use strict';

import EErrorCode from "../../constants/error-code";
import EStatus from "../../constants/status";
import EShopPaymentStatus from "../../constants/shop-payment-status";
import ProductItem from '../components/ProductItem.vue';
import EApprovalStatus from "../../constants/approval-status";
import * as Pagination from 'laravel-vue-pagination';
import ShopToolBar from '../components/ShopToolBar.vue';
import StarRating from 'vue-star-rating';
import EResourceType from "../../constants/resource-type";
import EVideoType from "../../constants/video-type";

let getNotificationTimeout = null;
const app = new Vue({
	el:'#shop-info',
    components: {
        ProductItem,
        ShopToolBar,
        StarRating,
        Pagination
    },
    data() {
        return {
            seeMore: false,
            map: null,
            arrMarker: [],
            shop: {},
            products: {},
            iconLoad: false,
            followers: {
                listFollower: [],
                dataPaginate: null,
                page: 1,
                pageSize: 20,
            },

            EStatus,
            EVideoType,
            EShopPaymentStatus,
            EResourceType,
            EApprovalStatus,
            selectedFile: null,
            shopId: $('#shopData').data('id'),
            hrefUpgrade: null,
            paging: 1,
            pageSize: 20,
            show: false,
            video: {
                index: 0,
                value: []
            },
            imageList: {
                index: 0,
                value: []
            }
        }
    },
    created() {
        try {
            $(document).on('initGoogleMapSuccess', () => {
                if (this.shop.latitude) {
                    this.initMap();
                }
            });
        } catch(e) {
            window.location.reload();
        }
        this.getListFollower();
    },
    mounted() {
        let listFollowerEL = this.$refs.listFollowerEL;
        $(listFollowerEL).scroll(() => {
            if (listFollowerEL.scrollHeight - listFollowerEL.clientHeight === this.$refs.listFollowerEL.scrollTop  && !this.iconLoad) {
                this.loadMoreFollower();
            }
        });
        this.getInfoShop();
        this.getProductList();
    },
    methods: {
        getInfoShop() {
            common.get({
                url: `/shop/${this.shopId}/info`,
            }).done(response => {
                if (response.error == EErrorCode.ERROR) {
                    return;
                }
                this.shop = response.shop;
                this.video.value = this.shop.videos;
                this.video.value.forEach(async (item) => {
                    if(item.videoType === EVideoType.YOUTUBE_VIDEO) {
                        item.path = this.getYoutubeEmbedLink(item.path);
                        item.thumbnail = this.getYoutubeThumbnail(item.path)
                    }
                    if(item.videoType === EVideoType.TIKTOK_VIDEO) {
                        let thumbnail = await this.getTikTokThumbnail(item.path);
                        this.$set(item, 'thumbnail', thumbnail);
                        item.path = this.getTikTokEmbedLink(item.path);
                    }
                });
                this.imageList.value = this.shop.images;
                this.initMap();
            }).always(() => {
            });
        },
        initMap() {
            let myLatlng = new google.maps.LatLng(this.shop.latitude, this.shop.longitude);
            let mapOptions = {
                zoom: 9,
                center: myLatlng,
                mapTypeId: 'roadmap',
            };
            this.map = new google.maps.Map(document.getElementById('map'),
                mapOptions);

            let marker = new google.maps.Marker({
                position: myLatlng,
                map: this.map,
            });

            this.arrMarker.push(marker);

            let infowindow = new google.maps.InfoWindow;
            infowindow.setContent(this.shop.address);
            infowindow.open(this.map, marker);

            marker.addListener('click', () => {
                infowindow.open(this.map, marker);
            });
        },
        editShop() {
            common.loadingScreen.show('body');
            window.location.assign("/shop/" + this.shopId + '/edit');
        },
        getProductList(paging) {
            if (paging == 0 || paging > this.products.last_page) {
                return false;
            }
            common.loadingScreen.show('body');
            common.get({
                url: '/shop/' + this.shopId + '/product/get',
                data: {
                    filter: {
                        approvalStatus: EApprovalStatus.APPROVED,
                        getForShop: true,
                        shopId: this.shopId,
                        orderBy: 'publish_at',
                        orderDirection: 'desc'
                    },
                    pageSize: this.pageSize,
                    page: paging ? paging : this.paging,
                }
            }).done(response => {
                if (paging) {
                    this.paging = paging;
                }
                if (response.error == EErrorCode.ERROR) {
                    return;
                }
                this.products = response.products;
                this.hrefUpgrade = '/payment/shop/' + this.shopId;
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        redirectTo() {
            common.loadingScreen.show('body');
            if (this.shop.status == EStatus.WAITING) {
                common.loadingScreen.hide('body');
                return
            } else if(this.shop.payment_status == EShopPaymentStatus.NOT_VERIFY) {
                window.location.assign("/shop/" + this.shopId + '/edit');
            } else {
                window.location.assign("/shop/" + this.shopId + "/product");
            }
        },
        nexrOrPreVideo(value){
            this.video.index += value;
        },
        nextOrPreImg(value){
            this.imageList.index += value;
        },
        showModal() {
            this.show = true;
            $('#modal-video').modal('show');
        },
        closeModal() {
            this.show = false;
            $('#modal-video').modal('hide');
        },
        followShop(follow) {
            this.shop.isFollowed = follow;
            common.post({
                url: '/shop/' + this.shopId + '/follow',
            }).done((res) => {
                this.getInfoShop();
            })
        },
        showModalFollower() {
            $('#modal-follower').modal('show');
        },
        getListFollower() {
            this.iconLoad = true;
            common.get({
                url: '/shop/' + this.shopId + '/follow',
                data: {
                    page: this.followers.page,
                    pageSize: this.followers.pageSize,
                }
            }).done(response => {
                if(response.error !== EErrorCode.NO_ERROR) {
                    common.showMsg2(response);
                    return;
                }
                response.followers.data.forEach(follower => {
                    this.followers.listFollower.push(follower);
                })
                this.followers.dataPaginate = response.followers;
            }).always(() => {
                this.iconLoad = false;
            });
            // for(let i=0;i<30;i++){
            //     let follower = {
            //         name:'Nguyễn Văn Hoàng Minh Tuấn' + i,
            //         avatar: 'https://vlxdst.bootech.vn/shop/41/avatar/2011/mu6YV76baPbPqEbcT3YPB4DYSErDev.jpg'
            //     }
            //     this.listFollower.push(follower);
            // }
        },
        loadMoreFollower() {
            if(this.followers.dataPaginate.to < this.followers.dataPaginate.total && this.followers.dataPaginate.to ) {
                this.followers.page += 1;
                this.getListFollower();
            }
        },
        zaloRedirectTo(link) {
            if (link) {
                window.location.assign(link);
            } else {
                bootbox.alert('Cửa hàng chưa đăng ký zalo');
            }
        },
        redirectToMessage() {
            window.location.assign(`/messenger/${this.shop.user_id}`);
        },
        getYoutubeEmbedLink(src) {
            if (src != null && src != '') {
                return 'https://youtube.com/embed/' + window.stringUtil.getYoutubeVideoId(src) + '?enablejsapi=1';
            }
            return null;
        },
        getYoutubeThumbnail(src) {
            if (src != null && src != '') {
                return 'https://img.youtube.com/vi/' + window.stringUtil.getYoutubeVideoId(src) + '/0.jpg';
            }
            return null;
        },
        getTikTokEmbedLink(src) {
            if (src != null && src != '') {
                return 'https://www.tiktok.com/embed/v2/' + window.stringUtil.getTikTokVideoId(src) ;
            }
            return null;
        },
        async getTikTokThumbnail(src) {
            let a = Object.assign({}, $.ajaxSettings.headers);
            delete $.ajaxSettings.headers;
            let getTiktokResource = $.ajax({
                url: `https://www.tiktok.com/oembed?url=${src}`,
                method: 'GET',
            }).done((result) => {
                return result;
            }).fail(() => false);
            $.ajaxSettings.headers = a;
            let result = await getTiktokResource;
            if(result) {
                return result.thumbnail_url;
            }
            return false;
        }
    }
});
