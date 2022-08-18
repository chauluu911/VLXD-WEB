<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <b-row class="mb-3">
                       <!--  <b-col style="min-width: 400px;">
                            <div class="d-inline-block h3 mr-4 text-primary">
                                {{ $t(parentCategoryId ? 'child-category-list' : 'category-list').toUpperCase() }}
                            </div>
                        </b-col> -->
                        <b-col class="text-right" style="min-width: 300px;">
                            <a-button
                                :icon-class="['mr-lg-2']"
                                variant="outline-primary"
                                class="mr-2"
                                @click="showModal()"
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
                        <template v-slot:cell(description)="data">
                            <div
                                v-html="data.item.description"
                                v-shave="{height: 22}"
                            />
                        </template>
                        <template v-slot:cell(action)="data">
                            <b-link
                                class="text-primary mx-1"
                                :disabled="processing"
                                v-b-tooltip.hover :title="$t('common.tooltip.edit')"
                                @click="showModal(data.item)"
                            >
                                <i class="fas fa-edit"/>
                            </b-link>
                            <a-button-delete
                                class="text-primary mx-1"
                                @click="deleteItem(data.item)"
                                :disabled="deleting[`${data.item.id}`] || !data.item.editable"
                                :deleting="deleting[`${data.item.id}`]"
                                tooltip :title="$t(data.item.editable ? 'common.tooltip.delete' : 'tooltip.not_editable')"
                            />
                        </template>
                    </a-table>
                    <paging @page-change="onPageChangeHandler" :total="table.total" :disabled="loading" ref="pagingEl"/>
                </div>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import productListMessages from '../../locales/back/product/list';
    import FPlusIcon from 'vue-feather-icons/icons/PlusIcon'
    import EErrorCode from "../../constants/error-code";
    import {mapState} from "vuex";
    import listMixin from "../mixins/list-mixin";

    export default {
        name: 'ProductList',
        i18n: {
            messages: productListMessages,
        },
        mixins: [listMixin],
        components: {
            FPlusIcon,
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
                     // {label: this.$t('table.image'), key: 'image', class: 'text-center align-middle', colWidth: '5%'},
                     {label: this.$t('table.code'), key: 'code', thClass: 'text-center align-middle', tdClass: 'col-text'},
                    {label: this.$t('table.name'), key: 'name', thClass: 'text-center align-middle', tdClass: 'col-text'},
                    {label: this.$t('table.description'), key: 'description', thClass: 'text-center align-middle', tdClass: 'col-text'},
                    {label: this.$t('table.price'), key: 'price', thClass: 'text-center align-middle', tdClass: 'col-text'},
                    // {label: this.$t('table.weight'), key: 'weight', thClass: 'text-center align-middle', tdClass: 'col-text'},
                    // {label: this.$t('table.unit'), key: 'unit', thClass: 'text-center align-middle', tdClass: 'col-text'},
                    {label: this.$t('table.created-at'), key: 'createdAt', thClass: 'text-center align-middle', tdClass: 'col-text'},
                    {label: this.$t('table.status'), key: 'status', thClass: 'text-center align-middle', tdClass: 'col-text'},
                    {label: this.$t('table.option'), key: 'action', class: 'text-center align-middle', colWidth: '15%'},
                ];
            },
            // defaultFilter() {
            //     return {
            //         type: this.categoryType,
            //         parentCategoryId: this.parentCategoryId,
            //     }
            // },
        },
        created() {
            this.$store.commit('updateBreadcrumbsState', [
                {
                    text: this.$t('product-list'),
                    to: { name: 'product.list'},
                },
            ]);
            this.$store.commit('updateFilterFormState', []);
            this.$store.commit('updateFilterValueState', {
                q: this.$route.query.q,
            });
            this.$store.commit('updateQueryFilterState', {
                enable: true,
                placeholder: this.$t('name_filter'),
            });
        },
        // beforeRouteUpdate(to, from, next) {
        //     this.categoryType = parseInt(ECategoryType.PRODUCT_CATEGORY);
        //     this.parentCategoryId = to.query.parentCategoryId;
        //     this.getListData({
        //         page: 1,
        //         size: this.sz,
        //     });
        //     next();
        // },
        methods: {
            
        }
    }
</script>
