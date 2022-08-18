import {mapState} from "vuex";
import EErrorCode from "../../constants/error-code";

const listMixin = {
    inject: ['Util', 'StringUtil'],
    data() {
        return {
            loading: false,
            processing: false,
            deleting: {},
            table: {
                total: 0,
                data: [],
            },
            pagination: {
                page: 1,
                size: 0,
            },
            filters: {},
            isFirstTimeGetList: true,
            exporting: {
                processId: null,
                route: null,
                loading: false,
            },
            isUsingCode: false,
        }
    },
    computed: {
        ...mapState(['filterValueState']),
        fields() {
            return [];
        },
        routes() {
            return {
                search: `${this.$route.meta.baseUrl}/search`,
                delete: `${this.$route.meta.baseUrl}/delete`,
            };
        },
        defaultFilter() {
            return {}
        },
    },
    watch: {
        filterValueState() {
            this.filters = this.getFilterValueState();
            this.getListData({page: 1, size: this.sz});
        }
    },
    methods: {
        onPageChangeHandler(paging) {
            this.getListData(paging)
        },
        onListDataFetchSuccess(paging, data) {
            this.table = data;
            this.pagination = {page: paging.page, size: paging.size};
        },
        getFilterValueState() {
            return this.filterValueState.value;
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
        async deleteItem(item) {
            let id = this.isUsingCode ? item.code : item.id;
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
};
export default listMixin;
