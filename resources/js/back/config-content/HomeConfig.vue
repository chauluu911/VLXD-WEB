<template>
    <div>
        <b-button-group class="mb-3">
            <b-button
                :to="{name: 'home-config.info', params: {homeConfigType: EHomeConfigType.HOME}}"
                variant="primary"
                active-class="secondary-active"
            >
                {{ $t('category.home') }}
            </b-button>
            <b-button
                :to="{name: 'home-config.info', params: {homeConfigType: EHomeConfigType.HOME_BUYER}}"
                variant="primary"
                active-class="secondary-active"
            >
                {{ $t('category.home_buyer') }}
            </b-button>
            <b-button
                :to="{name: 'home-config.info', params: { homeConfigType: EHomeConfigType.HOME_SELLER}}"
                variant="primary"
                active-class="secondary-active"
            >
                {{ $t('category.home_seller') }}
            </b-button>
            <b-button
                :to="{name: 'home-config.info', params: { homeConfigType: EHomeConfigType.HOME_ADS}}"
                variant="primary"
                active-class="secondary-active"
            >
                {{ $t('category.home_ads') }}
            </b-button>
            <b-button
                :to="{name: 'home-config.info', params: { homeConfigType: EHomeConfigType.ABOUT_US}}"
                variant="primary"
                active-class="secondary-active"
            >
                {{ $t('category.about_us') }}
            </b-button>
        </b-button-group>
        <div class="content__inner">
            <b-row class="mb-3 border-bottom border-primary">
                <b-col style="min-width: 400px;">
                    <div class="d-inline-block h3 mr-4 text-primary">
                        <span v-if="this.homeConfigType === EHomeConfigType.HOME">
                            {{ $t('category.home') }}
                        </span>
                        <span v-else-if="this.homeConfigType === EHomeConfigType.HOME_BUYER">
                            {{ $t('category.home_buyer') }}
                        </span>
                        <span v-else-if="this.homeConfigType === EHomeConfigType.HOME_SELLER">
                            {{ $t('category.home_seller') }}
                        </span>
                        <span v-else-if="this.homeConfigType === EHomeConfigType.HOME_ADS">
                            {{ $t('category.home_ads') }}
                        </span>
                        <span v-else>
                            {{ $t('category.about_us') }}
                        </span>
                    </div>
                </b-col>
                <b-col class="text-right mb-3" style="min-width: 300px;">
                    <a-button
                        :icon-class="['mr-lg-2']"
                        variant="primary"
                        class="mr-2"
                        @click="submit"
                        :loading="loadingState"
                    >
                        <template #icon>
                            <f-save-icon size="20" class="mr-lg-2"/>
                        </template>
                        <template #default>
                            {{ $t('common.button.save') }}
                        </template>
                    </a-button>
                </b-col>
            </b-row>
            <b-row class="mb-4">
                <b-col cols="48" class="mb-3">
                    <div class="h4 text-primary bg-white d-inline-block" style="margin-left: 5px; padding: 0 5px;">
                        {{ $t('content') }} (En)
                    </div>
                    <hr style="margin-top: -18px;"/>
                </b-col>
                <b-col cols="48">
                    <b-form-group ref="textValueEn" :label="$t('content')" label-class="required">
                        <ck-document
                            v-model="formData.value.textValueEn"
                            class="col-48 p-0"
                            :ck-class="['border border-top-0']"
                            :ck-style="{height: '400px'}"
                        >
                            <template #content>
                                <span class="small text-danger">{{ formData.error && formData.error.textValueEn && formData.error.textValueEn[0] }}</span>
                            </template>
                        </ck-document>
                    </b-form-group>
                </b-col>
            </b-row>
                <b-row class="mb-4">
                    <b-col cols="48" class="mb-3">
                        <div class="h4 text-primary bg-white d-inline-block" style="margin-left: 5px; padding: 0 5px;">
                            {{ $t('content') }} (Fr)
                        </div>
                        <hr style="margin-top: -18px;"/>
                    </b-col>
                    <b-col cols="48">
                        <b-form-group ref="textValueFr" :label="$t('content')" label-class="required">
                            <ck-document
                                v-model="formData.value.textValueFr"
                                class="col-48 p-0"
                                :ck-class="['border border-top-0']"
                                :ck-style="{height: '400px'}"
                            >
                                <template #content>
                                    <span class="small text-danger">{{ formData.error && formData.error.textValueFr && formData.error.textValueFr[0] }}</span>
                                </template>
                            </ck-document>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row class="mb-4">
                    <b-col cols="48" class="mb-3">
                        <div class="h4 text-primary bg-white d-inline-block" style="margin-left: 5px; padding: 0 5px;">
                            {{ $t('content') }} (Vi)
                        </div>
                        <hr style="margin-top: -18px;"/>
                    </b-col>
                    <b-col cols="48">
                        <b-form-group ref="textValueVi" :label="$t('content')" label-class="required">
                            <ck-document
                                v-model="formData.value.textValueVi"
                                class="col-48 p-0"
                                :ck-class="['border border-top-0']"
                                :ck-style="{height: '400px'}"
                            >
                                <template #content>
                                    <span class="small text-danger">{{ formData.error && formData.error.textValueVi && formData.error.textValueVi[0] }}</span>
                                </template>
                            </ck-document>
                        </b-form-group>
                    </b-col>
                </b-row>
        </div>
    </div>
</template>

<script>
import EHomeConfigType from "../../constants/home-config-type";
import homeConfigMessages from '../../locales/back/home-config';
import {mapState} from "vuex";
import CreatingFormMixin from "../mixins/creating-form-mixin";
import FSaveIcon from 'vue-feather-icons/icons/SaveIcon';

export default {
    name: 'HomeConfig',
    i18n: {
        messages: homeConfigMessages,
    },
    mixins: [CreatingFormMixin],
    components: {
        FSaveIcon
    },
    inject: ['Util'],
    data() {
        return {
            multiLang: false,
            homeConfigType: parseInt(this.$route.params.homeConfigType),
            EHomeConfigType
        };
    },
    computed: {
        ...mapState(['queryFilterState', 'loadingState']),
        fields() {
            return [
                {label: this.$t('table.column.no'), key: 'index', class: 'text-center align-middle', colWidth: '5%'},
                {
                    label: this.$t('table.column.name'),
                    key: 'name',
                    thClass: 'text-center align-middle',
                    tdClass: 'col-text'
                },
            ];
        },
        routes() {
            return {
                info: `${this.$route.meta.baseUrl}/info`,
                save: `${this.$route.meta.baseUrl}/save`
            }
        },
        defaultFilter() {
            return {
                type: this.homeConfigType,
            }
        },
    },
    created() {
        this.$store.commit('updateFilterFormState', []);
        this.$store.commit('updateBreadcrumbsState', [
            {
                text: this.$t('title'),
                to: {name: 'home-config'}
            },
        ]);
        this.$store.commit('updateQueryFilterState', {
            enable: false
        });
        this.initEditForm();
    },
    beforeRouteUpdate(to, from, next) {
        this.homeConfigType = parseInt(to.params.homeConfigType);
        this.initEditForm();
        next();
    },
    methods: {
        defaultFormData() {
            return this.getAttributeData({
                'value': {
                    'textValueEn': '',
                    'textValueFr': '',
                    'textValueVi': ''
                }
            });
        },
        initEditForm() {
            this.processing = true;
            this.Util.post({
                url: this.routes.info,
                data: {
                    'configNameEn': EHomeConfigType.valueToName(this.homeConfigType),
                    'configNameFr': EHomeConfigType.valueToName(this.homeConfigType, '.fr'),
                    'configNameVi': EHomeConfigType.valueToName(this.homeConfigType, '.vi')
                }
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
                this.$set(this.formData, 'value', res.data)
            }).always(() => {
                this.processing = false;
            });
        },
        getFormData() {
            return {
                ...this.formData.value,
                'configNameEn': EHomeConfigType.valueToName(this.homeConfigType),
                'configNameFr': EHomeConfigType.valueToName(this.homeConfigType, '.fr'),
                'configNameVi': EHomeConfigType.valueToName(this.homeConfigType, '.vi')
            };
        },
        onSaveSuccess(msg) {
            this.Util.showMsg('success', null, msg, {
                onHidden: () => {
                    this.Util.askUserWhenLeavePage(false);
                    this.initEditForm();
                }
            });
        },
        showErrors(errors, msg = null) {
            this.$store.commit('updateLoadingState', false);
            this.footerMsg = msg;
            this.formData.error= errors;
            switch (Object.keys(errors)[0]){
                case 'textValueEn':
                    this.$refs.textValueEn.$el.focus()
                    break;
                case 'textValueFr':
                    this.$refs.textValuaFr.$el.focus()
                    break;
                case 'textValueVi':
                    this.$refs.textValuaVi.$el.focus()
                    break;
            }

        },
    },
}
</script>
