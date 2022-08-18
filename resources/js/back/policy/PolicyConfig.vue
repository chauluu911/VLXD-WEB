<template>
    <div>
        <b-button-group class="mb-3">
            <b-button
                :to="{name: 'config.policy', params: {policyType: EPolicyType.BUY_POLICY}}"
                variant="primary"
                active-class="secondary-active"
            >
                Chính sách mua hàng
            </b-button>
            <b-button
                :to="{name: 'config.policy', params: {policyType: EPolicyType.TRANSPORTATION_POLICY}}"
                variant="primary"
                active-class="secondary-active"
            >
                Quy trình giải quyết khiếu nại
            </b-button>
            <b-button
                :to="{name: 'config.policy', params: { policyType: EPolicyType.PAYMENT_POLICY}}"
                variant="primary"
                active-class="secondary-active"
            >
                Chính sách thanh toán
            </b-button>
            <b-button
                :to="{name: 'config.policy', params: { policyType: EPolicyType.PRIVACY_POLICY}}"
                variant="primary"
                active-class="secondary-active"
            >
                Chính sách bảo mật
            </b-button>
            <b-button
                :to="{name: 'config.policy', params: { policyType: EPolicyType.TERMS_AND_CONDITIONS}}"
                variant="primary"
                active-class="secondary-active"
            >
                Điều khoản
            </b-button>
            <b-button
                :to="{name: 'config.policy', params: { policyType: EPolicyType.USER_GUIDE}}"
                variant="primary"
                active-class="secondary-active"
            >
                Hướng dẫn
            </b-button>
            <b-button
                :to="{name: 'config.policy', params: { policyType: EPolicyType.ABOUT_US}}"
                variant="primary"
                active-class="secondary-active"
            >
                Giới thiệu
            </b-button>
        </b-button-group>
        <div class="content__inner">
            <b-row class="mb-3 border-bottom border-primary">
                <b-col style="min-width: 400px;">
                    <div class="d-inline-block h3 mr-4 text-primary">
                        <span v-if="this.policyType === EPolicyType.BUY_POLICY">
                            Chính sách mua hàng
                        </span>
                        <span v-else-if="this.policyType === EPolicyType.TRANSPORTATION_POLICY">
                            Quy trình giải quyết khiếu nại
                        </span>
                        <span v-else-if="this.policyType === EPolicyType.PAYMENT_POLICY">
                            Chính sách thanh toán
                        </span>
                        <span v-else-if="this.policyType === EPolicyType.PRIVACY_POLICY">
                            Chính sách bảo mật
                        </span>
                        <span v-else-if="this.policyType === EPolicyType.TERMS_AND_CONDITIONS">
                            Điều khoản
                        </span>
                        <span v-else-if="this.policyType === EPolicyType.USER_GUIDE">
                            Hướng dẫn
                        </span>
                        <span v-else-if="this.policyType === EPolicyType.ABOUT_US">
                            Giới thiệu
                        </span>
                    </div>
                </b-col>
                <b-col class="text-right mb-3" style="min-width: 300px;">
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
                            {{ $t('common.button.save') }}
                        </template>
                    </a-button>
                </b-col>
            </b-row>
            <b-row class="mb-4">
                <b-col cols="48" class="mb-3">
                    <div class="h4 text-primary bg-white d-inline-block" style="margin-left: 5px; padding: 0 5px;">
                        {{ $t('content') }}
                    </div>
                    <hr style="margin-top: -18px;"/>
                </b-col>
                <b-col cols="48">
                    <b-form-group :label="$t('content')" label-class="required">
                        <ck-document
                            v-model="formData.value.textValue"
                            class="col-48 p-0"
                            :ck-class="['border border-top-0']"
                            :ck-style="{height: '400px'}"
                        >
                            <template #content>
                                <span class="small text-danger">{{ formData.error }}</span>
                            </template>
                        </ck-document>
                    </b-form-group>
                </b-col>
            </b-row>
        </div>
    </div>
</template>

<script>
import EPolicyType from "../../constants/policy-type";
import policyMessages from '../../locales/back/policy-config';
import {mapState} from "vuex";
import CreatingFormMixin from "../mixins/creating-form-mixin";
import FSaveIcon from 'vue-feather-icons/icons/SaveIcon';

export default {
    name: 'PolicyConfig',
    i18n: {
        messages: policyMessages,
    },
    mixins: [CreatingFormMixin],
    components: {
        FSaveIcon
    },
    inject: ['Util'],
    data() {
        return {
            policyType: parseInt(this.$route.params.policyType),
            EPolicyType
        };
    },
    computed: {
        ...mapState(['queryFilterState']),
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
                type: this.policyType,
            }
        },
    },
    created() {
        this.$store.commit('updateFilterFormState', []);
        this.$store.commit('updateBreadcrumbsState', [
            {
                text: this.$t('title'),
                to: {name: 'policy'}
            },
        ]);
        this.$store.commit('updateQueryFilterState', {
            enable: false
        });
        this.initEditForm();
    },
    beforeRouteUpdate(to, from, next) {
        this.policyType = parseInt(to.params.policyType);
        this.initEditForm();
        next();
    },
    methods: {
        defaultFormData() {
            return this.getAttributeData({
                'value': {
                    'textValue': '',
                }
            });
        },
        initEditForm() {
            this.processing = true;
            this.Util.post({
                url: this.routes.info,
                data: {
                    'configName': EPolicyType.valueToName(this.policyType),
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
                'configName': EPolicyType.valueToName(this.policyType),
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
    },
}
</script>
