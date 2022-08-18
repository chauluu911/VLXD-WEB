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
                                style="background: white !important;"
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
                                <router-link :to="{name: 'product-category', query: {parentCategoryId: data.item.id, level: parseInt(level) + 1}}">
                                    Xem danh sách
                                </router-link>
                            </span>
                        </template>
                        <template v-slot:cell(action)="data">
                            <b-link v-if="table.current_page != 1 || data.index != 0"
                                class="text-primary mx-1 cursor-pointer"
                                :disabled="processing"
                                v-b-tooltip.hover :title="$t('common.tooltip.display_front')"
                                @click="changePosition(data.item, 'up')"
                            >
                                <i class="fas fa-arrow-up"/>
                            </b-link>

                            <b-link v-if="table.current_page != table.last_page ||
                            (table.total % parseInt(table.per_page) - 1 != data.index && data.index !== parseInt(table.per_page) - 1)"
                                    class="text-primary mx-1 cursor-pointer"
                                    :disabled="processing"
                                    v-b-tooltip.hover :title="$t('common.tooltip.display_behind')"
                                    @click="changePosition(data.item, 'down')"
                            >
                                <i class="fas fa-arrow-down"/>
                            </b-link>
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
            :size="level != 1 ? 'sm' : 'xl'"
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
                        <b-col :md="level != 1 ? 48 : 12">
                            <b-form-group
                                :label="$t('modal.label.category-name')"
                                :label-class="'required'"
                                :state="!!modalData.errors.category"
                                :invalid-feedback="modalData.errors.category && modalData.errors.category.name && modalData.errors.category.name[0]"
                                v-if="level == 1"
                            >
                                <b-form-input
                                    class="w-100"
                                    v-model.trim="modalData.name"
                                    required
                                    :placeholder="$t('modal.placeholder.category-name')"
                                    :disabled="processing"
                                    :state="modalData.errors.category && modalData.errors.category.name && !modalData.errors.category.name[0]"
                                />
                            </b-form-group>
                            <b-form-group
                                :label="$t('modal.label.child-category')"
                                :state="!!modalData.errors.name"
                                :invalid-feedback="modalData.errors.name && modalData.errors.name"
                                :label-class="'required'"
                                v-else
                            >
                                <b-form-input
                                    class="w-100"
                                    v-model="modalData.name"
                                    required
                                    :placeholder="$t('modal.placeholder.child-category')"
                                    :disabled="processing"
                                    :state="modalData.errors.name && !modalData.errors.name"
                                />
                            </b-form-group>
                        </b-col>
                        <b-col :cols="level == 1 ? 48 : 6">
                            <div style="position: relative;">
                                <p class="mb-2">
                                    Logo
                                </p>
                                <label for="avatar" style="cursor: pointer;">
                                    <b-avatar square variant="primary" v-if="selectedFile == null" :src="modalData.image" size="50" text="LG"/>
                                    <span v-else id="resource"></span>
                                </label>
                                <input type="file" ref="fileInputEl" id="avatar" hidden="" @change="onSelectLogo">
                                <span
                                    :tooltip="{}" :title="$t('common.tooltip.delete')"
                                    class="position-absolute cursor-pointer"
                                    v-if="selectedFile != null" @click="removeAvatar"
                                    style="left: 40px; color: white; bottom: 39px;"
                                >
                                    <i class="fas fa-times"/>
                                </span>
                            </div>
                            <span
                                class="invalid-feedback d-block"
                                v-if="modalData.errors.category && modalData.errors.category.image &&
                                modalData.errors.category.image[0]"
                            >
                               {{modalData.errors.category.image[0]}}
                            </span>
                        </b-col>
                    </b-row>
                    <b-row class="mb-2" v-if="level == 1">
                        <b-col md="48">
                            <span style="font-weight: 800">{{$t('attribute')}}</span>
                        </b-col>
                    </b-row>
                    <b-row
                        class="border position-relative mb-4 mx-0 pt-2"
                        v-for="(item, index) in modalData.attribute"
                        :key="index + genKey"
                        v-if="item.status == EStatus.ACTIVE && level == 1"
                    >
                        <div
                            class="cursor-pointer position-absolute bg-white font-weight-bold" style="top: -13px; right: -9px"
                            v-b-tooltip.hover :title="$t('common.tooltip.delete')"
                            @click="removeAttribute(index)"
                        >
                            <i class="text-danger fas fa-times" style="font-size: 25px"></i>
                        </div>
                        <b-col md="10">
                            <b-form-group :label="$t('modal.label.attribute-name')" :state="!!modalData.errors.attribute" :invalid-feedback="modalData.errors.attribute && modalData.errors.attribute[index] && modalData.errors.attribute[index].attributeName && modalData.errors.attribute[index].attributeName[0]">
                                <b-form-input
                                    class="w-100"
                                    v-model.trim="item.attributeName"
                                    required
                                    :placeholder="$t('modal.placeholder.attribute-name')"
                                    :disabled="processing"
                                    :state="modalData.errors.attribute && modalData.errors.attribute[index] && modalData.errors.attribute[index].attributeName && !modalData.errors.attribute[index].attributeName[0]"
                                />
                            </b-form-group>
                        </b-col>
                        <b-col md="10">
                            <b-form-group :label="$t('modal.label.value-type')">
                                <b-form-select
                                    v-model="item.valueType"
                                    :options="listValueType"
                                    required
                                    :disabled="processing"
                                />
                            </b-form-group>
                        </b-col>
                        <b-col md="10">
                            <b-form-group :label="$t('modal.label.data-type')">
                                <b-form-select
                                    v-model="item.dataType"
                                    :options="listDataType"
                                    required
                                    :disabled="processing"
                                />
                            </b-form-group>
                        </b-col>
                        <b-col md="10" :class="item.valueType != EValueType.SINGLE ? 'd-block' : 'd-none'">
                            <b-form-group
                                :label="$t('modal.label.value')"
                                :state="!!modalData.errors.attribute"
                                :invalid-feedback="modalData.errors.attribute && modalData.errors.attribute[index] && modalData.errors.attribute[index].valueName && modalData.errors.attribute[index].valueName[0]">
                                <b-input-group>
                                    <b-form-input
                                        v-on:keyup.13="addValue(index)"
                                        v-model.trim="item.value"
                                        required
                                        :placeholder="$t('modal.placeholder.value')"
                                        :disabled="processing"
                                        :state="modalData.errors.attribute && modalData.errors.attribute[index] && modalData.errors.attribute[index].valueName && !modalData.errors.attribute[index].valueName[0]"
                                    />
                                    <b-input-group-append>
                                        <b-button variant="info" @click="addValue(index)">
                                            {{$t('add')}}
                                        </b-button>
                                    </b-input-group-append>
                                </b-input-group>
                                <div v-if="modalData.errors.attribute && modalData.errors.attribute[index] && modalData.errors.attribute[index].valueName && modalData.errors.attribute[index].valueName[0]" tabindex="-1" role="alert" aria-live="assertive" aria-atomic="true" class="invalid-feedback d-block">{{modalData.errors.attribute[index].valueName[0]}}</div>
                                <p class="mb-0 mt-1" v-for="(item, indexValue) in item.valueName">
                                    <span>
                                        + {{item}}
                                    </span>
                                    <i
                                        class="text-danger float-right cursor-pointer fas fa-times"
                                        style="line-height: 1.5"
                                        v-b-tooltip.hover :title="$t('common.tooltip.delete')"
                                        @click="removeValue(index, indexValue)"
                                    ></i>
                                </p>
                            </b-form-group>
                        </b-col>
                        <b-col md="8" :class="item.valueType != EValueType.SINGLE ? 'd-block' : 'd-none'">
                            <b-form-group label="Cho phép tìm kiếm">
                                <b-form-select
                                    v-model="item.enableFilter"
                                    :options="listFilter"
                                    required
                                    :disabled="processing"
                                />
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row class=mt-3 v-if="level == 1">
                        <b-col md="48" class="d-flex justify-content-center">
                            <b-button
                                variant="primary"
                                @click="addAttribute"
                            >
                                {{$t('modal.btn.add-attribute')}}
                            </b-button>
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
                categoryType: parseInt(ECategoryType.PRODUCT_CATEGORY),
                parentCategoryId: this.$route.query.parentCategoryId,
                childCategoryName: this.$route.query.name,
                modalData: this.$_formData(),
                showEditModal: false,
                processing: false,
                selectedFile: null,
                init: false,

                ECategoryType,
                EDataType,
                EValueType,
                EStatus,
                genKey: 1,
                level: this.$route.query.level || 1,
                breadcrumb: [
                    {
                        text: this.$t('category-list'),
                        to: { name: 'product-category.list', query: {'parentCategoryId': null, 'level': 1}},
                    },
                ],
                parentCategory: null
            }
        },
        computed: {
            ...mapState(['queryFilterState']),
            fields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'text-center align-middle', colWidth: '5%'},
                    {label: this.$t('table.logo'), key: 'image', class: 'text-center align-middle', colWidth: '5%'},
                    {label: this.$t('table.name'), key: 'name', thClass: 'text-center align-middle', tdClass: 'col-text'},
                    ...(
                        this.level != 3
                            ? [
                                {label: this.$t('table.child-category'), key: 'childCategory', thClass: 'text-center align-middle', tdClass: 'col-text'},
                        ]
                        : []
                    ),
                    {label: this.$t('table.option'), key: 'action', class: 'text-center align-middle', colWidth: '15%'},
                ];
            },
            defaultFilter() {
                return {
                    type: this.categoryType,
                    parentCategoryId: this.parentCategoryId,
                    parentCategory: null,
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
            this.$store.commit('updateBreadcrumbsState', this.breadcrumb);
            this.$store.commit('updateFilterFormState', []);
            this.$store.commit('updateFilterValueState', {
                q: this.$route.query.q,
                parentCategoryId : this.$route.query.parentCategoryId,
                level: this.$route.query.level || 1,
            });
            this.$store.commit('updateQueryFilterState', {
                enable: true,
                placeholder: this.$t('name_filter'),
            });
            // this.getListData({page: this.$route.query.page || 1, size: this.sz});
        },
        beforeRouteUpdate(to, from, next) {
            this.categoryType = parseInt(ECategoryType.PRODUCT_CATEGORY);
            this.parentCategoryId = to.query.parentCategoryId;
            this.level = to.query.level;
            this.$store.commit('updateFilterValueState', {
                q: to.query.q,
                parentCategoryId : to.query.parentCategoryId,
                level: to.query.level
            });
            this.pagination = {
                page: 1,
                pageSize: 10,
            }
            next();
        },
        watch: {
            "breadcrumb"() {
                this.$store.commit('updateBreadcrumbsState', this.breadcrumb);
            },
        },

        methods: {
            $_formData() {
                return {
                    id: null,
                    name: null,
                    image: null,
                    type: ECategoryType.PRODUCT_CATEGORY,
                    parentCategoryId: this.$route.query.parentCategoryId,
                    attribute: [
                        this.$_attribute(),
                    ],
                    errors: {
                        category: null,
                        attribute: null
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
                if (data.parentCategory != null) {
                    this.parentCategory = data.parentCategory;
                    this.breadcrumb = [
                        {
                            text: this.$t('category-list'),
                            to: { name: 'product-category.list', query: {'parentCategoryId': null, level: 1}},
                        },
                        {
                            text: this.parentCategory.name,
                            to: { name: 'product-category.list', query: {'parentCategoryId': this.parentCategory.id, 'level': 2}},
                        },
                        {
                            text: data.currentCategory.name,
                            to: { name: 'product-category.list', query: {'parentCategoryId': data.currentCategory.id, 'level': this.level}},
                        }
                    ]
                } else if (data.currentCategory != null) {
                    this.breadcrumb = [
                        {
                            text: this.$t('category-list'),
                            to: { name: 'product-category.list', query: {'parentCategoryId': null, level: 1}},
                        },
                        {
                            text: data.currentCategory.name,
                            to: { name: 'product-category.list', query: {'parentCategoryId': data.currentCategory.id, 'level': this.level}},
                        }
                    ]
                } else {
                    this.breadcrumb = [
                            {
                                text: this.$t('category-list'),
                                to: { name: 'product-category.list', query: {'parentCategoryId': null, level: 1}},
                            },
                        ];
                }
                this.pagination = {page: paging.page, size: paging.size};
            },
            showModal(item) {
                this.selectedFile = null;
                this.genKey ++;
                this.showEditModal = true;
                this.modalData = this.$_formData();
                if (!item) {
                    this.removeAttribute(0);
                    return
                }
                this.genKey ++;
                this.modalData.id = item.id;
                this.modalData.name = item.name;
                this.modalData.image = item.image;
                if (item.attribute.length > 0) {
                    this.modalData.attribute = [];
                    Object.keys(item.attribute).forEach((index) => {
                        this.modalData.attribute.push({
                            id: item.attribute[index].id,
                            attributeName: item.attribute[index].attribute_name,
                            value: null,
                            valueName: item.attribute[index].valueName,
                            enableFilter: item.attribute[index].enable_filter,
                            dataType: item.attribute[index].data_type,
                            valueType: item.attribute[index].value_type,
                            status: item.attribute[index].status
                        });
                    });
                } else {
                    this.removeAttribute(0);
                }
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
                        case 'image':
                            if (this.selectedFile != null) {
                                formData.append(key, this.selectedFile);
                            }
                            break;
                        case 'attribute':
                            Object.keys(this.modalData[key]).forEach((index) => {
                                formData.append(`attribute${index}[attributeName]`, this.modalData[key][index].attributeName ? this.modalData[key][index].attributeName : '');
                                formData.append(`attribute${index}[dataType]`, this.modalData[key][index].dataType);
                                formData.append(`attribute${index}[enableFilter]`, this.modalData[key][index].enableFilter);
                                // formData.append(`attribute${index}[type]`, this.modalData[key][index].type);
                                formData.append(`attribute${index}[valueName]`, this.modalData[key][index].valueName);
                                formData.append(`attribute${index}[valueType]`, this.modalData[key][index].valueType);
                                formData.append(`attribute${index}[status]`, this.modalData[key][index].status);
                                formData.append(`attribute${index}[id]`, this.modalData[key][index].id ? this.modalData[key][index].id : '');
                                formData.append('numberOfAttribute', parseInt(index) + 1);
                            });
                            break;
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
                    this.getListData (this.pagination);
                })
                .always(() => {
                    this.processing = false;
                    this.Util.loadingScreen.hide();
                });
            },
            changePosition(item, position) {
                this.Util.loadingScreen.show();
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/change-position`,
                    data: {
                        'id': item.id,
                        'position': position,
                        'parentCategoryId': this.$route.query.parentCategoryId,
                        'type': parseInt(ECategoryType.PRODUCT_CATEGORY),
                    },
                }).done(response => {
                    if (response.error == EErrorCode.ERROR) {
                        this.modalData.errors = response.msg;
                        return false;
                    }
                    this.Util.showMsg2(response);
                    this.getListData({page: 1, size: this.sz});
                })
                .always(() => {
                    this.Util.loadingScreen.hide();
                });
            },
            onSelectLogo(e) {
                $("#resource").text('');
                if (window.File && window.FileList && window.FileReader) {
                    let fileReader = new FileReader();
                    let files = e.target.files;
                    this.selectedFile = files[0];
                    fileReader.onload = (function(e) {
                        $("#resource").append("<img alt='avatar' width='50px' height='50px' src=\"" + e.target.result + "\"  />");
                    });
                    fileReader.readAsDataURL(this.selectedFile);
                }
            },
            removeAvatar() {
                this.selectedFile = null;
                this.$refs.fileInputEl.value = '';
            },
            addAttribute() {
                this.modalData.errors.attribute = null;
                this.modalData.attribute.push(this.$_attribute());
            },
            addValue(index) {
                if (!this.modalData.attribute[index].value) {
                    return;
                }
                this.modalData.attribute[index].valueName.push(this.modalData.attribute[index].value);
                this.modalData.attribute[index].value = null;
            },
            removeValue(index, indexValue) {
                this.modalData.attribute[index].valueName.splice(indexValue, 1);
            },
            removeAttribute(index) {
                if (this.modalData.attribute[index].id) {
                    this.modalData.attribute[index].status = EStatus.DELETED;
                    return false
                }
                this.modalData.attribute.splice(index, 1);
            }
        }
    }
</script>
