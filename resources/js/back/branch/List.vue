<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <b-row class="mb-4">
                        <b-col cols="48">
                            <b-button
                                :to="{
                                    name: `branch.create`,
                                    params: { action: 'create' }
                                }"
                                variant="outline-primary"
                                class="float-right ml-3"
                            >
                                <i class="fas fa-plus" />
                                {{
                                    $t("common.button.add2", {
                                        objectName: $t("object_name")
                                    })
                                }}
                            </b-button>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col md="48">
                            <a-table
                                :items="table.data"
                                :fields="branchFields"
                                :loading="loading"
                                :pagination="pagination"
                            >
                                <template #cell(action)="data">
                                    <!-- <router-link
                                        v-if="
                                            data.item.status !== EStatus.DELETED
                                        "
                                        :to="{
                                            name: 'branch.info',
                                            params: {
                                                branchId: data.item.id,
                                                action: 'edit'
                                            }
                                        }"
                                        :title="$t('common.tooltip.edit')"
                                        class="no-decoration"
                                    >
                                        <i class="text-primary fas fa-edit" />
                                    </router-link> -->
                                    <a-button-delete
                                        class="text-primary mx-1"
                                        @click="deleteItem(data.item)"
                                        :disabled="deleting[`${data.item.id}`]"
                                        :deleting="deleting[`${data.item.id}`]"
                                        tooltip
                                        :title="$t('common.tooltip.delete')"
                                    />
                                </template>
                            </a-table>
                            <paging
                                @page-change="onPageChangeHandler"
                                :disabled="loading"
                                :total="table.total"
                                ref="pagingEl"
                            />
                        </b-col>
                    </b-row>
                </div>
            </b-col>
        </b-row>
    </div>
</template>

<script>
import { mapState } from "vuex";
import ECustomerType from "../../constants/customer-type";
import branchListMessage from "../../locales/back/branch/branch-list";
import EErrorCode from "../../constants/error-code";
import listMixin from "../mixins/list-mixin";
import EStatus from "../../constants/status";

export default {
    inject: ["Util", "StringUtil", "DateUtil"],
    mixins: [listMixin],
    i18n: {
        messages: branchListMessage
    },
    data() {
        return {
            table: [],
            loading: false,
            processing: false,
            item: null,
            showModal: false,
            EErrorCode,
            EStatus
        };
    },
    computed: {
        ...mapState(["filterValueState", "queryFilterState"]),
        branchFields() {
            return [
                {
                    label: this.$t("table.column.no"),
                    key: "index",
                    class: "align-middle text-center",
                    colWidth: "5%"
                },
                {
                    label: this.$t("table.name_shop"),
                    key: "name_shop",
                    class: "align-middle text-center",
                    colWidth: "20%"
                },
                {
                    label: this.$t("table.name"),
                    key: "name",
                    class: "align-middle text-center",
                    colWidth: "20%"
                },
                {
                    label: this.$t("table.address"),
                    key: "address",
                    thClass: "text-center align-middle",
                    colWidth: "20%",
                    tdClass: "col-text"
                },
                {
                    label: this.$t("table.phone"),
                    key: "phone1",
                    class: "text-center align-middle",
                    colWidth: "15%"
                },
                {
                    label: this.$t("table.option"),
                    key: "action",
                    class: "text-center align-middle",
                    colWidth: "15%"
                }
            ];
        }
    },
    mounted() {
        // console.log(window.location.href);
    },
    created() {
        this.$store.commit("updateFilterFormState", [
            {
                type: "date",
                name: "createdAtFrom",
                placeholder: this.$t("placeholder.filter.created_at_from"),
                dropleft: true
            },
            {
                type: "date",
                name: "createdAtTo",
                placeholder: this.$t("placeholder.filter.created_at_to"),
                dropleft: true
            }
        ]);
        this.$store.commit("updateQueryFilterState", {
            enable: true,
            placeholder: this.$t("filter.name_shop")
        });
        this.$store.commit("updateFilterValueState", {
            q: this.$route.query.q
        });
        this.$store.commit("updateBreadcrumbsState", [
            {
                text: this.$t("branch-list"),
                to: { name: "branch.list" }
            }
        ]);
    },
    methods: {
        onListDataFetchSuccess(paging, data) {
            this.table = data;
            this.pagination = { page: paging.page, size: paging.size };
        }
    }
};
</script>

<style scoped></style>
