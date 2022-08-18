'use strict';

import creatingFormMixin from '../mixins/creating-form-mixin';
import ECreatingFormStage from '../../constants/creating-form-stage';
import ECategoryType from "../../constants/category-type";
import ImageCropper from "../../components/ImageCropper";

let viewData = $('#view-data').val();
viewData = !!viewData ? atob(viewData) : '{}';
viewData = JSON.parse(viewData);

const app = new Vue({
	name: 'AdsCreatingForm',
	el: '#ads-creating-form',
	mixins: [ creatingFormMixin ],
	components: { ImageCropper },
	data() {
		let stage;
		if (viewData.code) {
			stage = ECreatingFormStage.UPDATING;
		} else {
			stage = ECreatingFormStage.CREATING;
		}
		return {
			stage,
			acceptTermAndPolicy: this.getAttributeData(),
			initialized: false,
		}
	},
	computed: {
		routes() {
			return {
				countryList: '/public/country',
				areaList: '/public/area-stack',
				categoryList: '/public/category',
				info: this.formData.postCode ? `/promo/${this.formData.postCode}/info` : '/promo/temp/info',
				save: '/promo/save',
			}
		},
		areaFilterData() {
			return {
				pageSize: 15,
				countryId: this.formData.country.value ? this.formData.country.value.id : null,
			}
		},
		categoryFilterData() {
			return {
				pageSize: 15,
				type: ECategoryType.ADS
			};
		},
	},
	watch: {
		"acceptTermAndPolicy.value"() {
			this.acceptTermAndPolicy.error = false;
		},
		"acceptTermAndPolicy.error"(val) {
			if (val) {
				$('#term-and-policy-checkbox').tooltip({
					boundary: 'window',
					title: this.$t('auth.auth-area.please-accept-term-and-policy'),
					placement: 'top',
					trigger: 'focus',
				}).focus();
			} else {
				$('#term-and-policy-checkbox').tooltip('dispose')
			}
		}
	},
	created() {
		/*Object.keys(lessonListMessage).forEach((locale) => {
			this.$i18n.mergeLocaleMessage(locale, lessonListMessage[locale]);
		});*/
		if (viewData.adsCode) {
			this.initEditForm();
		}
	},
	methods: {
		defaultFormData() {
			return {
				url: this.getAttributeData(),
				image: this.getAttributeData({
					original: null,
					cropped: null,
					isEditing: true,
				}),
				country: this.getAttributeData(),
				area: this.getAttributeData(),
				category: this.getAttributeData(),
			};
		},
		initEditForm() {
			common.loadingScreen.show();
			return common.get({
				url: this.routes.info,
			}).done(async (res) => {
				if (res.error) {
					common.confirm(
						res.msg,
						(confirm) => {
							if (!confirm) {
								// this.$router.go(-1);
								return;
							}
							this.initEditForm();
						},
						{
							okTitle: this.$t('common.button.retry'),
							cancelTitle: this.$t('common.button.back'),
						},
					);
					return;
				}
				Object.keys(res.data).forEach((key) => {
					switch (key) {
						case 'code':
							break;
						case 'image':
							this.formData.image = this.getAttributeData({
								original: res.data[key].original,
								cropped: res.data[key].cropped,
								isEditing: false,
							})
							break;
						case 'category':
						case 'country':
						case 'url':
							try {
								this.$set(this.formData[this.StringUtil.switchCase(key, 'camel')], 'value', res.data[key]);
							} catch (e) {
								console.error(this.StringUtil.switchCase(key, 'camel'), e);
							}
							break;
					}
				});
			}).always(() => {
				common.loadingScreen.hide();
			});
		},
		onImageInputChange(evt) {
			if (!evt || !evt.target || !evt.target.files || !evt.target.files.length) {
				return;
			}

			for (let i = 0; i < evt.target.files.length; i++) {
				let image = {
					file: evt.target.files[i],
					src: null,
					loading: true,
				};
				this.formData.resources.value.push(image);
				fileUtil.fileToUrl(image.file).then((url) => {
					image.src = url;
					image.loading = false;
				});
			}

			$(evt.target).val('');
		},
		onFormSubmit() {
			return new Promise(async (resolve, reject) => {
				if (!this.acceptTermAndPolicy.value) {
					this.acceptTermAndPolicy.error = true;
					$('#term-and-policy-checkbox').focus();
					reject();
				}
				resolve();
			})
		},
		async getFormData() {
			if (!this.validateValue()) {
				return null;
			}

			let data = new FormData();
			await Promise.all(
				Object.keys(this.formData).map(async (key) => {
					switch (key) {
						case 'category':
						case 'country':
							if (this.formData[key].value) {
								data.append(key, this.formData[key].value.id);
							}
							break;
						case 'image':
							if (this.formData.image.original && typeof this.formData.image.original !== 'string') {
								data.append('image[original]', this.formData.image.original);
							}

							let croppedImage = await this.$refs.imageCropperEl.val();
							if (croppedImage) {
								data.append('image[cropped]', croppedImage);
							}
							break;
						default:
							let val = this.formData[key].value;
							if (val === null || typeof val === 'string' && !val.trim()) {
								break;
							}
							data.append(key, val);
							break;
					}
					return Promise.resolve();
				})
			);
			return data;
		},
		showErrors(errors, msg = null) {
			this.footerMsg = msg;
			Object.keys(this.formData).forEach((key) => {
				if (key === 'step' || key === 'adsCode') {
					return;
				}
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