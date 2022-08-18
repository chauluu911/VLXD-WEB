'use strict';

import EErrorCode from "../../constants/error-code";
import PostItem from '../components/PostItem.vue';
import FBookmarkIcon from 'vue-feather-icons/icons/BookmarkIcon';
import FMessageSquareIcon from 'vue-feather-icons/icons/MessageSquareIcon';
import FMailIcon from 'vue-feather-icons/icons/MailIcon';
import buyerMessage from "../../locales/front/buyer";

const app = new Vue({
	el:'#buyer-post-detail',
    components: {
        PostItem,
        FBookmarkIcon,
        FMessageSquareIcon,
        FMailIcon,
    },
    i18n: {
        messages: buyerMessage
    },
    data() {
        return {
            detail: {},
            otherSuggestions: {},
            infoBuyer: {
                fullName: null,
                numberPhone: null,
                position: null,
                message: null,
                career: null,
                yearsOfExperience: null,
                experience: null,
                idInterest: null,
            },
            accept: false,
            attach: false,
            isSendContact: true,
        }
    },
    created() {
        this.getPostDetail();
        this.getOtherSuggestion();
    },
    methods: {
        getPostDetail() {
            common.loadingScreen.show('body');
            return $.ajax({
                url: `/buyer/post/${$('#businessCode').val()}/detail`,
                method: 'POST',
            }).done((res) => {
                this.detail = res.detail;
                if (this.detail.category) {
                    if (this.detail.category.class1) {
                        this.detail.categoryName = this.detail.category.class1.name;
                    }else if (this.detail.category.class2) {
                        this.detail.categoryName = this.detail.category.class2.name;
                    }else if (this.detail.category.class3) {
                        this.detail.categoryName = this.detail.category.class3.name;
                    }else {
                        this.detail.categoryName = 'Chưa có';
                    }
                }else {
                    this.detail.categoryName = 'Chưa có'
                }
                this.infoBuyer = res.infoBuyer;
                this.isSendContact = res.isSendContact;
            })
            .always(function() {
                common.loadingScreen.hide('body');
            });
        },
        getOtherSuggestion() {
            let data = {
                filter: {
                    notLikeCode: $('#businessCode').val()
                },
                pageSize: 2
            };

            common.loadingScreen.show('body');
            return $.ajax({
                url: '/buyer/post/list',
                method: 'POST',
                data: data,
            }).done((res) => {
                this.otherSuggestions = res.data;
            })
            .always(function() {
                common.loadingScreen.hide('body');
            });
        },
        async sendContact() {
            if (this.isSendContact == false) {
                this.accept = false;   
            }
            if (this.accept != true) {
                return;
            }
            let formData = new FormData();
            formData.append('postCode', $('#businessCode').val());
            Object.keys(this.infoBuyer).map(async (key) => {
                switch (key) {
                    case 'idInterest':
                        if (this.infoBuyer[key]) {
                            formData.append(key, this.infoBuyer[key]);
                        }
                    case 'fullName':
                        if (this.infoBuyer[key]) {
                            formData.append(key, this.infoBuyer[key]);
                        }
                    case 'numberPhone':
                        if (this.infoBuyer[key]) {
                            formData.append(key, this.infoBuyer[key]);
                        }
                    case 'message':
                        if (this.infoBuyer[key]) {
                            formData.append(key, this.infoBuyer[key]);
                        }
                        break;
                    default:
                        if (this.infoBuyer[key] && this.attach == true) {
                            formData.append(key, this.infoBuyer[key]);
                        }
                        break;
                }
            });

            common.loadingScreen.show('body');
            return $.ajax({
                url: '/buyer/send-contact',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
            }).done((res) => {
                if (res.error == EErrorCode.NO_ERROR) {
                    common.showMsg('success', null, this.$t('msg.send_contact_success'));
                    this.getPostDetail();
                }else {
                    bootbox.alert(res.msg)
                }
            })
            .always(function() {
                common.loadingScreen.hide('body');
            });
        },

        saveSearchDataPost(code, isSave) {
            if (isSave) {
                return;
            }
            let data = {
                code: code,
            };
            common.loadingScreen.show('body');
            common.post({
                url: '/buyer/save-data-post',
                data: data,
            }).done(response => {
                if (response.error == EErrorCode.NO_ERROR) {
                    common.showMsg('success', null, 'Search setting have been saved');
                    this.getPostDetail();
                }else {
                    common.showMsg2(response);
                }
            })
            .always(() => {
                common.loadingScreen.hide('body');
            });
        }
    }
});
