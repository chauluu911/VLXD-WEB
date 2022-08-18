<template>
   	<b-row>
		<b-col cols="48">
			<div class="content__inner">
				<b-row class="pb-2" style="border-bottom: 3px solid gray">
					<b-col cols="24">
						<span class="span__title-detail mr-4">{{action == 'create' ? $t('common.button.add2', {'objectName': $t('object_name')}).toUpperCase() : $t('info').toUpperCase()}}</span>
					</b-col>
					<b-col cols="24">
						<template >
							<b-button variant="primary" class="float-right ml-3" @click="updateInfo">
								<i class="far fa-save"></i>
								{{$t('save')}}
							</b-button>
                            <b-link
                                @click="goBack"
								class="float-right btn">
								<span>{{$t('back')}}</span>
							</b-link>
						</template>
					</b-col>
				</b-row>
				<b-row>
				   	<b-col cols="16" class="pt-3">
						<div>
							<cropper-image
								ref="imageCropperEl"
								@cropper-created="onCropperCreated"
								@cropper-reset="resetCropper"
                   				:image-url="img.url"
                   				:aspect-ratios="[{name: null, value: null}]"
                   				:disable-size="true"
                   				:key="genKey">
       						<template #text-drop-image><div class="btn-upload">{{ $t('upload') }}</div></template>
       						</cropper-image>
       						<div v-if="formData.errors.blob && formData.errors.blob[0]" class="font-weight-bold invalid-feedback d-block">
								{{formData.errors.blob[0]}}
							</div>
						</div>
				   	</b-col>
				</b-row>
				<b-row>
				   <b-col cols="48" class="pt-3">
						<b-form>
							<b-row>
								<b-col md="16">
									<b-form-group class="font-weight-bold"
										:label-class="'required'"
									  	:label="$t('data.title')"
									  	:state="!!formData.errors.title"
									  	:invalid-feedback="formData.errors.title && formData.errors.title[0]">
										<b-form-input class="w-100" v-model.trim="formData.value.title" required
										  	:placeholder="$t('placeholder.title')"
										  	:disabled="disable"
										  	:state="formData.errors.title && !formData.errors.title[0]"
										  	@change="onInputChange"
										/>
									</b-form-group>
								</b-col>
							</b-row>
							<b-row>
								<b-col md="48">
									<b-form-group
										:label-class="'required'"
									  	:label="$t('data.content')"
									  	:state="!!formData.errors.content"
									  	:invalid-feedback="formData.errors.content && formData.errors.content[0]">
										<div v-if="formData.errors.content && formData.errors.content[0]" class="font-weight-bold invalid-feedback d-block">
											{{formData.errors.content[0]}}
										</div>
										<ck-document
											v-model="formData.value.content"
											class="w-100"
											:ck-class="['border border-top-0']"
											:ck-style="{height: '500px'}"
											:placeholder="$t('placeholder.content')"></ck-document>
									</b-form-group>
								</b-col>
							</b-row>
						</b-form>
				   </b-col>
				</b-row>
			</div>
		</b-col>
	</b-row>
</template>

<script>
	import {mapState} from "vuex";
	import newsInfoMessage from "../../locales/back/news/news-info";
	import EErrorCode from "../../constants/error-code";
	import EStatus from "../../constants/status";
	import CropperImage from '../../../js/components/ImageCropper';

	export default {
		i18n: {
			messages: newsInfoMessage
		},
		components: {
			CropperImage
		},
		inject: ['Util', 'DateUtil'],
		data() {
			return {
				formData: this.$_formData(),
				disable: this.$route.params.action == 'edit' || this.$route.params.action == 'create' ? false : true,
				info: null,
				action: this.$route.params.action,
				EErrorCode,
				EStatus,
				img: {
            		file: null,
            		blob: null,
            		url: null,
            	},
            	genKey: 0,
			}
		},
		computed: {
			...mapState(['filterValueState', 'queryFilterState']),
			routerName() {
                return 'new.info';
            },
            getBackRoute() {
                return 'new.list';
            }
		},
		created() {
			this.$store.commit('updateBreadcrumbsState', [
                {
                    text: this.$t('news-list'),
                    to: { name: 'news.list' }
                }
            ]);
            this.$store.commit('updateFilterFormState', [
                {
                    label: this.$t('constant.status.status'),
                    type: 'select',
                    name: 'status',
                    options: [
                        {
                            name: this.$t('constant.status.active'),
                            value: EStatus.ACTIVE,
                        },
                        {
                            name: this.$t('constant.status.deleted'),
                            value: EStatus.DELETED,
                        },
                    ]
                },
                {
                    type: 'date',
                    name: 'createdAtFrom',
                    placeholder: this.$t('placeholder.filter.created_at_from'),
                    dropleft: true,
                },
                {
                    type: 'date',
                    name: 'createdAtTo',
                    placeholder: this.$t('placeholder.filter.created_at_to'),
                    dropleft: true,
                },
            ]);
            this.$store.commit('updateQueryFilterState', {
                enable: true,
                placeholder: this.$t('filter.title') + ' / ' + this.$t('filter.content'),
            });
		},
		mounted() {
			if (this.$route.params.newsId) {
				this.getInfoNews();
			}
		},
		watch: {
		    filterValueState(val) {
		    	this.$router.push({name: 'news.list', query: val.value});
		    },
		},
		beforeRouteUpdate(to, from, next) {
            this.disable = to.params.action == 'edit' || to.params.action == 'create' ? false : true;
            if(this.disable) {
                this.formData = this.$_formData();
                this.getInfoNews();
            }
            next();
        },
		methods: {
			onInputChange() {
				if (this.disable == false) {
					this.Util.askUserWhenLeavePage();
				}
            },
            resetCropper() {
            	this.img = {
            		file: null,
            		blob: null,
            		url: null,
            	}
            },
            onCropperCreated({file, imageUrl, indexImg}) {
				setTimeout(() => {
	                this.$refs.imageCropperEl.val()
	                    .then(blob => {
	                        if (blob) {
	                        	console.log(blob);
	                        	this.img.file = file;
	                        	this.img.blob = blob;
	                        	this.img.url = imageUrl;
	                        }
	                    });
                }, 300)
			},
			$_formData() {
				return {
					value: {
						id: this.$route.params.newsId,
						title: null,
						short_content: null,
						content: null,
						image: null,
					},
					errors: {
						title: null,
						shortContent: null,
						content: null,
						image: null,
						blob: null
					}
				}
			},
			getInfoNews() {
				this.genKey ++;
				this.action = this.$route.params.action;
				this.formData = this.$_formData();
				this.selectedFile = null;
				this.Util.loadingScreen.show();
                this.Util.get({
                    url: `${this.$route.meta.baseUrl}/${this.formData.value.id}/info`,
                    data: {
                    	'type': this.userType,
                    },
                }).done(response => {
                	this.genKey ++;
                    if (response.error == EErrorCode.ERROR) {
                        this.Util.showMsg2(response);
                        this.$router.push({name: 'news.list'});
                    }
                    this.formData.value = response.news;
                    this.img.url = response.news.originalAvatarPath;
                }).fail((error) =>{
                	if (error.status == "404") {
                		this.$router.push({name: '404'});
                	}
                }).always(() => {
                    if (this.$route.params.action === 'edit') {
                    	this.disable = false;
                    }
                    this.Util.loadingScreen.hide();
                });
			},
			async deleteUser() {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirmDelete(this.$t('object_name'), resolve);
                });
                if (!confirm) {
                    return;
                }

                this.processing = true;
                this.Util.post({
                    url: `/api/back/seller/delete`,
                    data: {
                        id: this.formData.id,
                    },
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }
                    this.Util.showMsg('success', null, res.msg);
                    this.$router.push({name: 'news.list'});
                }).always(() => {
                    this.processing = false;
                });
            },
            async updateInfo() {
            	if (this.$route.params.action === 'edit') {
            		let confirm = await new Promise((resolve) => {
	                    this.Util.confirm(this.$t('confirm.edit'), resolve);
	                });
	                if (!confirm) {
	                    return;
	                }
            	}

				this.Util.loadingScreen.show();
                this.disable = true;
                let formData = new FormData();
                Object.keys(this.formData.value).forEach((key) => {
                    switch (key) {
                        case 'image':
                            if (this.img.file != null || this.img.blob != null) {
                            	formData.append('file', this.img.file);
                            	formData.append('blob', this.img.blob);
                            }
                            if (this.img.url != null) {
                            	formData.append('url', this.img.url);
                            }
                            break;
                        default:
                            if (this.formData.value.[key] == null) {
                            	this.formData.value.[key] = ''
                            }
                            formData.append(key, this.formData.value[key]);
                            break;
                    }
                });
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/save`,
                    data: formData,
                    errorModel: this.formData.errors,
                    processData: false,
                    contentType: false,
                }).done(response => {
                    if (response.error == EErrorCode.ERROR) {
                        this.formData.errors = response.msg;
                        return false;
                    }
                    this.Util.showMsg2(response);
                    this.$router.push({name: 'news.list'});
                }).always(() => {
                    this.disable = false;
                    this.Util.loadingScreen.hide();
                });
			},
            goBack() {
                this.$router.go(-1)
            }
		}
	}
</script>

<style scoped>

</style>
