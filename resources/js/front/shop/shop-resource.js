'use strict';

import EStatus from "../../constants/status";
import EErrorCode from "../../constants/error-code";
import ShopToolBar from '../components/ShopToolBar.vue';
import EShopResourceType from "../../constants/shop-resource-type";
import EVideoType from "../../constants/video-type";

let getNotificationTimeout = null;
const app = new Vue({
    el:'#shop-resource',
    components: {
        ShopToolBar,
    },
    data() {
        return {
            shop: {},
            EStatus,
            EVideoType,
            selectedFile: null,
            newVideoLink: '',
            shopId: $('#shopData').data('id'),
            videoList: {
                index: 0,
                value: []
            },
            imageList: {
                index: 0,
                value: []
            },
            typeOfResourceUploading: 'image',
            isShowVideoModal: false,
            permission: {},
        }
    },
    created() {
        this.searchResource();
        this.getPermissionShop();
    },
    computed: {
        route() {
            return {
                list: '/shop/' + this.shopId + '/resource/get',
                save: '/shop/' + this.shopId + '/resource/save',
                delete: '/shop/' + this.shopId + '/resource/delete',
            }
        }
    },

    mounted() {
        // $('#video-modal').on('hidden.bs.modal', function () {
        //     $('#video').trigger('pause');
        // })
        // $('#video-modal').on('hidden.bs.modal', function () {
        //     $('#video-yt').each(function(){
        //         this.contentWindow.postMessage('{"event":"command",' +
        //             '"func":"pauseVideo","args":""}', '*')
        //     });
        // })
        // $('#new-video-modal').on('hidden.bs.modal', function () {
        //     $('#new-video').trigger('pause');
        // })
    },
    methods: {
        showImgModal(index) {
            this.imageList.index = index;
            $('#image-modal').modal('show');
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
        showVideoModal(index) {
            this.isShowVideoModal = true;
            this.videoList.index = index;
            $('#video-modal').modal('show');
        },
        closeVideoModal() {
            this.isShowVideoModal = false;
            $('#video-modal').modal('hide');
        },
        onSelectImage(e) {
            if (this.permission.imageIntroduce.allow_upload_image == false) {
                $('#no-permission-warning').modal('show');
                $('#image-upload').val('');
                return false;
            }
            if(this.permission.imageIntroduce.num_image <= this.imageList.value.length) {
                this.typeOfResourceUploading = 'image';
                $('#uploadLimit').modal('show');
                $('#image-upload').val('');
                return false;
            }
            if (window.File && window.FileList && window.FileReader) {
                let fileReader = new FileReader();
                if(e.target.files.length == 0) {
                    return;
                }
                let files = e.target.files;
                if(files[0].type != 'image/jpeg' && files[0].type != 'image/png' && files[0].type != 'image/jpg'){
                    bootbox.alert('Hình không hợp lệ');
                    this.selectedFile = null;
                    return false;
                }
                this.selectedFile = files[0];
                fileReader.onload = (() => {
                    let newImg = {
                        file: this.selectedFile,
                        type: EShopResourceType.IMAGE,
                    }
                    this.updateResource(newImg)
                });
                fileReader.readAsDataURL(files[0]);
            }
        },
        onSelectVideo(e) {
            if (this.permission.videoIntroduce.allow_upload_video == false) {
                $('#no-permission-warning').modal('show');
                $('#video-upload').val('');
                return false;
            }
            if(this.permission.videoIntroduce.num_video <= this.videoList.value.length) {
                this.typeOfResourceUploading = 'video';
                $('#uploadLimit').modal('show');
                $('#video-upload').val('');
                return false;
            }
            if (window.File && window.FileList && window.FileReader) {
                let fileReader = new FileReader();
                if(e.target.files.length == 0) {
                    return;
                }
                let files = e.target.files;
                if(files[0].type != 'video/mp4' && files[0].type != 'video/webm' && files[0].type != 'video/ogg'){
                    bootbox.alert('Video không hợp lệ');
                    this.selectedFile = null;
                    return false;
                }
                this.selectedFile = files[0];
                fileReader.onload = (() => {
                    let newVideo = {
                        file: this.selectedFile,
                        type: EShopResourceType.VIDEO,
                    }
                    this.updateResource(newVideo)
                });
                let durationOfVideo;
                let video = document.createElement('video');
                video.preload = 'metadata';
                video.onloadedmetadata = () => {
                    window.URL.revokeObjectURL(video.src);
                    durationOfVideo = video.duration;
                    if (durationOfVideo > this.permission.videoIntroduce.upload_time) {
                        this.selectedFile = null;
                        bootbox.alert(`Bạn chỉ được đăng video có thời gian tối đa ${this.permission.videoIntroduce.upload_time}s`);
                        $('#video-upload').val('');
                    } else {
                        fileReader.readAsDataURL(this.selectedFile);
                    }
                }
                video.src = URL.createObjectURL(this.selectedFile);

            }
        },
        deleteResource(item) {
            bootbox.confirm({
                title: 'Thông báo',
                message: `Bạn muốn xóa ${item.type === EShopResourceType.IMAGE ? 'ảnh' : 'video'} này không`,
                buttons: {
                    confirm: {
                        label: 'Xác nhận',
                        className: 'btn-primary',
                    },
                    cancel: {
                        label: 'Bỏ qua',
                        className: 'btn-default'
                    }
                },
                callback: (result) => {
                    if (result) {
                        common.loadingScreen.show('body');
                        $.ajax({
                            url: this.route.delete,
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            type: 'POST',
                            data: {
                                id: item.id,
                            },
                            success: async (result) => {
                                if (result.error === EErrorCode.NO_ERROR) {
                                    common.loadingScreen.hide('body');
                                    await this.searchResource();
                                    bootbox.alert("Xoá thành công!!");
                                } else {
                                    common.loadingScreen.hide('body');
                                    bootbox.alert('Có lỗi vui lòng thử lại sau.');
                                }
                            },
                            error: function () {
                                common.loadingScreen.hide('body');
                                bootbox.alert('Có lỗi vui lòng thử lại sau.');
                            }
                        });
                    }
                }
            });
        },

        nextOrPreImg(value){
            this.imageList.index += value;
        },
        searchResource() {
            common.loadingScreen.show();
            return common.get({
                url: this.route.list,
            }).done(response => {
                if (response.error != EErrorCode.NO_ERROR) {
                    this.Util.showMsg2(response);
                    return false;
                }
                this.imageList.value= [];
                this.imageList.index = 0;
                this.videoList.value = [];
                this.videoList.index = 0;
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
                common.loadingScreen.hide();
            });
        },
        updateResource(resource) {
            let formData = new FormData();
            formData.append('newResourceType', resource.type);
            if(resource.file){
                formData.append('newResourceFile', resource.file);
            }
            if(resource.link) {
                formData.append('newResourceLink', resource.link);
            }
            common.loadingScreen.show();
            common.post({
                data: formData,
                url: this.route.save,
            }).done( async (response) => {
                if(response.error != EErrorCode.NO_ERROR) {
                    common.showMsg2(response);
                    return false;
                }
                await this.searchResource();
                common.showMsg2(response);
            }).always(() => {
                common.loadingScreen.hide();
            });
        },
        uploadVideoLink() {
            if (this.permission.videoIntroduce.allow_upload_video == false) {
                $('#no-permission-warning').modal('show');
                return false;
            }
            if(this.permission.videoIntroduce.num_video <= this.videoList.value.length) {
                this.typeOfResourceUploading = 'video';
                $('#uploadLimit').modal('show');
                return false;
            }
            if(!this.newVideoLink) {
                bootbox.alert('Link video không được để trống');
                return false;
            }
            if(!this.validateVideoLink(this.newVideoLink)){
                bootbox.alert('Link video không hợp lệ');
                return false;
            }
            let newVideo = {
                type: EShopResourceType.VIDEO,
                link: this.newVideoLink
            }
            this.newVideoLink = '';

            this.updateResource(newVideo)
        },
        validateVideoLink(link) {
            let validateTikTok = link.match(/^(?:https?:\/\/)?(?:www\.|m\.|vt\.|vm\.|mobile\.|touch\.|mbasic\.)?(?:tiktok.com)\//);
            let validateYoutube = link.match(/^(?:https?:\/\/)?(?:m\.|www\.|mobile\.|touch\.|mbasic\.)?(?:youtu\.be|youtube\.com)\//);
            return !!validateTikTok || !! validateYoutube;
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
