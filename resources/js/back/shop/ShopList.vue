<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <b-row class="mb-4">
                        <b-col cols="48">
                            <b-button v-if="table.total == 0" :to="{name: `shop.create`, params: {action: 'create'}}" variant="outline-primary" class="float-right ml-3">
                                <i class="fas fa-plus"/>
                                {{$t('common.button.add2', {
                                    'objectName': $t('shop')
                                })}}
                            </b-button>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col md="48">
                            <a-table
                                :items="table.data"
                                :fields="shopFields"
                                :loading="loading"
                                :pagination="pagination"
                            >
                                <template #cell(name)="data">
                                    <span class="text-info cursor-pointer" @click="showModalDetail(data.item)">
                                        {{data.item.name}}
                                    </span>
                                </template>
                                <template #cell(avatar)="data">
                                    <b-avatar
                                        v-if="data.item.avatar_type == 1"
                                        variant="primary"
                                        :src="data.item.avatar"
                                        size="30"
                                        class="bg-white"
                                    ></b-avatar>
                                    <span
                                        v-else
                                        class="b-avatar badge-secondary rounded-circle"
                                        style="width: 30px; height: 30px"
                                    >
                                        <span class="shop-avatar">
                                            <video autoplay muted loop :src="data.item.avatar" style="width: 110px"></video>
                                        </span>
                                    </span>
                                </template>
                                <template #cell(action)="data">
                                    <router-link
                                        v-if="data.item.status !== EStatus.DELETED"
                                        :to="{name: 'shop.info', params:{shopId: data.item.id, action: 'edit'}}"
                                        v-b-tooltip.hover :title="$t('common.tooltip.edit')"
                                        class="no-decoration">
                                        <i class="text-primary fas fa-edit"/>
                                    </router-link>
                                    <a-button-delete
                                        v-if="data.item.status !== EStatus.DELETED"
                                        class="text-primary mx-1"
                                        @click="deleteItem(data.item)"
                                        :disabled="deleting[`${data.item.id}`]"
                                        :deleting="deleting[`${data.item.id}`]"
                                        :title="$t('common.tooltip.delete')"
                                    />
                                    <span v-if="data.item.status == EStatus.WAITING"
                                          class="cursor-pointer"
                                          @click="approveShop(data.item.id)"
                                          v-b-tooltip.hover :title="$t('common.tooltip.approve')"
                                    >
                                        <i class="fas fa-check text-primary"></i>
                                    </span>
                                </template>
                            </a-table>
                            <paging @page-change="onPageChangeHandler" :disabled="loading" :total="table.total" ref="pagingEl"/>
                        </b-col>
                    </b-row>
                </div>
            </b-col>
        </b-row>
        <b-modal
            size="md"
            v-model="showModal"
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
                                        <td class="text-left p-1" style="width: 30%">
                                            {{$t('table.avatar')}}
                                        </td>
                                        <td class="text-left p-1">
                                            <b-avatar
                                                variant="primary"
                                                :src="item.avatar"
                                                size="30"
                                                class="bg-white"
                                            ></b-avatar>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left p-1" style="width: 30%">
                                            {{$t('table.code')}}
                                        </td>
                                        <td class="text-left p-1">
                                            {{ item.code }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left p-1" style="width: 30%">
                                            {{$t('table.name')}}
                                        </td>
                                        <td class="text-left p-1">
                                            {{ item.name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left p-1" style="width: 30%">
                                            {{$t('table.level')}}
                                        </td>
                                        <td class="text-left p-1">
                                            {{ ELevel.valueToName(item.level) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left p-1" style="width: 30%">
                                            {{$t('table.phone')}}
                                        </td>
                                        <td class="text-left p-1">
                                            {{ item.phone }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left p-1" style="width: 30%">
                                            {{$t('table.email')}}
                                        </td>
                                        <td class="text-left p-1">
                                            {{item.email}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left p-1" style="width: 30%">
                                            {{$t('table.address')}}
                                        </td>
                                        <td class="text-left p-1">
                                            {{item.address}}
                                        </td>
                                    </tr>
                                   <!-- <tr>-->
<!--                                        <td class="text-left p-1">-->
<!--                                            {{$t('table.description')}}-->
<!--                                        </td>-->
<!--                                        <td class="text-left p-1">-->
<!--                                            {{item.description}}-->
<!--                                        </td>-->
<!--                                    </tr> -->
                                    <tr>
                                        <td class="text-left p-1" style="width: 30%">
                                            {{$t('table.created_at')}}
                                        </td>
                                        <td class="text-left p-1">
                                            {{item.createdAt}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left p-1" style="width: 30%">
                                            {{$t('table.payment_status')}}
                                        </td>
                                        <td class="text-left p-1">
                                            {{item.strPaymentStatus}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left p-1" style="width: 30%">
                                            {{$t('table.status')}}
                                        </td>
                                        <td class="text-left p-1">
                                            {{item.strStatus}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left p-1" style="width: 30%">
                                            {{$t('table.facebook_page')}}
                                        </td>
                                        <td class="text-left p-1">
                                            <a style="word-break: break-all; max-width: 350px !important"
                                               :href="item.fb_page" target="_blank">
                                                {{item.fb_page}}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left p-1" style="width: 30%">
                                            {{$t('table.zalo_page')}}
                                        </td>
                                        <td class="text-left p-1"
                                            style="word-break: break-all;max-width: 350px !important">
                                            {{item.zalo_page}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left p-1" style="width: 30%">
                                            CMND, CCCD hoặc MST
                                        </td>
                                        <td class="text-left p-1"
                                            style="word-break: break-all;max-width: 350px !important">
                                            {{item.identity_code}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left p-1" style="width: 30%">
                                            Ngày cấp
                                        </td>
                                        <td class="text-left p-1"
                                            style="word-break: break-all;max-width: 350px !important">
                                            {{item.identityDate}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left p-1" style="width: 30%">
                                            Nơi cấp
                                        </td>
                                        <td class="text-left p-1"
                                            style="word-break: break-all;max-width: 350px !important">
                                            {{item.identity_place}}
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
    </div>
</template>

<script>
    import {mapState} from "vuex";
    import ECustomerType from "../../constants/customer-type";
    import shopManage from "../../locales/back/shop/shop-list";
    import EErrorCode from "../../constants/error-code";
    import EUserType from "../../constants/user-type";
    import listMixin from "../mixins/list-mixin";
    import EStatus from "../../constants/status";
    import EPaymentStatus from "../../constants/payment-status";
    import ELevel from "../../constants/level";

    export default {
        inject: ['Util', 'StringUtil', 'DateUtil'],
        i18n: {
            messages: shopManage
        },
        mixins: [listMixin],
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
                showModal: false,
                loading: false,
                processing: false,
                EErrorCode,
                EUserType,
                EStatus,
                ELevel,
            }
        },
        computed: {
            ...mapState(['filterValueState', 'queryFilterState']),
            shopFields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'align-middle text-center', colWidth: '3%'},
                    {label: this.$t('table.code'), key: 'code', class: 'align-middle text-center', colWidth: '5%'},
                    {label: this.$t('table.avatar'), key: 'avatar', class: 'align-middle text-center', colWidth: '10%'},
                    {label: this.$t('table.name'), key: 'name', thClass: 'text-center align-middle', colWidth: '12%', tdClass: 'col-text'},
                    {label: this.$t('table.phone'), key: 'phone', class: 'text-center align-middle', colWidth: '10%'},
                    {label: this.$t('table.email'), key: 'email', class: 'text-center align-middle', colWidth: '10%'},
                    // {label: this.$t('table.description'), key: 'description', class: 'text-center align-middle', colWidth: '10%'},
                    {label: this.$t('table.created_at'), key: 'createdAt', class: 'text-center align-middle', colWidth: '5%'},
                    {label: this.$t('table.status'), key: 'strStatus', class: 'text-center align-middle', colWidth: '10%'},
                    {label: this.$t('table.option'), key: 'action', class: 'text-center align-middle', colWidth: '8%'},
                ];
            },
        },
        created() {
            this.$store.commit('updateFilterFormState', [
                    {
                        label: this.$t('label.status'),
                        type: 'select',
                        name: 'status',
                        options: [
                            // {
                            //     name: this.$t('Đã duyệt, Đã xóa, Chờ duyệt'),
                            //     value: null,
                            // },
                            {
                                name: this.$t('Chờ duyệt, Hoạt động'),
                                value: EStatus.EXCEPT_DELETED,
                            },

                            {
                                name: this.$t('constant.status.active'),
                                value: EStatus.ACTIVE,
                            },
                            {
                                name: this.$t('constant.status.deleted'),
                                value: EStatus.DELETED,
                            },
                            {
                                name: this.$t('constant.status.pending'),
                                value: EStatus.WAITING,
                            },
                        ]
                    },
                    // {
                    //     label: this.$t('label.payment_status'),
                    //     type: 'select',
                    //     name: 'payment_status',
                    //     options: [
                    //         {
                    //             name: this.$t('constant.payment_status.paid'),
                    //             value: EPaymentStatus.PAYMENT_RECEIVED,
                    //         },
                    //         {
                    //             name: this.$t('constant.payment_status.unpaid'),
                    //             value: EPaymentStatus.WAITING,
                    //         },
                    //     ]
                    // },
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
            this.$store.commit('updateQueryFilterState', {
                    enable: true,
                    placeholder: this.$t('filter.shop'),
            });
            this.$store.commit('updateFilterValueState', {
                    q: this.$route.query.q,
                    status: EStatus.EXCEPT_DELETED,
                });
            this.$store.commit('updateBreadcrumbsState', [
                    {
                        text: this.$t('shop'),
                        to: { name: 'shop.list' }
                    },
            ]);
        },
        methods: {
            showModalDetail(item) {
                this.item = item;
                this.showModal = true;
            },
            onListDataFetchSuccess(paging, data) {
                this.table = data;
                this.pagination = {page: paging.page, size: paging.size};
            },
            async approveShop(id) {
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
