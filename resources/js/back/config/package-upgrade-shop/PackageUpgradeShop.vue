<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <!-- <b-row class="mb-3">
                        <b-col class="text-right" style="min-width: 300px;">
                            <a-button
                                :icon-class="['mr-lg-2']"
                                variant="outline-primary"
                                class="mr-2"
                                @click="$refs.createFormModalEl.showForm()"
                            >
                                <template #icon>
                                    <f-plus-icon size="20" class="mr-lg-2"/>
                                </template>
                                <template #default>
                                    {{ $t('common.button.add2', {objectName: $t('object_name').toLowerCase()}) }}
                                </template>
                            </a-button>
                        </b-col>
                    </b-row> -->
                    <a-table :items="table.data" :fields="fields" :loading="loading" :pagination="pagination">
                        <template v-slot:cell(price)="data">
                            <span>{{data.item.price}} VNĐ</span>
                        </template>
                        <template v-slot:cell(action)="data">
                            <b-link
                                v-if="data.item.status !== EStatus.DELETED"
                                :title="$t('common.tooltip.edit')"
                                class="no-decoration"
                                @click="$refs.createFormModalEl.showForm(data.item)"
                            >
                                <i class="text-primary fas fa-edit"/>
                            </b-link>
                            <!-- <a-button-delete
                                class="text-primary mx-1"
                                @click="deleteItem(data.item)"
                                :disabled="deleting[`${data.item.id}`]"
                                :deleting="deleting[`${data.item.id}`]"
                                tooltip :title="$t('common.tooltip.delete')"
                            /> -->
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
import {mapState} from "vuex";
import packageUpgradeShopListManage from "../../../locales/back/config/package-upgrade-shop";
import EErrorCode from "../../../constants/error-code";
import EUserType from "../../../constants/user-type";
import listMixin from "../../mixins/list-mixin";
import FPlusIcon from 'vue-feather-icons/icons/PlusIcon';
import CreatingForm from "./CreatingForm";
import EStatus from "../../../constants/status";
import paging from "../../../components/Paging";

export default {
    inject: ['Util', 'StringUtil', 'DateUtil'],
    mixins: [listMixin],
    i18n: {
        messages: packageUpgradeShopListManage
    },
    components: {
        FPlusIcon,
        CreatingForm,
        paging
    },
    data() {
        return {
            EStatus,

        }
    },
    computed: {
        ...mapState(['queryFilterState']),
        fields() {
            return [
                {label: this.$t('table.column.no'), key: 'index', class: 'text-center align-middle', colWidth: '5%'},
                {label: this.$t('table.name'), key: 'name', thClass: 'text-center align-middle', tdClass: 'col-text', colWidth: '35%'},
                {label: this.$t('table.price'), key: 'price', class: 'text-center align-middle', colWidth: '15%'},
                {label: this.$t('table.num-day'), key: 'numDay', class: 'text-center align-middle', colWidth: '10%'},
                {label: this.$t('table.option'), key: 'action', class: 'text-center align-middle', colWidth: '10%'},
            ];
        },
    },
    created() {
        this.$store.commit('updateFilterFormState', []);
        this.$store.commit('updateQueryFilterState', {
            enable: false,
        });
        this.$store.commit('updateBreadcrumbsState', [
            {
                text: 'Cấu hình gói nâng cấp cửa hàng',
                to: { name: 'config.package-upgrade-shop' }
            }
        ]);
        this.$store.commit('updateFilterValueState', {
            q: this.$route.query.q,
        });
        this.getData({page: this.$route.query.page || 1, size: this.sz});
    },
    methods: {
        getData() {
            this.Util.loadingScreen.show();
            this.Util.post({
                url: `${this.$route.meta.baseUrl}/search`,
            }).done(response => {
                console.log(response)
            })
                .always(() => {
                    this.Util.loadingScreen.hide();
                });
        },
         currentPage(paging) {
                this.$refs.pagingEl.setPage(paging);
            },
    }
}
</script>

<style scoped>

</style>
