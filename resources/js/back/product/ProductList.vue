<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <b-row>
                        <b-col md="48">
                            <a-table
                                :items="table.data"
                                :fields="fields"
                                :loading="loading"
                                :pagination="pagination"
                            >
                                <template #cell(name)="data">
                                    <span class="text-info cursor-pointer" @click="showModalProductDetail({productId: data.item.id})">
                                        {{data.item.name}}
                                    </span>
                                </template>
                                <template #cell(image)="data">
                                    <b-img
                                        variant="primary"
                                        :src="data.item.image"
                                        width="50px"
                                        height="50px"
                                        class="bg-white"
                                    ></b-img>
                                </template>
                                <!-- <template #cell(code)="data">
                                    <router-link :to="{name: 'product.info', params:{productId: data.item.productId, action: 'info'}}">
                                        {{data.item.code}}
                                    </router-link>
                                </template> -->
                                <template #cell(description)="data">
                                    <div
                                        style="white-space: break-spaces; word-break: break-all;"
                                        v-shave='{height: 44}'
                                        v-html='data.item.description'
                                        :title="data.item.description"
                                    />
                                </template>
                                <template #cell(action)="data">
                                    <!-- <router-link
                                        v-if="data.item.status !== EStatu.DELETED"
                                        :to="{name: 'product.info', params:{productId: data.item.id, action: 'edit'}}"
                                        :title="$t('common.tooltip.edit')"
                                        class="no-decoration">
                                        <i class="text-primary fas fa-edit"/>
                                    </router-link> -->
                                    <span
                                        v-if="data.item.approvalStatus === EStatus.WAITING && data.item.status != EStatus.DELETED"
                                        :title="$t('common.tooltip.approve')"
                                        class="cursor-pointer"
                                        @click="approveOrReject(data.item.id, 1)"
                                    >
                                        <i class="text-primary fas fa-check"/>
                                    </span>
                                    <a-button-delete
                                        v-if="data.item.status !== EStatus.DELETED"
                                        class="text-primary mx-1"
                                        @click="deleteItem(data.item)"
                                        :disabled="deleting[`${data.item.id}`]"
                                        :deleting="deleting[`${data.item.id}`]"
                                        :title="$t('common.tooltip.delete')"
                                    />
                                    <span
                                        v-if="data.item.approvalStatus === EStatus.WAITING && data.item.status != EStatus.DELETED"
                                        :title="$t('common.tooltip.reject')"
                                        class="cursor-pointer"
                                        @click="approveOrReject(data.item.id, -1)"
                                    >
                                        <i class="text-primary fas fa-times"/>
                                    </span>
                                </template>
                            </a-table>
                            <paging @page-change="onPageChangeHandler" :disabled="loading" :total="table.total" ref="pagingEl"/>
                        </b-col>
                    </b-row>
                </div>
            </b-col>
        </b-row>
        <product-detail-modal
            :info-from-parent="{
                productId: this.item ? this.item.productId : null,
                isShowProductDetailModal: this.isShowProductDetailModal,
            }"
            @isShowChanged = "isShowProductDetailModal = $event"
        >
        </product-detail-modal>
    </div>
</template>
<script>
    import {mapState} from "vuex";
    import YoutubeEmbed from '../component/YoutubeEmbed.vue';
    import ECustomerType from "../../constants/customer-type";
    import productListMessages from '../../locales/back/product/list';
    import EErrorCode from "../../constants/error-code";
    import listMixin from "../mixins/list-mixin";
    import EStatus from "../../constants/status";
    import EApprovalStatus from "../../constants/approval-status";
    import EProductStatus from "../../constants/product-status";
    import ProductDetailModal from "../component/ProductDetailModal";

    export default {
        components: {
            YoutubeEmbed,
            ProductDetailModal,
        },
        inject: ['Util', 'StringUtil', 'DateUtil'],
        mixins: [listMixin],
        i18n: {
            messages: productListMessages
        },
        data() {
            return {
                disableVideo: true,
                //countries: [],
                loading: false,
                processing: false,
                isShowProductDetailModal: false,
                showModal: false,
                EErrorCode,
                item: {},
                EStatus,
                EApprovalStatus,
                EProductStatus,
            }
        },
        computed: {
            ...mapState(['queryFilterState']),
            fields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'text-center align-middle', colWidth: '5%'},
                    {label: this.$t('table.image'), key: 'image', class: 'text-center align-middle', colWidth: '5%'},
                    {label: this.$t('table.code'), key: 'code', class: 'text-center align-middle', colWidth: '5%'},
                    {label: this.$t('table.name'), key: 'name', thClass: 'text-center align-middle', tdClass: 'col-text', colWidth: '15%'},
                    {label: this.$t('table.description'), key: 'description', class: 'text-center align-middle', tdClass: 'col-text', colWidth: '20%'},
                    {label: this.$t('table.price'), key: 'price', class: 'text-center align-middle', colWidth: '15%'},
                    // {label: this.$t('table.weight'), key: 'weight', thClass: 'text-center align-middle', tdClass: 'col-text'},
                    // {label: this.$t('table.unit'), key: 'unit', thClass: 'text-center align-middle', tdClass: 'col-text'},
                    {label: this.$t('table.created-at'), key: 'createdAt', class: 'text-center align-middle', colWidth: '10%'},
                    {label: this.$t('table.status'), key: 'statusStr', class: 'text-center align-middle', colWidth: '10%'},
                    {label: this.$t('table.option'), key: 'action', class: 'text-center align-middle', colWidth: '10%'},
                ];
            },
        },
        created() {
            this.$store.commit('updateBreadcrumbsState', [
                {
                    text: this.$t('product-list'),
                    to: { name: 'product.list' }
                },
                ...(
                    this.$route.query.shopId ? [{
                        text: this.$t('shop-code') + ': ' + this.$route.query.shopCode,
                        to: { name: 'shop.list', query:{q: this.$route.query.shopCode}}
                    }] : []
                ),
            ]);
            this.$store.commit('updateFilterFormState', [
                    {
                        label: this.$t('constant.status.status'),
                        type: 'select',
                        name: 'productStatus',
                        options: [
                            {
                                name: 'Chờ duyệt, Hoạt động',
                                value: EProductStatus.WAITING_AND_ACTIVE,
                            },
                            {
                                name: this.$t('constant.status.active'),
                                value: EProductStatus.ACTIVE,
                            },
                            {
                                name: this.$t('constant.status.pending'),
                                value: EProductStatus.WAITING,
                            },
                            {
                                name: this.$t('constant.status.deleted'),
                                value: EProductStatus.DELETED,
                            },
                            {
                                name: this.$t('constant.status.reject'),
                                value: EProductStatus.REJECTED,
                            },
                        ]
                    },
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
            this.$store.commit('updateFilterValueState', {
                    q: this.$route.query.q,
                    productStatus: this.$route.query.id ? EProductStatus.ACTIVE : EProductStatus.WAITING_AND_ACTIVE,
                    shop_id: this.$route.query.shopId,
                    id: this.$route.query.id
                });
            this.$store.commit('updateQueryFilterState', {
                enable: true,
                placeholder: this.$t('name_filter'),
            });
        },
        methods: {
            onListDataFetchSuccess(paging, data) {
                this.table = data;
                this.pagination = {page: paging.page, size: paging.size};
            },
            showModalProductDetail(item) {
                this.item = item;
                this.isShowProductDetailModal = true;
            },
            showVideo(item) {
                return this.StringUtil.getYoutubeVideoId(item.youtubeId);
            },
            async approveOrReject(id, approvalStatus) {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm(approvalStatus == EApprovalStatus.APPROVED ? this.$t('confirm.approve') : this.$t('confirm.deny'), resolve);
                });
                if (!confirm) {
                    return;
                }
                this.Util.loadingScreen.show();
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/approve-or-reject`,
                    data: {
                        id: id,
                        approval_status: approvalStatus
                    },
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }
                    await this.getListData(this.pagination);

                    this.Util.showMsg('success', null, res.msg);
                }).always(() => {
                    this.Util.loadingScreen.hide();
                });
            },
        }
    }
</script>

<style scoped>

</style>
