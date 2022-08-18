<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <b-row class="mb-1">
                        <b-col class="mb-2" style="min-width: 300px; max-width: 350px;">
                            <div class="d-inline-block">
                                {{ $t('total_count') }}: {{ table.total }}
                            </div>
                            <div class="d-inline-block">
                                {{ $t('sent_count') }}: {{ sentCount }}
                            </div>
                        </b-col>
                        <b-col class="text-right" style="min-width: 300px;">
                            <a-button :icon-class="['mr-lg-2']" variant="outline-primary" class="mr-2 mb-2" :to="{name: `notification.create`}">
                                <template #icon>
                                    <f-plus-icon size="20" class="mr-lg-2"/>
                                </template>
                                <template #default>
                                    {{ $t('common.button.add2', {objectName: $t('object_name').toLowerCase()}) }}
                                </template>
                            </a-button>
                            <button
                                v-if="selectedItems.length"
                                class="btn mb-2 btn-primary"
                                :disabled="processing"
                                @click="approvePayments()" ref="btnApprove"
                            >
                                Duyệt
                            </button>
                            <!-- <a-button :icon-class="['mr-lg-2']" variant="outline-primary" class="mb-2">
                                <template #icon>
                                    <f-download-icon size="20" class="mr-lg-2"/>
                                </template>
                                <template #default>
                                    <span class="d-none d-lg-inline">{{ $t('common.button.download') }}</span>
                                </template>
                            </a-button> -->
                        </b-col>
                    </b-row>
                    <a-table :items="table.data"
                        :fields="fields"
                        :loading="loading"
                        :pagination="pagination"
                    >
                        <template #cell(title)="data">
							<b-link :to="{name: 'notification.detail', params: {notificationScheduleId: data.item.id, action: 'detail'}}">
								{{ data.item.title }}
							</b-link>
                        </template>
                        <template #cell(content)="data">
                            <div
                                v-html="data.item.content"
                                v-shave="{height: 22}"
                            />
                        </template>
                        <template #cell(statusStr)="data">
                            <span v-if="data.item.approvalStatus == EApprovalStatus.WAITING">{{data.item.approvalStatusStr}}</span>
                            <span v-else>{{data.item.statusStr}}</span>
                        </template>
                        <template v-slot:cell(action)="data">
                            <template v-if="data.item.status !== EStatus.ACTIVE">
                                <div class="float-left">
                                    <input v-if="data.item.approvalStatus == EApprovalStatus.WAITING" type="checkbox" class="ml-2" :class="{'d-none': processing}" :value="{id: data.item.id}" v-model="selectedItems" style="margin-top: 5px">
                                </div>
                                <b-link
                                    class="text-primary mx-1"
                                    :to="{name: `notification.detail`, params: {notificationScheduleId: data.item.id, action: 'edit'}}"
                                    :disabled="processing"
                                    v-b-tooltip.hover :title="$t('common.tooltip.edit')"
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
                        </template>
                    </a-table>
                    <paging @page-change="onPageChangeHandler" :total="table.total" :disabled="loading" ref="pagingEl"/>
                </div>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import notificationScheduleListMessages from '../../locales/back/notification-management-list';
    import FDownloadIcon from 'vue-feather-icons/icons/DownloadIcon'
    import FPlusIcon from 'vue-feather-icons/icons/PlusIcon'
    import listMixin from '../mixins/list-mixin';
    import EStatus from '../../constants/status';
    import EApprovalStatus from '../../constants/approval-status';

    export default {
        name: 'SubscriptionPriceList',
        i18n: {
            messages: notificationScheduleListMessages,
        },
        inject: ['StringUtil', 'DateUtil'],
        mixins: [listMixin],
        components: {
            FDownloadIcon,
            FPlusIcon,
        },
        data() {
            return {
                sentCount: 0,
                EStatus,
                EApprovalStatus,
                selectedItems: [],
            };
        },
        computed: {
            fields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'text-center align-middle', colWidth: '5%'},
                    {label: this.$t('table.column.title'), key: 'title', class: 'align-middle', thClass: 'text-center', tdClass: 'col-text', colWidth: '18%'},
                    {label: this.$t('table.column.content'), key: 'content', class: 'align-middle', thClass: 'text-center', tdClass: 'col-text', colWidth: '30%'},
                    {label: this.$t('table.column.schedule_at'), key: 'scheduledAtStr', class: 'align-middle', thClass: 'text-center', tdClass: 'col-align-center', colWidth: '17%'},
                    {label: this.$t('table.column.target'), key: 'targetTypeStr', class: 'align-middle', thClass: 'text-center', tdClass: 'col-align-center', colWidth: '10%'},
                    {label: this.$t('table.column.status'), key: 'statusStr', class: 'align-middle', thClass: 'text-center', tdClass: 'col-align-center', colWidth: '10%'},
                    {label: this.$t('table.column.custom'), key: 'action', class: 'text-center align-middle', colWidth: '10%'},
                ];
            },
        },
        created() {
            this.$store.commit('updateBreadcrumbsState', [
                {
                    text: this.$t('title'),
                    to: {name: 'notification'}
                },
            ]);
            this.$store.commit('updateFilterFormState', [
                {
                    label: this.$t('filter.scheduled_at'),
                    type: 'date',
                    name: 'scheduledAtGt',
                    placeholder: this.$t('placeholder.filter.created_at_from'),
                    dropleft: true,
                },
                {
                    type: 'date',
                    name: 'scheduledAtLt',
                    placeholder: this.$t('placeholder.filter.created_at_to'),
                    dropleft: true,
                },
            ]);
            this.$store.commit('updateFilterValueState', {
                q: this.$route.query.q,
            })
            this.$store.commit('updateQueryFilterState', {
                enable: true,
                placeholder: this.$t('filter.name'),
            });
            // this.getListData({page: this.$route.query.page || 1, size: this.sz});
        },
        methods: {
            onListDataFetchSuccess(paging, data) {
                data.scheduleList.data.forEach((item) => {
                    if (item.scheduled_at) {
                        let date = new Date(item.scheduled_at);
                        item.scheduledAtStr = this.DateUtil.getDateTimeString(date, '/', ':', false, true, ' - ');
                    }
                });
                this.table = data.scheduleList;
                this.sentCount = data.sentCount;
                this.pagination = {page: paging.page, size: paging.size};
            },
            async approvePayments() {
                if (this.selectedItems.length == 0) {
                    return;
                }
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm('Bạn có muốn duyệt tất cả thông báo này không?', resolve);
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
