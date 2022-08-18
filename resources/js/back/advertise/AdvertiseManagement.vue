<template>
    <b-row>
        <b-col cols="48">
            <div class="content__inner">
                <b-row class="pb-2">
                    <b-col class="d-flex-center-y" style="min-width: 300px;">
                        <div class="d-inline-block">
                            {{ $t('total_count') }}: {{ advertiseList.total }}
                        </div>
                    </b-col>
                    <b-col class="text-right" style="min-width: 300px;">
                            <span id="post-approve-btn-wrapper">
                                <a-button
                                    :icon-class="['mr-lg-2']"
                                    variant="primary"
                                    :loading="processing"
                                    :disabled="!isSelectAny"
                                    @click="approveAdvertises"
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
                            {{ $t('common.button.approve') }}
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
                <b-row>
                    <b-col cols="48">
                        <a-table
                            :items="advertiseList.data"
                            :fields="advertiseFields"
                            :loading="loading"
                            :pagination="pagination"
                        >
                            <template #cell(title)="data">
                                <span>
                                    <router-link :to="{name: route.edit, params:{code: data.item.code, action: 'detail'}}">
                                        {{data.item.title}}
                                    </router-link>
                                </span>
                            </template>
                            <template #cell(image)="data">
                                <img :src="data.item.image" width="50px" height="50px" alt="Ad image">
                            </template>
                            <template #cell(duration)="data">
                                <span v-if="data.item.validFrom">
                                    {{DateUtil.getDateTimeString2(new Date(data.item.validFrom))}} to {{DateUtil.getDateTimeString2(new Date(data.item.validTo))}}
                                </span>
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
                            <template #cell(action)="data">
                                <b-link v-if="data.item.status === EStatus.ACTIVE" class="text-primary mx-1">
                                    <router-link :to="{name: route.edit, params:{code: data.item.code, action: 'edit'}}" class="no-decoration">
                                        <i class="fas fa-edit" :title="$t('common.tooltip.edit')"/>
                                    </router-link>
                                </b-link>
                                <a-button-delete
                                    v-if="data.item.status === EStatus.ACTIVE"
                                    class="text-primary mx-1"
                                    @click="deleteAdvertise(data.item.code)"
                                    :disabled="deleting[`${data.item.code}`]"
                                    :deleting="deleting[`${data.item.code}`]"
                                    :tooltip="{ boundary: 'window' }" :title="$t('common.tooltip.delete')"
                                />
                            </template>
                        </a-table>
                        <paging @page-change="onPageChangeHandler" :disabled="loading" :total="advertiseList.total" ref="pagingEl"/>
                    </b-col>
                </b-row>
            </div>
        </b-col>
    </b-row>
</template>

<script>
    import {mapState} from "vuex";
    import FDownloadIcon from 'vue-feather-icons/icons/DownloadIcon';
    import FCheckSquareIcon from 'vue-feather-icons/icons/CheckSquareIcon';
    import ECustomerType from "../../constants/customer-type";
    import userListManage from "../../locales/back/user/user-detail";
    import EErrorCode from "../../constants/error-code";
    import EStatus from "../../constants/status";
    import EAdvertiseStatus from "../../constants/advertise-status";

    export default {
        inject: ['Util', 'StringUtil', 'DateUtil'],
        i18n: {
            messages: userListManage
        },
        components: {
            FDownloadIcon,
            FCheckSquareIcon,
        },
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
                advertiseList: {
                    data: [],
                    total: 0,
                },
                loading: false,
                processing: false,
                deleting: {},
                EErrorCode,
                ECustomerType,
                EStatus,
                EAdvertiseStatus,
            }
        },
        computed: {
            ...mapState(['filterValueState', 'queryFilterState']),
            advertiseFields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'align-middle text-center', colWidth: '5%'},
                    {label: this.$t('tab.advertise-list.table.image'), key: 'image', class: 'text-center align-middle', colWidth: '10%'},
                    {label: this.$t('tab.advertise-list.table.title'), key: 'title', class: 'text-center align-middle', colWidth: '10%'},
                    {label: this.$t('tab.advertise-list.table.advertising-package'), key: 'package', class: 'text-center align-middle', colWidth: '15%'},
                    ...(
                        !this.$route.params.userCode ? [{label: this.$t('tab.advertise-list.table.advertiser-id'), key: 'userCode', class: 'text-center align-middle', colWidth: '10%'}] : []
                    ),
                    {label: this.$t('tab.advertise-list.table.duration'), key: 'duration', class: 'text-center align-middle', colWidth: '20%'},
                    {label: this.$t('tab.advertise-list.table.status'), key: 'status', class: 'text-center align-middle', colWidth: '10%'},
                    {label: this.$t('tab.advertise-list.table.option'), key: 'action', class: 'text-center align-middle', colWidth: '10%'},
                ];
            },
            route() {
                return {
                    search: this.$route.params.userCode ? `${this.$route.meta.baseUrl}/${this.$route.params.userCode}/promo-list` : `${this.$route.meta.baseUrl}/search`,
                    delete: this.$route.params.userCode ? `${this.$route.meta.baseUrl}/promo-list/delete` : `${this.$route.meta.baseUrl}/delete`,
                    edit: 'promo.edit'
                }
            },
            isSelectAll() {
                let waitingRows = this.advertiseList.data.filter((item) => item.status === EStatus.WAITING);
                return waitingRows.length && waitingRows.every((item) => item.selected);
            },
            isSelectAny() {
                let waitingRows = this.advertiseList.data.filter((item) => item.status === EStatus.WAITING);
                return waitingRows.length && waitingRows.some((item) => item.selected);
            },
            selectedRows() {
                return this.advertiseList.data
                    .filter((item) => item.status === EStatus.WAITING && item.selected);
            }
        },
        created() {
            this.$store.commit('updateBreadcrumbsState', [
                ...(
                    this.$route.params.userCode
                        ? [
                            {
                                text: 'Danh sách người quảng cáo',
                                to: { name: 'advertiser.list' }
                            },
                            {
                                text: this.$route.params.userCode,
                                to: { name: 'advertiser.info' }
                            }
                        ]
                        : [
                            {
                                text: 'Danh sách quảng cáo',
                                to: { name: 'advertise' }
                            }
                        ]
                ),
            ]);
            this.$store.commit('updateFilterFormState', [
                {
                    label: this.$t('tab.advertise-list.table.status'),
                    type: 'select',
                    name: 'status',
                    options: [
                        {
                            name: this.$t('tab.advertise-list.status.approved'),
                            value: EAdvertiseStatus.APPROVED,
                        },
                        {
                            name: this.$t('tab.advertise-list.status.waiting'),
                            value: EAdvertiseStatus.WAITING,
                        },
                        {
                            name: this.$t('tab.advertise-list.status.expired'),
                            value: EAdvertiseStatus.EXPIRED,
                        },
                        {
                            name: this.$t('tab.advertise-list.status.deleted'),
                            value: EAdvertiseStatus.DELETED,
                        }
                    ]
                },
                {
                    type: 'date',
                    name: 'validFrom',
                    placeholder: this.$t('placeholder.filter.created_at_from'),
                    dropleft: true,
                },
                {
                    type: 'date',
                    name: 'validTo',
                    placeholder: this.$t('placeholder.filter.created_at_to'),
                    dropleft: true,
                },
            ]);
            this.$store.commit('updateQueryFilterState', {
                enable: true,
                placeholder: this.$t('filter.advertise'),
            });
            this.$store.commit('updateFilterValueState', {
                q: this.$route.query.q,
            })
        },
        watch: {
            filterValueState(val) {
                window.history.pushState(null, null, this.StringUtil.getUrlWithQueries(window.location.pathname, {...val.value, page: this.pagination.page}));
                this.getAvertiseList({page: 1, size: this.sz});
            }
        },
        methods: {
            setAllSelectValue() {
                let value = !this.isSelectAll;
                this.advertiseList.data.forEach((item) => {
                    item.selected = value;
                });
            },
            onPageChangeHandler(paging) {
                this.getAvertiseList(paging);
            },
            getAvertiseList(paging) {
                this.loading = true;
                let reqData = Object.assign({
                    pageSize: paging.size || this.pagination.size,
                    page: paging.page ? paging.page : 1,
                    filter: {
                        customerType: this.customerType,
                        userCode: this.$route.params.userCode,
                        ...this.filterValueState.value,
                    }
                });
                return this.Util.get({
                    url: this.route.search,
                    data: reqData,
                }).done(response => {
                    if (response.error !== EErrorCode.NO_ERROR) {
                        this.Util.showMsg2(response);
                        return false;
                    }
                    response.advertiseList.data.forEach((item) => {
                        item.selected = false;
                    });
                    this.advertiseList = response.advertiseList;
                    this.pagination = {page: paging.page, size: paging.size};
                    if (!this.pagination.size) {
                        this.pagination.size = this.advertiseList.per_page
                    }
                }).always(() => {
                    this.loading = false;
                    this.Util.loadingScreen.hide();
                });
            },
            async deleteAdvertise(code) {
                if (this.deleting[`${code}`]) {
                    return;
                }

                let confirm = await new Promise((resolve) => {
                    this.Util.confirmDelete(this.$t('tab.advertise-list.advertise'), resolve);
                });
                if (!confirm) {
                    return;
                }

                this.deleting[`${code}`] = true;
                this.processing = true;
                this.Util.post({
                    url: this.route.delete,
                    data: {
                        code: code,
                    },
                }).done(async (res) => {
                    if (res.error !== EErrorCode.NO_ERROR) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }

                    await this.getAvertiseList(this.pagination);

                    this.Util.showMsg('success', null, res.msg);
                }).always(() => {
                    this.deleting[`${code}`] = false;
                    this.processing = false;
                });
            },
            async approveAdvertises(code) {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm(
                        this.$tc('common.confirmation.approveMulti', this.selectedRows.length, {
                            objectName: this.$tc('tab.advertise-list.advertises', this.selectedRows.length)
                        }),
                        resolve
                    );
                });
                if (!confirm) {
                    return;
                }
                let adsCodeList = this.selectedRows.map((item) => item.code);
                this.Util.loadingScreen.show();
                this.processing = true;
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/approve`,
                    data: {
                        adsCodeList: adsCodeList,
                    },
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }

                    await this.getAvertiseList(this.pagination);

                    this.Util.showMsg('success', null, res.msg);
                }).always(() => {
                    this.processing = false;
                    this.Util.loadingScreen.hide();
                });
            }
        }
    }
</script>

<style scoped>

</style>
