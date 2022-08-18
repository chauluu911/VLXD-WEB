<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <b-row class="mb-4" v-if="userType == EUserType.INTERNAL_USER">
                        <b-col cols="48">
                            <b-button :to="{name: `employee.create`, params: {action: 'create'}}" variant="outline-primary" class="float-right ml-3">
                                <i class="fas fa-plus"/>
                                {{$t('common.button.add2', {
                                    'objectName': $t('employee')
                                })}}
                            </b-button>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col md="48">
                            <a-table
                                :items="table.data"
                                :fields="userFields"
                                :loading="loading"
                                :pagination="pagination"
                            >
                                <template #cell(avatar)="data">
                                    <b-avatar
                                        variant="primary"
                                        :src="data.item.avatar"
                                        size="30"
                                        class="bg-white"
                                    ></b-avatar>
                                </template>
                                <template #cell(name)="data">
                                    <span class="text-info cursor-pointer" @click="showModalDetail(data.item)">
                                        {{data.item.name}}
                                    </span>
                                </template>
                                <template #cell(phone-email)="data">
                                    <span class="" >
                                        {{data.item.phone}} / {{data.item.email}}
                                    </span>
                                </template>
                                <template #cell(score)="data">
                                    <span class="text-info cursor-pointer" @click="showModalEditBalance(data.item.scoreType, data.item.score, data.item.id)">
                                        {{data.item.score}}
                                    </span>
                                </template>
                                <template #cell(coins)="data">
                                    <span class="text-info cursor-pointer" @click="showModalEditBalance(data.item.coinsType, data.item.coins, data.item.id)">
                                        {{data.item.coins}}
                                    </span>
                                </template>
                                <template #cell(action)="data">
                                    <router-link
                                        v-if="data.item.status !== EStatus.DELETED"
                                        :to="{name: routerName, params:{userId: data.item.id, action: 'edit'}}"
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
                                    <b-link
                                        v-if="data.item.status !== EStatus.DELETED"
                                        v-b-tooltip.hover :title="$t('common.tooltip.reset_password')"
                                        class="text-primary mx-1">
                                        <img src="/images/icon/reset-password.svg" width="16px" @click="resetPassword(data.item.id)" alt="Reset icon">
                                    </b-link>
                                    <span v-if="data.item.status == EStatus.WAITING && userType == EUserType.NORMAL_USER"
                                          class="cursor-pointer"
                                          @click="approveUser(data.item.id)"
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
                                        <td class="text-left w-25 p-1">
                                            {{$t('table.avatar')}}
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
                                            {{$t('table.affiliate-code')}}
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ item.affiliateCode }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            {{$t('table.code')}}
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ item.code }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            {{$t('table.name')}}
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ item.name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            {{$t('table.gender')}}
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ item.genderStr }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            {{$t('table.phone')}}
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ item.phone }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            {{$t('table.dob')}}
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ item.date_of_birth }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            {{$t('table.email')}}
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{item.email}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            {{$t('table.address')}}
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{item.address}}
                                        </td>
                                    </tr>
                                    <template v-if="userType != EUserType.INTERNAL_USER">
                                        <tr>
                                            <td class="text-left w-25 p-1">
                                                {{$t('table.score')}}
                                            </td>
                                            <td class="text-left w-75 p-1">
                                                {{item.score}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-left w-25 p-1">
                                                {{$t('table.coins')}}
                                            </td>
                                            <td class="text-left w-75 p-1">
                                                {{item.coins}}
                                            </td>
                                        </tr>
                                        <!-- <tr>
                                            <td class="text-left w-25 p-1">
                                                Người giới thiệu
                                            </td>
                                            <td class="text-left w-75 p-1">
                                                {{item.parent_user_name}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-left w-25 p-1">
                                                Số điện thoại người giới thiệu
                                            </td>
                                            <td class="text-left w-75 p-1">
                                                {{item.parent_user_phone}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-left w-25">
                                                Last activities
                                            </td>
                                            <td class="text-left w-75">
                                                {{ item.activities_time }}
                                            </td>
                                        </tr> -->
                                        <tr v-if="item.shopId">
                                            <td class="text-left w-25">
                                                {{$t('table.shop')}}
                                            </td>
                                            <td class="text-left w-75">
                                                <span>
                                                    {{ item.shopName }}
                                                </span>
                                                <router-link
                                                    :to="{name: 'shop.info', params:{shopId: item.shopId, action: 'edit'}}"
                                                    class="no-decoration text-info float-right"
                                                >
                                                    Go to shop
                                                </router-link>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td class="text-left w-25 p-1">
                                                {{$t('table.permission')}}
                                            </td>
                                            <td class="text-left w-75 p-1">
                                               <span v-for="(role, index) in item.role">
                                                   {{role}} <span v-if="index != item.role.length - 1">,</span>
                                               </span>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            {{$t('table.created_at')}}
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
        <b-modal
            size="sm"
            v-model="showModalBalance"
            no-close-on-esc no-close-on-backdrop
            busy:hide-header-close="processing"
            :title="item.type == EWalletType.POINT ? $t('edit-score') : $t('edit-coins')"
            header-class=""
            @hidden=""
        >
            <template v-slot:default>
                <b-form>
                    <b-row class="my-2">
                        <b-col md="48">
                            <span v-if="item.value < 0" style="color: #dc3545; font-weight: 700; font-size: 14px">{{error[0]}}</span>
                            <b-input-group>
                                <template v-slot:prepend>
                                    <b-input-group-prepend>
                                        <b-button
                                            v-if="item.value > 0"
                                            v-on:click="isHidden = false"
                                            variant="info"
                                            @click="changeBalanceValue(-1)"
                                        >
                                            -
                                        </b-button>
                                    </b-input-group-prepend>
                                </template>
                                <money
                                    v-model="item.value"
                                    v-bind="money"
                                    class="form-control"
                                >
                                </money>
                                <template v-slot:append>
                                    <b-input-group-prepend>
                                        <b-button
                                            variant="info"
                                            @click="changeBalanceValue(1)">
                                            +
                                        </b-button>
                                    </b-input-group-prepend>
                                </template>
                          </b-input-group>
                        </b-col>
                    </b-row>
                </b-form>
            </template>
            <template v-slot:modal-footer="{ hide }">
                <b-button variant="outline-danger" :disabled="processing" @click="hide('forget')">{{ $t('close') }}</b-button>
                <b-button variant="primary" :disabled="processing" @click="editBalance">{{ $t('ok') }}</b-button>
            </template>
        </b-modal>
    </div>
</template>
<script>
    import {mapState} from "vuex";
    import ECustomerType from "../../constants/customer-type";
    import ELevel from "../../constants/level";
    import userListManage from "../../locales/back/user/user-list";
    import EErrorCode from "../../constants/error-code";
    import EUserType from "../../constants/user-type";
    import EWalletType from "../../constants/wallet-type";
    import listMixin from "../mixins/list-mixin";
    import EStatus from "../../constants/status";

    export default {
        inject: ['Util', 'StringUtil', 'DateUtil'],
        i18n: {
            messages: userListManage
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
                table: [],
                loading: false,
                processing: false,
                resettingPassUser: {},
                EErrorCode,
                ECustomerType,
                EUserType,
                EStatus,
                ELevel,
                EWalletType,

                disableGift: false,
                showModal: false,
                showModalBalance: false,
                isHidden: true,
                error: [],
                item: {},
                money: {
                    thousands: ',',
                    precision: 0,
                },
            }
        },
        computed: {
            ...mapState(['filterValueState', 'queryFilterState']),
            routerName() {
                if (this.userType == EUserType.NORMAL_USER) {
                    return 'customer.info';
                }else {
                    return 'employee.info';
                }
            },
            userFields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'align-middle text-center', colWidth: '5%'},
                    {label: this.$t('table.code'), key: 'code', class: 'align-middle text-center', colWidth: '5%'},
                    {label: this.$t('table.avatar'), key: 'avatar', class: 'align-middle text-center', colWidth: '10%'},
                    {label: this.$t('table.name'), key: 'name', thClass: 'text-center align-middle', colWidth: '20%', tdClass: 'col-text'},
                    {label: this.$t('table.phone-email'), key: 'phone-email', class: 'text-center align-middle', colWidth: '10%'}, ...(
                        this.userType != EUserType.INTERNAL_USER ? [
                            {label: this.$t('table.score'), key: 'score', class: 'text-center align-middle', colWidth: '10%'},
                            {label: this.$t('table.coins'), key: 'coins', class: 'text-center align-middle', colWidth: '10%'},
                        ] : []
                    ),
                    {label: this.$t('table.created_at'), key: 'createdAt', class: 'text-center align-middle', colWidth: '7%'},
                    {label: this.$t('table.status'), key: 'strStatus', class: 'text-center align-middle', colWidth: '10%'},
                    {label: this.$t('table.option'), key: 'action', class: 'text-center align-middle', colWidth: '15%'},
                ];
            },
        },
        watch: {
            userType() {
                this.$store.commit('updateQueryFilterState', {
                        enable: true,
                        placeholder: this.$t('filter.user'),
                    });
                this.$store.commit('updateFilterValueState', {
                    q: this.$route.query.q,
                    status: this.userType !== EUserType.INTERNAL_USER ? EStatus.EXCEPT_DELETED : EStatus.ACTIVE,
                });
                console.log('----->', this.$route.query.status);
                this.$store.commit('updateBreadcrumbsState', [
                    ...(
                        this.userType == EUserType.INTERNAL_USER ? [{
                            text: this.$t('employee-list'),
                            to: { name: 'employee.list'}
                        }] : []
                    ),
                    ...(
                        this.userType == EUserType.NORMAL_USER ? [{
                            text: this.$t('customer-list'),
                            to: { name: 'customer.list'}
                        }] : []
                    ),
                ]);
                this.$store.commit('updateFilterFormState', [
                    {
                        label: this.$t('label.status'),
                        type: 'select',
                        name: 'status',
                        options: [
                        ...( this.userType !== EUserType.INTERNAL_USER ?
                        [
                            {
                                name: 'Chờ duyệt, Hoạt động',
                                value: EStatus.EXCEPT_DELETED,
                            },
                            {
                                name: this.$t('constant.status.pending'),
                                value: EStatus.WAITING,
                            }
                        ]: []),
                            {
                                name: this.$t('constant.status.deleted'),
                                value: EStatus.DELETED,
                            },
                            {
                                name: this.$t('constant.status.active'),
                                value: EStatus.ACTIVE,
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
            },
        },
        created() {
            this.$store.commit('updateQueryFilterState', {
                    enable: true,
                    placeholder: this.$t('filter.user'),
                });
            this.$store.commit('updateFilterValueState', {
                q: this.$route.query.q,
                status: this.userType !== EUserType.INTERNAL_USER ? EStatus.EXCEPT_DELETED : EStatus.ACTIVE,
            });
            this.$store.commit('updateBreadcrumbsState', [
                ...(
                    this.userType == EUserType.INTERNAL_USER ? [{
                        text: this.$t('employee-list'),
                        to: { name: 'employee.list' }
                    }] : []
                ),
                ...(
                    this.userType == EUserType.NORMAL_USER ? [{
                        text: this.$t('customer-list'),
                        to: { name: 'customer.list' }
                    }] : []
                ),
            ]);
            this.$store.commit('updateFilterFormState', [
                {
                    label: this.$t('label.status'),
                    type: 'select',
                    name: 'status',
                    options: [
                        ...( this.userType !== EUserType.INTERNAL_USER ?
                         [
                            {
                                name: 'Chờ duyệt, Hoạt động',
                                value: EStatus.EXCEPT_DELETED,
                            },
                            {
                                name: this.$t('constant.status.pending'),
                                value: EStatus.WAITING,
                            }
                        ]: []),
                            {
                                name: this.$t('constant.status.deleted'),
                                value: EStatus.DELETED,
                            },
                            {
                                name: this.$t('constant.status.active'),
                                value: EStatus.ACTIVE,
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
        },
        methods: {
            showModalDetail(item) {
                this.item = item;
                this.showModal = true;
            },
            showModalEditBalance(type, value, userId) {
                this.item = {
                    userId: userId,
                    type: type,
                    value: value,
                };
                this.showModalBalance = true;
            },
            resetPassword(id) {
                if (this.resettingPassUser[`${id}`]) {
                    return;
                }
                this.Util.confirmResetPassword(this.$t('object_name'), confirm => {
                    if (!confirm) {
                        return;
                    }
                    this.resettingPassUser[`${id}`] = true;
                    this.processing = true;
                    this.Util.post({url: `${this.$route.meta.baseUrl}/${id}/reset-password`})
                        .done(response => {
                            this.Util.showMsg2(response);
                            this.getListData(this.pagination);
                        })
                        .always(() => {
                            this.resettingPassUser[`${id}`] = false;
                            this.processing = false;
                        });
                });
            },
            changeBalanceValue(value) {
                if (value < 0 && this.item.balance == 0) {
                    return
                }
                this.item.value += value;
            },
            async getListData(paging) {
                let paginate = {
                pageSize: paging.size || this.pagination.size,
                page: Number.isInteger(paging.page) ? paging.page : 1,
            };
            if (this.isFirstTimeGetList) {
                paginate = {
                    pageSize: this.$route.query.pageSize || paginate.pageSize,
                    page: this.$route.query.page || paginate.page,
                };
                this.isFirstTimeGetList = false;
            }
            let reqData = Object.assign({
                ...paginate,
                filter: {
                    ...this.defaultFilter,
                    ...this.filters,
                    type: this.userType,
                }
            });

            this.loading = true;

            return this.Util.post({
                url: this.routes.search,
                data: reqData,
            }).done((res) => {
                if (res.error !== EErrorCode.NO_ERROR) {
                    this.Util.showMsg('error', null, res.msg);
                    return;
                }

                this.onListDataFetchSuccess(paging, res.data);

                this.$nextTick(() => {
                    this.pagination = {page: paginate.page, size: paginate.size || paginate.pageSize};
                    if (!this.pagination.size) {
                        this.pagination.size = res.data.per_page
                    }
                    this.$refs.pagingEl && this.$refs.pagingEl.setPage(this.pagination, false);

                    window.history.pushState(null, null, this.StringUtil.getUrlWithQueries(window.location.pathname, {
                        ...this.filterValueState.value,
                        page: this.pagination.page != 1 ? this.pagination.page : null,
                        pageSize: this.pagination.size != this.sz ? this.pagination.size : null,
                    }));
                });
            }).fail((err) => {
                console.error(err);
                this.Util.showMsg('error', null, this.$t('common.error.unknown'));
            }).always(() => {
                this.loading = false;
            });
            },
            async editBalance() {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm(this.item.type == EWalletType.POINT ? this.$t('confirm.edit-score') : this.$t('confirm.edit-coins'), resolve);
                });
                if (!confirm) {
                    return;
                }
                if (this.item.value < 0) {
                    this.error.push('Không nhập số âm!');
                    return;
                }

                this.Util.loadingScreen.show();
                this.processing = true;
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/edit-balance`,
                    data: {
                        userId: this.item.userId,
                        balance: this.item.value,
                        type: this.item.type
                    },
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }

                    await this.getListData(this.pagination);

                    this.Util.showMsg('success', null, res.msg);
                }).always(() => {
                    this.processing = false;
                    this.showModalBalance = false;
                    this.Util.loadingScreen.hide();
                });
            },
            async approveUser(id){
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
            async deleteItem(item) {
                let id = this.isUsingCode ? item.code : item.id;
                if (this.deleting[`${id}`]) {
                    return;
                }

                let confirm = await new Promise((resolve) => {
                    let text = item.get_shop ? `User "${item.name}" đang có cửa hàng "${item.get_shop.name}" với
                    cấp độ "${ELevel.valueToName(item.get_shop.level)}", khi xóa user thì cửa hàng cũng sẽ bị xóa theo.
                     Bạn có chắc chắn muốn xóa user này không?` : 'Bạn có chắc chắn muốn xóa user này không? ';
                    this.Util.confirm(text, resolve);
                });
                if (!confirm) {
                    return;
                }
                this.deleting[`${id}`] = true;
                this.processing = true;
                this.Util.post({
                    url: this.routes.delete,
                    data: {
                        ...(
                            this.isUsingCode ? {code: item.code} : {id: item.id}
                        )
                    }
                }).done(async (res) => {
                    if (res.error !== EErrorCode.NO_ERROR) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }

                    await this.getListData(this.pagination);

                    this.Util.showMsg('success', null, res.msg);
                }).fail(() => {
                    this.Util.showMsg('error', null, this.$t('common.error.unknown'));
                }).always(() => {
                    this.deleting[`${id}`] = false;
                    this.processing = false;
                });
            },
        }
    }
</script>

<style scoped>

</style>
