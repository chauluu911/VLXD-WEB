<template>
    <b-row>
        <b-col cols="48">
            <div class="content__inner">
                <b-row class="pb-2">
                    <b-col cols="24">
                        <span v-if="!this.customerType" class="span__title-detail">{{$t('tab.title.contact-history').toUpperCase()}}</span>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="48">
                        <a-table
                            :items="contactList.data" 
                            :fields="userFields" 
                            :loading="loading" 
                            :pagination="pagination"
                        >
                            <template #cell(createdAt)="data">
                                <span>
                                    {{DateUtil.getDateTimeString2(new Date(data.item.createdAt))}}
                                </span>
                            </template>
                        </a-table>
                        <paging @page-change="onPageChangeHandler" :disabled="loading" :total="contactList.total" ref="pagingEl"/>
                    </b-col>
                </b-row>
            </div>
        </b-col>
    </b-row>
</template>

<script>
    import {mapState} from "vuex";
    import ECustomerType from "../../constants/customer-type";
    import userListManage from "../../locales/back/user/user-detail";
    import EErrorCode from "../../constants/error-code";

    export default {
        inject: ['Util', 'StringUtil', 'DateUtil'],
        i18n: {
            messages: userListManage
        },
        props: {
            customerType: {
                type: Number,
                default: null,
            },
        },
        data() {
            return {
                pagination: {
                    page: 1,
                    size: null,
                },
                contactList: [],
                loading: false,

                EErrorCode,
                ECustomerType,
            }
        },
        computed: {
            ...mapState(['filterValueState']),
            userFields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'align-middle text-center', colWidth: '5%'},
                    ...(
                        this.customerType == ECustomerType.SELLER || this.customerType == null ?
                        [{label: this.$t('tab.contact.table.id-buyer'), key: this.customerType == null ? 'buyerCode' : 'code', class: 'text-center align-middle', colWidth: '12%'}] : []
                    ),
                    ...(
                        this.customerType ==  ECustomerType.BUYER ?
                        [{label: this.$t('tab.contact.table.id-seller'), key: 'code', class: 'text-center align-middle', colWidth: '12%'}] : []
                    ),
                    {label: this.customerType == null ? this.$t('tab.contact.table.buyer-name') : this.$t('tab.contact.table.name'), key: this.customerType == null ? 'buyerName' : 'name', class: 'text-center align-middle', colWidth: '12%'},
                    {label: this.$t('tab.contact.table.status'), key: 'connectStatus', class: 'text-center align-middle', colWidth: '12%'},
                    ...(
                        this.customerType ==  null ?
                        [{label: this.$t('tab.contact.table.id-seller'), key: 'sellerCode', class: 'text-center align-middle', colWidth: '12%'}] : []
                    ),
                    ...(
                        this.customerType ==  null ?
                        [{label: this.$t('tab.contact.table.seller-name'), key: 'sellerName', class: 'text-center align-middle', colWidth: '12%'}] : []
                    ),
                    {label: this.$t('tab.contact.table.time'), key: 'createdAt', class: 'text-center align-middle', colWidth: '12%'},
                ];
            },
            route() {
                return {
                    search: this.customerType == null ? `${this.$route.meta.baseUrl}/search` : `${this.$route.meta.baseUrl}/${this.$route.params.userCode}/connection`
                }
            }
        },
        created() {
            this.$store.commit('updateBreadcrumbsState', [
                {
                    text: 'Danh sách Liên hệ',
                    to: { name: 'connection.list' }
                },
                ...(
                    this.customerType == ECustomerType.SELLER ? [
                        {
                            text: this.$route.params.userCode,
                            to: { name: 'seller.info' }
                        }
                    ] : []
                ),
                ...(
                    this.customerType == ECustomerType.BUYER ? [
                        {
                            text: this.$route.params.userCode,
                            to: { name: 'buyer.info' }
                        }
                    ] : []
                ),
            ]);
            this.$store.commit('updateFilterValueState', {
                q: this.$route.query.q,
            })
        },
        watch: {
            filterValueState(val) {
                window.history.pushState(null, null, this.StringUtil.getUrlWithQueries(window.location.pathname, {...val.value, page: this.pagination.page}));
                this.getContactList({page: 1, size: this.sz});
            }
        },
        methods: {
            onPageChangeHandler(paging) {
                this.getContactList(paging);
            },
            getContactList(paging) {
                this.loading = true;
                let reqData = Object.assign({
                    pageSize: paging.size || this.pagination.size,
                    page: paging.page ? paging.page : 1,
                    filter: {
                        customerType: this.customerType,
                        ...this.filterValueState.value,
                    }
                });
                this.Util.get({
                    url: this.route.search,
                    data: reqData,
                }).done(response => {
                        if (response.error == EErrorCode.ERROR) {
                            this.Util.showMsg2(response);
                            if (this.customerType == ECustomerType.BUYER) {
                                this.$router.push({name: 'buyer.list', query: val.value});
                            }else if(this.customerType == ECustomerType.SELLER) {
                                this.$router.push({name: 'seller.list', query: val.value});
                            } else {
                                this.$router.push({name: 'connection.list', query: val.value});
                            }
                            return false;
                        }
                        this.contactList = response.contactList;
                        this.pagination = {page: paging.page, size: paging.size};
                        if (!this.pagination.size) {
                            this.pagination.size = this.contactList.per_page
                        }
                    }).always(() => {
                            this.loading = false;
                            this.Util.loadingScreen.hide();
                        });
            },
        }
    }
</script>

<style scoped>

</style>
