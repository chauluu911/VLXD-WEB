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
                        <template #cell(image)="data">
                            <b-avatar
                                square
                                variant="primary"
                                :src="data.item.image"
                                size="50"
                                text="LG"
                            ></b-avatar>
                        </template>
                        <template #cell(name)="data">
                            <div :title="data.item.name" v-shave="{height: 66}">
                                {{ data.item.name }}
                            </div>
                        </template>
                        <template #cell(childCategory)="data">
                            <span>
                                <span v-shave="{height: 30, character: '...'}">
                                    {{data.item.childCategory}}
                                </span>
                                <router-link :to="{name: 'issue-report', query: {parentCategoryId: data.item.id, name: data.item.name}}">
                                    Xem danh sách
                                </router-link>
                            </span>
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
        <b-modal
            size="sm"
            v-model="showEditModal"
            no-close-on-esc no-close-on-backdrop
            busy:hide-header-close="processing"
            :title="$t(modalData.id ? 'edit' : 'create')"
            header-class=""
            @hidden=""
        >
            <template v-slot:default>
                <b-form>
                    <b-row class="mb-3">
                        <b-col :md="48">
                            <b-form-group
                            :label="$t('modal.label.category-name')"
                            :state="!!modalData.errors.name"
                            :invalid-feedback="modalData.errors.category && modalData.errors.category.name[0]"
                            :label-class="'required'"
                            v-if="!parentCategoryId"
                            >
                                <b-form-input
                                    class="w-100"
                                    v-model="modalData.name"
                                    required
                                    :placeholder="$t('modal.placeholder.category-name')"
                                    :disabled="processing"
                                    :state="modalData.errors.category && !modalData.errors.category.name[0]"
                                    v-on:keyup.13=""
                                />
                            </b-form-group>
                            <b-form-group
                            :label="$t('modal.label.child-category')"
                            :state="!!modalData.errors.name"
                            :invalid-feedback="modalData.errors.name && modalData.errors.name[0]"
                            :label-class="'required'"
                            v-else
                            >
                                <b-form-input
                                    class="w-100"
                                    v-model="modalData.name"
                                    required
                                    :placeholder="$t('modal.placeholder.child-category')"
                                    :disabled="processing"
                                    :state="modalData.errors.name && !modalData.errors.name[0]"
                                    v-on:keyup.13=""
                                />
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-form>
            </template>
            <template v-slot:modal-footer="{ hide }">
                <b-button variant="outline-primary" :disabled="processing" @click="hide('forget')">{{ $t('common.button.cancel') }}</b-button>
                <a-button class="btn-primary" :loading="processing" @click="saveCategory">Lưu</a-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
    import ECategoryType from "../../../constants/category-type";
    import categoryListMessages from '../../../locales/back/category-management-list';
    import FDownloadIcon from 'vue-feather-icons/icons/DownloadIcon'
    import FPlusIcon from 'vue-feather-icons/icons/PlusIcon'
    import EErrorCode from "../../../constants/error-code";
    import {mapState} from "vuex";
    import listMixin from "../../mixins/list-mixin";
    import EDataType from "../../../constants/category-attribute-type";
    import EValueType from "../../../constants/category-attribute-value-type";
    import EStatus from "../../../constants/status";
    import CreatingForm from "./CreatingForm";

    export default {
        name: 'CategoryList',
        i18n: {
            messages: categoryListMessages,
        },
        mixins: [listMixin],
        components: {
            FDownloadIcon,
            FPlusIcon,
            CreatingForm,
        },
        data() {
            return {
                categoryType: parseInt(ECategoryType.ISSUE_REPORT),
                parentCategoryId: this.$route.query.parentCategoryId,
                childCategoryName: this.$route.query.name,
                modalData: this.$_formData(),
                showEditModal: false,
                processing: false,
                selectedFile: null,
                init: false,
                breadcrumb: [
                    {
                        text: 'Danh mục báo tin sai',
                        to: { name: 'issue-report.list', query: {'parentCategoryId': null}},
                    },
                ],

                ECategoryType,
                EDataType,
                EValueType,
                EStatus,
                genKey: 1,
            }
        },
        watch: {
            "breadcrumb"() {
                this.$store.commit('updateBreadcrumbsState', this.breadcrumb);
            },
        },
        computed: {
            ...mapState(['queryFilterState']),
            fields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'text-center align-middle', colWidth: '5%'},
                    {label: this.$t('table.name'), key: 'name', thClass: 'text-center align-middle', tdClass: 'col-text'},
                    ...(
                        this.parentCategoryId == null || this.parentCategoryId == ''
                            ? [
                                {label: this.$t('table.child-category'), key: 'childCategory', thClass: 'text-center align-middle', tdClass: 'col-text'},
                            ]
                            : []
                    ),
                    {label: this.$t('table.option'), key: 'action', class: 'text-center align-middle', colWidth: '10%'},
                ];
            },
            defaultFilter() {
                return {
                    type: this.categoryType,
                    parentCategoryId: this.parentCategoryId,
                }
            },
            attributeList() {
                return {
                    pageSize: 15,
                    categoryId: this.modalData.id ? this.modalData.id : null,
                };
            },
            listDataType() {
                return [
                    {
                        value: EDataType.NUMBER,
                        text: 'Số',
                    },
                    {
                        value: EDataType.TEXT,
                        text: 'Chử',
                    },
                    {
                        value: EDataType.DATE,
                        text: 'Ngày tháng',
                    },
                ]
            },
            listValueType() {
                return [
                    {
                        value: EValueType.SINGLE,
                        text: 'Nhập',
                    },
                    {
                        value: EValueType.SELECT,
                        text: 'Chọn',
                    },
                ]
            },
            listFilter() {
                return [
                    {
                        value: true,
                        text: 'Cho phép',
                    },
                    {
                        value: false,
                        text: 'không cho phép',
                    },
                ]
            }
        },
        created() {
            this.$store.commit('updateFilterFormState', []);
            this.$store.commit('updateFilterValueState', {
                q: this.$route.query.q,
                parentCategoryId: this.$route.query.parentCategoryId,
                name: this.$route.query.name,
            });
            this.$store.commit('updateQueryFilterState', {
                enable: true,
                placeholder: this.$t('name_filter'),
            });
            this.$store.commit('updateBreadcrumbsState', this.breadcrumb);
        },
        beforeRouteUpdate(to, from, next) {
            this.categoryType = parseInt(ECategoryType.ISSUE_REPORT);
            this.parentCategoryId = to.query.parentCategoryId;
            // this.getListData(this.pagination);
            this.$store.commit('updateFilterValueState', {
                q: to.query.q,
                parentCategoryId: to.query.parentCategoryId,
                name: to.query.name,
            });
            next();
        },
        methods: {
            $_formData() {
                return {
                    id: null,
                    name: null,
                    type: ECategoryType.ISSUE_REPORT,
                    parentCategoryId: this.$route.query.parentCategoryId,
                    errors: {
                        category: null,
                        attribute: null,
                        name: null,
                    }
                }
            },
            $_attribute() {
                return {
                    id: null,
                    attributeName: null,
                    value: null,
                    valueName: [],
                    enableFilter: false,
                    dataType: EDataType.TEXT,
                    valueType: EValueType.SINGLE,
                    status: EStatus.ACTIVE
                }
            },
            onListDataFetchSuccess(paging, data) {
                this.table = data.categoryList;
                this.pagination = {page: paging.page, size: paging.size};
                if(data.currentCategory != null) {
                    this.breadcrumb = [
                        {
                            text: 'Danh mục báo tin sai',
                            to: { name: 'issue-report.list', query: {'parentCategoryId': null}},
                        },
                        {
                            text: data.currentCategory.name,
                            to: { name: 'issue-report.list', query: {'parentCategoryId': data.currentCategory.id}},
                        }
                    ]
                } else {
                    this.breadcrumb = [
                        {
                            text: 'Danh mục báo tin sai',
                            to: { name: 'issue-report.list', query: {'parentCategoryId': null}},
                        },
                    ];
                }
            },
            showModal(item) {
                this.selectedFile = null;
                this.genKey ++;
                this.showEditModal = true;
                this.modalData = this.$_formData();
                if (!item) {
                    return
                }
                this.genKey ++;
                this.modalData.id = item.id;
                this.modalData.name = item.name;
            },
            async saveCategory() {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm(this.modalData.id ? this.$t('confirm.edit') : this.$t('confirm.create'), resolve);
                });
                if (!confirm) {
                    return;
                }

                this.Util.loadingScreen.show();
                this.processing = true;
                let formData = new FormData();
                Object.keys(this.modalData).forEach((key) => {
                    switch (key) {
                        default:
                            if (this.modalData[key] == null) {
                                this.modalData[key] = ''
                            }
                            formData.append(key, this.modalData[key]);
                            break;
                    }
                });
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/save`,
                    data: formData,
                    //errorModel: this.modalData.errors,
                    processData: false,
                    contentType: false,
                }).done(response => {
                    if (response.error == EErrorCode.ERROR) {
                        this.modalData.errors = response.errors;
                        return false;
                    }
                    this.showEditModal = false;
                    this.Util.showMsg2(response)
                    this.getListData(this.pagination);
                })
                .always(() => {
                    this.processing = false;
                    this.Util.loadingScreen.hide();
                });
            },
        }
    }
</script>
