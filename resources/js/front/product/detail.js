'use strict';

import EErrorCode from "../../constants/error-code";
import ProductItem from '../components/ProductItem.vue';
import EApprovalStatus from "../../constants/approval-status";
import EStatus from "../../constants/status";
import EProductType from "../../constants/product-type";
import EPaymentMethod from "../../constants/payment-method";
import 'vue-slick-carousel/dist/vue-slick-carousel.css';
import 'vue-slick-carousel/dist/vue-slick-carousel-theme.css';
import VueSlickCarousel from 'vue-slick-carousel';
import StarRating from 'vue-star-rating';
import EResourceType from "../../constants/resource-type";

let getNotificationTimeout = null;
const app = new Vue({
	el:'#product-detail',
    components: {
        ProductItem,
        VueSlickCarousel,
        StarRating
    },
    data() {
        return {
            map: null,
            EApprovalStatus,
            EProductType,
            EResourceType,
            EStatus,
            EPaymentMethod,

            code: $('#productData').data('code'),
            isProductOfShop: $('#productData').data('product-of-shop'),
            shopId: $('#productData').data('shop-id'),
            userId: $('#productData').data('user-id'),
            data: {
                area: null,
            },
            imageList: {
                index: 0,
                value: []
            },
            disableVideo: true,
            disableIndicatorOfImageModalBtn: false,
            checkedSubscriptionPrice: {
                subscriptionPriceId: null
            },
            displaySavedLinkNoti: 'none',
            shop: {},
            similarProduct: null,
            permission: {
                num_product_remain: null,
                num_video_introduce_remain: 0,
                num_image_in_product: 0
            },
            dataReport: {
                value: [],
                parentId: null,
                reportChoosed: null,
                content: null,
                errors: {
                    content: null,
                    parentId: null,
                    reportChoosed: null
                }
            },
            isSaved: false,
            arrMarker: [],
            height: null,
            settings: {
                "infinite": false,
                "initialSlide": 0,
                "speed": 500,
                "slidesToShow": 6,
                "slidesToScroll": 1,
                "swipeToSlide": true,
                "draggable": false,
                "responsive": [
                    {
                        "breakpoint": 1024,
                        "settings": {
                            "slidesToShow": 3,
                            "slidesToScroll": 3,
                        }
                    },
                    {
                        "breakpoint": 600,
                        "settings": {
                            "slidesToShow": 2,
                            "slidesToScroll": 2,
                        }
                    },
                    {
                        "breakpoint": 480,
                        "settings": {
                            "slidesToShow": 2,
                            "slidesToScroll": 1,
                        }
                    }
                ],
            }
        }
    },
    created() {
        this.getInfoProduct();
        if(this.shopId) {
            this.getPermissionShop();
        }
    },
    mounted() {
        $('.custom-control-input').prop('indeterminate', true);
        $('#carouselExampleIndicators').on('slide.bs.carousel', (e) => {
            if(this.imageList.index  != e.to) {
                this.imageList.index = e.to;
            }
            this.disableIndicatorOfImageModalBtn = false;
        })
    },
    computed: {
        getYoutubeId() {
            if (this.data.youtubeId) {
                return 'https://youtube.com/embed/' + window.stringUtil.getYoutubeVideoId(this.data.youtubeId);
            }
            return null;
        },
        route() {
            return {
                info: this.shopId ? `/shop/${this.shopId}/product/info` : '/product/info',
            }
        },

    },
    updated() {
        if (this.similarProduct) {
            Object.keys(this.similarProduct).forEach((item, index) => {
                if ($('#product' + this.index).height() > 22) {
                    this.height = $('#product' + this.index).height();
                }
            })
        }
    },
    methods: {
        getPermissionShop() {
            common.loadingScreen.show();
            common.post({
                url: '/shop/' + this.shopId + '/permission',
            }).done(response => {
                this.permission = response.permission;
            })
            .always(() => {
                common.loadingScreen.hide();
            });
        },
        showMap() {
            $('#map-modal').modal('show');
            let myLatlng = new google.maps.LatLng(this.data.latitude ? this.data.latitude : 10.777788619129023,
                this.data.longitude ? this.data.longitude : 106.69668674468994);
            let mapOptions = {
                zoom: 15,
                center: myLatlng,
                mapTypeId: 'roadmap',
            };
            this.map = new google.maps.Map(document.getElementById('map'),
                mapOptions);

            this.arrMarker.forEach(marker => {
                marker.setMap(null);
            });

            let marker = new google.maps.Marker({
                position: myLatlng,
                map: this.map,
            });

            this.arrMarker.push(marker);
            let infowindow = new google.maps.InfoWindow;
            infowindow.setContent(this.data.area);
            infowindow.open(this.map, marker);

            marker.addListener('click', () => {
                if (this.data.area) {
                    infowindow.open(this.map, marker);
                }
            });
            if (this.data.area) {
                infowindow.setContent(this.data.area);
                infowindow.open(this.map, marker);
                marker.addListener('click', () => {
                    infowindow.open(this.map, marker);
                });
            }
        },
        getInfoShop() {
            common.loadingScreen.show('body');
            common.get({
                url: `/shop/${this.data.shop.id}/info`,
            }).done(response => {
                if (response.error == EErrorCode.ERROR) {
                    return;
                }
                this.shop = response.shop;
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        getQuestions() {
            common.loadingScreen.show('body');
            common.get({
                url: `/shop/${this.data.shop.id}/info`,
            }).done(response => {
                if (response.error == EErrorCode.ERROR) {
                    return;
                }
                this.shop = response.shop;
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        getInfoProduct() {
            common.loadingScreen.show();
            common.post({
                url: this.route.info,
                data: {
                    code: this.code,
                    getSubscription: true,
                }
            }).done(response => {
                if (response.error === EErrorCode.ERROR) {
                    window.location.assign(response.redirectTo);
                }
                this.data = response.products;
                this.imageList.value = response.products.image;
                if (this.data.video.length > 0) {
                    this.disableVideo = false;
                    if (this.data.video[0].typeVideo == 2) {
                        this.data.video[0].path_to_resource = 'https://youtube.com/embed/' +
                        window.stringUtil.getYoutubeVideoId(this.data.video[0].path_to_resource);
                    }
                    if (this.data.video[0].typeVideo == 3) {
                        this.data.video[0].path_to_resource = 'https://www.tiktok.com/embed/v2/' + window.stringUtil.getTikTokVideoId(this.data.video[0].path);
                    }
                }
                if (this.data.subscription) {
                    let validFrom = new Date(this.data.subscription.valid_from);
                    let validTo = new Date(this.data.subscription.valid_to);
                    this.data.subscription.valid_from = validFrom;
                    this.data.subscription.valid_to = validTo;
                }
                this.isSaved = this.data.isSaved ? true : false;
                this.checkedSubscriptionPrice.subscriptionPriceId = this.data.subscriptionPrices[0].id;
                if (!this.isProductOfShop) {
                    this.getSimilarProduct();
                    this.getInfoShop();
                    this.getQuestions();
                }
            })
            .always(() => {
                common.loadingScreen.hide();
            });
        },
        nextOrPreImg(value){
            if(!this.disableIndicatorOfImageModalBtn) {
                this.disableIndicatorOfImageModalBtn = true;
                this.imageList.index += value;
                $('#carouselExampleIndicators').carousel(this.imageList.index);
            }
        },
        chooseSubscription(index) {
            this.checkedSubscriptionPrice.subscriptionPriceId = this.data.subscriptionPrices[index].id;
        },
        redirectToEdit() {
             window.location.assign('/shop/' + this.shopId + '/product/' + this.data.code);
        },
        getSimilarProduct() {
            common.loadingScreen.show('body');
            common.get({
                url: '/product/get',
                data: {
                    filter: {
                        categoryId: this.data.category.id,
                        notId: this.data.productId,
                        status: EStatus.ACTIVE,
                        getSubscription: true,
                        approvalStatus: EApprovalStatus.APPROVED
                    },
                }
            }).done(response => {
                if (response.error == EErrorCode.ERROR) {
                    return;
                }
                this.similarProduct = response.products.data;
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        addToCart() {
            common.loadingScreen.show('body');
            common.get({
                url: '/product/add-to-cart',
                data: {
                    productId: this.data.productId,
                    shopId: this.data.shop.id,
                    price: this.data.price,
                    quantity: 1,
                    unit: this.data.unit
                }
            }).done(response => {
                if (response.error != EErrorCode.NO_ERROR) {
                    if (response.redirectTo) {
                        window.location.assign(response.redirectTo);
                    }
                }
                bootbox.alert(response.msg, () => {
                    window.location.reload();
                });
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        savePayment() {
            common.loadingScreen.show('body');
            common.get({
                url: `/shop/${this.shopId}/product/${this.code}/payment`,
                data: {
                    productId: this.data.productId,
                    subscriptionPriceId: this.checkedSubscriptionPrice.subscriptionPriceId,
                }
            }).done(response => {
                if (response.error == EErrorCode.ERROR) {
                    return;
                }
                window.location.assign(`/payment/product/${this.data.productId}/${response.subscriptionPriceId}`);
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        redirectToMessage() {
            window.location.assign(`/messenger/${this.shop.user_id}`);
        },
        redirectToMessageWithQuestion(question) {
            if (!this.userId) {
                window.location.assign('/login');
            } else {
                let message = encodeURIComponent(`Sản phẩm [${this.data.name}]: ${question}`);
                let url = `/messenger/${this.shop.user_id}/${message}` ;
                window.location.assign(url);
            }
        },
        showModalReport(item) {
            if (!this.userId) {
                window.location.assign('/login');
                return;
            } else {
                this.dataReport.value = [];
                this.dataReport.parentId = null;
                this.dataReport.reportChoosed = null;
                this.dataReport.value = item;
                this.dataReport.reportChoosed = item.id;
                if (this.dataReport.value.childCategory &&
                    this.dataReport.value.childCategory.length == 0) {
                    this.dataReport.parentId = item.id;
                }
                $('#exampleModal').modal('show');
            }
        },
        report() {
            common.loadingScreen.show('body');
            common.post({
                url: '/product/report',
                data: {
                    productId: this.data.productId,
                    content: this.dataReport.content,
                    parentId: this.dataReport.parentId,
                    reportChoosed: this.dataReport.reportChoosed,
                },
                 errorModel: this.dataReport.errors,
            }).done(response => {
                bootbox.alert(response.msg);
                $('#exampleModal').modal('hide');
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        deleteProduct() {
            bootbox.confirm({
                title: 'Thông báo',
                message: 'Bạn chắc chắn muốn xóa sản phẩm này không',
                buttons: {
                    confirm: {
                        label: 'Xác nhận',
                        className: 'btn-primary',
                    },
                    cancel: {
                        label: 'Không',
                        className: 'btn-default'
                    }
                },
                callback: (result) => {
                    if (result) {
                        common.loadingScreen.show();
                        common.post({
                            data: {
                                id: this.data.productId,
                                shopId: this.shopId
                            },
                            url: '/product/delete',
                        }).done(response => {
                            if (response.error == EErrorCode.NO_ERROR) {
                                bootbox.alert('Xóa sản phẩm thành công', () => {
                                    window.location.assign(response.redirectTo);
                                });
                            }

                        })
                        .always(() => {
                            common.loadingScreen.hide();
                        });
                    }
                }
            });
        },
        interestProduct() {
            if (this.isSaved) {
                this.isSaved = false;
            } else {
                this.isSaved = true;
            }
            common.post({
                url: this.isSaved ? '/product/interest' : '/product/un-interest',
                data: {
                    code: this.data.code
                }
            }).done(response => {
                if (response.redirectTo) {
                    window.location.assign(response.redirectTo);
                    return;
                }
                window.common.showMsg('success', null, this.isSaved ? 'Lưu tin thành công' :
                    'Bỏ lưu tin thành công');
            })
        },
        copyLink() {
            let $temp = $("<input>");
            $("body").append($temp);
            $temp.val(window.location.href).select();
            document.execCommand("copy");
            $temp.remove();
            this.$set(this, 'displaySavedLinkNoti', 'block');
            setTimeout(() => {
                this.$set(this, 'displaySavedLinkNoti', 'none');
            }, 1000);

        },
        showModalImage() {
            $('#modal-image').modal('show');
        },
        redirectToProductList(categoryId, parentCategoryId) {
            window.location.assign(
                stringUtil.getUrlWithQueries('/product', {categoryId: categoryId,
                    parentCategoryId: parentCategoryId, page: 1})
            );
        },
        showModalNotify() {
            $('#modal-notidy').modal('show');
        },
        pushProduct() {
            common.loadingScreen.show();
            common.post({
                data: {
                    'paymentMethod': EPaymentMethod.FREE
                },
                url: `/payment/product/${this.data.productId}/${this.checkedSubscriptionPrice.subscriptionPriceId}`,
            }).done(response => {
                if (response.error == EErrorCode.NO_ERROR) {
                    bootbox.alert('Đẩy tin thành công', () => {
                        window.location.reload();
                    });
                } else {
                   bootbox.alert(response.msg);
                }

            })
            .always(() => {
                common.loadingScreen.hide();
            });
        }
    }
});
