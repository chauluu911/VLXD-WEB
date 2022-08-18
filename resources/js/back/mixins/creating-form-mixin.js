import ECreatingFormStage from '../../constants/creating-form-stage';
import EErrorCode from "../../constants/error-code";

const CreatingFormMixin = {
    props: {
        loading: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            formData: this.defaultFormData(),
            stage: this.getFormStage(this.$route),
            processing: false,
            deleting: false,
            ECreatingFormStage,
            backRoute: null,
        }
    },
    computed: {
        isEditing() {
            return ~[
                ECreatingFormStage.UPDATING,
                ECreatingFormStage.CREATING
            ].indexOf(this.stage);
        },
        routes() {
            return {
                save: `${this.$route.meta.baseUrl}/save`,
                info: `${this.$route.meta.baseUrl}/info`,
                delete: `${this.$route.meta.baseUrl}/delete`,
            }
        },
    },
    beforeRouteLeave (to, from, next) {
        if (this.stage === ECreatingFormStage.SUCCESS || this.stage === ECreatingFormStage.READONLY) {
            next();
            return;
        }
        const answer = window.confirm(this.$t('common.confirmation.leavePage'));
        if (answer) {
            this.Util.askUserWhenLeavePage(false);
            next()
        } else {
            next(false)
        }
    },
    methods: {
        // $route, from or to
        getFormStage(route) {
            if (route.params.action === 'edit') {
                return ECreatingFormStage.UPDATING;
            } else {
                return ECreatingFormStage.CREATING;
            }
        },
        onInputChange() {
            this.Util.askUserWhenLeavePage(this.isEditing);
        },
        getAttributeData(additionField = {}) {
            return {
                value: null,
                error: null,
                ...additionField,
            }
        },
        defaultFormData() {
            return {
                name: this.getAttributeData(),
            }
        },
        showErrors(errors, msg = null) {
            this.footerMsg = msg;
            Object.keys(this.formData).forEach((key) => {
                if (key === 'id') {
                    return;
                }
                this.formData[key].error = errors[key] && errors[key][0] || null;
            });
        },
        validateValue() {
            let requiredField = ['name'];
            let isValid = true;
            /*requiredField.forEach((key) => {
				let hasError = false;
				switch (key) {
					default:
						if (!this.formData[key].value || !this.formData[key].value.length) {
							hasError = true;
						}
						break;
				}
				this.formData[key].error = hasError ? this.$t('validation.required', {
					attribute: this.$t(`attributes.${this.StringUtil.switchCase(key, 'snake')}`),
					Attribute: this.$t(`attributes.${this.StringUtil.switchCase(key, 'snake')}`),
				}) : null;
				isValid = isValid && !hasError;
			});*/
            return isValid;
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
                    default:
                        data[key] = this.formData[key].value;
                        break;
                }
            });
            return data;
        },
        async submit() {
            this.$store.commit('updateLoadingState', true);
            if (this.stage === ECreatingFormStage.UPDATING) {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm(this.$t('edit.confirm'), resolve);
                });
                if (!confirm) {
                    this.$store.commit('updateLoadingState', false);
                    return;
                }
            }
            let data = this.getFormData();
            if (!data) {
                return;
            }

            this.processing = true;
            this.Util.post({
                url: this.routes.save,
                data,
                failCb: this.showErrors,
            }).done(async (res) => {
                if (res.error !== EErrorCode.NO_ERROR) {
                    this.Util.showMsg('error', null, res.msg);
                    this.processing = false;
                    return;
                }

                this.onSaveSuccess(res.msg);
            }).fail(() => {
                this.processing = false;
            }).always(() => {
                this.$store.commit('updateLoadingState', false);
            });
        },
        onSaveSuccess(msg) {
            this.Util.showMsg('success', null, msg, {
                onHidden: () => {
                    this.Util.askUserWhenLeavePage(false);
                    this.stage = ECreatingFormStage.SUCCESS;
                    this.$nextTick(() => {
                        this.backRoute ? this.$router.push(this.backRoute) : this.$router.go(-1);
                    });
                }
            });
        },
        async deleteItem() {
            if (this.deleting) {
                return;
            }

            let confirm = await new Promise((resolve) => {
                this.Util.confirmDelete(this.$t('object_name'), resolve);
            });
            if (!confirm) {
                return;
            }
            this.deleting = true;
            this.processing = true;
            this.Util.post({
                url: this.routes.delete,
                data: {
                    id: this.formData.id,
                }
            }).done(async (res) => {
                if (res.error) {
                    this.Util.showMsg('error', null, res.msg);
                    return;
                }

                this.onDeleteSuccess(res.msg);
            }).fail(() => {
                this.Util.showMsg('error', null, this.$t('common.error.unknown'));
            }).always(() => {
                this.deleting = false;
                this.processing = false;
            });
        },
        onDeleteSuccess(msg) {
            this.Util.showMsg('success', null, msg, {
                onHidden: () => {
                    this.Util.askUserWhenLeavePage(false);
                    this.stage = ECreatingFormStage.SUCCESS;
                    this.$nextTick(() => {
                        this.backRoute ? this.$router.push(this.backRoute) : this.$router.go(-1);
                    });
                }
            });
        },
    }
};
export default CreatingFormMixin;
