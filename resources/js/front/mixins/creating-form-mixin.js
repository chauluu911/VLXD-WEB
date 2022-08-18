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
            stage: null,
            processing: false,
            backRoute: null,
            ECreatingFormStage,
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
                save: `${window.location.pathname}/save`,
                info: `${window.location.pathname}/info`,
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
        onFormSubmit() {
            return new Promise(async (resolve, reject) => {
                if (this.stage === ECreatingFormStage.UPDATING) {
                    let confirm = await new Promise((resolve2) => {
                        this.Util.confirm(this.$t('edit.confirm'), resolve2);
                    });
                    if (!confirm) {
                        reject();
                    }
                }
                resolve();
            })
        },
        async submit() {
            try {
                await this.onFormSubmit();
            } catch (e) {
                if (e) {
                    console.error(e);
                }
                return;
            }

            let data = this.getFormData();
            if (!data) {
                return;
            }

            common.loadingScreen.show();
            common.post({
                url: this.routes.save,
                data,
                failCb: this.showErrors,
            }).done(async (res) => {
                if (res.error !== EErrorCode.NO_ERROR) {
                    // common.showMsg('error', null, res.msg);
                    common.alert(res.msg);
                    common.loadingScreen.hide();
                    return;
                }

                this.onSaveSuccess(res.msg, res.data, res);
            }).fail(() => {
                common.loadingScreen.hide();
            });
        },
        onSaveSuccess(msg, data) {
            this.Util.showMsg('success', null, msg, {
                onHidden: () => {
                    this.Util.askUserWhenLeavePage(false);
                    this.stage = ECreatingFormStage.SUCCESS;
                    this.$nextTick(() => {
                        this.backRoute ? window.location.push(this.backRoute) : window.history.go(-1);
                    });
                }
            });
        },
        async deleteItem() {
            if (this.deleting) {
                return;
            }

            let confirm = await new Promise((resolve) => {
                common.confirmDelete(this.$t('object_name'), resolve);
            });
            if (!confirm) {
                return;
            }
            this.deleting = true;
            this.processing = true;
            common.post({
                url: this.routes.delete,
                data: {
                    id: this.formData.id,
                }
            }).done(async (res) => {
                if (res.error) {
                    // common.showMsg('error', null, res.msg);
                    common.alert(res.msg);
                    return;
                }

                this.onDeleteSuccess(res.msg);
            }).fail(() => {
                common.showMsg('error', null, this.$t('common.error.unknown'));
            }).always(() => {
                this.deleting = false;
                this.processing = false;
            });
        },
        onDeleteSuccess(msg) {
            common.showMsg('success', null, msg, {
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
