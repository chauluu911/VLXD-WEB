<template>
    <b-modal
        ref="createFormModalEl"
        :no-close-on-esc="loading"
        :no-close-on-backdrop="loading"
        :hide-header-close="loading"
        :title="$t('common.title.create', {'objectName': $t('object_name')})"
        header-class="border-0"
        body-class="py-0"
        footer-class="justify-content-end border-0 pt-0"
    >
        <template #default>
            <b-form>
                <b-row>
                   <b-col md="48">
                        <b-form-group
                            class="font-weight-bold"
                            :label="$t('modal.name')"
                            :disabled="processing"
                            :state="!!formData.errors.name"
                            :invalid-feedback="formData.errors.name && formData.errors.name[0]"
                        >
                            <b-form-input
                                v-model.trim="formData.name"
                                :placeholder="$t('modal.name')"
                                rows="3"
                                max-rows="6"
                                :state="formData.errors.name && !formData.errors.name[0]"
                            />
                        </b-form-group>
                        <b-form-group
                            class="font-weight-bold"
                            :label="$t('modal.num-day')"
                            :disabled="processing"
                            :state="!!formData.errors.numDay"
                            :invalid-feedback="formData.errors.numDay && formData.errors.numDay[0]"
                        >
                            <b-form-input
                                v-model.trim="formData.numDay"
                                :placeholder="$t('modal.num-day')"
                                :state="formData.errors.numDay && !formData.errors.numDay[0]"
                            />
                        </b-form-group>
                        <b-form-group
                            class="font-weight-bold"
                            :label="$t('modal.price')"
                            :disabled="processing"
                            :state="!!formData.errors.price"
                            :invalid-feedback="formData.errors.price && formData.errors.price[0]"
                        >
                            <b-form-input
                                v-model.trim="formData.price"
                                :placeholder="$t('modal.price')"
                                :state="formData.errors.price && !formData.errors.price[0]"
                            />
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
            <b-button variant="outline-primary" :disabled="processing" @click="hide('forget')">{{ $t('common.button.cancel') }}</b-button>
            <a-button @click="submit" variant="primary" :loading="loading">{{ $t('common.button.add') }}</a-button>
        </template>
    </b-modal>
</template>

<script>
    import EStatus from "../../../constants/status";
    import packageListManage from "../../../locales/back/config/package";
    import CreatingFormMixin from '../../mixins/creating-form-mixin';
    import ECreatingFormStage from '../../../constants/creating-form-stage';
    import EErrorCode from "../../../constants/error-code";
    import ESubcriptionPriceType from "../../../constants/subcription-price-type";

    export default {
        i18n: {
            messages: packageListManage,
        },
        mixins: [CreatingFormMixin],
        inject: ['Util'],

        data() {
            return {
                formData: this.$_formData(),
                processing: false,
                footerMsg: null,
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
                    numDay: null,
                    price: null,
                    errors: {
                        name: null,
                        numDay: null,
                        price: null,
                    }
                };
            },

            initEditForm(item) {
                this.formData = this.$_formData();
                if (!item) {
                    return;
                }
                this.formData.name = item.name;
                this.formData.numDay = item.numDay;
                this.formData.id = item.id;
                this.formData.price = item.price;
            },

            getFormData() {
                if (!this.validateValue()) {
                    return null;
                }

                let data = {};
                Object.keys(this.formData).forEach((key) => {
                    switch (key) {
                        default:
                            if (!this.formData[key]) {
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
                    this.Util.confirm(this.$t('confirm.create') , resolve);
                });
                if (!confirm) {
                    return;
                }
                this.Util.loadingScreen.show();
                let data = this.getFormData();
                if (!data) {
                    return;
                }

                data = {
                    ...data,
                    type : ESubcriptionPriceType.PUSH_PRODUCT
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
