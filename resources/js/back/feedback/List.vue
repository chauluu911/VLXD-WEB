<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <b-row class="mb-3">
                        <b-col class="d-flex-center-y justify-content-between" style="min-width: 300px; max-width: 350px;">
                            <div class="d-inline-block">
                                {{ $t('total_count') }}: {{ table.total }}
                            </div>
                            <div class="d-inline-block">
                                {{ $t('total_count') }}: {{ displayedFeedbackCount }}/{{ maxNumberOfFeedbackDisplayed }}
                            </div>
                        </b-col>
                        <b-col class="text-right" style="min-width: 300px;">
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
                    <a-table :items="table.data" :fields="fields" :loading="loading" :pagination="pagination">
                        <template #cell(avatar)="data">
                            <div class="text-center">
                                <b-avatar :src="data.item.avatarPath" size="36"></b-avatar>
                            </div>
                        </template>
                        <template #cell(content)="data">
                            <div  v-see-more="{height: 22, expandBtn: $t('common.see-more'), collapseBtn: $t('common.collapse')}">{{ data.item.content }}</div>
                        </template>
                        <template #cell(displayStatus)="data">
                            <b-select
                                @change="onFeedbackStatusChange($event, data.item)"
                                v-model="data.item.display_status"
                                style="min-width: 100px;"
                            >
                                <option v-for="item in displayStatusList" :value="item.value">
                                    {{ item.name }}
                                </option>
                            </b-select>
                        </template>
                        <template v-slot:cell(action)="data">
                            <b-link
                                class="text-primary mx-1"
                                :disabled="processing"
                                :title="$t('common.tooltip.edit')"
                                @click="$refs.createFormModalEl.showForm(data.item)"
                            >
                                <i class="fas fa-edit"/>
                            </b-link>
                            <a-button-delete
                                class="text-primary mx-1"
                                @click="deleteItem(data.item)"
                                :disabled="deleting[`${data.item.id}`]"
                                :deleting="deleting[`${data.item.id}`]"
                                tooltip :title="$t('common.tooltip.delete')"
                            />
                        </template>
                    </a-table>
                    <paging @page-change="onPageChangeHandler" :total="table.total" :disabled="loading" ref="pagingEl"/>
                </div>
            </b-col>
        </b-row>
        <creating-form ref="createFormModalEl" @save-success="getListData({page: 1, size: sz})"/>
    </div>
</template>

<script>
    import feedbackListMessages from '../../locales/back/feedback-management-list';
    import FDownloadIcon from 'vue-feather-icons/icons/DownloadIcon'
    import listMixin from '../mixins/list-mixin';
    import EDisplayStatus from '../../constants/display-status';
    import {mapState} from "vuex";
    import EStatus from "../../constants/status";
    import CreatingForm from "./CreatingForm";

    export default {
        name: 'PostList',
        i18n: {
            messages: feedbackListMessages,
        },
        inject: ['StringUtil', 'DateUtil'],
        mixins: [listMixin],
        components: {
            FDownloadIcon,
            CreatingForm,
        },
        data() {
            return {
                processing: false,
                displayedFeedbackCount: 0,
                maxNumberOfFeedbackDisplayed: 0,
            }
        },
        computed: {
            ...mapState(['filterValueState', 'filterFormState','queryFilterState']),
            fields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'text-center align-middle', colWidth: '5%'},
                    {label: this.$t('table.column.post_id'), key: 'user_code', class: 'text-center align-middle', colWidth: '10%'},
                    {label: this.$t('table.column.avatar'), key: 'avatar', thClass: 'text-center align-middle', tdClass: 'col-text', colWidth: '10%'},
                    {label: this.$t('table.column.name'), key: 'user_name', thClass: 'image-thead text-center align-middle', tdClass: 'col-text', colWidth: '20%'},
                    {label: this.$t('table.column.status'), key: 'displayStatus', thClass: 'text-center align-middle', tdClass: 'col-text', colWidth: '10%'},
                    {label: this.$t('table.column.content'), key: 'content', thClass: 'text-center align-middle', tdClass: 'col-text', colWidth: '25%'},
                    {label: this.$t('table.column.custom'), key: 'action', class: 'text-center align-middle', colWidth: '10%'},
                ];
            },
            displayStatusList() {
                return [
                    {
                        name: this.$t('constant.display_status.hidden'),
                        value: EDisplayStatus.HIDDEN,
                    },
                    {
                        name: this.$t('constant.display_status.showing'),
                        value: EDisplayStatus.SHOWING,
                    },
                ]
            },
            routes() {
                return {
                    search: `${this.$route.meta.baseUrl}/search`,
                    delete: `${this.$route.meta.baseUrl}/delete`,
                    displayStatus: `${this.$route.meta.baseUrl}/display-status`,
                };
            },
        },
        created() {
            this.$store.commit('updateBreadcrumbsState', [
                {
                    text: this.$t('title'),
                    to: ''
                },
            ]);
            this.$store.commit('updateFilterFormState', [
                {
                    label: this.$t('table.column.status'),
                    type: 'select',
                    name: 'status',
                    options: [
                        {
                            name: this.$t('constant.display_status.hidden'),
                            value: EDisplayStatus.HIDDEN,
                        },
                        {
                            name: this.$t('constant.display_status.showing'),
                            value: EDisplayStatus.SHOWING,
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
                placeholder: this.$t('name_filter'),   
            });
            this.$store.commit('updateFilterValueState', {
                q: this.$route.query.q,
            })
        },
        methods: {
            onListDataFetchSuccess(paging, data) {
                data.feedbackList.data.forEach((item) => {
                    item.oldDisplayStatus = item.display_status;
                });
                this.table = data.feedbackList;
                this.displayedFeedbackCount = data.displayedFeedbackCount;
                this.maxNumberOfFeedbackDisplayed = data.maxNumberOfFeedbackDisplayed;
                this.pagination = {page: paging.page, size: paging.size};
            },
            async onFeedbackStatusChange(newDisplayStatus, item) {
                if (item.oldDisplayStatus === newDisplayStatus) {
                    return;
                }

                let newDisplayStatusName = newDisplayStatus === EDisplayStatus.SHOWING
                    ? this.$t('constant.display_status.showing')
                    : this.$t('constant.display_status.hidden');
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm(this.$t('confirmation.change_display_status', {displayStatus: newDisplayStatusName}), resolve);
                });
                if (!confirm) {
                    item.display_status = item.oldDisplayStatus;
                    return;
                }

                this.Util.loadingScreen.show();
                this.processing = true;
                this.Util.post({
                    url: this.routes.displayStatus,
                    data: {
                        id: item.id,
                        status: newDisplayStatus,
                    }
                }).done(async (res) => {
                    if (res.error) {
                        item.display_status = item.oldDisplayStatus;
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }

                    await this.getListData(this.pagination);

                    this.Util.showMsg('success', null, res.msg);
                }).fail(() => {
                    this.Util.showMsg('error', null, this.$t('common.error.unknown'));
                    item.display_status = item.oldDisplayStatus;
                }).always(() => {
                    this.Util.loadingScreen.hide();
                    this.processing = false;
                });
            },
        }
    }
</script>
