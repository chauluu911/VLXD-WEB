<template>
    <div>
        <b-row>
            <b-col cols="48" class="mb-3">
                <div class="content__inner">
                    <b-row class="pb-2" style="border-bottom: 3px solid gray">
                        <b-col cols="24">
                            <span class="span__title-detail mr-4">{{!disabled ? $t('tab.title.edit-advertise').toUpperCase() : $t('tab.title.user-info').toUpperCase()}}
                            </span>
                            <div class="btn text-white cursor-default mb-2" v-if="!!detailPayment.statusStr" :class="detailPayment.status == EStatus.DELETED ? 'bg-danger' : 'bg-primary'">
                                <span>
                                    {{ detailPayment.statusStr }}
                                </span>
                            </div>
                        </b-col>
                        <b-col cols="24" v-if="detailPayment.status != EStatus.DELETED">
                            <template v-if="disabled == true">
                                <b-button variant="outline-primary" class="float-right ml-3" @click="deleteAdvertise">
                                    <i class="fas fa-trash-alt"/>
                                    {{$t('common.tooltip.delete')}}
                                </b-button>
                                <router-link :to="{name: route.edit, params:{code: this.$route.params.code, action: 'edit'}}" class="no-decoration">
                                    <b-button variant="primary" class="float-right ml-3">
                                        <i class="fas fa-edit"/>
                                        {{$t('common.tooltip.edit')}}
                                    </b-button>
                                </router-link>
                            </template>
                            <template v-else>
                                <b-button variant="primary" class="float-right ml-3" @click="saveAdvertise">
                                    <i class="far fa-save"></i>
                                    {{$t('tab.info.edit.save')}}
                                </b-button>
                                <b-button
                                    @click="onBack"
                                    class="no-decoration float-right btn">
                                    <span>{{$t('tab.info.edit.back')}}</span>
                                </b-button>
                            </template>
                            <span id="post-approve-btn-wrapper">
                                <a-button
                                    v-if="detailPayment.status == EStatus.WAITING"
                                    @click="approveAdvertises"
                                    :tooltip="$t('common.button.approve')"
                                >
                                    <template #icon>
                                        <f-check-square-icon size="20" class="mr-lg-2"/>
                                    </template>
                                    <template #default>
                                        <span class="d-none d-lg-inline">{{ $t('common.button.approve') }}</span>
                                    </template>
                                </a-button>
                            </span>
                            <b-tooltip target="post-approve-btn-wrapper">
                                {{ $t('common.button.approve') }}
                            </b-tooltip>
                        </b-col>
                    </b-row>
                    <b-row class="mt-3">
                        <b-col md="48">
                            <b-form>
                                <b-row>
                                    <b-col md="30">
                                        <b-row>
                                            <b-col md="24">
                                                <b-form-group
                                                    class="font-weight-bold"
                                                    :label="$t('tab.advertise-list.form-edit.label.advertise-id')"
                                                >
                                                    <b-form-input class="w-100" v-model="formData.code"
                                                        required
                                                        :placeholder="$t('tab.advertise-list.form-edit.label.advertise-id')"
                                                        :disabled="true"
                                                        @change="onInputChange"
                                                    />
                                                </b-form-group>
                                            </b-col>
                                            <b-col md="24">
                                                <b-form-group
                                                    class="font-weight-bold"
                                                    :label="$t('tab.advertise-list.form-edit.label.advertiser-id')"
                                                >
                                                    <b-form-input
                                                        class="w-100"
                                                        v-model="detailPayment.userCode"
                                                        :disabled="true"
                                                        @change="onInputChange"
                                                    />
                                                </b-form-group>
                                            </b-col>
                                        </b-row>
                                        <b-row>
                                            <b-col md="48">
                                                <label class="font-weight-bold required">{{$t('tab.advertise-list.form-edit.label.category')}}</label>
                                            </b-col>
                                        </b-row>
                                        <b-row>
                                            <b-col md="16">
                                                <b-form-group class="font-weight-bold"
                                                    :state="!!formData.errors.class1"
                                                    :invalid-feedback="formData.errors.class1 && formData.errors.class1[0]"
                                                >
                                                    <c-select
                                                        id="class-2-name-input"
                                                        v-model="formData.category.class1"
                                                        :error="formData.class1 && formData.class1[0]"
                                                        :placeholder="$t('tab.advertise-list.form-edit.placeholder.category', {
                                                            index: 1
                                                        })"
                                                        :disabled="disabled"
                                                        :search-route="'/api/back/category/all'"
                                                        :custom-request-data="class1CategoryFilterData"
                                                        :class="{'is-invalid': formData.errors.class1 && formData.errors.class1[0]}"
                                                        :watchFilterChange="!init"
                                                    />
                                                </b-form-group>
                                            </b-col>
                                            <b-col md="16">
                                                <b-form-group class="font-weight-bold"
                                                    :state="!!formData.errors.class2"
                                                    :invalid-feedback="formData.errors.class2 && formData.errors.class2[0]"
                                                >
                                                    <c-select
                                                        id="class-2-name-input"
                                                        v-model="formData.category.class2"
                                                        :error="formData.class2 && formData.class2[0]"
                                                        :placeholder="$t('tab.advertise-list.form-edit.placeholder.category', {
                                                            index: 2
                                                        })"
                                                        :disabled="disabled"
                                                        :search-route="'/api/back/category/all'"
                                                        :custom-request-data="class2CategoryFilterData"
                                                        :class="{'is-invalid': formData.errors.class2&& formData.errors.class2[0]}"
                                                        :watchFilterChange="!init"
                                                    />
                                                </b-form-group>
                                            </b-col>
                                            <b-col md="16">
                                                <b-form-group class="font-weight-bold"
                                                    :state="!!formData.errors.class3"
                                                    :invalid-feedback="formData.errors.class3 && formData.errors.class3[0]"
                                                >
                                                    <c-select
                                                        id="class-2-name-input"
                                                        v-model="formData.category.class3"
                                                        :error="formData.class3 && formData.class3[0]"
                                                        :placeholder="$t('tab.advertise-list.form-edit.placeholder.category', {
                                                            index: 3
                                                        })"
                                                        :disabled="disabled"
                                                        :search-route="'/api/back/category/all'"
                                                        :custom-request-data="class3CategoryFilterData"
                                                        :class="{'is-invalid': formData.errors.class3 && formData.errors.class3[0]}"
                                                        :watchFilterChange="!init"
                                                    />
                                                </b-form-group>
                                            </b-col>
                                        </b-row>
                                        <b-row>
                                             <b-col md="16">
                                                <b-form-group
                                                    class="font-weight-bold"
                                                    :label="$t('country')"
                                                >
                                                    <b-form-select
                                                        v-model="currentCountry"
                                                        :options="listCountry"
                                                    />
                                                </b-form-group>
                                            </b-col>
                                        </b-row>
                                        <b-row>
                                             <b-col md="48">
                                                <b-form-group
                                                    class="font-weight-bold"
                                                    :label-class="'required'"
                                                    :label="$t('tab.advertise-list.form-edit.label.titleEn')"
                                                    :state="!!formData.errors.titleEn"
                                                    :invalid-feedback="formData.errors.titleEn && formData.errors.titleEn[0]"
                                                >
                                                    <b-form-input
                                                        class="w-100"
                                                        v-model="formData.title.en"
                                                        required
                                                        :placeholder="$t('tab.advertise-list.form-edit.placeholder.titleEn')"
                                                        :disabled="disabled"
                                                        :state="formData.errors.titleEn && !formData.errors.titleEn[0]"
                                                        @change="onInputChange"
                                                        :key='`titleEn${genKey}`'
                                                    />
                                                </b-form-group>
                                            </b-col>
                                        </b-row>
                                        <b-row v-if="currentCountry.code == ELanguage.FR">
                                             <b-col md="48">
                                                <b-form-group
                                                    class="font-weight-bold"
                                                    :label-class="'required'"
                                                    :label="$t('tab.advertise-list.form-edit.label.titleFr')"
                                                    :state="!!formData.errors.titleFr"
                                                    :invalid-feedback="formData.errors.titleFr && formData.errors.titleFr[0]"
                                                >
                                                    <b-form-input
                                                        class="w-100"
                                                        v-model="formData.title.fr"
                                                        required
                                                        :placeholder="$t('tab.advertise-list.form-edit.placeholder.titleFr')"
                                                        :disabled="disabled"
                                                        :state="formData.errors.titleFr && !formData.errors.titleFr[0]"
                                                        @change="onInputChange"
                                                        :key='`titleFr${genKey}`'
                                                    />
                                                </b-form-group>
                                            </b-col>
                                        </b-row>
                                        <b-row v-if="currentCountry.code == ELanguage.VI">
                                             <b-col md="48">
                                                <b-form-group
                                                    class="font-weight-bold"
                                                    :label-class="'required'"
                                                    :label="$t('tab.advertise-list.form-edit.label.titleVi')"
                                                    :state="!!formData.errors.titleVi"
                                                    :invalid-feedback="formData.errors.titleVi && formData.errors.titleVi[0]"
                                                >
                                                    <b-form-input
                                                        class="w-100"
                                                        v-model="formData.title.vi"
                                                        required
                                                        :placeholder="$t('tab.advertise-list.form-edit.placeholder.titleVi')"
                                                        :disabled="disabled"
                                                        :state="formData.errors.titleVi && !formData.errors.titleVi[0]"
                                                        @change="onInputChange"
                                                        :key='`titleVi${genKey}`'
                                                    />
                                                </b-form-group>
                                            </b-col>
                                        </b-row>
                                    </b-col>
                                    <b-col md="18">
                                        <b-form-group
                                            class="font-weight-bold"
                                            :label-class="'required'"
                                            :label="$t('tab.advertise-list.form-edit.label.image')"
                                            :state="!!formData.errors.image"
                                            :invalid-feedback="formData.errors.image && formData.errors.image[0]"
                                        >
                                            <image-cropper ref="imageCropperEl"
                                                @cropper-created="onCropperCreated"
                                                :background-image="'#e9ecef'"
                                                :image-url="imageUrl"
                                                :aspect-ratios="[{name: '9:21', value: 9 / 21}]"
                                                :errors="formData.errors.image"
                                                :disabled="disabled"
                                            />
                                        </b-form-group>
                                    </b-col>
                                </b-row>
                            </b-form>
                        </b-col>
                    </b-row>
                </div>
            </b-col>
        </b-row>
        <b-row v-if="disabled">
            <b-col cols="48">
                <div class="content__inner">
                    <b-row class="pb-2 mb-3" style="border-bottom: 3px solid gray">
                        <b-col cols="24">
                            <span class="span__title-detail">{{$t('tab.advertise-list.detail-payment').toUpperCase()}}</span>
                        </b-col>
                    </b-row>
                    <b-row class="pb-2">
                        <b-col cols="48">
                            <b-form>
                                <b-row>
                                    <b-col md="16">
                                        <b-form-group class="font-weight-bold"
                                            :label="$t('tab.advertise-list.form-detail.label.advertising-package')"
                                        >
                                            <b-form-input
                                                class="w-100"
                                                v-model="detailPayment.advertisingPackage"
                                                :disabled="true"
                                                @change="onInputChange"
                                            />
                                        </b-form-group>
                                    </b-col>
                                    <b-col md="16">
                                        <b-form-group class="font-weight-bold"
                                            :label="$t('tab.advertise-list.form-detail.label.duration')"
                                        >
                                            <b-form-input
                                                class="w-100"
                                                v-model="detailPayment.duration"
                                                :disabled="true"
                                                @change="onInputChange"
                                            />
                                        </b-form-group>
                                    </b-col>
                                    <b-col md="16">
                                        <b-form-group class="font-weight-bold"
                                            :label="$t('tab.advertise-list.form-detail.label.status')"
                                        >
                                            <b-form-input class="w-100" v-model="detailPayment.statusStr" :disabled="true"/>
                                        </b-form-group>
                                    </b-col>
                                </b-row>
                            </b-form>
                        </b-col>
                    </b-row>
                </div>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import {mapState} from "vuex";
    import ECustomerType from "../../constants/customer-type";
    import userDetail from "../../locales/back/user/user-detail";
    import EErrorCode from "../../constants/error-code";
    import ImageCropper from '../../components/ImageCropper';
    import ECategoryType from "../../constants/category-type";
    import EStatus from "../../constants/status";
    import ELanguage from "../../constants/language";
    import FCheckSquareIcon from 'vue-feather-icons/icons/CheckSquareIcon';

    export default {
        inject: ['Util', 'StringUtil', 'DateUtil'],
        i18n: {
            messages: userDetail
        },
        components: {ImageCropper, FCheckSquareIcon},
        props: {
            customerType: {
                type: Number,
            },
        },
        data() {
            return {
                pagination: {
                    page: 1,
                    size: null,
                },
                loading: false,
                formData: this.$_formData(),
                disabled: this.$route.params.action == 'edit' ? false : true,
                detailPayment: [],
                selectedFile: null,
                imageUrl: null,
                countries: [],
                currentCountry: {
                    code: ELanguage.EN,
                    languages: ["en"],
                },
                genKey: 0,
                EErrorCode,
                ECustomerType,
                ECategoryType,
                EStatus,
                ELanguage,
                init: true,
            }
        },
        computed: {
            ...mapState(['filterValueState']),
            route() {
                return {
                    getInfo: `/api/back/promo/info`,
                    save: `${this.$route.meta.baseUrl}/${this.$route.params.code}/save`,
                    edit: 'promo.edit',
                }
            },
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
                    parentCategoryId: this.formData.category.class1 ? this.formData.category.class1.id : null,
                };
            },
            class3CategoryFilterData() {
                return {
                    pageSize: 15,
                    type: ECategoryType.BUSINESS_CLASS_3,
                    parentCategoryId: this.formData.category.class2 ? this.formData.category.class2.id : null,
                };
            },
            listCountry() {
                return this.countries.map((item) => {
                    return {
                        value: {
                            code: item.language_code,
                            languages: item.languages
                        },
                        text: item.name,
                    }
                });
            }
        },
        created() {
            this.$store.commit('updateBreadcrumbsState', [
                {
                    text: 'Danh sách quảng cáo',
                    to: { name: 'promo' }
                },
                {
                    text: this.$route.params.code,
                    to: { name: 'promo.edit' }
                }
            ]);
            this.getAdvertiseInfo();
            this.getCountryList();
        },
        beforeRouteUpdate(to, from, next) {
            this.disabled = to.params.action == 'edit' ? false : true;
            this.formData.errors = this.$_formData().errors;
            next();
        },
        watch: {
            filterValueState(val) {
                if (this.customerType == ECustomerType.BUYER) {
                    this.$router.push({name: 'buyer.list', query: val.value});
                }else if(this.customerType == ECustomerType.SELLER) {
                    this.$router.push({name: 'seller.list', query: val.value});
                } else {
                    this.$router.push({name: 'advertiser.list', query: val.value});
                }
            },
        },
        methods: {
            onInputChange() {
                if (this.disabled == false) {
                    this.Util.askUserWhenLeavePage();
                }
            },
            getCountryList() {
                this.Util.loadingScreen.show();
                this.Util.post({
                    url: '/api/back/country/search',
                }).done(response => {
                        this.countries = response.countryList;
                    }).always(() => {
                            this.Util.loadingScreen.hide();
                        });
            },
            getAdvertiseInfo() {
                this.genKey++;
                this.selectedFile = null;
                this.loading = true;
                let reqData = Object.assign({
                    filter: {
                        adsCode: this.$route.params.code,
                        first: true,
                    }
                });
                this.Util.get({
                    url: this.route.getInfo,
                    data: reqData,
                }).done(response => {
                        if (response.error == EErrorCode.ERROR) {
                            this.Util.showMsg2(response);
                            this.$router.push({name: 'promo.management'});
                            return false;
                        }
                        this.detailPayment = response.advertiseList;
                        this.formData.title.en = this.detailPayment.titleEn;
                        this.formData.title.fr = this.detailPayment.titleFr;
                        this.formData.title.vi = this.detailPayment.titleVi;
                        this.imageUrl = this.detailPayment.imageAds;
                        this.formData.category = this.detailPayment.adsCategory;
                        if (this.detailPayment.validFrom) {
                            this.detailPayment.duration = this.DateUtil.getDateTimeString2(new Date(this.detailPayment.validFrom)) + ' to ' + this.DateUtil.getDateTimeString2(new Date(this.detailPayment.validTo))
                        }
                        this.$nextTick(() => {
                            this.init = false;
                        })
                    }).fail((error) =>{
                        if (error.status == "404") {
                            this.$router.push({name: '404'});
                        }
                    }).always(() => {
                        this.Util.loadingScreen.hide();
                    });
            },
            $_formData() {
                return {
                    code: this.$route.params.code,
                    category: {
                        class1: null,
                        class2: null,
                        class3: null,
                    },
                    title: {
                        en: null,
                        fr: null,
                        vi: null,
                    },
                    croppedImage: null,
                    errors: {
                        class1: null,
                        class2: null,
                        class3: null,
                        titleEn: null,
                        titleFr: null,
                        titleVi: null,
                        image: null,
                    }
                }
            },
            onCropperCreated({file, imageUrl}) {
                this.selectedFile = file;
                this.imageUrl = imageUrl;
            },
            async deleteAdvertise() {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirmDelete(this.$t('tab.advertise-list.advertise'), resolve);
                });
                if (!confirm) {
                    return;
                }
                this.Util.loadingScreen.show();
                this.Util.post({
                    url: '/api/back/promo/delete',
                    data: {
                        code: this.formData.code,
                    },
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }

                    this.$router.push({name: 'promo.management'});

                    this.Util.showMsg('success', null, res.msg);
                }).always(() => {
                    this.Util.loadingScreen.hide();
                });
            },
            async saveAdvertise() {
                this.init = true;
                this.Util.loadingScreen.show();
                let formData = new FormData();
                await Promise.all(
                    Object.keys(this.formData).map(async (key) => {
                        switch (key) {
                            case 'croppedImage':
                                if (this.selectedFile) {
                                    let blob = await this.$refs.imageCropperEl.val();
                                    if (!blob) {
                                        blob = '';
                                        this.selectedFile = '';
                                    }
                                    formData.append('croppedImage', blob);
                                    formData.append('files', this.selectedFile);
                                    formData.append('imageUrl', this.imageUrl);
                                }
                                break;
                            case 'category':
                                formData.append('class1', this.formData.category.class1 != null ?
                                    this.formData.category.class1.id : '');
                                formData.append('class2', this.formData.category.class2 != null ?
                                    this.formData.category.class2.id : '');
                                formData.append('class3', this.formData.category.class3 != null ?
                                    this.formData.category.class3.id : '');
                                break;
                            case 'title':
                                Object.keys(this.currentCountry.languages).forEach((index) => {
                                    formData.append(`title${this.StringUtil.ucfirst(this.currentCountry.languages[index])}`, this.formData.title[this.currentCountry.languages[index]]);
                                });
                                break;
                            default:
                                if (this.formData[key]) {
                                    formData.append(key, this.formData[key]);
                                }
                                break;
                        }
                        return Promise.resolve();
                    })
                );
                this.Util.post({
                    url: this.route.save,
                    data: formData,
                    errorModel: this.formData.errors,
                    processData: false,
                    contentType: false,
                }).done(response => {
                        if (response.error == EErrorCode.ERROR) {
                            this.formData.errors = response.msg;
                        }
                        this.Util.showMsg2(response);
                        this.getAdvertiseInfo();
                    }).always(() => {
                            this.Util.loadingScreen.hide();
                        });
            },
            async approveAdvertises(code) {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm(
                        this.$t('common.confirmation.approve', {
                            objectName: this.$t('tab.advertise-list.advertise')
                        }),
                        resolve
                    );
                });
                if (!confirm) {
                    return;
                }
                this.Util.loadingScreen.show();
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/approve`,
                    data: {
                        adsCodeList: this.$route.params.code,
                    },
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }

                    await this.getAdvertiseInfo();
                    this.Util.showMsg('success', null, res.msg);
                }).always(() => {
                    this.Util.loadingScreen.hide();
                });
            },
            onBack(){
                this.$router.go(-1)
            }
        }
    }
</script>

<style scoped>

</style>
