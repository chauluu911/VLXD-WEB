'use strict';

import EErrorCode from "../../constants/error-code";
import Profile from "../components/Profile"

const app = new Vue({
    el:'#change-password',
    components : {
        Profile,
    },
    data() {
        return {
            value: {
                oldPassword: '',
                newPassword: '',
                confirmPassword: '',
            },
            errors: {
                oldPassword: null,
                newPassword: null,
                confirmPassword: null,
            }
        };
    },

    created() {
    },

    methods: {
        changePassword() {
            common.loadingScreen.show('body');

            common.post({
                url: '/profile/personal-info/change-password',
                data: {
                    oldPassword: this.value.oldPassword,
                    newPassword: this.value.newPassword,
                    confirmPassword: this.value.confirmPassword,
                },
            }).done((res) => {
                if (res.error !== EErrorCode.NO_ERROR) {
                    this.errors = res.errors;
                    return;
                }
                this.errors = {}
                common.showMsg('success',null,res.msg,{onHidden : () => {
                        window.location.assign(res.profile);
                    }})
            }).fail((res) => {
                this.errors = res.responseJSON.errors
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
    }
});
