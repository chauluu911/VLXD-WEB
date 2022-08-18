'use strict';

import creatingFormMixin from '../mixins/creating-form-mixin';
import ECreatingFormStage from '../../constants/creating-form-stage';

let viewData = $('#view-data').val();
viewData = !!viewData ? atob(viewData) : '{}';
viewData = JSON.parse(viewData);

const app = new Vue({
	name: 'AdsSubscriptionForm',
	el: '#ads-subscription-form',
	mixins: [ creatingFormMixin ],
	computed: {
		routes() {
			return {
				save: `/post/${viewData.code}/subscription`,
			}
		},
	},
	created() {
		/*Object.keys(lessonListMessage).forEach((locale) => {
			this.$i18n.mergeLocaleMessage(locale, lessonListMessage[locale]);
		});*/
		common.askUserWhenLeavePage();
	},
	methods: {
		defaultFormData() {
			return {
				subscriptionPrice: this.getAttributeData(),
			};
		},
		onFormSubmit() {
			return new Promise(async (resolve, reject) => {
				common.confirm(this.$t('Bạn có chắc muốn thanh toán với gói 3 tháng không?'), (confirm) => {
					confirm ? resolve() : reject();
				});
			})
		},
		validateValue() {
			let requiredField = ['subscriptionPrice'];
			let isValid = true;
			requiredField.forEach((key) => {
				let hasError = false;
				switch (key) {
					default:
						if (!this.formData[key].value) {
							hasError = true;
						}
						break;
				}
				this.formData[key].error = true;
				isValid = isValid && !hasError;
			});
			return isValid;
		},
		getFormData() {
			if (!this.validateValue()) {
				return null;
			}

			let data = {};
			Object.keys(this.formData).forEach((key) => {
				switch (key) {
					default:
						data[key] = this.formData[key].value;
						break;
				}
			});
			return data;
		},
		showErrors(errors, msg = null) {
			this.footerMsg = msg;
			Object.keys(this.formData).forEach((key) => {
				this.formData[key].error = errors[key] && errors[key][0] || null;
			});
		},
		onSaveSuccess(msg, data, response) {
			common.askUserWhenLeavePage(false);
			this.stage = ECreatingFormStage.SUCCESS;
			common.showMsg('success', null, msg, {
				onHidden: () => {
					window.location.assign(response.redirectTo);
				}
			})
		},
	},
});