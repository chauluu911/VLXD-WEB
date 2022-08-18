<template>
    <b-modal
        ref="createFormModalEl" 
        :no-close-on-esc="loading"
        :no-close-on-backdrop="loading"  
        :hide-header-close="loading"
        :title="$t('common.title.edit', {'objectName': $t('object_name')})"
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
                            :label="$t('modal.content')"
                            :disabled="processing"
                            :state="!!formData.errors.content"
                            :invalid-feedback="formData.errors.content && formData.errors.content[0]"
                        >
                            <b-form-textarea
                                v-model.trim="formData.content"
                                :placeholder="$t('modal.content')"
                                rows="3"
                                max-rows="6"
                                :state="formData.errors.content && !formData.errors.content[0]"
                            />
                        </b-form-group>
                   </b-col>
                </b-row>
                <b-row>
                   <b-col md="16">
                        <b-form-group 
                            class="font-weight-bold"
                            :label="$t('constant.status.status')"
                            :disabled="processing"
                        >
                            <b-form-select v-model="formData.displayStatus" :options="displayStatusList"/>
                        </b-form-group>
                   </b-col>
                </b-row>
            </b-form>
            <b-alert variant="danger" :show="!!footerMsg">{{footerMsg}}</b-alert>
        </template>
        <template v-slot:modal-footer="{ hide }">
            <b-button variant="outline-primary" :disabled="loading">{{ $t('common.button.cancel') }}</b-button>
            <a-button @click="submit" variant="primary" :loading="loading">{{ $t('common.button.edit') }}</a-button>
        </template>
    </b-modal>
</template>

<script>
    import EStatus from "../../constants/status";
    import feedbackListMessages from '../../locales/back/feedback-management-list';
    import CreatingFormMixin from '../mixins/creating-form-mixin';
    import ECreatingFormStage from '../../constants/creating-form-stage';
    import EDisplayStatus from '../../constants/display-status';
    import EErrorCode from "../../constants/error-code";

    export default {
        i18n: {
            messages: feedbackListMessages,
        },
        drop: {
            displayStatusList: {
                type: Object,
                default: null
            }
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
            displayStatusList() {
                return [
                    {
                        text: this.$t('constant.display_status.hidden'),
                        value: EDisplayStatus.HIDDEN,
                    },
                    {
                        text: this.$t('constant.display_status.showing'),
                        value: EDisplayStatus.SHOWING,
                    },
                ]
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
                    content: null,
                    id: null,
                    displayStatus: null,
                    errors: {
                        content: null,
                    }
                };
            },

            initEditForm(item) {
                this.formData.content = item.content;
                this.formData.displayStatus = item.oldDisplayStatus;
                this.formData.id = item.id;
            },

            getFormData() {
                if (!this.validateValue()) {
                    return null;
                }

                let data = {};
                Object.keys(this.formData).forEach((key) => {
                    switch (key) {
                        case 'id':
                            data[key] = this.formData[key];
                            break;
                        case 'content':
                            data[key] = this.formData[key];
                            break;
                        default:
                            data[key] = this.formData[key];
                            break;
                    }
                });
                return data;
            },
            async submit() {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm(this.$t('confirmation.edit'), resolve);
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
                    // if (res.error !== EErrorCode.NO_ERROR) {
                    //     this.Util.showMsg('error', null, res.msg);
                    //     this.processing = false;
                    //     return;
                    // }

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
