<template>
    <b-modal
        ref="createFormModalEl"
        :no-close-on-esc="loading"
        :no-close-on-backdrop="loading"
        :hide-header-close="loading"
        :title="$t('common.title.config', {'objectName': $t('object_name')})"
        header-class="border-0"
        body-class="py-0"
        footer-class="justify-content-end border-0 pt-0"
    >
        <template #default>
            <b-form>
                <b-row>
                    <b-col md="48">
                        <div class="font-size-16px pb-3">{{formData.name}}</div>

                        <b-form-group
                            class="font-weight-bold"
                            :label="$t('table.num-product')"
                            :disabled="processing"
                            :state="!!formData.errors.numProduct"
                            :invalid-feedback="formData.errors.numProduct
                            && formData.errors.numProduct[0]"
                        >
                            <label class="font-weight-light">
                                Để trống để gán số tin đăng tối đa là không giới hạn
                            </label>
                            <b-form-input
                                v-model.trim="formData.numProduct"
                                :placeholder="$t('table.num-product')"
                                rows="3"
                                max-rows="6"
                                type="number"
                                :state="formData.errors.numProduct
                                && !formData.errors.numProduct[0]"
                            />
                        </b-form-group>
                        <b-form-group
                            class="font-weight-bold"
                            :label="$t('table.num-image-in-product')"
                            :disabled="processing"
                            :state="!!formData.errors.numImageInProduct"
                            :invalid-feedback="formData.errors.numImageInProduct
                            && formData.errors.numImageInProduct[0]"
                        >
                            <b-form-input
                                v-model.trim="formData.numImageInProduct"
                                :placeholder="$t('table.num-image-in-product')"
                                rows="3"
                                max-rows="6"
                                type="number"
                                :state="formData.errors.numImageInProduct
                                && !formData.errors.numImageInProduct[0]"
                            />
                        </b-form-group>
                        <b-form-group
                            class="font-weight-bold"
                            :label="$t('table.num-push-product-in-month')"
                            :disabled="processing"
                            :state="!!formData.errors.numPushProductInMonth"
                            :invalid-feedback="formData.errors.numPushProductInMonth
                            && formData.errors.numPushProductInMonth[0]"
                        >
                            <b-form-input
                                v-model.trim="formData.numPushProductInMonth"
                                :placeholder="$t('table.num-push-product-in-month')"
                                rows="3"
                                max-rows="6"
                                type="number"
                                :state="formData.errors.numPushProductInMonth
                                && !formData.errors.numPushProductInMonth[0]"
                            />
                        </b-form-group>
                        <label class="font-weight-bold">
                            {{ $t('table.priority-show-search-product') }}
                            <a-checkbox v-model="formData.priorityShowSearchProduct"
                                        :checked-state="formData.priorityShowSearchProduct"
                            ></a-checkbox>
                        </label>
                        <label class="font-weight-bold">
                            {{ $t('table.create-notification') }}
                            <a-checkbox v-model="formData.enableCreateNotification"
                                        :checked-state="formData.enableCreateNotification"
                            ></a-checkbox>
                        </label>
                        <b-form-group
                            class="font-weight-bold"
                            :label="$t('table.video-introduce')"
                            :disabled="processing"
                        >
                            <div class="">
                                <label class="font-weight-light">
                                    {{ $t('modal.allow-upload-video') }}
                                    <a-checkbox v-model="formData.videoIntroduceAllowUploadVideo"
                                        :checked-state="formData.videoIntroduceAllowUploadVideo"
                                    ></a-checkbox>
                                </label>
                                <div class="font-weight-light">
                                    <b-form-group
                                        class="mb-0"
                                        :label="$t('modal.max-upload-time')"
                                        :disabled="processing || !formData.videoIntroduceAllowUploadVideo"
                                        :state="!!formData.errors.videoIntroduceUploadTime"
                                        :invalid-feedback="formData.errors.videoIntroduceUploadTime
                                        && formData.errors.videoIntroduceUploadTime[0]">
                                        <b-form-input
                                            v-model="formData.videoIntroduceUploadTime"
                                            :placeholder="$t('modal.max-upload-time')"
                                            type="number"
                                            :state="formData.errors.videoIntroduceUploadTime
                                            && !formData.errors.videoIntroduceUploadTime[0]"
                                        />
                                    </b-form-group>
                                </div>
                                <div class="font-weight-light">
                                    <b-form-group
                                        class="mb-0"
                                        :label="$t('modal.num-video')"
                                        :disabled="processing || !formData.videoIntroduceAllowUploadVideo"
                                        :state="!!formData.errors.videoIntroduceNumVideo"
                                        :invalid-feedback="formData.errors.videoIntroduceNumVideo
                                        && formData.errors.videoIntroduceNumVideo[0]">
                                        <b-form-input
                                            v-model="formData.videoIntroduceNumVideo"
                                            type="number"
                                            :state="formData.errors.videoIntroduceNumVideo
                                            && !formData.errors.videoIntroduceNumVideo[0]"
                                        />
                                    </b-form-group>
                                </div>
                            </div>
                        </b-form-group>
                        <b-form-group
                            class="font-weight-bold"
                            :label="$t('table.avatar')"
                            :disabled="processing"
                            :state="!!formData.errors.name"
                            :invalid-feedback="formData.errors.name && formData.errors.name[0]"
                        >
                            <div class="">
                                <label class="font-weight-light">
                                    {{ $t('modal.allow-upload-video') }}
                                    <a-checkbox v-model="formData.avatarAllowUploadVideo"
                                                :checked-state="formData.avatarAllowUploadVideo"
                                    ></a-checkbox>
                                </label>
                                <div class="font-weight-light">
                                    <b-form-group
                                        :label="$t('modal.max-upload-time')"
                                        :disabled="processing || !formData.avatarAllowUploadVideo"
                                        :state="!!formData.errors.avatarUploadTime"
                                        :invalid-feedback="formData.errors.avatarUploadTime
                                        && formData.errors.avatarUploadTime[0]">
                                        <b-form-input
                                            v-model="formData.avatarUploadTime"
                                            :placeholder="$t('modal.max-upload-time')"
                                            type="number"
                                            :state="formData.errors.avatarUploadTime
                                            && !formData.errors.avatarUploadTime[0]"
                                        />
                                    </b-form-group>
                                </div>
                                <div class="font-weight-light ">
                                    <label class="d-flex align-items-center m-0">
                                        {{$t('modal.type-of-avatar')}}
                                    </label>
                                    <b-form-select
                                        v-model="formData.avatarType"
                                        :placeholder="$t('modal.type-of-avatar')"
                                        :options="avatarTypeOptions"
                                    >
                                    </b-form-select>

                                </div>
                            </div>
                        </b-form-group>
                        <b-form-group
                            class="font-weight-bold"
                            :label="$t('table.video-in-product')"
                            :disabled="processing"
                            :state="!!formData.errors.name"
                            :invalid-feedback="formData.errors.name && formData.errors.name[0]"
                        >
                            <div class="">
                                <label class="font-weight-light">
                                    {{ $t('modal.allow-upload-video') }}
                                    <a-checkbox v-model="formData.videoInProductAllowUploadVideo"
                                        :checked-state="formData.videoInProductAllowUploadVideo"
                                    ></a-checkbox>
                                </label>
                                <div class="font-weight-light">
                                    <b-form-group
                                        class="mb-0"
                                        :label="$t('modal.max-upload-time')"
                                        :disabled="processing || !formData.videoInProductAllowUploadVideo"
                                        :state="!!formData.errors.videoInProductUploadTime"
                                        :invalid-feedback="formData.errors.videoInProductUploadTime
                                        && formData.errors.videoInProductUploadTime[0]">
                                        <b-form-input
                                            v-model="formData.videoInProductUploadTime"
                                            :placeholder="$t('modal.max-upload-time')"
                                            type="number"
                                            :state="formData.errors.videoInProductUploadTime
                                            && !formData.errors.videoInProductUploadTime[0]"
                                        />
                                    </b-form-group>
                                </div>
                            </div>
                        </b-form-group>
                        <b-form-group
                            class="font-weight-bold"
                            :label="$t('table.post-banner')"
                            :disabled="processing"
                            :state="!!formData.errors.name"
                            :invalid-feedback="formData.errors.name && formData.errors.name[0]"
                        >
                            <div class="">
                                <label class="font-weight-light">
                                    {{ $t('modal.allow-upload-banner') }}
                                    <a-checkbox v-model="formData.bannerInHomeAllowUploadBanner"
                                                :checked-state="formData.bannerInHomeAllowUploadBanner"
                                    ></a-checkbox>
                                </label>
                                <div class="font-weight-light">
                                    <b-form-group
                                        class="mb-0"
                                        :label="$t('modal.num-day-show')"
                                        :disabled="processing || !formData.bannerInHomeAllowUploadBanner"
                                        :state="!!formData.errors.bannerInHomeNumDayShow"
                                        :invalid-feedback="formData.errors.bannerInHomeNumDayShow
                                        && formData.errors.bannerInHomeNumDayShow[0]">
                                        <b-form-input
                                            v-model="formData.bannerInHomeNumDayShow"
                                            :placeholder="$t('modal.num-day-show')"
                                            type="number"
                                            :state="formData.errors.bannerInHomeNumDayShow
                                            && !formData.errors.bannerInHomeNumDayShow[0]"
                                        />
                                    </b-form-group>
                                </div>
                            </div>
                        </b-form-group>
                        <b-form-group
                            class="font-weight-bold"
                            :label="$t('table.image-introduce')"
                            :disabled="processing"
                            :state="!!formData.errors.name"
                            :invalid-feedback="formData.errors.name && formData.errors.name[0]"
                        >
                            <div class="">
                                <label class="font-weight-light">
                                    {{ $t('modal.allow-upload-image') }}
                                    <a-checkbox v-model="formData.imageIntroduceAllowUpdateImage"
                                                :checked-state="formData.imageIntroduceAllowUpdateImage"
                                    ></a-checkbox>
                                </label>
                                <div class="font-weight-light">
                                    <b-form-group
                                        class="mb-0"
                                        :label="$t('modal.num-image')"
                                        :disabled="processing || !formData.imageIntroduceAllowUpdateImage"
                                        :state="!!formData.errors.imageIntroduceNumImage"
                                        :invalid-feedback="formData.errors.imageIntroduceNumImage
                                        && formData.errors.imageIntroduceNumImage[0]">
                                        <b-form-input
                                            v-model="formData.imageIntroduceNumImage"
                                            :placeholder="$t('modal.num-image')"
                                            type="number"
                                            :state="formData.errors.imageIntroduceNumImage
                                            && !formData.errors.imageIntroduceNumImage[0]"
                                        />
                                    </b-form-group>
                                </div>
                            </div>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col md="16">

                    </b-col>
                </b-row>
            </b-form>
            <b-alert variant="danger" :show="!!footerMsg">{{footerMsg}}</b-alert>
        </template>
        <template v-slot:modal-footer="{ hide }">
            <b-button variant="outline-primary"
                      :disabled="processing" @click="hide('forget')">
                {{ $t('common.button.cancel') }}</b-button>
            <a-button @click="submit" variant="primary" :loading="loading">
                {{ $t('common.button.edit') }}</a-button>
        </template>
    </b-modal>
</template>

<script>
import EStatus from "../../../constants/status";
import packageListManage from "../../../locales/back/config/package";
import CreatingFormMixin from '../../mixins/creating-form-mixin';
import ECreatingFormStage from '../../../constants/creating-form-stage';
import EErrorCode from "../../../constants/error-code";
import shopLevelListManage from "../../../locales/back/config/shop-level";

export default {
    i18n: {
        messages: shopLevelListManage,
    },
    mixins: [CreatingFormMixin],
    inject: ['Util'],

    data() {
        return {
            formData: this.$_formData(),
            processing: false,
            footerMsg: null,
            avatarTypeOptions: [
                { value: 1, text: 'Tròn' },
                { value: 2, text: 'Vuông' },
            ]
        }
    },

    computed: {
        routes() {
            return {
                save: `${this.$route.meta.baseUrl}/save`,
            }
        },
    },

    methods: {
        showForm(item = null, forceReset = false) {
            this.stage = ECreatingFormStage.UPDATING;
            this.initEditForm(item);
            this.$refs.createFormModalEl.show();
        },

        $_formData() {
            return {
                name: null,
                id: null,
                numProduct: null,
                numImageInProduct: null,
                numPushProductInMonth:null,
                priorityShowSearchProduct: false,
                enableCreateNotification: false,
                videoIntroduceAllowUploadVideo: false,
                videoIntroduceNumVideo: null,
                videoIntroduceUploadTime: null,
                avatarAllowUploadVideo: false,
                avatarUploadTime: null,
                avatarType: null,
                videoInProductAllowUploadVideo: false,
                videoInProductUploadTime: null,
                bannerInHomeAllowUploadBanner:false,
                bannerInHomeNumDayShow:null,
                imageIntroduceAllowUpdateImage:false,
                imageIntroduceNumImage:null,
                errors: {

                }
            };
        },

        initEditForm(item) {
            this.formData = this.$_formData();
            if (!item) {
                return;
            }

            this.formData.name = item.name;
            this.formData.id = item.id;
            this.formData.numProduct = item.numProduct;
            this.formData.numImageInProduct = item.numImageInProduct;
            this.formData.numPushProductInMonth = item.numPushProductInMonth;
            this.formData.priorityShowSearchProduct = item.priorityShowSearchProduct;
            this.formData.enableCreateNotification = item.enableCreateNotification;

            this.formData.videoIntroduceAllowUploadVideo = item.videoIntroduce.allow_upload_video;
            this.formData.videoIntroduceUploadTime = item.videoIntroduce.upload_time;
            this.formData.videoIntroduceNumVideo = item.videoIntroduce.num_video;

            this.formData.videoInProductAllowUploadVideo = item.videoInProduct.allow_upload_video;
            this.formData.videoInProductUploadTime = item.videoInProduct.upload_time;

            this.formData.avatarAllowUploadVideo = item.avatar.allow_upload_video;
            this.formData.avatarType = item.avatar.type;
            this.formData.avatarUploadTime = item.avatar.upload_time;

            this.formData.bannerInHomeAllowUploadBanner = item.bannerInHome.allow_upload_banner;
            this.formData.bannerInHomeNumDayShow = item.bannerInHome.num_day_show;

            this.formData.imageIntroduceAllowUpdateImage = item.imageIntroduce.allow_upload_image;
            this.formData.imageIntroduceNumImage = item.imageIntroduce.num_image;

        },

        getFormData() {
            if (!this.validateValue()) {
                return null;
            }

            let data = {};
            Object.keys(this.formData).forEach((key) => {
                switch (key) {
                    default:
                        if (this.formData[key] == null) {
                            return;
                        }
                        data[key] = this.formData[key];
                        break;
                }
            });
            return data;
        },
        async submit() {
            let confirm = await new Promise((resolve) => {
                this.Util.confirm(this.$t('confirm.edit') , resolve);
            });
            if (!confirm) {
                return;
            }
            this.Util.loadingScreen.show();
            let data = this.getFormData();
            if (!data) {
                return;
            }

            this.processing = true;
            this.Util.post({
                url: this.routes.save,
                data,
                failCb: this.showErrors,
                isModal: true,
            }).done(async (res) => {
                if (res.error !== EErrorCode.NO_ERROR) {
                    this.Util.showMsg('error', null, res.msg);
                    this.processing = false;
                    return;
                }
                this.onSaveSuccess(res.msg);
                this.processing = false;
                this.footerMsg = null;
            }).fail(() => {
                this.processing = false;
            }).always(() => {
                this.Util.loadingScreen.hide();
            });
        },

        showErrors(errors, msg = null) {
            this.footerMsg = msg;
            Object.keys(this.formData).forEach((key) => {
                if (key === 'id') {
                    return;
                }
                this.formData.errors = errors;
            });
        },

        onSaveSuccess(msg) {
            this.$emit('save-success');
            this.Util.showMsg('success', null, msg, {
                onHidden: () => {
                    this.Util.askUserWhenLeavePage(false);
                    this.stage = ECreatingFormStage.SUCCESS;
                }
            });
            this.Util.loadingScreen.hide();
            this.$refs.createFormModalEl.hide();
        },
    },
}
</script>
