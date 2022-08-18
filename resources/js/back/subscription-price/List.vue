<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <b-row class="mb-1">
                        <b-col class="mb-2" style="min-width: 400px;">
                            <div class="d-inline-block">
                                {{ $t('total_count') }}: {{ table.total }}
                            </div>
                        </b-col>
                        <b-col class="text-right" style="min-width: 300px;">
                            <a-button :icon-class="['mr-lg-2']" variant="outline-primary" class="mr-2 mb-2" :to="{name: `${formRouteNamePrefix}.create`}">
                                <template #icon>
                                    <f-plus-icon size="20" class="mr-lg-2"/>
                                </template>
                                <template #default>
                                    {{ $t('common.button.add2', {objectName: $t('object_name').toLowerCase()}) }}
                                </template>
                            </a-button>
                            <a-button :icon-class="['mr-lg-2']" variant="outline-primary" class="mb-2">
                                <template #icon>
                                    <f-download-icon size="20" class="mr-lg-2"/>
                                </template>
                                <template #default>
                                    <span class="d-none d-lg-inline">{{ $t('common.button.download') }}</span>
                                </template>
                            </a-button>
                        </b-col>
                    </b-row>
                    <a-table :items="table.data" :fields="fields" :loading="loading" :pagination="pagination">
                        <template #cell(price)="data">
                            <div v-for="price in data.item.prices">
                                {{ price.currency_sign }}&#160;{{ price.priceStr }}
                            </div>
                        </template>
                        <template v-slot:cell(action)="data">
                            <b-link
                                class="text-primary mx-1"
                                :to="{name: `${formRouteNamePrefix}.detail`, params: {subscriptionPriceId: data.item.id, action: 'edit'}}"
                                :disabled="processing"
                                v-b-tooltip.hover :title="$t('common.tooltip.edit')"
                            >
                                <i class="fas fa-edit"/>
                            </b-link>
                            <a-button-delete
                                class="text-primary mx-1"
                                @click="deleteItem(data.item)"
                                :disabled="deleting[`${data.item.id}`]"
                                :deleting="deleting[`${data.item.id}`]"
                                tooltip :title="$t('common.tooltip.delete')"
                            />
                        </template>
                    </a-table>
                    <paging @page-change="onPageChangeHandler" :total="table.total" :disabled="loading" ref="pagingEl"/>
                </div>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import getSubscriptionPriceListMessages from '../../locales/back/subscription-price-management-list';
    import FDownloadIcon from 'vue-feather-icons/icons/DownloadIcon'
    import FPlusIcon from 'vue-feather-icons/icons/PlusIcon'
    import listMixin from '../mixins/list-mixin';
    import EConfigTableName from '../../constants/config-table-name';

    export default {
        name: 'SubscriptionPriceList',
        i18n: {},
        inject: ['StringUtil', 'DateUtil'],
        mixins: [listMixin],
        components: {
            FDownloadIcon,
            FPlusIcon,
        },
        props: {
            tableName: {
                type: String,
                required: true,
            }
        },
        computed: {
            fields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'text-center align-middle', colWidth: '5%'},
                    {label: this.$t('table.column.name'), key: 'name', class: 'align-middle', thClass: 'text-center', tdClass: 'col-text', colWidth: '16%'},
                    {label: this.$t('table.column.count'), key: 'subscription_count', class: 'align-middle', thClass: 'text-center', tdClass: 'col-number', colWidth: '16%'},
                    {label: this.$t('table.column.price'), key: 'price', class: 'align-middle', thClass: 'text-center', tdClass: 'col-number', colWidth: '12%'},
                    {label: this.$t('table.column.duration'), key: 'numberOfMonth', class: 'align-middle', thClass: 'text-center', tdClass: 'col-align-center', colWidth: '15%'},
                    {label: this.$t('table.column.custom'), key: 'action', class: 'text-center align-middle', colWidth: '10%'},
                ];
            },
            defaultFilter() {
                return {
                    tableName: this.tableName,
                }
            },
            routes() {
                return {
                    search: `${this.$route.meta.baseUrl}/${this.tableName}/search`,
                    delete: `${this.$route.meta.baseUrl}/delete`,
                };
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
            let messages = getSubscriptionPriceListMessages(this.tableName);
            Object.keys(messages || {}).forEach((lang) => {
                this.$i18n.setLocaleMessage(lang, messages[lang]);
            });
            this.$store.commit('updateBreadcrumbsState', [
                {
                    text: this.$t('title'),
                    to: ''
                },
            ]);
            this.$store.commit('updateFilterFormState', []);
            this.$store.commit('updateFilterValueState', {
                q: this.$route.query.q,
            })
            this.$store.commit('updateQueryFilterState', {
                enable: true,
                placeholder: this.$t('name_filter'),
            });
        },
        methods: {
            onListDataFetchSuccess(paging, data) {
                data.data.forEach((item) => {
                    item.numberOfMonth = `${item.number_of_usage_month} ${this.$tc('month', item.number_of_usage_month)}`;
                });
                this.table = data;
                this.pagination = {page: paging.page, size: paging.size};
            },
        }
    }
</script>
