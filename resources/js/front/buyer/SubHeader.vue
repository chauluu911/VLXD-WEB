<template>
	<div>
	    <div class="row bg-white" style="line-height: 3.5" id="accordionExample">
	        <div class="col-md-8 pr-0 text-center">
	            <a
	                data-toggle="collapse"
	                href="#collapseOne" 
	                class="text-primary text-decoration-none"
	            >
	            	<span class="mr-1">
	            		{{$t('sub-header.label.make-a-new-search')}}
	            	</span>
	                <f-chevron-down-icon size="1.5x" class="custom-class"></f-chevron-down-icon>
	            </a>
	          </div>
	          <div class="col-md-6 p-0 text-center">
	            <a 
	                href="javascript:void(0)" 
	                class="text-decoration-none text-primary text-decoration-none text-primary border-right border-left px-2"
	                @click="modifyMySearch"
	            >
	                {{$t('sub-header.label.modify-my-search')}}
	            </a>
	          </div>
	          <div class="col-md-5 p-0 text-center">
	            <a 
	                href="javascript:void(0)"
	                class="text-decoration-none text-primary"
	                @click="saveMySearch"
	            >
	                {{$t('sub-header.label.save-my-search')}}
	            </a>
	          </div>
	          <div class="col-md-5 p-0 text-center">
	            <a 
	                href="#collapseThree"
	                data-toggle="collapse"
	                class="text-decoration-none text-primary text-decoration-none text-primary border-left px-2"
	                @click="search()"
	            >
	                {{$t('sub-header.label.saved-business')}}
	            </a>
	          </div>
	    </div>
	    <div class="row bg-white">
	    	<div class="col-md-48 border-top">
	            <div class="row">
	                <div class="col-md-48">
	                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample" :class="{'hide': collapse}">
	                        <div class="card-body p-0">
	                            <form>
	                            	<div class="row mt-3">
                                        <div class="col-md-48">
                                            <div class="form-group mb-2">
                                                <span for="key-search" class="text-primary3">
                                                	{{$t('sub-header.label.key-search')}}
                                                </span>
                                                <input type="text" class="form-control" id="key-search" placeholder="Key search" autocomplete="false" v-model="formData.q" :placeholder="$t('sub-header.placeholder.key-search')">
                                            </div>
                                        </div>
                                    </div>
	                                <div class="row">
	                                    <div class="col-md-32">
	                                        <div class="row">
	                                            <div class="col-md-24">
	                                                <div class="form-group mb-2">
	                                                    <span for="country" class="text-primary3">
	                                                    	{{$t('sub-header.label.country')}}
	                                                	</span>
	                                                    <c-select
	                                                        id="country"
	                                                        v-model="formData.country"
	                                                        :search-route="'public/country'"
	                                                        :custom-request-data="countryFilterData"
	                                                        :placeholder="$t('sub-header.placeholder.country')"
	                                                        :watchFilterChange="!init"
	                                                    >
	                                                    </c-select>
	                                                </div>
	                                            </div>
	                                            <div class="col-md-12">
	                                                <div class="form-group mb-2">
	                                                    <span class="text-primary3">
	                                                        {{$t('sub-header.label.min-price')}}
	                                                    </span>
	                                                    <div class="input-group">
	                                                        <div class="input-group-prepend">
	                                                            <span class="input-group-text bg-white border-right-0" v-if="formData.country">
	                                                                {{formData.country.currency_sign}}
	                                                            </span>
	                                                        </div>
	                                                        <money 
	                                                        	v-model="formData.priceLt"
	                                                        	v-bind="money"
	                                                        	class="form-control border-left-0 px-0"
	                                                        >
	                                                        </money>
	                                                    </div>
	                                                </div>
	                                            </div>
	                                            <div class="col-md-12">
	                                                <div class="form-group mb-2">
	                                                    <span class="text-primary3">
	                                                        {{$t('sub-header.label.max-price')}}
	                                                    </span>
	                                                    <div class="input-group">
	                                                        <div class="input-group-prepend">
	                                                            <span class="input-group-text bg-white border-right-0" v-if="formData.country">
	                                                                {{formData.country.currency_sign}}
	                                                            </span>
	                                                        </div>
	                                                        <money 
	                                                        	v-model="formData.priceGt"
	                                                        	v-bind="money"
	                                                        	class="form-control border-left-0 px-0"
	                                                        >
	                                                        	
	                                                        </money>
	                                                    </div>
	                                                </div>
	                                            </div>
	                                        </div>
	                                        <div class="row">
	                                            <div class="col-md-48">
	                                                <div class="form-group mb-0">
	                                                    <span for="city" class="text-primary3">{{$t('sub-header.label.city-state')}}</span>
	                                                    <c-select
	                                                        id="city"
	                                                        v-model="formData.province"
	                                                        :search-route="'/public/area-stack'"
	                                                        :custom-request-data="areaFilterData"
	                                                        :watchFilterChange="!init"
	                                                        :disabled="!formData.country"
	                                                        :multiple="true"
	                                                        :taggable="true"
	                                                        ref="areafilterEL"
	                                                        :placeholder="$t('sub-header.placeholder.city-state')"
	                                                    >
	                                                    </c-select>
	                                                </div>
	                                            </div>
	                                        </div>
	                                    </div>
	                                    <div class="col-md-16">
			                                <div class="row">
			                                    <div class="col-md-48">
			                                    	<span class="text-primary3">
			                                    		{{$t('sub-header.label.category')}}
			                                    	</span>
			                                        <div class="form-group mb-1">
			                                            <c-select
			                                                id="class-1"
			                                                v-model="formData.currentClass1"
			                                                :search-route="'public/category'"
			                                                :custom-request-data="class1CategoryFilterData"
			                                                :watchFilterChange="!init"
			                                                :placeholder="$t('sub-header.placeholder.category')"
			                                            />
			                                        </div>
			                                    </div>
			                                    <div class="col-md-48">
			                                    	<span>
	                                                    &nbsp
	                                                </span>
			                                        <div class="row">
	                                                	<div class="col-4 pr-0">
	                                                		<f-down-right-icon size="1.5x" class="custom-class mt-1"></f-down-right-icon>
	                                                	</div>
	                                                	<div class="col-44 pl-2">
	                                                		<div class="form-group mt-1 mb-2">
					                                            <c-select
					                                                id="class-2"
					                                                v-model="formData.currentClass2"
					                                                :disabled="!formData.currentClass1"
					                                                :search-route="'public/category'"
					                                                :custom-request-data="class2CategoryFilterData"
					                                                :watchFilterChange="!init"
					                                                ref="subCategoryfilterEL"
					                                                :placeholder="$t('sub-header.placeholder.child-category')"
					                                            />
					                                        </div>
	                                                	</div>
	                                                </div>
			                                    </div>
			                                </div>
	                                    </div>
	                                </div>
	                                <div class="row my-3">
	                                	<div class="col-48 text-right">
	                                		<a 
	                                            href="javascript:void(0)" 
	                                            class="text-decoration-none mr-3 text-primary3"
	                                            @click="resetFormData"
	                                        >
	                                            {{$t('sub-header.label.clear-search')}}
	                                        </a>
	                                        <button 
	                                            type="button" 
	                                            class="btn btn-primary2 rounded-pill" 
	                                            data-toggle="collapse" 
	                                            href="#collapseOne1" 
	                                            @click="search"
	                                            style="width: 150px" 
	                                        >
	                                            {{$t('sub-header.label.search')}}
	                                        </button>
	                                    </div>
	                                </div>
	                            </form>
	                        </div>
	                    </div>
	                </div>
	            </div>
	        </div>
	    </div>
	</div>
</template>

<script>
    import ECategoryType from "../../constants/category-type";
    import EErrorCode from "../../constants/error-code";
    import EAreaType from "../../constants/area-type";
    import FDownRightIcon from 'vue-feather-icons/icons/CornerDownRightIcon';
    import FChevronDownIcon from 'vue-feather-icons/icons/ChevronDownIcon';
    import VMoney from 'v-money';
    import buyerMessage from "../../locales/front/buyer";

    let filterData = $('#filter-data').val();
	filterData = atob(filterData) || '{}';
	filterData = JSON.parse(filterData);

    export default {
        props: {
            total: {
                type: Number,
                default: null,
            },
            currentCountry: {
                type: Object,
                default: null
            }
        },
        i18n: {
            messages: buyerMessage
        },
        components: {
        	FDownRightIcon,
        	FChevronDownIcon,
        	VMoney
        },
        data() {
            return {
                collapse: false,
                option: this.$_option(),

                state: {},
                formData: this.$_formData(),

                ECategoryType,
                EErrorCode,
                EAreaType,
                init: false,

                money: {
			        thousands: ',',
			        precision: 0,
                },
                isEdit: false,
                filters: filterData.filter,
            }
        },
        created() {
            this.getCountryList();
            this.getCategoryList();
            this.getAreaList();
        },
        mounted() {
        	Object.keys(this.filters).map(async (key) => {
                switch (key) {
                    case 'q':
                        this.formData.q = this.filters.q;
                        break;
                    case 'priceLt':
                        this.formData.priceLt = this.filters.priceLt;
                        break;
                    case 'priceGt':
                        this.formData.priceGt = this.filters.priceGt;
                        break;
                }
            });
        },
        computed: {
            class1CategoryFilterData() {
                return {
                    pageSize: 15,
                    type: ECategoryType.BUSINESS_CLASS_1
                };
            },
            class2CategoryFilterData() {
                return {
                    pageSize: 15,
                    type: ECategoryType.BUSINESS_CLASS_2,
                    parentCategoryId: this.formData.currentClass1 ? this.formData.currentClass1.id : null,
                };
            },
            countryFilterData() {
                return {
                    pageSize: 15,
                }
            },
            areaFilterData() {
                return {
                    pageSize: 15,
                    countryId: this.formData.country ? this.formData.country.id : null
                };
            },
        },
        watch: {
            currentCountry(val) {
                if (!this.formData.country) {
                	this.formData.country = val;
                }
            },
        },
        methods: {
        	getCountryList() {
                common.loadingScreen.show('body');
                common.getFront({
                    url: 'public/country',
                    data: {
                    	'get': true,
                    }
                }).done(response => {
                    if (this.filters.countryId) {
                    	response.data.data.find((item) => {
                    		if (item.id == this.filters.countryId) {
                    			this.formData.country = {
                    				id: item.id,
                    				name: item.name,
                    				currency_sign: item.currency_sign
                    			}
                    		}
                    	});
                    }
                })
                .always(() => {
                    common.loadingScreen.hide('body');
                });
            },
            getAreaList() {
                common.loadingScreen.show('body');
                common.getFront({
                    url: 'public/area-stack',
                }).done(response => {
                    if (this.filters.areaId) {
                    	response.data.data.find((item) => {
                    		let arrArea = this.filters.areaId.split(',');
                    		for (var i = 0; i < arrArea.length; i++) {
                    			if (item.id == arrArea[i]) {
	                    			this.formData.province.push(item);
                    			}
                    		}
                    		
                    	});
                    }
                })
                .always(() => {
                    common.loadingScreen.hide('body');
                });
            },
            getCategoryList() {
                common.loadingScreen.show('body');
                common.post({
                    url: 'public/category',
                    data: {
                    	'get': true,
                    }
                }).done(response => {
                    if (this.filters.categoryId) {
	                    response.data.find((item) => {
	                    	if (item.id == this.filters.categoryId) {
	                			if (item.type == ECategoryType.BUSINESS_CLASS_2) {
	                				let class1 = response.data.find(val => val.id == item.parent_category_id);
	                				this.formData.currentClass1 = class1;
		                			this.$nextTick(() => {
				                        this.formData.currentClass2 = item;
				                    });	
	                			}else {
	                				this.formData.currentClass1 = {
		                				id: item.id,
		                				name: item.name,
		                			}
	                			}
	                		}
	                    });
	                }
                })
                .always(() => {
                    common.loadingScreen.hide('body');
                });
            },
            $_option() {
                return {
                    saveBusiness: false,
                    sortBy: {
                        active: false,
                        upPrice: false,
                        downPrice: false,
                        newest: false,
                    }
                }
            },
            $_formData() {
                return {
                    currentClass1: null,
                    currentClass2: null,
                    currentClass3: null,
                    q: null,
                    priceLt: 0,
                    priceGt: 0,
                    country: null,
                    province: [],
                    newest: false,
                    upPrice: false,
                    downPrice: false,
                    saveBusiness: false,
                }
            },
            resetFormData() {
            	this.isEdit = false;
                this.categoryList2 = {};
                this.categoryList3 = {};
                this.formData = this.$_formData();
                this.formData.country = this.currentCountry;
            },
            saveMySearch() {
            	this.isEdit = true;
                let data = {
                    total: this.total,
                };
                Object.keys(this.parseData()).map(async (key) => {
                    switch (key) {
                        default:
                            if (this.parseData()[key]) {
                                data[key] = this.parseData()[key];
                            }
                            break;
                    }
                });
                common.loadingScreen.show('body');
                common.post({
                    url: 'buyer/save-my-search',
                    data: data,
                }).done(response => {
                    if (response.error == EErrorCode.NO_ERROR) {
                        common.showMsg('success', null, this.$t('msg.save_success'));
                    }else {
                        common.showMsg2(response);
                    }
                })
                .always(() => {
                    common.loadingScreen.hide('body');
                });
            },
            modifyMySearch() {
            	this.isEdit = true;
                this.init = true;
                common.loadingScreen.show('body');
                common.post({
                    url: 'buyer/get-my-search',
                }).done(response => {
                    if (response.data.category) {
                        this.formData.currentClass1 = response.data.category.class1;
                        this.formData.currentClass2 = response.data.category.class2;
                    }
                    if (response.data.content.area_id) {
                        this.formData.province = response.data.content.area_id;
                    }
                    this.formData.country = response.data.content.country_id;
                    Object.keys(response.data.content).forEach((key) => {
                        switch (key) {
                            case 'q':
                                this.formData.q = response.data.content.q;
                                break;
                            case 'price_gt':
                                this.formData.priceGt = response.data.content.price_gt;
                                break;
                            case 'price_lt':
                                this.formData.priceLt = response.data.content.price_lt;
                                break;
                        }
                    });
                    this.$nextTick(() => {
                        this.init = false;
                        this.$refs.areafilterEL.searchItemList('');
                        this.$refs.subCategoryfilterEL.searchItemList('');
                    });
                })
                .always(() => {
                    common.loadingScreen.hide('body');
                });
            },
            parseData() {
                let data = {};
                Object.keys(this.formData).map(async (key) => {
                    switch (key) {
                        case 'currentClass1':
                            if (this.formData[key]) {
                                data['categoryId'] = this.formData[key].id;
                            }
                            break;
                        case 'currentClass2':
                            if (this.formData[key]) {
                                data['categoryId'] = this.formData[key].id;
                            }
                            break;
                        case 'currentClass3':
                            if (this.formData[key]) {
                                data['categoryId'] = this.formData[key].id;
                            }
                            break;
                        case 'country':
                            if (this.formData[key]) {
                            	data['countryId'] = this.formData[key].id;  
                            }
                            break;
                        case 'province':
                            if (this.formData[key] && this.formData[key].length > 0) {
                            	data['areaId'] = [];
                            	this.formData[key].forEach((item) => {
			                        data['areaId'].push(item.id);
			                    });
                            }
                            break;
                        default:
                            if (this.formData[key] != null && this.formData[key] != false) {
                                data[key] = this.formData[key];
                            }
                            break;
                    }
                });
                return data;
            },
            search() {
                this.$emit('on-search', {data: this.parseData()});
            },
        }
    }
</script>

<style scoped>

</style>
