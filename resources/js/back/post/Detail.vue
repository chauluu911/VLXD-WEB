<template>
    <b-row>
        <b-col cols="48" class="mb-4">
            <div class="content__inner custom-shadow-1">
                <b-row class="mb-3 border-bottom border-primary">
                    <b-col class="d-flex-center-y mb-2" style="min-width: 400px;">
                        <div class="creating-form-title d-inline-block mr-4 text-primary">
                            {{ $t('basic_info_title') }}
                        </div>
                        <div
                            v-if="formData.sellStatus.value === ESellStatus.SOLD || formData.status.value === EStatus.ACTIVE || formData.status.value === EStatus.DELETED"
                            class="btn text-white cursor-default"
                            :class="[formData.status.value === EStatus.ACTIVE ? 'bg-primary' : 'bg-danger']"
                        >
                            <span v-if="formData.sellStatus.value === ESellStatus.SOLD">
                                {{ $t('constant.sell_status.sold') }}
                            </span>
                            <span v-else-if="formData.status.value === EStatus.ACTIVE">
                                {{ $t('constant.status.approved') }}
                            </span>
							<span v-else>
								{{ $t('constant.status.deleted') }}
							</span>
                        </div>
                    </b-col>
                    <b-col class="text-right mb-2" style="min-width: 300px;">
                        <a-button
                            v-if="isViewing && formData.sellStatus.value !== ESellStatus.SOLD && formData.status.value === EStatus.WAITING"
                            class="mr-3"
                            variant="primary"
                            :loading="processing"
                            @click="approvePost"
                        >
                            <template #icon>
                                <f-check-square-icon size="20" class="mr-lg-2"/>
                            </template>
                            <template #default>
                                {{ $t('common.button.approve') }}
                            </template>
                        </a-button>
                        <b-link @click="$router.go(-1)" :disabled="loading || processing" class="text-primary mr-3">
                            {{ $t('common.button.back') }}
                        </b-link>
                    </b-col>
                </b-row>
                <b-form>
                    <b-row>
                        <b-col :md="12">
                            <b-form-group :label="$t('attribute.post_id')">
                                <b-input
                                    v-model.trim="formData.postCode"
                                    trim
                                    type="text"
                                    disabled
                                />
                            </b-form-group>
                        </b-col>
                        <b-col :md="12">
                            <b-form-group :label="$t('attribute.seller_id')">
                                <b-input
                                    v-model.trim="formData.sellerCode"
                                    trim
                                    type="text"
                                    disabled
                                />
                            </b-form-group>
                        </b-col>
                        <b-col :md="24">
                            <b-form-group :label="$t('attribute.category')">
                                <b-row>
                                    <b-col cols="16">
                                        <b-input
                                            :value="formData.category.class1.value && formData.category.class1.value.name"
                                            type="text"
                                            :class="{'bg-white': isViewing}"
                                            disabled
                                        />
                                    </b-col>
                                    <b-col cols="16">
                                        <b-input
                                            :value="formData.category.class2.value && formData.category.class2.value.name"
                                            type="text"
                                            :class="{'bg-white': isViewing}"
                                            disabled
                                        />
                                    </b-col>
                                    <b-col cols="16">
                                        <b-input
                                            :value="formData.category.class3.value && formData.category.class3.value.name"
                                            type="text"
                                            :class="{'bg-white': isViewing}"
                                            disabled
                                        />
                                    </b-col>
                                </b-row>
                            </b-form-group>
                        </b-col>
                        <b-col :md="24">
                            <b-form-group :label="$t('attribute.country')">
                                <b-input
                                    :value="formData.country.value && formData.country.value.name"
                                    type="text"
                                    :class="{'bg-white': isViewing}"
                                    disabled
                                />
                            </b-form-group>
                        </b-col>
                        <b-col :md="24">
                            <b-form-group :label="$t('attribute.region')">
                                <b-input
                                    v-model.trim="formData.region.value"
                                    type="text"
                                    :class="{'bg-white': isViewing}"
                                    disabled
                                />
                            </b-form-group>
                        </b-col>
                        <b-col :md="24" v-for="field in confidentialFields" :key="field.labelKey">
                            <b-form-group :label="$t(field.labelKey)">
                                <b-input-group class="border-right">
                                    <b-input
                                        v-model.trim="formData[field.name].value"
                                        type="text"
                                        class="border-right-0"
                                        :class="{'bg-white': isViewing}"
                                        disabled
                                    />
                                    <b-input-group-append v-if="~formData.confidential.indexOf(field.name_snake)">
                                        <b-input-group-text class="bg-transparent border-left-0" style="padding: 0 10px 0 5px;">
                                            <f-shield-icon
                                                size="24"
                                                class="text-primary"
                                                v-b-tooltip.hover
                                                :title="$t('tooltip.confidential')"
                                            />
                                        </b-input-group-text>
                                    </b-input-group-append>
                                </b-input-group>
                            </b-form-group>
                        </b-col>
                        <b-col :md="24" v-for="(field, index) in formData.additionInfo" :key="field.name">
                            <b-form-group :label="field.name">
                                <b-input-group class="border-right">
                                    <b-input
                                        v-model.trim="formData.additionInfo[index].value"
                                        type="text"
                                        class="border-right-0"
                                        :class="{'bg-white': isViewing}"
                                        disabled
                                    />
                                    <b-input-group-append v-if="field.confidential">
                                        <b-input-group-text class="bg-transparent border-left-0" style="padding: 0 10px 0 5px;">
                                            <f-shield-icon
                                                size="24"
                                                class="text-primary"
                                                v-b-tooltip.hover
                                                :title="$t('tooltip.confidential')"
                                            />
                                        </b-input-group-text>
                                    </b-input-group-append>
                                </b-input-group>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-form>
            </div>
        </b-col>
        <b-col cols="48" class="mb-4">
            <div class="content__inner custom-shadow-1">
                <b-row class="mb-3 border-bottom border-primary">
                    <b-col style="min-width: 400px;">
                        <div class="creating-form-title d-inline-block mr-4 text-primary">
                            {{ $t('summary_title') }}
                        </div>
                    </b-col>
                </b-row>
                <b-form>
                    <b-row>
                        <b-col :md="24">
                            <b-form-group :label="$t('attribute.title')">
                                <b-input
                                    v-model.trim="formData.title.value"
                                    type="text"
                                    :class="{'bg-white': isViewing}"
                                    disabled
                                />
                            </b-form-group>
                        </b-col>
                        <b-col :md="24">
                            <label>{{ $t('attribute.price') }}</label>
                            <b-row>
                                <b-col :cols="16" v-for="(price, index) in formData.prices" :key="price.currency_sign">
                                    <b-form-group>
                                        <b-input-group>
                                            <b-input-group-prepend>
                                                <b-input-group-text class="bg-transparent">
                                                    {{ price.currency_sign }}
                                                </b-input-group-text>
                                            </b-input-group-prepend>
                                            <b-input
                                                v-model.trim="price.priceStr"
                                                type="text"
                                                :class="{'bg-white': isViewing}"
                                                disabled
                                            />
                                        </b-input-group>
                                    </b-form-group>
                                </b-col>
                            </b-row>

                        </b-col>
                        <b-col :md="24">
                            <div class="float-right mb-1">
                                <b-button
                                    variant="light"
                                    class="square text-primary"
                                    style="font-size: 18px; padding: 0 10px;"
                                    @click="$refs.imageCarouselEl.prev()"
                                >
                                    <i class="fas fa-chevron-left"></i>
                                </b-button>
                                <b-button
                                    variant="light"
                                    class="square text-primary"
                                    style="font-size: 18px; padding: 0 9px 0 11px;"
                                    @click="$refs.imageCarouselEl.next()"
                                >
                                    <i class="fas fa-chevron-right"></i>
                                </b-button>
                            </div>
                            <label>{{ $t('attribute.image') }}</label>
                            <b-carousel
                                ref="imageCarouselEl"
                                no-wrap
                                :interval="0"
                            >
                                <b-carousel-slide v-for="(row, index) in parsedImageList" :key="index">
                                    <template #img>
                                        <div
                                            v-for="(resourcePath, rIndex) in row"
                                            class="d-inline-block"
                                            :class="{'mr-2': rIndex < maxImagePerCol - 1}"
                                            :style="{width: `calc(${(1 / row.length * 100)}% - ${0.5 * (maxImagePerCol - 1) / maxImagePerCol}rem)`}"
                                        >
                                            <div
                                                v-if="resourcePath"
                                                class="overflow-hidden bg-light position-relative"
                                                style="width: 100%; height: 0; padding-bottom: 75%;"
                                            >
                                                <img :src="resourcePath" class="div-mask w-100" :alt="index">
                                            </div>
                                            <div v-else class="d-flex-center overflow-hidden bg-light"
                                                 style="width: 100%; height: 0; padding-bottom: 75%;">
                                            </div>
                                        </div>
                                    </template>
                                </b-carousel-slide>
                            </b-carousel>
                        </b-col>
                        <b-col :md="24">
                            <b-form-group :label="$t('attribute.description')">
                                <b-textarea
                                    v-model.trim="formData.description.value"
                                    type="text"
                                    :class="{'bg-white': isViewing}"
                                    rows="4"
                                    disabled
                                />
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-form>
            </div>
        </b-col>
        <b-col cols="48">
            <div class="content__inner custom-shadow-1">
                <b-row class="mb-3 border-bottom border-primary">
                    <b-col style="min-width: 400px;">
                        <div class="creating-form-title d-inline-block mr-4 text-primary">
                            {{ $t('detail_payment_title') }}
                        </div>
                    </b-col>
                </b-row>
                <b-form>
                    <b-row v-for="(subscription, index) in subscriptionList" :key="index">
                        <b-col :md="16">
                            <b-form-group :label="!index ? $t('attribute.subscription_name') : ''">
                                <b-input
                                    :value="subscription.name"
                                    type="text"
                                    disabled
                                />
                            </b-form-group>
                        </b-col>
                        <b-col :md="16">
                            <b-form-group :label="!index ? $t('attribute.display_time') : ''">
                                <b-input
                                    :value="subscription.displayTimeStr"
                                    type="text"
                                    disabled
                                />
                            </b-form-group>
                        </b-col>
                        <b-col :md="16">
                            <b-form-group :label="!index ? $t('attribute.status') : ''">
                                <b-input
                                    :value="subscription.paymentStatusStr"
                                    type="text"
                                    disabled
                                />
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-form>
            </div>
        </b-col>
    </b-row>
</template>

<script>
    import postManagementFormMessages from '../../locales/back/post-management-form';
    import CreatingFormMixin from '../mixins/creating-form-mixin';
    import FSaveIcon from 'vue-feather-icons/icons/SaveIcon';
    import FShieldIcon from 'vue-feather-icons/icons/ShieldIcon';
    import FCheckSquareIcon from 'vue-feather-icons/icons/CheckSquareIcon';
    import ECreatingFormStage from '../../constants/creating-form-stage';
    import {mapState} from "vuex";
    import EStatus from '../../constants/status';
    import ESellStatus from '../../constants/sell-status';

    export default {
        name: 'PostForm',
        mixins: [CreatingFormMixin],
        i18n: {
            messages: postManagementFormMessages,
        },
        inject: ['Util', 'StringUtil', 'DateUtil'],
        components: {
            FSaveIcon,
            FShieldIcon,
            FCheckSquareIcon,
        },
        data() {    
            let stage;
            if (!this.$route.params.postCode) {
                stage = ECreatingFormStage.CREATING;
            } else if (this.$route.params.action === 'edit') {
                stage = ECreatingFormStage.UPDATING;
            } else {
                stage = ECreatingFormStage.READONLY;
            }
            return {
                stage,
                subscriptionList: [],
                maxImagePerCol: 5,
                EStatus,
                ESellStatus,
            };
        },
        computed: {
            ...mapState(['sidebarState']),
            isEditing() {
                return this.stage === ECreatingFormStage.UPDATING;
            },
            isViewing() {
                return this.stage === ECreatingFormStage.READONLY;
            },
            routes() {
                return {
                    // search: `${this.$route.meta.baseUrl}/all`,
                    // save: `${this.$route.meta.baseUrl}/save`,
                    info: `${this.$route.meta.baseUrl}/${this.$route.params.postCode}/info`,
                    approve: `${this.$route.meta.baseUrl}/approve`,
                }
            },
            confidentialFields() {
                return [
                    {
                        name: 'businessName',
                        name_snake: 'business_name',
                        labelKey: 'attribute.business_name',
                    },
                    {
                        name: 'ownerName',
                        name_snake: 'owner_name',
                        labelKey: 'attribute.owner',
                    },
                    {
                        name: 'address',
                        name_snake: 'address',
                        labelKey: 'attribute.address',
                    },
                    {
                        name: 'businessRegistrationNumber',
                        name_snake: 'business_registration_number',
                        labelKey: 'attribute.business_registration_number',
                    },
                    {
                        name: 'headquarterAddress',
                        name_snake: 'headquarter_address',
                        labelKey: 'attribute.headquarter_address',
                    }
                ];
            },
            parsedImageList() {
                let result = [];
                if (!this.formData.resources.value) {
                    return [
                        [null, null, null, null, null]
                    ]
                }
                for (let i = 0; i < Math.max(this.formData.resources.value.length, this.maxImagePerCol); i += this.maxImagePerCol) {
                    let row = this.formData.resources.value.slice(i, i + this.maxImagePerCol);
                    if (row.length < this.maxImagePerCol) {
                        let length = row.length;
                        for (let j = 0; j < this.maxImagePerCol - length; j++) {
                            row.push(null);
                        }
                    }
                    result.push(row);
                }
                return result;
            }
        },
        created() {
            this.$store.commit('updateFilterFormState', []);
            this.$store.commit('updateBreadcrumbsState', [
                {
                    text: this.$t('post_list_title'),
                    to: {name: 'post.list'}
                },
            ]);
            if (this.$route.params.postCode) {
                this.initEditForm();
            }
        },
        mounted() {
            this.calculateMaxImageNumberToShow();
            window.addEventListener('resize', this.calculateMaxImageNumberToShow);
        },
        beforeDestroy() {
            window.removeEventListener('resize', this.calculateMaxImageNumberToShow);
        },
        methods: {
            calculateMaxImageNumberToShow() {
                let width = $(this.$refs.imageCarouselEl.$el).width();
                if (width) {
                    if (width > 500) {
                        this.maxImagePerCol = 5;
                    } else if (width > 400) {
                        this.maxImagePerCol = 4;
                    } else {
                        this.maxImagePerCol = 3;
                    }
                }
            },
            defaultFormData() {
                return {
                    postCode: this.$route.params.postCode,
                    sellerCode: '',
                    category: {
                        class1: this.getAttributeData(),
                        class2: this.getAttributeData(),
                        class3: this.getAttributeData()
                    },
                    resources: this.getAttributeData(),
                    prices: [],
                    country: this.getAttributeData(),
                    region: this.getAttributeData(),
                    businessName: this.getAttributeData({confidential: false}),
                    ownerName: this.getAttributeData({confidential: false}),
                    address: this.getAttributeData({confidential: false}),
                    businessRegistrationNumber: this.getAttributeData({confidential: false}),
                    headquarterAddress: this.getAttributeData({confidential: false}),
                    additionInfo: [],
                    title: this.getAttributeData(),
                    description: this.getAttributeData(),
                    confidential: [],
                    status: this.getAttributeData(),
                    sellStatus: this.getAttributeData(),
                };
            },
            initEditForm() {
                this.processing = true;
                return this.Util.get({
                    url: this.routes.info,
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.confirm(
                            res.msg,
                            (confirm) => {
                                if (!confirm) {
                                    this.$router.go(-1);
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

                    this.$store.commit('updateBreadcrumbsState', [
                        {
                            text: this.$t('post_list_title'),
                            to: {name: 'post.list'}
                        },
                        {
                            text: res.data.code,
                            to: '',
                        }
                    ]);
                    Object.keys(res.data).forEach((key) => {
                        switch (key) {
                            case 'code':
                                break;
                            case 'category':
                                this.$set(this.formData.category.class1, 'value', res.data[key].class1);
                                this.$set(this.formData.category.class2, 'value', res.data[key].class2);
                                this.$set(this.formData.category.class3, 'value', res.data[key].class3);
                                break;
                            case 'confidential':
                                Object.keys(res.data.confidential || {}).forEach((field) => {
                                    if (res.data.confidential[field]) {
                                        this.formData.confidential.push(field);
                                    }
                                });
                                break;
                            case 'user_code':
                                this.formData.sellerCode = res.data[key];
                                break;
                            case 'addition_info':
                            case 'prices':
                                (res.data[key] || []).map((item) => {
                                    return {
                                        error: null,
                                        ...item,
                                    };
                                });
                                this.formData[this.StringUtil.switchCase(key, 'camel')] = res.data[key] || [];
                                break;
                            case 'subscriptions':
                                res.data[key].forEach((item) => {
                                    let validFrom = '',
                                        validTo = '';
                                    if (item.valid_from) {
                                        let date = new Date(item.valid_from);
                                        validFrom = this.DateUtil.getDateTimeString(date);
                                    }
                                    if (item.valid_to) {
                                        let date = new Date(item.valid_to);
                                        validTo = this.DateUtil.getDateTimeString(date);
                                    }
                                    item.displayTimeStr = `${validFrom} - ${validTo}`;
                                });
                                this.subscriptionList = res.data[key];
                                break;
                            case 'country':
                            case 'region':
                            case 'business_name':
                            case 'owner_name':
                            case 'address':
                            case 'business_registration_number':
                            case 'headquarter_address':
                            case 'title':
                            case 'description':
                            case 'resources':
                            case 'status':
                            case 'sell_status':
                                try {
                                    this.$set(this.formData[this.StringUtil.switchCase(key, 'camel')], 'value', res.data[key]);
                                } catch (e) {
                                    console.error(this.StringUtil.switchCase(key, 'camel'), e);
                                }
                                break;
                        }
                    });
                }).always(() => {
                    this.processing = false;
                });
            },
            async approvePost() {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm(
                        this.$t('common.confirmation.approve', {
                            objectName: this.$t('object_name')
                        }),
                        resolve
                    );
                });

                if (!confirm) {
                    return;
                }

                this.processing = true;
                this.Util.post({
                    url: this.routes.approve,
                    data: {
                        postCodeList: [this.formData.postCode],
                    }
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }

                    await this.initEditForm();

                    this.Util.showMsg('success', null, res.msg);
                }).fail(() => {
                    this.Util.showMsg('error', null, this.$t('common.error.unknown'));
                }).always(() => {
                    this.processing = false;
                });
            },
        },
    }
</script>
