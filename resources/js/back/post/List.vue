<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <b-row class="mb-3">
                        <b-col class="d-flex-center-y" style="min-width: 300px;">
                            <div class="d-inline-block">
                                {{ $t('total_count') }}: {{ table.total }}
                            </div>
                        </b-col>
                        <b-col class="text-right" style="min-width: 300px;">
                            <span id="post-approve-btn-wrapper">
                                <a-button
                                    :icon-class="['mr-lg-2']"
                                    variant="primary"
                                    :loading="processing"
                                    :disabled="!isSelectAny"
                                    @click="approvePosts"
                                >
                                    <template #icon>
                                        <f-check-square-icon size="20" class="mr-lg-2"/>
                                    </template>
                                    <template #default>
                                        <span class="d-none d-lg-inline">{{ $t('common.button.approve') }}</span>
                                    </template>
                                </a-button>
                            </span>
                            <b-tooltip target="post-approve-btn-wrapper" v-if="!isSelectAny">
                                {{ $t('tooltip.select_one_post') }}
                            </b-tooltip>
                            <a-button :icon-class="['mr-lg-2']" variant="outline-primary">
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
                        <template #cell(code)="data">
                            <b-link :to="{name: 'post.detail', params: {postCode: data.item.code}}">
                                {{ data.item.code }}
                            </b-link>
                        </template>
                        <template #cell(resources)="{item}">
                            <template v-for="(resource, index) in item.resources">
                                <div class="position-relative d-inline-block mr-2"
                                     v-if="index < maxImagePerCol"
                                >
                                    <div class="d-flex-center overflow-hidden" style="width: 50px; height: 50px;">
                                        <img :src="resource" :alt="index" class="w-100">
                                    </div>

                                    <div v-if="item.resources.length > maxImagePerCol && index === maxImagePerCol - 1"
                                         class="div-mask text-white d-flex-center h4"
                                         style="background: #cccccccc;"
                                    >
                                        +{{ item.resources.length - maxImagePerCol }}
                                    </div>
                                </div>
                            </template>
                        </template>
                        <template #cell(price)="data">
                            <div v-for="price in data.item.prices">
                                <div v-if="price.priceStr !== ''">
                                    {{ price.currency_sign }}&#160;{{ price.priceStr }}
                                </div>
                            </div>
                        </template>
                        <template #head(status)="data">
                            <div class="d-flex">
                                <span class="mr-2">{{ data.label }}</span>
                                <b-checkbox
                                    :checked="isSelectAll"
                                    :disabled="processing"
                                    @change="setAllSelectValue"
                                />
                            </div>
                        </template>
                        <template #cell(status)="data">
                            <div
                                v-if="data.item.sell_status === ESellStatus.SOLD"
                                :class="[ESellStatus.getTextVariant(data.item.sell_status)]"
                            >
                                {{ data.item.sellStatusStr }}
                            </div>
                            <div
                                v-else
                                :class="[EStatus.getTextVariant(data.item.status)]"
                                class="d-flex"
                            >
                                <span class="mr-2">{{ data.item.statusStr }}</span>
                                <b-checkbox
                                    v-if="data.item.status === EStatus.WAITING"
                                    v-model="data.item.selected"
                                    :disabled="processing"
                                />
                            </div>
                        </template>
                        <template #cell(sellerCode)="data">
							<b-link :to="{name: 'seller.detail', params: {userCode: data.item.user_code}}">
								{{ data.item.user_code }}
							</b-link>
                        </template>
                        <template #cell(paymentStatus)="data">
                            <div :class="[EPaymentStatus.getTextVariant(data.item.payment_status)]">
                                {{ data.item.paymentStatusStr }}
                            </div>
                        </template>
                        <template #cell(displayTime)="data">
                            <span v-html="data.item.validFromStr"></span>
                            <span>-</span>
                            <span v-html="data.item.validToStr"></span>
                        </template>
                        <template v-slot:cell(action)="data">
                            <!--<b-link
                                class="text-primary mx-1"
                                :to="{name: 'category.edit', params: {categoryId: data.item.id}}"
                                :disabled="processing || !data.item.editable"
                                v-b-tooltip.hover :title="$t(data.item.editable ? 'common.tooltip.edit' : 'tooltip.not_editable')"
                            >
                                <i class="fas fa-edit"/>
                            </b-link>-->
                            <a-button-delete
                                v-if="data.item.status !== EStatus.DELETED"
                                class="text-primary mx-1"
                                @click="deleteItem(data.item)"
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
    import postListMessages from '../../locales/back/post-management-list';
    import FDownloadIcon from 'vue-feather-icons/icons/DownloadIcon';
    import FCheckSquareIcon from 'vue-feather-icons/icons/CheckSquareIcon';
    import listMixin from '../mixins/list-mixin';
    import EStatus from '../../constants/status';
    import ESellStatus from '../../constants/sell-status';
    import EPaymentStatus from '../../constants/payment-status';

    export default {
        name: 'PostList',
        i18n: {
            messages: postListMessages,
        },
        inject: ['StringUtil', 'DateUtil'],
        mixins: [listMixin],
        components: {
            FDownloadIcon,
            FCheckSquareIcon,
        },
        data() {
            return {
                maxImagePerCol: 3,
                EStatus,
                ESellStatus,
                EPaymentStatus,
            }
        },
        computed: {
            fields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'text-center align-middle', colWidth: '5%'},
                    {label: this.$t('table.column.post_id'), key: 'code', thClass: 'text-center align-middle', tdClass: 'col-align-center', colWidth: '11%'},
                    {label: this.$t('table.column.image'), key: 'resources', thClass: 'image-thead text-center align-middle', tdClass: 'col-text', colWidth: '11%'},
                    {label: this.$t('table.column.title'), key: 'title', thClass: 'text-center align-middle', tdClass: 'col-text', colWidth: '16%'},
                    {label: this.$t('table.column.price'), key: 'price', thClass: 'text-center align-middle', tdClass: 'col-number', colWidth: '12%'},
                    {label: this.$t('table.column.seller_id'), key: 'sellerCode', thClass: 'text-center align-middle', tdClass: 'col-text', colWidth: '10%'},
                    {label: this.$t('table.column.display_time'), key: 'displayTime', thClass: 'text-center align-middle', tdClass: 'col-align-center', colWidth: '15%'},
                    {label: this.$t('table.column.payment_status'), key: 'paymentStatus', thClass: 'text-center align-middle', tdClass: 'col-text', colWidth: '6%'},
                    {label: this.$t('table.column.status'), key: 'status', thClass: 'text-center align-middle', tdClass: 'col-text', colWidth: '6%'},
                    {label: this.$t('table.column.custom'), key: 'action', class: 'text-center align-middle', colWidth: '8%'},
                ];
            },
            routes() {
                return {
                    search: `${this.$route.meta.baseUrl}/search`,
                    delete: `${this.$route.meta.baseUrl}/delete`,
                    approve: `${this.$route.meta.baseUrl}/approve`,
                };
            },
            belongToOneUser() {
                return !!this.$route.params.userCode;
            },
            defaultFilter() {
                let result = {};
                if (this.belongToOneUser) {
                    result.userCode = this.$route.params.userCode;
                }
                return result;
            },
            isSelectAll() {
                let waitingRows = this.table.data.filter((item) => item.status === EStatus.WAITING);
                return waitingRows.length && waitingRows.every((item) => item.selected);
            },
            isSelectAny() {
                let waitingRows = this.table.data.filter((item) => item.status === EStatus.WAITING);
                return waitingRows.length && this.table.data.some((item) => item.selected);
            },
            selectedRows() {
                return this.table.data
                    .filter((item) => item.status === EStatus.WAITING && item.selected);
            }
        },
        created() {
            this.$store.commit('updateBreadcrumbsState', [
                {
                    text: this.$t('title'),
                    to: ''
                },
            ]);
            this.$store.commit('updateFilterFormState', [
                {
                    type: 'date',
                    name: 'validFrom',
                    label: this.$t('placeholder.filter.created_at_from'),
                },
                {
                    type: 'date',
                    name: 'validTo',
                    label: this.$t('placeholder.filter.created_at_to'),
                },
            ]);
            this.$store.commit('updateQueryFilterState', {
                enable: true,
                placeholder: this.$t('name_filter'),
            });
            this.$store.commit('updateFilterValueState', {
                q: this.$route.query.q,
            })
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
                let width = $('.image-thead').width();
                if (width) {
                    this.maxImagePerCol = Math.max(Math.floor(width / 58), 1);
                }
            },
            setAllSelectValue() {
                let value = !this.isSelectAll;
                this.table.data.forEach((item) => {
                    item.selected = value;
                });
            },
            onListDataFetchSuccess(paging, data) {
                data.data.forEach((item) => {
                    if (item.valid_from) {
                        let date = new Date(item.valid_from);
                        try {
                            item.validFromStr = this.StringUtil.sp2nbsp(this.DateUtil.getDateTimeString(date));
                        } catch (e) { }
                    }
                    if (item.valid_to) {
                        let date = new Date(item.valid_to);
                        try {
                            item.validToStr = this.StringUtil.sp2nbsp(this.DateUtil.getDateTimeString(date));
                        } catch (e) { }
                    }
                    item.selected = false;
                });
                this.table = data;
                this.pagination = {page: paging.page, size: paging.size};
            },
            async approvePosts() {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm(
                        this.$tc('common.confirmation.approveMulti', this.selectedRows.length, {
                            objectName: this.$tc('object_names', this.selectedRows.length)
                        }),
                        resolve
                    );
                });

                if (!confirm) {
                    return;
                }
                let postCodeList = this.selectedRows.map((item) => item.code);
                this.processing = true;
                this.Util.post({
                    url: this.routes.approve,
                    data: {
                        postCodeList,
                    }
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }

                    await this.getListData(this.pagination);

                    this.Util.showMsg('success', null, res.msg);
                }).fail(() => {
                    this.Util.showMsg('error', null, this.$t('common.error.unknown'));
                }).always(() => {
                    this.processing = false;
                });
            },
        }
    }
</script>
