<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <b-row>
                        <b-col md="48">
                            <a-table
                                :items="orderList.data"
                                :fields="userFields"
                                :loading="loading"
                                :pagination="pagination"
                            >
                                <template #cell(buyer)="data">
                                    <span>{{data.item.buyerName}}  {{data.item.buyerPhone ? '-' : ''}} {{data.item.buyerPhone}}
                                    </span>
                                </template>
                                <template #cell(receiver)="data">
                                    <span>{{data.item.receiverName}}  {{data.item.receiverPhone ? '-' : ''}} {{data.item.receiverPhone}}
                                    </span>
                                </template>
                                <template #cell(code)="data">
                                    <span class="text-info cursor-pointer"
                                          @click="showOrderDetailModal({shopId: data.item.shopId, orderId: data.item.orderId})"
                                    >
                                        {{data.item.code}}
                                    </span>
                                </template>
<!--                                <template #cell(action)="data">-->
<!--                                    <a-button-delete-->
<!--                                        v-if="-->
<!--                                        data.item.status !== EOrderStatus.DELETED"-->
<!--                                        class="text-primary mx-1"-->
<!--                                        @click="deleteOrder(data.item.orderId)"-->
<!--                                        :disabled="deleting[`${data.item.orderId}`]"-->
<!--                                        :deleting="deleting[`${data.item.orderId}`]"-->
<!--                                        :title="$t('common.tooltip.delete')"-->
<!--                                    />-->
<!--                                </template>-->
                            </a-table>
                            <paging @page-change="onPageChangeHandler" :disabled="loading" :total="orderList.total" ref="pagingEl"/>
                        </b-col>
                    </b-row>
                </div>
            </b-col>
        </b-row>
        <order-detail-modal
            :info-from-parent="{
                shopId: this.item ? this.item.shopId : null,
                orderId: this.item ? this.item.orderId: null,
                isShowOrderDetailModal: this.isShowOrderDetailModal,
            }"
            @isShowChanged = "isShowOrderDetailModal = $event"
        >
        </order-detail-modal>
    </div>
</template>

<script>
    import {mapState} from "vuex";
    import ECustomerType from "../../constants/customer-type";
    import shopOrderManage from "../../locales/back/shop/shop-order";
    import OrderDetailModal from "../component/OrderDetailModal";
    import EErrorCode from "../../constants/error-code";
    import EOrderStatus from '../../constants/order-status';
    import EStatus from "../../constants/status";
    import EUserType from "../../constants/user-type";
    import EPaymentStatus from "../../constants/payment-status";
    import EDeliveryStatus from "../../constants/delivery-status"

    export default {
        inject: ['Util', 'StringUtil', 'DateUtil'],
        components: {
            OrderDetailModal,
        },
        i18n: {
            messages: shopOrderManage,
        },
        props: {
            customerType: {
                type: Number,
                default: null
            },
            userType: {
                type: Number,
                default: null
            },
        },
        data() {
            return {
                orderList: [],
                filter: {
                    q: null,
                },
                pagination: {
                    page: 1,
                    size: null,
                },
                //countries: [],
                loading: false,
                item: null,
                processing: false,
                isShowOrderDetailModal: false,
                deleting: {},
                EErrorCode,
                EOrderStatus,
            }
        },
        computed: {
            ...mapState(['filterValueState', 'queryFilterState']),
            userFields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'align-middle text-center', colWidth: '5%'},
                    // {label: this.$t('table.user-id'), key: 'code', class: 'align-middle text-center', colWidth: '5%'},
                    {label: this.$t('table.code'), key: 'code', class: 'align-middle text-center', colWidth: '5%'},
                    {label: this.$t('table.price'), key: 'price', class: 'text-center align-middle', colWidth: '10%', tdClass: 'col-text'},
                    {label: this.$t('table.buyer'), key: 'buyer', class: 'text-center align-middle', colWidth: '10%', tdClass: 'col-text'},
                    {label: this.$t('table.receiver'), key: 'receiver', class: 'text-center align-middle', colWidth: '10%', tdClass: 'col-text'},
                    {label: this.$t('table.payment-method'), key: 'paymentMethod', class: 'text-center align-middle', colWidth: '15%', tdClass: 'col-text'},
                    {label: this.$t('table.created-at'), key: 'createdAt', class: 'text-center align-middle', colWidth: '15%', tdClass: 'col-text'},
                    {label: this.$t('table.status'), key: 'statusStr', class: 'text-center align-middle', colWidth: '10%'},
                    {label: this.$t('table.payment-status'), key: 'paymentStatusStr', class: 'text-center align-middle', colWidth: '10%'},
                    {label: this.$t('table.delivery-status'), key: 'deliveryStatusStr', class: 'text-center align-middle', colWidth: '10%'},
                    // {label: this.$t('table.option'), key: 'action', class: 'text-center align-middle', colWidth: '8%'},
                ]
            },
        },
        watch: {
            filterValueState(val) {
                window.history.pushState(null, null, this.StringUtil.getUrlWithQueries(window.location.pathname, {...val.value, page: this.pagination.page}));
                this.searchOrder({page: 1, size: this.sz});
            },
        },
        created() {
            this.breadCrumb();
            this.resetFilter(this.$route);
            this.filterState();
            this.searchOrder({page: 1, size: this.sz});
        },
        methods: {
            resetFilter(route) {
                this.$store.commit('updateQueryFilterState', {
                    enable: true,
                    placeholder: this.$t('filter_name'),
                });
                this.$store.commit('updateFilterValueState', {
                    q: route.query.q,
                });
            },
            filterState() {
                this.$store.commit('updateFilterFormState', [
                    {
                        label: this.$t('label.status'),
                        type: 'select',
                        name: 'status',
                        options: [
                            {
                                name: this.$t('filter_form.status.waiting'),
                                value: EOrderStatus.WAITING,
                            },
                            {
                                name: this.$t('filter_form.status.confirmed'),
                                value: EOrderStatus.CONFIRMED
                            },
                            {
                                name: this.$t('filter_form.status.cancel_by_shop'),
                                value: EOrderStatus.CANCEL_BY_SHOP
                            },
                            {
                                name: this.$t('filter_form.status.cancel_by_user'),
                                value: EOrderStatus.CANCEL_BY_USER
                            },
                        ]
                    },
                    {
                        label: this.$t('label.payment_status'),
                        type: 'select',
                        name: 'paymentStatus',
                        options: [
                            {
                                name: this.$t('constant.payment_status.paid'),
                                value: EPaymentStatus.PAYMENT_RECEIVED,
                            },
                            {
                                name: this.$t('constant.payment_status.unpaid'),
                                value: EPaymentStatus.WAITING,
                            },
                        ]
                    },
                    {
                        label: this.$t('label.delivery_status'),
                        type: 'select',
                        name: 'deliveryStatus',
                        options: [
                            {
                                name: this.$t('constant.delivery_status.waiting_for_approval'),
                                value: EDeliveryStatus.WAITING_FOR_APPROVAL
                            },
                            {
                                name: this.$t('constant.delivery_status.on_the_way'),
                                value: EDeliveryStatus.ON_THE_WAY,
                            },
                            {
                                name: this.$t('constant.delivery_status.delivery_success'),
                                value: EDeliveryStatus.DELIVERY_SUCCESS
                            },
                            {
                                name: this.$t('constant.delivery_status.customer_refuse'),
                                value: EDeliveryStatus.CUSTOMER_REFUSE,
                            },
                        ]
                    },
                    {
                        label: this.$t('label.create_at'),
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
            },
            breadCrumb() {
                this.$store.commit('updateBreadcrumbsState', [
                    {
                        text: this.$t('order-list'),
                        to: {name: 'shop.order', params:{shopId: this.$route.params.shopId}}
                    },
                ]);
            },
            onPageChangeHandler(paging) {
                this.searchOrder(paging);
            },
            searchOrder(paging) {
                this.loading = true;
                let reqData = Object.assign({
                    pageSize: paging.size || this.pagination.size,
                    page: paging.page ? paging.page : 1,
                    filter: {
                        type: this.userType,
                        ...this.filterValueState.value,
                    }
                });
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/${this.$route.params.shopId}/order`,
                    data: reqData,
                }).done(response => {
                        if (response.error == EErrorCode.ERROR) {
                            this.Util.showMsg2(response);
                            return false;
                        }
                        this.orderList = response.orders;
                        this.pagination = {page: paging.page, size: paging.size};
                        if (!this.pagination.size) {
                            this.pagination.size = this.orderList.per_page
                        }
                        response.orders.data.forEach((item, index) => {
                            if (item.createdAt) {
                                let date = new Date(item.createdAt);
                                try {
                                    this.orderList.data[index].createdAt = this.DateUtil.getDateTimeString2(date, '/', ':',false, false, ' - ');
                                } catch (e) {
                                    console.log(e)
                                }
                            }
                        });
                    })
                    .always(() => {
                        this.loading = false;
                        this.Util.loadingScreen.hide();
                    });
            },
            async deleteOrder(id) {
                if (this.deleting[`${id}`]) {
                    return;
                }

                let confirm = await new Promise((resolve) => {
                    this.Util.confirmDelete(this.$t('object_name'), resolve);
                });
                if (!confirm) {
                    return;
                }

                this.deleting[`${id}`] = true;
                this.processing = true;
                this.Util.post({
                    url: `/api/back/shop/${this.$route.params.shopId}/order/delete`,
                    data: {
                        id: id,
                    },
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }

                    await this.searchOrder(this.pagination);

                    this.Util.showMsg('success', null, res.msg);
                }).always(() => {
                    this.deleting[`${id}`] = false;
                    this.processing = false;
                });
            },
            async approveOrder(id) {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirmApprove(this.$t('object_name'), resolve);
                });
                if (!confirm) {
                    return;
                }
                this.Util.loadingScreen.show();
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/approve`,
                    data: {
                        id: id,
                    },
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }

                    await this.searchOrder(this.pagination);

                    this.Util.showMsg('success', null, res.msg);
                }).always(() => {
                    this.Util.loadingScreen.hide();
                });
            },
            showOrderDetailModal(item) {
                this.item = item;
                this.isShowOrderDetailModal = true;
            }
        }
    }
</script>

<style scoped>

</style>
