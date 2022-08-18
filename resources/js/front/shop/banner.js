'use strict';

import EErrorCode from "../../constants/error-code";
import EStatus from "../../constants/status";
import EShopPaymentStatus from "../../constants/shop-payment-status";
import ShopToolBar from '../components/ShopToolBar.vue';
import CropperImage from '../../../js/components/ImageCropper';
import EBannerType from "../../constants/banner-type";
import EBannerActionType from "../../constants/banner-action-type";
import EPlatform from "../../constants/platform";

let getNotificationTimeout = null;
const app = new Vue({
	el:'#banner',
    components: {
        ShopToolBar,
        CropperImage
    },
    data() {
        return {
            shopId: $('#shopData').data('id'),
            bannerId: $('#shopData').data('banner-id'),
            status: null,
            banners: {},
            validate: {},
            EBannerType,
            EBannerActionType,
            EStatus,
            EPlatform,

            webRatio: {
                name: '16/5',
                value: '3.2'
            },

            mobileRatio: {
                name: '16/9',
                value: '1.77777777778'
            },

            formData: this.$_formData(),
            validate: {},
        }
    },
    created() {
        this.getBanner();
    },
    methods: {
        $_formData() {
            return {
                isEdit: false,
                web: {
                    url: null,
                    file: null,
                    blob: null,
                    ratio: '16/5',
                    edit: false,
                    bannerId: null,
                },
                mobile: {
                    url: null,
                    file: null,
                    blob: null,
                    ratio: '16/9',
                    edit: false,
                    bannerId: null,
                },
                actionType: EBannerActionType.DO_NOTHING,
                link: null,
                type: EBannerType.MAIN_BANNER_ON_HOME_SCREEN,
                status: null,
                statusString: null,
                createdAt: null,
                id: null,
                errors: {
                    webUrl: null,
                    webFile: null,
                    webBlob: null,
                    mobileUrl: null,
                    mobileFile: null,
                    mobileBlob: null,
                    link: null,
                }
            }
        },
        getBanner() {
            common.loadingScreen.show('body');
            common.get({
                url: `/shop/${this.shopId}/banner/get`,
                data: {
                    status: this.status,
                    shopId: this.shopId
                }
            }).done(response => {
                this.banners = response.banners;
                this.validate = response.validate;
            }).always(() => {
                let item = this.banners.find((item) => item.id == this.bannerId);
                if (item) {
                    this.showModalCropper(item);
                    window.history.pushState(null, null, stringUtil.getUrlWithQueries('/shop/' + this.shopId + '/banner')
                    );
                    this.bannerId == null;
                }
                common.loadingScreen.hide('body');
            });
        },
        showModalCropper(item) {
            this.formData = this.$_formData();
            this.$refs.imageWebCropperEl.resetSelectedFile();
            this.$refs.imageMobileCropperEl.resetSelectedFile();
            if (item) {
                this.formData.isEdit = true;
                this.formData.actionType = item.action_on_click_type;
                this.formData.link = item.action_on_click_target;
                this.formData.type = item.type;
                this.formData.status = item.status;
                this.formData.createdAt = item.createdAt;
                this.formData.statusString = item.statusString;
                this.formData.id = item.id;
                if (item.platform == EPlatform.MOBILE) {
                    this.formData.mobile.bannerId = item.id;
                    this.formData.mobile.edit = true;
                    this.formData.web.edit = false;
                    if (item.status != EStatus.ACTIVE && item.status != EStatus.SUSPENDED) {
                        this.formData.mobile.url = item.original_resource_path;
                    } else {
                        this.formData.mobile.url = item.path_to_resource;
                    }
                    setTimeout(() => {
                        this.$refs.imageMobileCropperEl.setRatio(this.mobileRatio, 
                            this.formData.mobile.url, this.formData.mobile.file);
                    }, 300);
                    
                } else {
                    this.formData.web.bannerId = item.id;
                    this.formData.web.edit = true;
                    this.formData.mobile.edit = false;
                    if (item.status != EStatus.ACTIVE && item.status != EStatus.SUSPENDED) {
                        this.formData.web.url = item.original_resource_path;
                    } else {
                        this.formData.web.url = item.path_to_resource;
                    }
                    setTimeout(() => {
                        this.$refs.imageWebCropperEl.setRatio(this.webRatio, 
                            this.formData.web.url, this.formData.web.file);
                    }, 300);
                }
            } else {
                this.formData.isEdit = false;
                this.formData.mobile.edit = false;
                this.formData.web.edit = false;
            }
            $('#modal-cropper').modal('show');
        },
        validateImageWeb(img, type) {
            let item = this.validate.web.find((item) => item.name == type);
            let minHeight = item.size.minHeight;
            let minWidth = item.size.minWidth;
            if(img.width < minWidth || img.height < minHeight) {
                this.formData.web.file = null;
                this.formData.web.url = null;
                let error = [];
                error.push(item.size.guideMsg);
                this.formData.errors.webFile = error;
                this.$refs.imageWebCropperEl.resetSelectedFile();
                return false;
            } else {
                this.formData.errors.webFile = null;
            }
        },
        validateImageMobile(img, type) {
            let item = this.validate.mobile.find((item) => item.name == type);
            let minHeight = item.size.minHeight;
            let minWidth = item.size.minWidth;
            if(img.width < minWidth || img.height < minHeight) {
                this.formData.mobile.file = null;
                this.formData.mobile.url = null;
                let error = [];
                error.push(item.size.guideMsg);
                this.formData.errors.mobileFile = error;
                this.$refs.imageMobileCropperEl.resetSelectedFile();
                return false;
            } else {
                this.formData.errors.mobileFile = null;
            }
        },
        onCropperWebCreated({file, imageUrl, indexImg}) {
            setTimeout(() => {
                this.$refs.imageWebCropperEl.val()
                    .then(blob => {
                        if (blob) {
                            let img = new Image();
                            img.src = URL.createObjectURL(file);
                            img.onload = () => {
                                switch(this.formData.type) {
                                    case EBannerType.MAIN_BANNER_ON_HOME_SCREEN:
                                        this.validateImageWeb(img, 'home');
                                        break;
                                }
                            }
                            this.formData.web.file = file;
                            this.formData.web.blob = blob;
                            this.formData.web.url = imageUrl;
                        }
                    });
            }, 300)
        },
        resetCropperWeb() {
            this.formData.web.file = null;
            this.formData.web.blob = null;
            this.formData.web.url = null;
        },
        onCropperMobileCreated({file, imageUrl, indexImg}) {
            setTimeout(() => {
                this.$refs.imageMobileCropperEl.val()
                    .then(blob => {
                        if (blob) {
                            let img = new Image();
                            img.src = URL.createObjectURL(file);
                            img.onload = () => {
                                switch(this.formData.type) {
                                    case EBannerType.MAIN_BANNER_ON_HOME_SCREEN:
                                        this.validateImageMobile(img, 'home');
                                        break;
                                }
                            }
                            this.formData.mobile.file = file;
                            this.formData.mobile.blob = blob;
                            this.formData.mobile.url = imageUrl;
                        }
                    });
            }, 300)
        },
        resetCropperMobile() {
            this.formData.mobile.file = null;
            this.formData.mobile.blob = null;
            this.formData.mobile.url = null;
        },
        cropImageMobile(data, index) {
            setTimeout(() => {
                this.$refs.imageMobileCropperEl.val()
                    .then(blob => {
                        if (blob) {
                            this.formData.mobile.blob = blob;
                        }
                    });
            }, 300)
        },
        cropImageWeb(data, index) {
            setTimeout(() => {
                this.$refs.imageWebCropperEl.val()
                    .then(blob => {
                        if (blob) {
                            this.formData.web.blob = blob;
                        }
                    });
            }, 300)
        },
        changeAction(type) {
            this.formData.actionType = type;
        },
        deleteBanner() {
            bootbox.confirm({
                title: 'Thông báo',
                message: 'Bạn chắc chắn muốn xóa Banner này không',
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
                                id: this.formData.id,
                            },
                            url: 'banner/delete',
                        }).done(response => {
                            if (response.error == EErrorCode.NO_ERROR) {
                                bootbox.alert('Xóa Banner thành công', () => {
                                    window.location.reload();
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
        saveBanner() {
            common.loadingScreen.show();
            let formData = new FormData();
            Object.keys(this.formData).forEach((key) => {
                switch (key) {
                    case 'mobile':
                        Object.keys(this.formData[key]).forEach((item) => {
                            if (this.formData[key][item] && item != 'file') {
                                formData.append(`mobile${item.charAt(0).toUpperCase() + item.slice(1)}`, 
                                this.formData.[key][item]);    
                            } else {
                                if (this.formData.mobile.file == null) {
                                    this.formData.mobile.file = ''
                                }
                                formData.append('mobileFile', this.formData.mobile.file);
                            }
                        });
                        break;
                    case 'web':
                        Object.keys(this.formData[key]).forEach((item) => {
                            if (this.formData[key][item] && item != 'file') {
                                formData.append(`web${item.charAt(0).toUpperCase() + item.slice(1)}`, 
                                this.formData.[key][item]);    
                            } else {
                                if (this.formData.web.file == null) {
                                    this.formData.web.file = ''
                                }
                                formData.append('webFile', this.formData.web.file);
                            }
                        });
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
                url: `/shop/${this.shopId}/banner/save`,
                processData: false,
                contentType: false,
                errorModel: this.formData.errors,
            }).done(response => {
                $('#modal-cropper').modal('hide');
                bootbox.alert(response.msg, () => {
                    window.location.reload();
                })
            })
            .always(() => {
                common.loadingScreen.hide();
            });
        },
    }
});
