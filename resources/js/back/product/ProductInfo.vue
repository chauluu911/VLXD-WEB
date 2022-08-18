<template>
   	<b-row>
		<b-col cols="48">
			<div class="content__inner">
				<b-row class="pb-2" style="border-bottom: 3px solid gray">
					<b-col cols="24">
						<span class="span__title-detail mr-4">{{action == 'create' ? $t('common.button.add2', {'objectName': $t('employee')}).toUpperCase() : $t('info').toUpperCase()}}</span>
						<div class="btn text-white cursor-default mb-2" v-if="!!status.string" :class="status.value == EStatus.DELETED ? 'bg-danger' : 'bg-primary'">
                            <span>
                                {{ status.string }}
                            </span>
                        </div>
					</b-col>
					<b-col cols="24" v-if="action === 'create'">
						<b-button variant="primary" class="float-right ml-3" @click="updateInfo">
							<i class="far fa-save"></i>
							{{$t('save')}}
						</b-button>
					</b-col>
					<b-col cols="24" v-if="status.value != EStatus.DELETED && action !== 'create'">
						<template v-if="disable">
							<b-button variant="outline-primary" class="float-right ml-3" @click="deleteShop">
								<i class="fas fa-trash-alt"/>
								{{$t('common.tooltip.delete')}}
							</b-button>
							<b-button :to="{name: routerName, params:{userId: formData.id, action: 'edit'}}" variant="outline-primary" class="float-right">
								<i class="fas fa-edit"/>
								{{$t('common.tooltip.edit')}}
							</b-button>
						</template>
						<template v-else>
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
				   <b-col cols="48" class="pt-3">
						<b-form>
							<b-row>

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
	import ECustomerType from "../../constants/customer-type";
	import EUserType from "../../constants/user-type";
	import shopInfoMessage from "../../locales/back/shop/shop-info";
	import EErrorCode from "../../constants/error-code";
	import EStatus from "../../constants/status";

	export default {
		i18n: {
			messages: shopInfoMessage
		},
		inject: ['Util', 'DateUtil'],
		data() {
			return {
				ECustomerType,
				formData: this.$_formData(),
				disable: this.$route.params.action == 'edit' || this.$route.params.action == 'create' ? false : true,
				selectedFile: null,
				info: null,
				status: {
					value: null,
					string: null
				},
				action: this.$route.params.action,
				EErrorCode,
				//	: [],
				roles: [],
				EStatus,
			}
		},
		computed: {
			...mapState(['filterValueState', 'queryFilterState']),
			routerName() {
                if (this.customerType == ECustomerType.SELLER) {
                    return 'seller.info';
                }else if(this.customerType == ECustomerType.BUYER) {
                    return 'buyer.info';
                }else if(this.customerType == ECustomerType.ADVERTISER) {
                    return 'advertiser.info';
                }else {
                    return 'employee.info';
                }
            },
            getBackRoute() {
                if (this.customerType === ECustomerType.BUYER) {
                    return 'buyer.list';
                } else if (this.customerType === ECustomerType.SELLER) {
                    return 'seller.list';
                } else if (this.customerType === ECustomerType.ADVERTISER) {
                    return 'advertiser.list';
                } else {
                    return 'employee.list';
                }
            }
		},
		created() {
			this.$store.commit('updateBreadcrumbsState', [

            ]);
            this.$store.commit('updateFilterFormState', [
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
                placeholder: this.$t('filter.shop'),
            });
		},
		mounted() {
			this.getProductInfo();
		},
		watch: {
		    filterValueState(val) {
		    	this.$router.push({name: 'product.list', query: val.value});
		    },
		},
		beforeRouteUpdate(to, from, next) {
            this.disable = to.params.action == 'edit' || to.params.action == 'create' ? false : true;
            if(this.disable) {
                this.formData = this.$_formData();
                this.getInfoUser();
            }
            next();
        },
		methods: {
			onInputChange() {
				if (this.disable == false) {
					this.Util.askUserWhenLeavePage();
				}
            },
			$_formData() {
				return {
					productId: this.$route.params.productId,
					name: null,
					phone: null,
					email: null,
					address: null,
					image: null,
					description: null,
					errors: {
						name: null,
						phone: null,
						email: null,
						description: null,
						address: null,
					}
				}
			},
			onSelectAvatar(e) {
				$("#resource").text('');
	            if (window.File && window.FileList && window.FileReader) {
	            	let fileReader = new FileReader();
	            	let files = e.target.files;
	            	this.selectedFile = files[0];
	            	fileReader.onload = (function(e) {
	            		$("#resource").append("<img alt='avatar' class=\"avatar\" src=\"" + e.target.result + "\"  />");
                    });
	            	fileReader.readAsDataURL(this.selectedFile);
	            }
			},
			removeAvatar() {
				this.selectedFile = null;
				this.$refs.fileInputEl.value = '';
			},
			getProductInfo() {
				this.action = this.$route.params.action;
				this.formData = this.$_formData();
				this.selectedFile = null;
				this.Util.loadingScreen.show();
                this.Util.get({
                    url: `${this.$route.meta.baseUrl}/${this.formData.productId}/info`,
                }).done(response => {
                        if (response.error == EErrorCode.ERROR) {
                            this.Util.showMsg2(response);
                            this.$router.push({name: 'product.list'});
                        }
                        console.log(response)
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
			async deleteShop() {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirmDelete(this.$t('object_name'), resolve);
                });
                if (!confirm) {
                    return;
                }

                this.processing = true;
                this.Util.post({
                    url: `/api/back/shop/delete`,
                    data: {
                        id: this.formData.productId,
                    },
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }
                    this.Util.showMsg('success', null, res.msg);
                    if (this.customerType == ECustomerType.BUYER) {
                    	this.$router.push({name: 'buyer.list'});
                    }else if(this.customerType == ECustomerType.SELLER) {
                    	this.$router.push({name: 'seller.list'});
                    }else if(this.customerType == ECustomerType.ADVERTISER) {
                    	this.$router.push({name: 'advertiser.list'});
                    }else {
                    	this.$router.push({name: 'employee.list'});
                    }
                }).always(() => {
                    this.processing = false;
                });
            },
            async updateInfo() {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm(this.$route.params.action === 'create' ? this.$t('confirm.create') : this.$t('confirm.edit'), resolve);
                });
                if (!confirm) {
                    return;
                }

				this.Util.loadingScreen.show();
                this.disable = true;
                let formData = new FormData();
                Object.keys(this.formData).forEach((key) => {
                    switch (key) {
                        case 'country':
                        	if (this.formData[key].value == null) {
                        		this.formData[key].value = '';
                        	}
                        	formData.append(key, this.formData[key].value);
                            break;
                        case 'image':
                            if (this.selectedFile != null) {
                            	formData.append(key, this.selectedFile);
                            }
                            break;
                        case 'dob':
                        	let date = new Date(this.formData[key]);
                            formData.append(key, this.DateUtil.getDateString(date));
                            break;
                        case 'role':
                        	let selectedRoles = this.roles.filter(item => item.enable).map(item => item.id);
                        	if (selectedRoles.length == 0) {
                        		selectedRoles = '';
                        	}else {
                        		selectedRoles.forEach((item, index) => {
                                    formData.append('role[]', item);
                                });
                        	}
                            break;
                        default:
                            if (this.formData[key] == null) {
                            	this.formData[key] = ''
                            }
                            formData.append(key, this.formData[key]);
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
                        // if (this.userType == EUserType.INTERNAL_USER) {
                        // 	this.$router.push({name: 'employee.list'});
                        // 	return;
                        // }
                        this.getInfoShop();
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
