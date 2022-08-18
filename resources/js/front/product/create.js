'use strict';

import EErrorCode from "../../constants/error-code";
import EApprovalStatus from "../../constants/approval-status";
import EProductType from "../../constants/product-type";
import ECategoryDataType from "../../constants/category-data-type";
import ECategoryType from "../../constants/category-type";
import ECategoryValueType from "../../constants/category-value-type";
import OpenIndicator from 'vue-select/src/components/OpenIndicator.vue';

let getNotificationTimeout = null;
const app = new Vue({
	el:'#create-product',
    components: {OpenIndicator},
    data() {
        return {
            EProductType,
            ECategoryDataType,
            ECategoryValueType,
            ECategoryType,

            step: $('#productData').data('code') ? 4 : 1,
            shopId: $('#productData').data('shop-id'),
            formData: this.$_formData(),
            categoryList: {},
            childCategoryList: {},
            attribute: {},
            selectedFile: null,
            isLeavePage: true,
            categoryList: {},
            image: [],
            imageRender: [],
            video: [],
            videoRender: [],
            permission: {
                num_product_remain: 0,
                num_video_introduce_remain: 0,
                num_image_in_product: 0
            },
            genkey: 1,
        }
    },
    created() {
       if (this.shopId) {
            this.getCategory();
            this.getPermissionShop();
       }
    },
    mounted() {
        $('#exampleModalCenter').modal('show');
    },
    watch: {
        "formData.type"() {
            this.onInputChange(this.isLeavePage);
        }
    },
    computed: {
        getYoutubeId() {
            if (this.formData.linkVideo != null && this.formData.linkVideo != '') {
                return 'https://youtube.com/embed/' + window.stringUtil.getYoutubeVideoId(this.formData.linkVideo);
            }
            return null;
        },
    },
    methods: {
        onInputChange(isLeavePage) {
            common.askUserWhenLeavePage(isLeavePage);
        },
        getInfoProduct() {
            common.loadingScreen.show();
            common.post({
                url: 'info',
                data: {
                    code: this.formData.code
                }
            }).done(response => {
                if (response.error === EErrorCode.ERROR) {
                    window.location.assign(response.redirectTo);
                }
                //this.formData.linkVideo = response.products.youtubeId;
                this.formData.name = response.products.name;
                this.formData.type = response.products.type;
                this.formData.unit = response.products.unit;
                this.formData.price = response.products.price;
                this.formData.description = response.products.description;
                if (response.products.category.parent_category_id) {
                    this.formData.category.id = response.products.category.parent_category_id;
                    this.formData.category.childCategoryId = response.products.category.id;
                } else {
                    this.formData.category.id = response.products.category.id;
                }
                response.products.attribute.forEach((item) => {
                    this.formData.category.attribute.push({
                        id: item.id,
                        value: item.value
                    });
                });
                let item = this.categoryList.find((item) => item.id == this.formData.category.id);
                this.attribute = item.attribute;

                response.products.image.forEach((item) => {
                    this.formData.image.push(item.path);
                    this.formData.oldResource.image.push(item.path_to_resource);
                });

                response.products.video.forEach(async (item) => {
                    if (item.type == 2) {
                        this.formData.video.push({
                            type: item.type,
                            src: 'https://img.youtube.com/vi/' + 
                            window.stringUtil.getYoutubeVideoId(item.path_to_resource) + '/0.jpg'
                        });
                    } else if (item.type == 3) {
                        let thumbnail = await this.getTikTokThumbnail(item.path);
                        this.formData.video.push({
                            type: item.type,
                            //src: this.getTikTokEmbedLink(item.path_to_resource),
                            src: !!thumbnail.thumbnail_url ? thumbnail.thumbnail_url : '/images/logo-tiktok.png'
                        });
                    } else {
                        this.formData.video.push({
                            type: item.type,
                            src: item.path
                        });
                    }
                    this.formData.oldResource.video.push(item.path_to_resource);
                });
            })
            .always(() => {
                common.loadingScreen.hide();
            });
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
            let result = $.ajax({
                url: `https://www.tiktok.com/oembed?url=${src}`,
                method: 'GET',
            }).done((result) => {

               return result;
            })
            $.ajaxSettings.headers = a;
            return result;

        },
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
        getCategory() {
            common.loadingScreen.show();
            common.post({
                url: '/category',
                data: {
                    notType: ECategoryType.ISSUE_REPORT
                }
            }).done(response => {
                this.categoryList = response.data;
                if (this.step == 4) {
                    this.getInfoProduct();
                }
            })
            .always(() => {
                common.loadingScreen.hide();
            });
        },
        $_formData() {
            return {
                code: $('#productData').data('code'),
                type: null,
                category: {
                    id: null,
                    name: null,
                    childCategoryId: null,
                    attribute: [],
                },
                image: [],
                video: [],
                oldResource: {
                    image: [],
                    video: [],
                },
                linkVideo: null,
                unit: null,
                description: null,
                name: null,
                price: 0,
                errors: {
                    type: null,
                    image: null,
                    linkVideo: null,
                    unit: null,
                    description: null,
                    name: null,
                    price: null,
                    imageCount: null,
                    videoCount: null,
                    attributeName: null

                }
            }
        },
        nextStep(val) {
            switch (this.step) {
                case 1:
                    this.formData.type = val;
                    this.step ++;
                    break;
                case 2:
                    this.formData.category.id = val.id;
                    let item = this.categoryList.find((item) => item == val);
                    if (this.childCategoryList != item.child_categories) {
                        this.attribute = [];
                        this.formData.category.attribute = [];
                    }
                    this.childCategoryList = item.child_categories;
                    if (this.childCategoryList.length > 0) {
                        this.step ++;
                    } else {
                        this.step = 4;
                    }
                    this.attribute = item.attribute;
                    for (let i = 0; i < this.attribute.length; i++) {
                        this.formData.category.attribute.push({
                            id: item.attribute[i].id,
                            value: []
                        });
                    }
                    break;
                case 3:
                    this.formData.category.childCategoryId = val.id;
                    this.step ++;
                    break;
            }
        },
        back(value) {
            if (value < this.step) {
                this.step = value;
            }
            if (value == 2) {
                this.attribute = [];
                this.formData.category.attribute = [];
            }
        },
        prePage() {
            history.back();
        },
        onSelectImage(e) {
            if (window.File && window.FileList && window.FileReader) {
                let files = e.target.files;
                for (let i = 0; i < files.length; i++) {
                    if (this.formData.image.length + this.image.length >= this.permission.num_image_in_product) {
                        return false;
                    }
                    let fileReader = new FileReader();
                    this.selectedFile = files[i];
                    if (this.selectedFile.type == "image/jpeg" || this.selectedFile.type == "image/png"
                        || this.selectedFile.type == "image/jpg") {
                        this.image.push(this.selectedFile);
                        fileReader.onload = ((e) => {
                            var file = e.target;
                            this.imageRender.push(e.target.result);
                        });
                        fileReader.readAsDataURL(this.selectedFile);
                    }
                }
            }
            this.$refs.fileImageInputEl.value = null;
        },
        onSelectVideo(e) {
            if (window.File && window.FileList && window.FileReader) {
                if (this.video.length == 1) {
                    bootbox.alert('Chỉ được đăng 1 video');
                    return;
                }
                let fileReader = new FileReader();
                let files = e.target.files;
                this.selectedFile = files[0];
                if (this.selectedFile.type == "video/mp4" && this.video.length == 0) {
                    this.video.push({
                        type: 'file',
                        src: this.selectedFile
                    });
                    fileReader.onload = ((e) => {
                        var file = e.target;
                        this.videoRender.push({
                            type: 'file',
                            src: e.target.result
                        });
                    });
                    let durationOfVideo;
                    let video = document.createElement('video');
                    video.preload = 'metadata';
                    video.onloadedmetadata = () => {
                        window.URL.revokeObjectURL(video.src);
                        durationOfVideo = video.duration;
                        if (durationOfVideo > this.permission.video_in_product.upload_time) {
                            this.selectedFile = null;
                            this.video.splice(this.video.length - 1, 1);
                            this.videoRender.splice(this.videoRender.length - 1, 1);
                            bootbox.alert('Video vượt quá ' + this.permission.video_in_product.upload_time + ' giây');
                        }
                    }
                    video.src = URL.createObjectURL(this.selectedFile);
                    fileReader.readAsDataURL(this.selectedFile);
                }
                this.$refs.fileVideoInputEl.value = null;
            }
        },
        removeImage(index) {
            this.$refs.fileImageInputEl.value = null;
            this.image.splice(index, 1);
            this.imageRender.splice(index, 1);
        },
        removeVideo(index) {
            this.$refs.fileVideoInputEl.value = null;
            this.video.splice(index, 1);
            this.videoRender.splice(index, 1);
            //this.renderImage();
            //$("#image-product" + index).text('');
        },
        removeOldImage(index) {
            this.formData.image.splice(index, 1);
            this.formData.oldResource.image.splice(index, 1);
        },
        removeOldVideo(index) {
            this.formData.video.splice(index, 1);
            this.formData.oldResource.video.splice(index, 1);
        },
        saveProduct() {
            // if (this.permission.num_product_remain == 0) {
            //     bootbox.alert('bạn đã hết số lần đăng tin/sản phẩm');
            //     return;
            // }
            common.loadingScreen.show();
            let formData = new FormData();
            Object.keys(this.formData).forEach((key) => {
                switch (key) {
                    case 'category':
                        formData.append('categoryId', this.formData[key].id);
                        if (this.formData[key].childCategoryId) {
                            formData.append('childCategoryId', this.formData[key].childCategoryId);
                        }
                        formData.append('numberOfAttribute', this.formData[key].attribute.length);
                        for (let i = 0; i < this.formData[key].attribute.length; i++) {
                            formData.append(`attribute[id][]`, this.formData[key].attribute[i].id);
                            //formData.append(`attribute[name][]`, this.formData[key].attribute[i].value);
                            formData.append(`attributeName[]`, this.formData[key].attribute[i].value);
                        }
                        break;
                    case 'image':
                        for (let i = 0; i < this.image.length; i++) {
                            formData.append('image[]', this.image[i]);
                        }
                        formData.append('imageCount', this.formData[key].length + this.image.length);
                        break;
                    case 'video':
                        for (let i = 0; i < this.video.length; i++) {
                            formData.append('video[]', this.video[i].src);
                        }
                        formData.append('videoCount', this.formData[key].length + this.video.length);
                        break;
                    case 'oldResource':
                        for (let i = 0; i < this.formData[key].image.length; i++) {
                            formData.append('oldResource[image][]', this.formData[key].image[i]);
                        }
                        for (let i = 0; i < this.formData[key].video.length; i++) {
                            formData.append('oldResource[video][]', this.formData[key].video[i]);
                        }
                        break;
                    default:
                        if (this.formData[key] != null &&  this.formData[key] != '') {
                            formData.append(key, this.formData[key]);
                        }
                        break;
                }
            });
            common.post({
                data: formData,
                url: 'save',
                processData: false,
                contentType: false,
                errorModel: this.formData.errors,
            }).done(response => {
                if (response.error == EErrorCode.NO_ERROR) {
                    this.isLeavePage = false;
                    common.askUserWhenLeavePage(false);
                    if (this.formData.code) {
                        bootbox.alert(response.msg, () => {
                            window.location.assign(window.location.pathname + '/detail');
                        })
                    } else {
                        bootbox.alert(response.msg, () => {
                            window.location.reload();
                        })
                    }
                }else {
                    bootbox.alert(response.msg);
                }
            })
            .always(() => {
                common.loadingScreen.hide();
            });
        },
        validateVideoLink(link) {
            let validateTikTok = link.match(/^(?:https?:\/\/)?(?:www\.|m\.|vt\.|vm\.|mobile\.|touch\.|mbasic\.)?(?:tiktok.com)\//);
            let validateYoutube = link.match(/^(?:https?:\/\/)?(?:m\.|www\.|mobile\.|touch\.|mbasic\.)?(?:youtu\.be|youtube\.com)\//);
            return !!validateTikTok || !! validateYoutube;
        },
        addLinkVideo() {
            if (this.video.length > 0 || this.formData.video.length > 0) {
                bootbox.alert('Chỉ được đăng 1 video');
                this.formData.linkVideo = null;
                return false;
            }
            if (this.formData.linkVideo.lastIndexOf('tiktok') > -1) {
                if(!this.validateVideoLink(this.formData.linkVideo)){
                    this.formData.errors.linkVideo = ['Link không hợp lệ'];
                    this.formData.linkVideo = null;
                    return false;
                }
                common.post({
                    data: {
                        'url': this.formData.linkVideo
                    },
                    url: '/thumbnail-titok',
                }).done(async (response) => {
                    let thumbnail = await this.getTikTokThumbnail(response.data.src);
                    this.formData.errors.linkVideo = null;
                    this.videoRender.push({
                        type: 3,
                        src: !!thumbnail.thumbnail_url ? thumbnail.thumbnail_url : '/images/logo-tiktok.png',
                    });
                    this.video.push({
                        type: 3,
                        src: this.formData.linkVideo
                    });
                    this.formData.linkVideo = null;

                    
                });
            } else {
                this.formData.errors.linkVideo = null;
                this.videoRender.push({
                    type: 2,
                    src: 'https://img.youtube.com/vi/' + 
                    window.stringUtil.getYoutubeVideoId(this.formData.linkVideo) + 
                    '/0.jpg'
                });
                this.video.push({
                    type: 2,
                    src: this.formData.linkVideo
                });
                this.formData.linkVideo = null;
            }
            // if (this.video.length == 0) {
            //     this.formData.errors.linkVideo = null;
            //     this.videoRender.push({
            //         type: 'link',
            //         src: 'https://img.youtube.com/vi/' + 
            //         window.stringUtil.getYoutubeVideoId(this.formData.linkVideo) + 
            //         '/0.jpg'
            //     });
            //     this.video.push({
            //         type: 'link',
            //         src: this.formData.linkVideo
            //     });
            //     this.formData.linkVideo = null;
            // } else {
            //     bootbox.alert('Chỉ được đăng 1 video');
            //     this.formData.linkVideo = null;
            // }
        }
    }
});
