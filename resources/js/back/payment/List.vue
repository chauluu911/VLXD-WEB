<template>
    <div>
        <b-row>
            <b-col md="48" class="mb-3">
                <b-button-group>
                    <b-button
                        class="btn__option border-0"
                        :class="{'btn__option--active': typeList === 'order' }"
                        @click="changeOption('order', null)"
                    >
                        Đơn hàng
                    </b-button>
                    <b-button
                        class="btn__option border-0"
                        :class="{'btn__option--active': typeList === 'subscription' && paymentType == ESubcriptionPriceType.UPGRADE_SHOP}"
                        @click="changeOption('subscription', ESubcriptionPriceType.UPGRADE_SHOP)"
                    >
                        Nâng cấp cửa hàng
                    </b-button>
                    <b-button
                        class="btn__option border-0"
                        :class="{'btn__option--active': typeList === 'subscription' && paymentType == ESubcriptionPriceType.PUSH_PRODUCT}"
                        @click="changeOption('subscription', ESubcriptionPriceType.PUSH_PRODUCT)"
                    >
                        Gói đẩy tin
                    </b-button>
                    <b-button
                        class="btn__option border-0"
                        :class="{'btn__option--active': typeList === 'subscription' && paymentType == ESubcriptionPriceType.BUY_COINS}"
                        @click="changeOption('subscription', ESubcriptionPriceType.BUY_COINS)"
                    >
                        Mua xu
                    </b-button>
                </b-button-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <b-row class="mb-3">
                        <b-col class="text-right" style="min-width: 300px;">
                            <button
                                class="btn btn-primary"
                                v-if="typeList === 'subscription' && selectedItems.length"
                                :disabled="processing"
                                @click="approvePayments()" ref="btnApprove"
                            >
                                {{ $t('approve') }}
                            </button>
                        </b-col>
                    </b-row>
                    <a-table :items="table.data" :fields="fields" :loading="loading" :pagination="pagination">
                        <template #cell(totalStr)="data">
                            <span>{{ data.item.currency_sign }}&#160;{{ data.item.totalStr }}</span>
                        </template>
                        <template #cell(updatedAt)="data">
                            <span v-html="data.item.updatedAt"></span>
                        </template>
                        <template #cell(userName)="data">
                            <span class="text-info cursor-pointer" @click="showModalUserDetail(data.item.user)">
                                {{data.item.userName}}
                            </span>
                        </template>
                        <template #cell(shopName)="data">
                            <span class="text-info cursor-pointer" @click="showModalShopDetail(data.item.shop)">
                                {{data.item.shopName}}
                            </span>
                        </template>
                        <template #cell(shopOfOrderName)="data">
                            <span class="text-info cursor-pointer" @click="showModalShopDetail(data.item.shopOfOrder)">
                                {{data.item.shopOfOrder.name}}
                            </span>
                        </template>
                        <template #cell(productName)="data">
                            <span class="text-info cursor-pointer" @click="showModalProductDetail({productId: data.item.product.id})">
                                {{data.item.product ? data.item.product.name : null}}
                            </span>
                        </template>
                        <template #cell(action)="data">
                            <div class="d-flex justify-content-center">
                                <div class="float-left">
                                    <input v-if="data.item.status == EPaymentStatus.WAITING" type="checkbox" :class="{'d-none': processing}" :value="{id: data.item.id}" v-model="selectedItems" style="margin-top: 5px">
                                </div>
                            </div>
                        </template>
                        <template #cell(code)="data">
                                    <span class="text-info cursor-pointer"
                                          @click="showOrderDetailModal({shopId: data.item.shopOfOrder.id, orderId: data.item.id})"
                                    >
                                        {{data.item.code}}
                                    </span>
                        </template>
                    </a-table>
                    <paging @page-change="onPageChangeHandler" :total="table.total" :disabled="loading" ref="pagingEl"/>
                </div>
            </b-col>
        </b-row>
        <b-modal
            size="md"
            v-model="isShowShopDetailModal"
            no-close-on-esc no-close-on-backdrop
            busy:hide-header-close="processing"
            :title="$t('info')"
            header-class=""
            @hidden=""
        >
            <template v-slot:default>
                <b-form>
                    <b-row class="mb-2">
                        <b-col cols="48">
                            <table class="w-100 table-striped">
                                <tbody>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Hình ảnh
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            <b-avatar
                                                variant="primary"
                                                :src="item.avatar"
                                                size="30"
                                                class="bg-white"
                                            ></b-avatar>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            ID
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ item.code }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Tên cửa hàng
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ item.name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Cấp độ cửa hàng
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ ELevel.valueToName(item.level) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Số điện thoại
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ item.phone }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Email
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{item.email}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Địa chỉ
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{item.address}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Ngày tạo
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{item.createdAt}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Trạng thái thanh toán
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{item.strPaymentStatus}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Trạng thái
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{item.strStatus}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Fanpage(Facebook)
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            <a style="word-break: break-all; max-width: 350px !important"
                                               :href="item.fb_page" target="_blank">
                                                {{item.fb_page}}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Fanpage(Zalo)
                                        </td>
                                        <td class="text-left w-75 p-1"
                                            style="word-break: break-all;max-width: 350px !important">
                                            {{item.zalo_page}}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </b-col>
                        <b-col>
                            <b-link  class="float-right" :to="{name: 'product.list', query:{shopId: item.id, shopCode: item.code}}">Danh sách sản phẩm</b-link>
                        </b-col>
                    </b-row>
                </b-form>
            </template>
            <template v-slot:modal-footer="{ hide }">
                <b-button variant="outline-primary" :disabled="processing" @click="hide('forget')">{{ $t('close') }}</b-button>
            </template>
        </b-modal>
        <b-modal
            size="md"
            v-model="isShowUserDetailModal"
            no-close-on-esc no-close-on-backdrop
            busy:hide-header-close="processing"
            :title="$t('Khách hàng')"
            header-class=""
            @hidden=""
        >
            <template v-slot:default>
                <b-form>
                    <b-row class="mb-2">
                        <b-col cols="48">
                            <table class="w-100 table-striped">
                                <tbody>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Hình ảnh
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            <b-avatar
                                                variant="primary"
                                                :src="item.avatar"
                                                size="30"
                                                class="bg-white"
                                            ></b-avatar>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Mã giới thiệu
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ item.affiliateCode }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            ID
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ item.code }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Tên
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ item.name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Giới tính
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ item.genderStr }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Số điện thoại
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ item.phone }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Ngày sinh
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ item.date_of_birth }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Email
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{item.email}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Địa chỉ
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{item.address}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Điểm
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{item.score}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Xu
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{item.coins}}
                                        </td>
                                    </tr>
                                    <tr v-if="item.shopId">
                                        <td class="text-left w-25">
                                            Cửa hàng
                                        </td>
                                        <td class="text-left w-75">
                                            <span>
                                                {{item.shopName}}
                                            </span>
                                            <router-link
                                                :to="{name: 'shop.info', params:{shopId: item.shopId, action: 'edit'}}"
                                                class="no-decoration text-info float-right"
                                            >
                                                Go to shop
                                            </router-link>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            Ngày tạo
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{item.createdAt}}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </b-col>
                    </b-row>
                </b-form>
            </template>
            <template v-slot:modal-footer="{ hide }">
                <b-button variant="outline-primary" :disabled="processing" @click="hide('forget')">{{ $t('close') }}</b-button>
            </template>
        </b-modal>
        <order-detail-modal
            :info-from-parent="{
                shopId: this.item ? this.item.shopId : null,
                orderId: this.item ? this.item.orderId: null,
                isShowOrderDetailModal: this.isShowOrderDetailModal,
            }"
            @isShowChanged = "isShowOrderDetailModal = $event"
        >

        </order-detail-modal>
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
    import paymentListMessages from '../../locales/back/payment-management-list';
    import YoutubeEmbed from '../component/YoutubeEmbed.vue';
    import FDownloadIcon from 'vue-feather-icons/icons/DownloadIcon'
    import OrderDetailModal from "../component/OrderDetailModal";
    import ProductDetailModal from "../component/ProductDetailModal";
    import listMixin from '../mixins/list-mixin';
    import EPaymentStatus from '../../constants/payment-status';
    import EStatus from '../../constants/status';
    import ESubcriptionPriceType from '../../constants/subcription-price-type';
    import EPaymentMethod from "../../constants/payment-method";
    import ELevel from "../../constants/level";
    import EOrderStatus from "../../constants/order-status";
    import EDeliveryStatus from "../../constants/delivery-status";

    export default {
        name: 'PostList',
        i18n: {
            messages: paymentListMessages,
        },
        inject: ['StringUtil', 'DateUtil'],
        mixins: [listMixin],
        components: {
            FDownloadIcon,
            YoutubeEmbed,
            OrderDetailModal,
            ProductDetailModal,
        },
        data() {
            return {
                EPaymentStatus,
                EStatus,
                ELevel,
                ESubcriptionPriceType,
                processing: false,
                selectedItems: [],
                paymentType: null,
                typeList: 'order',
                item: null,
                disableVideo: false,
                isShowUserDetailModal: false,
                isShowShopDetailModal: false,
                isShowProductDetailModal: false,
                isShowOrderDetailModal: false,
            }
        },
        watch: {
            typeList() {
                this.updateFilterFormAndQueryFilter();
            },
            paymentType() {
                this.updateFilterFormAndQueryFilter();
            }
        },

        computed: {
            fields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'text-center align-middle', colWidth: '5%'},
                    ...(
                        this.typeList === 'order' ? [
                            {label: this.$t('table.column.order_code'), key: 'code', thClass: 'image-thead text-center align-middle', tdClass: 'col-text', colWidth: '15%'},
                        ] : []
                    ),
                    ...(
                        this.paymentType === ESubcriptionPriceType.UPGRADE_SHOP ||
                        this.paymentType === ESubcriptionPriceType.PUSH_PRODUCT ? [
                            {label: this.$t('table.column.shop_name'), key: 'shopName', thClass: 'image-thead text-center align-middle', tdClass: 'col-text', colWidth: '15%'},
                        ] : [
                            {label: this.$t('table.column.name'), key: 'userName', thClass: 'image-thead text-center align-middle', tdClass: 'col-text', colWidth: '15%'},
                        ]
                    ),
                    ...(
                        this.typeList === 'order' ? [
                            {label: this.$t('table.column.shop_name'), key: 'shopOfOrderName', thClass: 'image-thead text-center align-middle', tdClass: 'col-text', colWidth: '15%'},
                        ] : []
                    ),
                    ...(
                        this.paymentType === ESubcriptionPriceType.UPGRADE_SHOP ? [
                            {label: this.$t('table.column.upgrade_pakage'), key: 'name', class: 'text-center align-middle', colWidth: '10%'},
                        ] : []
                    ),
                    ...(
                        this.paymentType === ESubcriptionPriceType.PUSH_PRODUCT ? [
                            {label: this.$t('table.column.product'), key: 'productName', class: 'text-center align-middle', colWidth: '10%'},
                            {label: this.$t('table.column.push_post'), key: 'name', class: 'text-center align-middle', colWidth: '10%'},
                        ] : []
                    ),
                    ...(
                        this.typeList === 'subscription' ? [
                            {label: this.$t('table.column.payment_method'), key: 'paymentMethod', thClass: 'text-center align-middle', tdClass: 'col-align-center', colWidth: '15%'},
                        ] : []
                    ),
                    {label: this.$t('table.column.amount'), key: 'price', thClass: 'text-center align-middle', tdClass: 'col-number', colWidth: '10%'},
                    {label: this.$t('table.column.updated_at'), key: 'createdAt', class: 'text-center align-middle', colWidth: '10%'},
                    {label: this.$t('table.column.status'), key: 'statusStr', class: 'text-center align-middle', colWidth: '10%'},
                    ...(
                        this.typeList === 'subscription' ? [
                            {label: this.$t('table.column.option'), key: 'action', class: 'text-center align-middle', colWidth: '5%'},
                        ] : []
                    ),
                    ...(
                        this.typeList === 'order' ? [
                            {label: this.$t('table.column.payment_status'), key: 'paymentStatusStr', class: 'text-center align-middle', colWidth: '10%'},
                            {label: this.$t('table.column.delivery_status'), key: 'deliveryStatusStr', class: 'text-center align-middle', colWidth: '10%'},
                        ] : []
                    ),

                ];
            },
            defaultFilter() {
                return {
                    typeList: this.typeList === 'subscription' ? 'subscription' : 'order',
                }
            },
        },
        created() {
            this.$store.commit('updateBreadcrumbsState', [
                {
                    text: this.$t('title'),
                    to: ''
                },
            ]);
            this.$store.commit('updateQueryFilterState', {
                enable: true,
                placeholder: this.$t('filter.order'),
            });
            this.$store.commit('updateFilterValueState', {
                q: this.$route.query.q,
                paymentType: this.paymentType,
                paymentStatus: null,
            });
            this.updateFilterFormAndQueryFilter();

            // this.getListData({page: this.$route.query.page || 1, size: this.sz});
        },
        methods: {
            updateFilterFormAndQueryFilter() {
                if(this.typeList === 'subscription'){
                    this.$store.commit('updateFilterFormState', [
                        {
                            label: this.$t('constant.payment_method.payment_method'),
                            type: 'select',
                            name: 'paymentMethod',
                            options: [
                                ...(
                                    this.paymentType === ESubcriptionPriceType.UPGRADE_SHOP ||
                                    this.paymentType === ESubcriptionPriceType.PUSH_PRODUCT ? [
                                        {
                                            name: this.$t('constant.payment_method.coin'),
                                            value: EPaymentMethod.COIN
                                        },
                                    ] : []
                                ),
                                {
                                    name: this.$t('constant.payment_method.bank_transfer'),
                                    value: EPaymentMethod.BANK_TRANSFER
                                },
                                {
                                    name: this.$t('constant.payment_method.payment_gateway'),
                                    value: EPaymentMethod.PAYMENT_GATEWAY,
                                },
                            ]
                        },
                        {
                            label: this.$t('constant.payment_method.payment_method'),
                            type: 'date',
                            name: 'createdAtGt',
                            placeholder: this.$t('placeholder.filter.created_at_from'),
                            dropleft: true,
                        },
                        {
                            label: this.$t('constant.payment_method.payment_method'),
                            type: 'date',
                            name: 'createdAtLt',
                            placeholder: this.$t('placeholder.filter.created_at_to'),
                            dropleft: true,
                        },
                    ]);
                    switch (this.paymentType) {
                        case ESubcriptionPriceType.PUSH_PRODUCT:
                            this.$store.commit('updateQueryFilterState', {
                                enable: true,
                                placeholder: this.$t('filter.push_post'),
                            });
                            break;
                        case ESubcriptionPriceType.BUY_COINS:
                            this.$store.commit('updateQueryFilterState', {
                                enable: true,
                                placeholder: this.$t('filter.buy_coins'),
                            });
                            break;
                        case ESubcriptionPriceType.UPGRADE_SHOP:
                            this.$store.commit('updateQueryFilterState', {
                                enable: true,
                                placeholder: this.$t('filter.upgrade_pakage'),
                            });
                            break;
                    }
                } else {
                    this.$store.commit('updateFilterFormState', [
                        {
                            label: this.$t('label.status'),
                            type: 'select',
                            name: 'status',
                            options: [
                                {
                                    name: this.$t('filter.status.waiting'),
                                    value: EOrderStatus.WAITING,
                                },
                                {
                                    name: this.$t('filter.status.confirmed'),
                                    value: EOrderStatus.CONFIRMED
                                },
                                {
                                    name: this.$t('filter.status.cancel_by_shop'),
                                    value: EOrderStatus.CANCEL_BY_SHOP
                                },
                                {
                                    name: this.$t('filter.status.cancel_by_user'),
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
                            name: 'createdAtGt',
                            placeholder: this.$t('placeholder.filter.created_at_from'),
                            dropleft: true,
                        },
                        {
                            type: 'date',
                            name: 'createdAtLt',
                            placeholder: this.$t('placeholder.filter.created_at_to'),
                            dropleft: true,
                        },
                    ]);
                    this.$store.commit('updateQueryFilterState', {
                        enable: true,
                        placeholder: this.$t('filter.order'),
                    });
                }
            },
            showModalUserDetail(item) {
                this.item = item;
                let date = new Date(item.createdAt);
                this.item.createdAt = this.DateUtil.getDateString(date, '/', false);
                this.isShowUserDetailModal = true;
            },
            showModalShopDetail(item) {
                this.item = item;
                let date = new Date(item.createdAt);
                this.item.createdAt = this.DateUtil.getDateString(date, '/', false);
                this.isShowShopDetailModal = true;
            },
            showModalProductDetail(item) {
                this.item = item;
                this.isShowProductDetailModal = true;
            },
            getYoutubeId(item) {
                return this.StringUtil.getYoutubeVideoId(item.youtubeId);
            },
            changeOption(typeList, paymentType) {
                this.filterValueState.value.paymentType = paymentType;
                this.paymentType = paymentType;
                this.typeList = typeList;
                this.getListData(this.pagination);
            },
            currentPage(paging) {
                this.$refs.pagingEl.setPage(paging);
            },
            onListDataFetchSuccess(paging, data) {
                data.data.forEach((item, index) => {
                    let date = new Date(item.createdAt);
                    data.data[index].createdAt = this.DateUtil.getDateTimeString(date, '/', ':', false, false, ' - ');
                })
                this.table = data;
                this.pagination = {page: paging.page, size: paging.size};
            },
            showOrderDetailModal(item) {
                this.item = item;
                this.isShowOrderDetailModal = true;
            },

            async approvePayments() {
                if (this.selectedItems.length == 0) {
                    return;
                }
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm(this.$t('confirm.approve'), resolve);
                });
                if (!confirm) {
                    return;
                }
                this.processing = true;
                this.Util.loadingScreen.show();
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/approve`,
                    data: {
                        items: this.selectedItems
                    },
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }
                    this.selectedItems = [];
                    await this.getListData(this.pagination);

                    this.Util.showMsg('success', null, res.msg);
                }).always(() => {
                    this.processing = false;
                    this.Util.loadingScreen.hide();
                });
            },
        }
    }
</script>
