<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <b-row class="mb-4">
                        <b-col cols="48">
                            <b-button :to="{name: `news.create`, params: {action: 'create'}}" variant="outline-primary" class="float-right ml-3">
                                <i class="fas fa-plus"/>
                                {{$t('common.button.add2', {
                                    'objectName': $t('object_name')
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
                                        :src="data"
                                        size="30"
                                        class="bg-white"
                                    ></b-avatar>
                                </template>
                                <template #cell(contentSub)="data">
                                    <a href="javascript:void(0)" @click="showModalDetail(data.item)">
                                        <div v-html="data.item.contentSub" v-shave="{height: 24, character: '...'}"></div>
                                    </a>
                                </template>
                                <template #cell(action)="data">
                                    <router-link
                                        v-if="data.item.status !== EStatus.DELETED"
                                        :to="{name: 'news.info', params:{newsId: data.item.id, action: 'edit'}}"
                                        :title="$t('common.tooltip.edit')"
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
                                </template>
                            </a-table>
                            <paging @page-change="onPageChangeHandler" :disabled="loading" :total="table.total" ref="pagingEl"/>
                        </b-col>
                    </b-row>
                </div>
            </b-col>
        </b-row>
        <b-modal
            size="xl"
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
                                            {{$t('table.title')}}
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            {{ item.title }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left w-25 p-1">
                                            {{$t('table.content')}}
                                        </td>
                                        <td class="text-left w-75 p-1">
                                            <ck-document
                                                v-model="item.content"
                                                class="w-100"
                                                :read-only="true"
                                                :ck-class="['border border-top-0']"
                                                :ck-style="{height: '500px'}"
                                            ></ck-document>
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
    </div>
</template>

<script>
    import {mapState} from "vuex";
    import ECustomerType from "../../constants/customer-type";
    import newsListMessage from "../../locales/back/news/news-list";
    import EErrorCode from "../../constants/error-code";
    import listMixin from "../mixins/list-mixin";
    import EStatus from "../../constants/status";

    export default {
        inject: ['Util', 'StringUtil', 'DateUtil'],
        mixins: [listMixin],
        i18n: {
            messages: newsListMessage
        },
        data() {
            return {
                //countries: [],
                loading: false,
                processing: false,
                item: null,
                showModal: false,
                EErrorCode,
                EStatus,
            }
        },
        computed: {
            ...mapState(['filterValueState', 'queryFilterState']),
            userFields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'align-middle text-center', colWidth: '5%'},
                    {label: this.$t('table.avatar'), key: 'avatar', class: 'align-middle text-center', colWidth: '5%'},
                    {label: this.$t('table.title'), key: 'title', thClass: 'text-center align-middle', colWidth: '30%', tdClass: 'col-text'},
                    {label: this.$t('table.content'), key: 'contentSub', thClass: 'text-center align-middle', colWidth: '37%', tdClass: 'col-text'},
                    {label: this.$t('table.created-at'), key: 'createdAt', class: 'text-center align-middle', colWidth: '5%'},
                    {label: this.$t('table.status'), key: 'strStatus', class: 'text-center align-middle', colWidth: '10%'},
                    {label: this.$t('table.option'), key: 'action', class: 'text-center align-middle', colWidth: '8%'},
                ];
            },
        },
        created() {
            this.$store.commit('updateFilterFormState', [
                {
                    label: this.$t('constant.status.status'),
                    type: 'select',
                    name: 'status',
                    options: [
                        {
                            name: this.$t('constant.status.active'),
                            value: EStatus.ACTIVE,
                        },
                        {
                            name: this.$t('constant.status.deleted'),
                            value: EStatus.DELETED,
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
            this.$store.commit('updateQueryFilterState', {
                    enable: true,
                    placeholder: this.$t('filter.title') + ' / ' + this.$t('filter.content'),
                });
            this.$store.commit('updateFilterValueState', {
                q: this.$route.query.q,
                status: EStatus.ACTIVE,
            });
            this.$store.commit('updateBreadcrumbsState', [
                {
                    text: this.$t('news-list'),
                    to: { name: 'news.list' },
                }
            ]);
        },
        methods: {
            onListDataFetchSuccess(paging, data) {
                this.table = data;
                this.pagination = {page: paging.page, size: paging.size};
            },
            showModalDetail(item) {
                this.item = item;
                this.showModal = true;
            },
        }
    }
</script>

<style scoped>

</style>
