<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <b-row class="mb-3">
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
                    </b-row>
                    <a-table :items="table.data" :fields="fields" :loading="loading" :pagination="pagination">
                        <template v-slot:cell(price)="data">
                            <span>{{data.item.price}} VNĐ</span>
                        </template>
                        <template v-slot:cell(action)="data">
                            <!-- <b-link
                                class="text-primary mx-1"
                                :disabled="processing"
                                v-b-tooltip.hover :title="$t('common.tooltip.edit')"
                                @click="$refs.createFormModalEl.showForm(data.item)"
                            >
                                <i class="fas fa-edit"/>
                            </b-link> -->
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
    import {mapState} from "vuex";
    import packageListManage from "../../../locales/back/config/package";
    import EErrorCode from "../../../constants/error-code";
    import EUserType from "../../../constants/user-type";
    import listMixin from "../../mixins/list-mixin";
    import FPlusIcon from 'vue-feather-icons/icons/PlusIcon';
    import CreatingForm from "./CreatingForm";

    export default {
        inject: ['Util', 'StringUtil', 'DateUtil'],
        mixins: [listMixin],
        i18n: {
            messages: packageListManage
        },
        components: {
            FPlusIcon,
            CreatingForm
        },
        data() {
            return {
                
            }
        },
        computed: {
            ...mapState(['queryFilterState']),
            fields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'text-center align-middle', colWidth: '5%'},
                    {label: this.$t('table.name'), key: 'name', thClass: 'text-center align-middle', tdClass: 'col-text', colWidth: '45%'},
                    {label: this.$t('table.num-day'), key: 'numDay', class: 'text-center align-middle', colWidth: '10%'},
                    {label: this.$t('table.price'), key: 'price', class: 'text-center align-middle', colWidth: '15%'},
                    {label: this.$t('table.created-at'), key: 'createdAt', class: 'text-center align-middle', colWidth: '10%'},
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
                    text: 'Cấu hình gói đẩy sản phẩm',
                    to: { name: 'config.package-push-product' }
                }
            ]);
            //this.getData();
            this.$store.commit('updateFilterValueState', {
                q: this.$route.query.q,
            });
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
        }
    }
</script>

<style scoped>

</style>
