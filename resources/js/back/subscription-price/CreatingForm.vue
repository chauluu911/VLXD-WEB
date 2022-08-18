<template>
    <b-row>
        <b-col cols="48">
            <div class="content__inner">
                <b-row class="mb-3 border-bottom border-primary">
                    <b-col style="min-width: 400px;">
                        <div class="d-inline-block h3 mr-4 text-primary">
                            {{ $t(`title`) }}
                        </div>
                    </b-col>
                    <b-col class="text-right mb-3" style="min-width: 300px;">
                        <b-link @click="$router.go(-1)" :disabled="loading || processing" class="text-primary mr-3">
                            {{ $t('common.button.back') }}
                        </b-link>
                        <a-button
                            :icon-class="['mr-lg-2']"
                            variant="primary"
                            class="mr-2"
                            @click="submit"
                            :loading="loading || processing"
                        >
                            <template #icon>
                                <f-save-icon size="20" class="mr-lg-2"/>
                            </template>
                            <template #default>
                                {{ $t('common.button.save2', {objectName: $t('object_name').toLowerCase()}) }}
                            </template>
                        </a-button>
                    </b-col>
                </b-row>
                <b-form>
                    <b-row>
                        <b-col
                            :md="sidebarState.active ? 48 : 24"
                            :lg="24"
                        >
                            <b-row>
                                <b-col cols="48">
                                    <b-form-group
                                        :label="`${$t('attribute.name')} (En)`"
                                        :label-class="isEditing ? 'required' : ''"
                                    >
                                        <b-input
                                            v-model.trim="formData.nameEn.value"
                                            trim
                                            type="text"
                                            :placeholder="$t('placeholder.nameEn')"
                                            :state="formData.nameEn.error ? false : null"
                                            @change="onInputChange"
                                            :disabled="!isEditing"
                                        />
                                        <b-form-invalid-feedback>
                                            {{ formData.nameEn.error }}
                                        </b-form-invalid-feedback>
                                    </b-form-group>
                                </b-col>
                                <b-col cols="48">
                                    <b-form-group
                                        :label="`${$t('attribute.name')} (Fr)`"
                                        :label-class="isEditing ? 'required' : ''"
                                    >
                                        <b-input
                                            v-model.trim="formData.nameFr.value"
                                            trim
                                            type="text"
                                            :placeholder="$t('placeholder.nameFr')"
                                            :state="formData.nameFr.error ? false : null"
                                            @change="onInputChange"
                                            :disabled="!isEditing"
                                        />
                                        <b-form-invalid-feedback>
                                            {{ formData.nameFr.error }}
                                        </b-form-invalid-feedback>
                                    </b-form-group>
                                </b-col>
                                <b-col cols="48">
                                    <b-form-group
                                        :label="`${$t('attribute.name')} (Vi)`"
                                        :label-class="isEditing ? 'required' : ''"
                                    >
                                        <b-input
                                            v-model.trim="formData.nameVi.value"
                                            trim
                                            type="text"
                                            :placeholder="$t('placeholder.nameVi')"
                                            :state="formData.nameVi.error ? false : null"
                                            @change="onInputChange"
                                            :disabled="!isEditing"
                                        />
                                        <b-form-invalid-feedback>
                                            {{ formData.nameVi.error }}
                                        </b-form-invalid-feedback>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                        </b-col>
                        <b-col
                            :md="sidebarState.active ? 48 : 24"
                            :lg="24"
                        >
                            <b-row>
                                <b-col cols="48">
                                    <label :class="[isEditing ? 'required' : '']">{{ $t('attribute.price') }}</label>
                                    <b-row>
                                        <b-col
                                            v-for="country in countryList"
                                            :key="country.currency_sign"
                                            :cols="48"
                                            :sm="16"
                                            :md="48"
                                            :lg="sidebarState.active ? 48 : 16"
                                            :xl="16"
                                        >
                                            <b-form-group>
                                                <b-input-group>
                                                    <b-input-group-prepend>
                                                        <b-input-group-text class="bg-transparent">
                                                            {{ country.currency_sign }}
                                                        </b-input-group-text>
                                                    </b-input-group-prepend>
                                                    <b-input
                                                        v-model.trim="formData[`price${country.country_code}`].value"
                                                        type="text"
                                                        :class="{'bg-white': !isEditing}"
                                                        :placeholder="`${$t('placeholder.price')} (${country.currency_code})`"
                                                        :disabled="!isEditing"
                                                    />
                                                </b-input-group>
                                            </b-form-group>
                                        </b-col>
                                    </b-row>
                                </b-col>
                                <b-col cols="48">
                                    <b-form-group
                                        :label="$t('attribute.duration')"
                                        :label-class="isEditing ? 'required' : ''"
                                    >
                                        <b-input
                                            v-model.trim="formData.numberOfMonth.value"
                                            trim
                                            type="number"
                                            :min="0"
                                            :placeholder="$t('placeholder.duration')"
                                            :state="formData.numberOfMonth.error ? false : null"
                                            @change="onInputChange"
                                            :disabled="!isEditing"
                                        />
                                        <b-form-invalid-feedback>
                                            {{ formData.numberOfMonth.error }}
                                        </b-form-invalid-feedback>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                        </b-col>
                    </b-row>
                </b-form>
            </div>
        </b-col>
    </b-row>
</template>

<script>
    import getSubscriptionPriceFormMessages from '../../locales/back/subscription-price-management-form';
    import CreatingFormMixin from '../mixins/creating-form-mixin';
    import FSaveIcon from 'vue-feather-icons/icons/SaveIcon';
    import ECreatingFormStage from '../../constants/creating-form-stage';
    import {mapState} from "vuex";
    import EConfigTableName from "../../constants/config-table-name";

    export default {
        name: 'CategoryForm',
        mixins: [CreatingFormMixin],
        i18n: {},
        inject: ['Util', 'StringUtil'],
        components: {
            FSaveIcon,
        },
        props: {
            tableName: {
                type: String,
                required: true,
            }
        },
        data() {
            let stage;
            if (!this.$route.params.subscriptionPriceId) {
                stage = ECreatingFormStage.CREATING;
            } else if (this.$route.params.action === 'edit') {
                stage = ECreatingFormStage.UPDATING;
            } else {
                stage = ECreatingFormStage.READONLY;
            }
            return {
                stage,
                countryList: [],
            };
        },
        computed: {
            ...mapState(['sidebarState']),
            routes() {
                return {
                    search: `${this.$route.meta.baseUrl}/all`,
                    save: `${this.$route.meta.baseUrl}/${this.tableName}/save`,
                    info: this.$route.params.subscriptionPriceId
                        ? `${this.$route.meta.baseUrl}/${this.$route.params.subscriptionPriceId}/info`
                        : `${this.$route.meta.baseUrl}/info`,
                }
            },
            formRouteNamePrefix() {
                switch (this.tableName) {
                    case EConfigTableName.ADS:
                        return 'ads-subscription-price';
                    case EConfigTableName.POST:
                        return 'post-subscription-price';
                }
                return null;
            }
        },
        created() {
            let messages = getSubscriptionPriceFormMessages(this.tableName);
            Object.keys(messages || {}).forEach((lang) => {
                this.$i18n.setLocaleMessage(lang, messages[lang]);
            });
            this.backRoute = {
                name: `${this.formRouteNamePrefix}.list`,
            };
            this.$store.commit('updateFilterFormState', []);
            this.$store.commit('updateBreadcrumbsState', [
                {
                    text: this.$t('title'),
                    to: {name: `${this.formRouteNamePrefix}.list`}
                },
            ]);
            this.initEditForm();
        },
        methods: {
            defaultFormData() {
                return {
                    tableName: this.tableName,
                    nameEn: this.getAttributeData(),
                    nameVi: this.getAttributeData(),
                    nameFr: this.getAttributeData(),
                    numberOfMonth: this.getAttributeData(),
                    // priceVN, priceUS, priceFR
                };
            },
            initEditForm() {
                this.processing = true;
                this.Util.get({
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

                    if (res.data.table_name) {
                        this.formData.tableName = res.data.table_name;
                    }
                    res.data.prices.forEach((price) => {
                        this.countryList.push({
                            ...price,
                            price: null,
                        })
                        this.$set(this.formData, `price${price.country_code}`, this.getAttributeData({
                            value: price.price || null,
                        }));
                    });
                    this.formData.id = res.data.id;
                    this.formData.nameEn.value = res.data.nameEn || '';
                    this.formData.nameVi.value = res.data.nameVi || '';
                    this.formData.nameFr.value = res.data.nameFr || '';
                    this.formData.numberOfMonth.value = res.data.numberOfMonth || 0;
                }).always(() => {
                    this.processing = false;
                });
            },
            getFormData() {
                if (!this.validateValue()) {
                    return null;
                }

                let data = {};
                Object.keys(this.formData).forEach((key) => {
                    switch (key) {
                        case 'id':
                        case 'tableName':
                            data[key] = this.formData[key];
                            break;
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
                    if (key === 'id' || key === 'tableName') {
                        return;
                    }
                    this.formData[key].error = errors[key] && errors[key][0] || null;
                });
            },
        },
    }
</script>
