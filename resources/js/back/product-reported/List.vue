<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <b-row>
                        <b-col md="48">
                            <a-table
                                :items="productReportList.data"
                                :fields="fields"
                                :loading="loading"
                                :pagination="pagination"
                            >
                                <template #cell(userName)="data">
                                    {{data.item.userName}} / {{data.item.userPhone}}
                                </template>
                                <template #cell(productName)="data">
                                    <b-link :to="{name: 'product.list', query:{id: data.item.productId}}">{{data.item.productName}}
                                    </b-link>
                                </template>
                            </a-table>
                            <paging @page-change="onPageChangeHandler" :disabled="loading" :total="productReportList.total" ref="pagingEl"/>
                        </b-col>
                    </b-row>
                </div>
            </b-col>
        </b-row>
    </div>
</template>
<script>
    import {mapState} from "vuex";
    import EErrorCode from "../../constants/error-code";
    import EStatus from "../../constants/status";
    import ECategoryType from "../../constants/category-type";

    export default {
        inject: ['Util', 'StringUtil', 'DateUtil'],
        data() {
            return {
                productReportList: [],
                filter: {
                    q: null,
                },
                ECategoryType,
                pagination: {
                    page: 1,
                    size: null,
                },
                reportList: [
                    {
                        name: 'Tất cả',
                        value: null
                    },
                ],
                loading: false,
                processing: false,
            }
        },
        computed: {
            ...mapState(['filterValueState', 'queryFilterState']),
            fields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'text-center align-middle', colWidth: '5%'},
                    {label: 'Người báo / SĐT', key: 'userName', class: 'text-center align-middle', colWidth: '15%'},
                    {label: 'Tên bài đăng', key: 'productName', class: 'text-center align-middle', tdClass: 'col-text', colWidth: '20%'},
                    {label: 'Loại báo cáo', key: 'reportType', class: 'text-center align-middle', tdClass: 'col-text', colWidth: '15%'},
                    {label: 'Nội dung', key: 'content', class: 'text-center align-middle', tdClass: 'col-text', colWidth: '35%'},
                    {label: 'Thời gian', key: 'createdAt', class: 'text-center align-middle', colWidth: '10%'},
                ];
            },
        },
        watch: {
            filterValueState(val) {
                window.history.pushState(null, null, this.StringUtil.getUrlWithQueries(window.location.pathname, {...val.value, page: this.pagination.page}));
                this.searchProduct({page: 1, size: this.sz});
            },
            
        },
        created() {
            this.breadCrumb();
            this.resetFilter(this.$route);
            this.searchProduct({page: 1, size: this.sz});
            this.searchIssueReportList();
        },
        methods: {
            resetFilter(route) {
                this.$store.commit('updateQueryFilterState', {
                    enable: true,
                    placeholder: 'Tên người báo cáo / SĐT / Tên bài đăng',
                });
                this.$store.commit('updateFilterFormState', [
                    {
                        label: 'Loại báo cáo',
                        type: 'select',
                        name: 'categoryType',
                        options: this.reportList,
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
                    categoryType: null
                });
            },
            breadCrumb() {
                this.$store.commit('updateBreadcrumbsState', [
                    {
                        text: 'Danh sách báo sai',
                        to: { name: 'issue-report.list' }
                    },
                ]);
            },
            onPageChangeHandler(paging) {
                this.searchProduct(paging);
            },
            showModalDetail(item) {
                this.item = item;
                this.bordered = true;
            },
            showVideo(item) {
                return this.StringUtil.getYoutubeVideoId(item.youtubeId);
            },
            searchIssueReportList() {
                let reqData = Object.assign({
                    filter: {
                        type: ECategoryType.ISSUE_REPORT,
                        getAllCategory: true
                    }
                });
                this.Util.post({
                    url: '/api/back/issue-report/search',
                    data: reqData,
                }).done(response => {
                    response.data.forEach((item) => {
                        this.reportList.push({
                            name: item.name,
                            value: item.id
                        });
                    });
                })
                .always(() => {
                    this.loading = false;
                    this.Util.loadingScreen.hide();
                });
            },
            searchProduct(paging) {
                this.loading = true;
                let reqData = Object.assign({
                    pageSize: paging.size || this.pagination.size,
                    page: paging.page ? paging.page : 1,
                    filter: {
                        ...this.filterValueState.value,
                    }
                });
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/search`,
                    data: reqData,
                }).done(response => {
                    if (response.error == EErrorCode.ERROR) {
                        this.Util.showMsg2(response);
                        return false;
                    }
                    this.productReportList = response.reportList;
                    this.pagination = {page: paging.page, size: paging.size};
                    if (!this.pagination.size) {
                        this.pagination.size = this.productReportList.per_page
                    }
                    response.reportList.data.forEach((item, index) => {
                        if (item.createdAt) {
                            let date = new Date(item.createdAt);
                            try {
                                this.productReportList.data[index].createdAt = this.DateUtil.getDateString(date, '/', false);
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
        }
    }
</script>

<style scoped>

</style>
