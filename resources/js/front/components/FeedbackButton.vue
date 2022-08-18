<template>
	<div class="d-inline-block">
		<a href="javascript:void(0)" class="text-black" @click="showFeedbackModal">
			<div class="d-flex-center-y">
				<span class="material-icons-outlined mr-2">&#xE0BE;</span>
				{{ $t('button.feedback') }}
			</div>
		</a>
		<div ref="feedbackModalEl" class="modal" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-sm" role="document">
				<div class="modal-content">
					<div class="modal-body">
						<h4 class="text-center">{{ $t('title.feedback') }}</h4>
						<form>
							<div class="form-group">
								<label for="feedback-user-code-input">{{ $t('attribute.userCode') }}</label>
								<input
									id="feedback-user-code-input"
									v-model="formData.userCode.value"
									class="form-control bg-white"
									:class="{'is-invalid': formData.userCode.error}"
								>
							</div>
							<div class="form-group">
								<label for="feedback-content-input">{{ $t('attribute.content') }}</label>
								<textarea
									id="feedback-content-input"
									v-model="formData.content.value"
									class="form-control bg-white"
									rows="3"
									:class="{'is-invalid': formData.content.error}"
								/>
							</div>
							<button
								type="button"
								class="btn btn-primary2 rounded-pill w-100"
								data-dismiss="modal"
								@click="submit"
							>
								{{ $t('button.send') }}
							</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import creatingFormMixin from "../mixins/creating-form-mixin";
	import Vuex from "vuex";
	import feedbackFormMessages from "../../locales/front/feedback-form";
	import ECreatingFormStage from "../../constants/creating-form-stage";

	export default {
		name: "FeedbackButton",
		mixins: [ creatingFormMixin ],
		computed: {
			...Vuex.mapState(['authState']),
			routes() {
				return {
					save: '/feedback/save',
				}
			}
		},
		watch: {
			authState(val) {
				this.formData.userCode.value = val.code;
			}
		},
		created() {
			Object.keys(feedbackFormMessages).forEach((locale) => {
				this.$i18n.mergeLocaleMessage(locale, feedbackFormMessages[locale]);
			});
		},
		methods: {
			defaultFormData() {
				return {
					userCode: this.getAttributeData({
						value: this.authState ? this.authState.code : null,
					}),
					content: this.getAttributeData(),
				}
			},
			showFeedbackModal() {
				this.formData = this.defaultFormData();
				$(this.$refs.feedbackModalEl).modal('show');
			},
			onSaveSuccess(msg, data, response) {
				common.askUserWhenLeavePage(false);
				this.stage = ECreatingFormStage.SUCCESS;
				common.showMsg('success', null, msg);
			},
		},
	}
</script>