<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <b-row>
                        <b-col cols="48">
                            <button
                                v-if="selectedItems.length"
                                class="float-right ml-2 btn mb-2 btn-primary"
                                :disabled="processing"
                                @click="approveBanners()" ref="btnApprove"
                            >
                                Duyệt
                            </button>
                            <button
                                v-if="selectedItems.length"
                                class="float-right ml-2 btn mb-2 btn-danger"
                                :disabled="processing"
                                @click="rejectBanners()" ref="btnReject"
                            >
                                Từ chối
                            </button>
                            <b-button
                                variant="outline-primary"
                                class="float-right mb-3"
                                @click="showModalEdit()"
                            >
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
                                :fields="bannerFields"
                                :loading="loading"
                                :pagination="pagination"
                            >
                                <template #cell(image)="data">
                                    <b-avatar
                                        variant="primary"
                                        square
                                        :src="data.item.path_to_resource"
                                        size="50"
                                        class="bg-white"
                                    ></b-avatar>
                                </template>
                                <template #cell(creator)="data">
                                    <template v-if="data.item.creator">
                                        <div v-if="data.item.creator.type === EUserType.ADMIN">
                                            {{data.item.creator.name}}
                                        </div>
                                        <div v-else-if="data.item.creator.type === EUserType.INTERNAL_USER">
                                            <div class="text-info cursor-pointer" @click="showModalUserDetail(data.item)">
                                                <div>
                                                    {{data.item.creator.name}}
                                                </div>
                                                <div>
                                                    {{data.item.creator.id}}
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else>
                                            <div class="text-info cursor-pointer" @click="showModalShopDetail(data.item)">
                                                <div>
                                                    {{data.item.creator.name}}
                                                </div>
                                                <div>
                                                    {{data.item.creator.id}}
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                                 <template #cell(actionOnClick)="data">
                                    <p class="mb-0">{{data.item.actionOnClick}}</p>
                                    <b-link :href="data.item.action_on_click_target">{{data.item.action_on_click_target}}</b-link>
                                </template>
                                <template #cell(action)="data">
                                    <div class="float-left">
                                        <input v-if="data.item.status == EStatus.WAITING" type="checkbox" class="ml-1 mr-2" :class="{'d-none': processing}" :value="{id: data.item.id}" v-model="selectedItems" style="margin-top: 5px">
                                    </div>
                                    <b-link
                                        v-if="data.item.status !== EStatus.DELETED"
                                        :title="$t('common.tooltip.edit')"
                                        class="no-decoration"
                                        @click="showModalEdit(data.item)"
                                    >
                                        <i class="text-primary fas fa-edit"/>
                                    </b-link>
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
            size="lg"
            v-model="showModal"
            no-close-on-esc no-close-on-backdrop
            busy:hide-header-close="processing"
            :title="formData.id ? $t('edit') : $t('create')"
            header-class=""
            @hidden=""
        >
            <template v-slot:default>
                <b-form>
                    <b-row class="my-2">
                        <b-col md="48">
                            <b-form>
                                <b-row class="px-5">
                                    <b-col md="18">
                                        <b-form-group class="font-weight-bold"
                                            :label-class="'required'"
                                            :label="$t('form.platform')"
                                            :state="!!formData.errors.platform"
                                            :invalid-feedback="formData.errors.platform && formData.errors.platform[0]"
                                        >
                                            <b-form-select
                                                @input="changePlatForm"
                                                v-model="formData.value.platform"
                                                :options="platformList"
                                                :state="formData.errors.platform && !formData.errors.platform[0]"
                                            ></b-form-select>
                                        </b-form-group>
                                    </b-col>
                                </b-row>
                                <b-row class="px-5">
                                    <b-col md="18">
                                        <b-form-group class="font-weight-bold"
                                            :label-class="'required'"
                                            :label="$t('form.banner-type')"
                                            :state="!!formData.errors.type"
                                            :invalid-feedback="formData.errors.type && formData.errors.type[0]"
                                        >
                                            <b-form-select
                                                @input="changeType"
                                                v-model="formData.value.type"
                                                :options="typeList"
                                                :state="formData.errors.type && !formData.errors.type[0]"
                                            ></b-form-select>
                                        </b-form-group>
                                    </b-col>
                                </b-row>
                                <b-row class="px-5">
                                    <b-col md="18">
                                        <b-form-group class="font-weight-bold"
                                            :label-class="'required'"
                                            :label="$t('form.action-click')"
                                        >
                                            <b-form-select
                                                v-model="formData.value.actionType"
                                                :options="actionTypeList"
                                            ></b-form-select>
                                        </b-form-group>
                                    </b-col>
                                    <b-col md="18" v-if="formData.value.actionType == EBannerActionType.OPEN_WEBSITE">
                                        <b-form-group class="font-weight-bold"
                                            :label-class="'required'"
                                            :label="$t('form.link')"
                                            :state="!!formData.errors.link"
                                            :invalid-feedback="formData.errors.link && formData.errors.link[0]">
                                        <b-form-input class="w-100" v-model.trim="formData.value.link" required
                                            :state="formData.errors.link && !formData.errors.link[0]"
                                        />
                                    </b-form-group>
                                    </b-col>
                                </b-row>
                                <b-row>
                                    <b-col v-if="formData.value.url" md="48" class="d-flex justify-content-center">
                                        <b-button-group>
                                            <b-button
                                                v-if="formData.value.type != EBannerType.TRADEMARK && formData.value.platform != EPlatform.WEB"
                                                :variant="cropperAttribute.name == '16/9' ? 'info' : 'primary'"
                                                @click="changeValueAttribute('16/9', '1.77777777778')"
                                            >
                                                16:9
                                            </b-button>
                                            <b-button
                                                v-if="(formData.value.platform == EPlatform.WEB && formData.value.type != EBannerType.TRADEMARK) || (formData.value.type == EBannerType.TRADEMARK && formData.value.platform == EPlatform.MOBILE)"
                                                :variant="cropperAttribute.name == '16/5' ? 'info' : 'primary'"
                                                @click="changeValueAttribute('16/5', '3.2')"
                                            >
                                                16:5
                                            </b-button>
                                            <b-button
                                                v-if="formData.value.type == EBannerType.TRADEMARK && formData.value.platform != EPlatform.WEB_AND_MOBILE && formData.value.platform != EPlatform.MOBILE"
                                                :variant="cropperAttribute.name == '11/1' ? 'info' : 'primary'"
                                                @click="changeValueAttribute('11/1', '11')"
                                            >
                                                11:1
                                            </b-button>
                                            <template v-if="formData.value.type == EBannerType.PROMOTION">
                                                <b-button
                                                    :variant="cropperAttribute.name == '4/3' ? 'info' : 'primary'"
                                                    @click="changeValueAttribute('4/3', '1.33333333333')"
                                                >
                                                    4:3
                                                </b-button>
                                                <b-button
                                                    :variant="cropperAttribute.name == '1/1' ? 'info' : 'primary'"
                                                    @click="changeValueAttribute('1/1', '1')"
                                                >
                                                    1:1
                                                </b-button>
                                                <b-button
                                                    :variant="cropperAttribute.name == '3/2' ? 'info' : 'primary'"
                                                    @click="changeValueAttribute('3/2', '1.5')"
                                                >
                                                    3:2
                                                </b-button>
                                                <b-button
                                                    :variant="cropperAttribute.name == '9/16' ? 'info' : 'primary'"
                                                    @click="changeValueAttribute('9/16', '0.5625')"
                                                >
                                                    9:16
                                                </b-button>
                                                <b-button
                                                    :variant="cropperAttribute.name == '3/4' ? 'info' : 'primary'"
                                                    @click="changeValueAttribute('3/4', '0.75')"
                                                >
                                                    3:4
                                                </b-button>
                                                <b-button
                                                    :variant="cropperAttribute.name == '2/3' ? 'info' : 'primary'"
                                                    @click="changeValueAttribute('2/3', '0.66666666666')"
                                                >
                                                    2:3
                                                </b-button>
                                            </template>
                                        </b-button-group>
                                    </b-col>
                                    <b-col md="48" class="d-flex justify-content-center mt-2">
                                        <span v-if="formData.errors.ratio != null" style="color: #dc3545; font-weight: 700; font-size: 12px">{{formData.errors.ratio[0]}}</span>
                                    </b-col>
                                    <b-col md="48">
                                        <div class="px-5">
                                            <cropper-image
                                                ref="imageCropperEl"
                                                @cropper-created="onCropperCreated"
                                                @cropper-reset="resetCropper"
                                                @crop-image="cropImageData"
                                                :image-url="formData.value.url"
                                                :aspect-ratios="[{name: cropperAttribute.name, value: cropperAttribute.value}]"
                                                :disable-size="true">
                                            <template #text-drop-image>
                                                <div
                                                    class="p-5"
                                                >
                                                    {{ $t('common.button.upload') }}
                                                </div>
                                            </template>
                                            </cropper-image>
                                            <span class="text-danger mt-3 mb-0" v-if="formData.errors.file">{{formData.errors.file[0]}}</span>
                                        </div>
                                    </b-col>
                                </b-row>
                            </b-form>
                        </b-col>
                    </b-row>
                </b-form>
            </template>
            <template v-slot:modal-footer="{ hide }">
                <b-button variant="outline-danger" :disabled="processing" @click="hide('forget')">
                    {{ $t('common.button.cancel') }}
                </b-button>
                <b-button variant="primary" :disabled="processing" @click="saveBanner">
                    {{ $t('common.button.save') }}
                </b-button>
            </template>
        </b-modal>
        <b-modal
            size="md"
            v-model="isShowModalUserDetail"
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
                                        {{$t('user_detail_modal.avatar')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        <b-avatar
                                            variant="primary"
                                            :src="userItem.creator.avatar"
                                            size="30"
                                            class="bg-white"
                                        ></b-avatar>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left w-25 p-1">
                                        {{$t('user_detail_modal.code')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        {{ userItem.creator.id }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left w-25 p-1">
                                        {{$t('user_detail_modal.name')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        {{ userItem.creator.name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left w-25 p-1">
                                        {{$t('user_detail_modal.phone')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        {{ userItem.creator.phone }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left w-25 p-1">
                                        {{$t('user_detail_modal.email')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        {{userItem.creator.email}}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left w-25 p-1">
                                        {{$t('user_detail_modal.address')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        {{userItem.creator.address}}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left w-25 p-1">
                                        {{$t('user_detail_modal.dob')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        {{userItem.creator.dob}}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left w-25 p-1">
                                        {{$t('user_detail_modal.status')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        {{userItem.creator.strStatus}}
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
        <b-modal
            size="md"
            v-model="isShowModalShopDetail"
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
                                        {{$t('shop_detail_modal.avatar')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        <b-avatar
                                            variant="primary"
                                            :src="shopItem.creator.avatar"
                                            size="30"
                                            class="bg-white"
                                        ></b-avatar>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left w-25 p-1">
                                        {{$t('shop_detail_modal.code')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        {{ shopItem.creator.id }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left w-25 p-1">
                                        {{$t('shop_detail_modal.name')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        {{ shopItem.creator.name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left w-25 p-1">
                                        {{$t('shop_detail_modal.phone')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        {{ shopItem.creator.phone }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left w-25 p-1">
                                        {{$t('shop_detail_modal.email')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        {{shopItem.creator.email}}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left w-25 p-1">
                                        {{$t('shop_detail_modal.address')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        {{shopItem.creator.address}}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left w-25 p-1">
                                        {{$t('shop_detail_modal.description')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        {{shopItem.creator.description}}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left w-25 p-1">
                                        {{$t('shop_detail_modal.created_at')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        {{shopItem.creator.created_at}}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left w-25 p-1">
                                        {{$t('shop_detail_modal.payment_status')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        {{shopItem.creator.strPaymentStatus}}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left w-25 p-1">
                                        {{$t('shop_detail_modal.status')}}
                                    </td>
                                    <td class="text-left w-75 p-1">
                                        {{shopItem.creator.strStatus}}
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
    import bannerListManage from "../../locales/back/config/banner";
    import EErrorCode from "../../constants/error-code";
    import EStatus from "../../constants/status";
    import EBannerType from "../../constants/banner-type";
    import EBannerActionType from "../../constants/banner-action-type";
    import EPlatform from "../../constants/platform";
    import CropperImage from '../../../js/components/ImageCropper';
    import EUserType from "../../constants/customer-type";
    import listMixin from "../mixins/list-mixin";

    export default {
        mixins: [listMixin],
        inject: ['Util', 'StringUtil', 'DateUtil'],
        i18n: {
            messages: bannerListManage
        },
        components: {
            CropperImage
        },
        data() {
            return {
                bannerList: [],
                loading: false,
                processing: false,
                selectedItems: [],
                deleting: {},
                EErrorCode,
                userItem: null,
                shopItem: null,
                EStatus,
                showModal: false,
                isShowModalUserDetail: false,
                isShowModalShopDetail: false,
                formData: this.$_formData(),
                EBannerType,
                EBannerActionType,
                EPlatform,
                EUserType,
                validate: {},
                // image: {
                //     url: null,
                //     selected: null,
                //     blob: null,
                // },
                cropperAttribute: {
                    name: null,
                    value: null
                },
            }
        },
        computed: {
            ...mapState(['filterValueState', 'queryFilterState']),
            bannerFields() {
                return [
                    {label: this.$t('table.column.no'), key: 'index', class: 'align-middle text-center', colWidth: '5%'},
                    {label: this.$t('table.image'), key: 'image', class: 'align-middle text-center', colWidth: '8%'},
                    {label: this.$t('table.type'), key: 'typeString', class: 'text-center align-middle', colWidth: '10%'},
                    {label: this.$t('table.platform'), key: 'platformString', class: 'text-center align-middle', colWidth: '8%'},
                    {label: this.$t('table.valid-from'), key: 'valid_from', class: 'text-center align-middle', colWidth: '8%'},
                    {label: this.$t('table.valid-to'), key: 'valid_to', class: 'text-center align-middle', colWidth: '8%'},
                    {label: this.$t('table.action-click'), key: 'actionOnClick', class: 'text-center align-middle', colWidth: '29%'},
                    {label: this.$t('table.creator'), key: 'creator', class: 'text-center align-middle', colWidth: '8%'},
                    {label: this.$t('table.status'), key: 'statusStr', class: 'text-center align-middle', colWidth: '8%'},
                    {label: this.$t('table.option'), key: 'action', class: 'text-center align-middle', colWidth: '8%'},
                ];
            },
            typeList() {
                return [
                    ...(
                        this.formData.value.platform != EPlatform.WEB_AND_MOBILE ?
                            [
                                ...(this.formData.value.platform != EPlatform.WEB ? [
                                    {
                                        value: EBannerType.SHOW_AS_POP_UP_AFTER_LOG_IN,
                                        text: this.$t('flash-screen'),
                                    },
                                ] : []),
                                {
                                    value: EBannerType.MAIN_BANNER_ON_HOME_SCREEN,
                                    text: this.$t('home'),
                                },
                                {
                                    value: EBannerType.TRADEMARK,
                                    text: 'Thương hiệu',
                                }
                            ] :
                            [
                                {
                                value: EBannerType.PROMOTION,
                                    text: this.$t('Promotion'),
                                },
                            ]
                    ),
                ]
            },
            actionTypeList() {
                return [
                    {
                        value: EBannerActionType.DO_NOTHING,
                        text: this.$t('do-nothing'),
                    },
                    {
                        value: EBannerActionType.OPEN_WEBSITE,
                        text: this.$t('open-web'),
                    },
                ]
            },
            platformList() {
                return [
                    {
                        value: EPlatform.WEB_AND_MOBILE,
                        text: this.$t('form.web-and-mobile'),
                    },
                    {
                        value: EPlatform.WEB,
                        text: this.$t('Web'),
                    },
                    {
                        value: EPlatform.MOBILE,
                        text: this.$t('Mobile'),
                    },
                ]
            }
        },
        watch: {
            // filterValueState(val) {
            //     window.history.pushState(null, null, this.StringUtil.getUrlWithQueries(window.location.pathname, {...val.value, page: this.pagination.page}));
            //     this.searchBanner({page: 1, size: this.sz});
            // },
        },
        created() {
            this.$store.commit('updateFilterValueState', {
                q: this.$route.query.q,
                type: null,
                status: null,
                platform: null,
            });
            // this.breadCrumb();
            // this.searchBanner({page: 1, size: this.sz});
            this.$store.commit('updateQueryFilterState', {
                enable: false,
            });
            this.$store.commit('updateFilterFormState', [
                {
                    label: 'Trạng thái',
                    type: 'select',
                    name: 'status',
                    options: [
                        {
                            name: 'Tất cả',
                            value: null,
                        },
                        {
                            name: this.$t('constant.status.pending'),
                            value: EStatus.WAITING,
                        },
                        {
                            name: this.$t('constant.status.active'),
                            value: EStatus.ACTIVE,
                        },
                    ]
                },
                {
                    label: this.$t('form.banner-type'),
                    type: 'select',
                    name: 'type',
                    options: [
                        {
                            value: null,
                            name: this.$t('constant.target_type.all'),
                        },
                        {
                            value: EBannerType.SHOW_AS_POP_UP_AFTER_LOG_IN,
                            name: this.$t('flash-screen'),
                        },
                        {
                            value: EBannerType.MAIN_BANNER_ON_HOME_SCREEN,
                            name: this.$t('home'),
                        },
                        {
                            value: EBannerType.PROMOTION,
                            name: this.$t('Promotion'),
                        },
                        {
                            value: EBannerType.TRADEMARK,
                            name: 'Thương hiệu',
                        }
                    ]
                },
                {
                    label: this.$t('table.platform'),
                    type: 'select',
                    name: 'platform',
                    options: [
                        {
                            value: null,
                            name: this.$t('constant.target_type.all'),
                        },
                        {
                            value: EPlatform.WEB_AND_MOBILE,
                            name: this.$t('form.web-and-mobile'),
                        },
                        {
                            value: EPlatform.WEB,
                            name: this.$t('Web'),
                        },
                        {
                            value: EPlatform.MOBILE,
                            name: this.$t('Mobile'),
                        },
                    ]
                }
            ]);
        },
        methods: {
            $_formData() {
                return {
                    value: {
                        id: null,
                        url: null,
                        file: null,
                        blob: null,
                        actionType: EBannerActionType.DO_NOTHING,
                        link: null,
                        type: EBannerType.SHOW_AS_POP_UP_AFTER_LOG_IN,
                        ratio: null,
                        platform: EBannerType.SHOW_AS_POP_UP_AFTER_LOG_IN ? EPlatform.MOBILE : EBannerType.PROMOTION ? EPlatform.WEB_AND_MOBILE : '',
                    },
                    errors: {
                        file: null,
                        actionType: null,
                        link: null,
                        type: null,
                        platform: null,
                        ratio: null
                    }
                }
            },
            breadCrumb() {
                this.$store.commit('updateBreadcrumbsState', [
                    {
                        text: 'Banner',
                        to: { name: 'employee.list' }
                    }
                ]);
            },
            onPageChangeHandler(paging) {
                this.getListData(paging);
            },
            // searchBanner(paging) {
            //     this.loading = true;
            //     let reqData = Object.assign({
            //         pageSize: paging.size || this.pagination.size,
            //         page: paging.page ? paging.page : 1,
            //         filter: {
            //             ...this.filterValueState.value,
            //         }
            //     });
            //     this.Util.post({
            //         url: `${this.$route.meta.baseUrl}/search`,
            //         data: reqData,
            //     }).done(response => {
            //         if (response.error == EErrorCode.ERROR) {
            //             this.Util.showMsg2(response);
            //             return false;
            //         }
            //         this.bannerList = response.banners;
            //         this.validate = response.validate;
            //         this.pagination = {page: paging.page, size: paging.size};
            //         if (!this.pagination.size) {
            //             this.pagination.size = this.bannerList.per_page
            //         }
            //         response.banners.data.forEach((item, index) => {
            //             if (item.valid_from) {
            //                 let date = new Date(item.valid_from);
            //                 try {
            //                     this.bannerList.data[index].valid_from = this.DateUtil.getDateString(date, '/', false);
            //                 } catch (e) {
            //                     console.log(e)
            //                 }
            //             }
            //             if (item.valid_to) {
            //                 let date = new Date(item.valid_to);
            //                 try {
            //                     this.bannerList.data[index].valid_to = this.DateUtil.getDateString(date, '/', false);
            //                 } catch (e) {
            //                     console.log(e)
            //                 }
            //             }
            //             if(item.creator.created_at) {
            //                 let date = new Date(item.creator.created_at);
            //                 try {
            //                     this.bannerList.data[index].creator.created_at = this.DateUtil.getDateString(date, '/', false);
            //                 } catch (e) {
            //                     console.log(e)
            //                 }
            //             }
            //         });
            //     })
            //     .always(() => {
            //         this.loading = false;
            //         this.Util.loadingScreen.hide();
            //     });
            // },
            onListDataFetchSuccess(paging, data) {
                this.table = data.banner;
                this.validate = data.validate;
                this.pagination = {page: paging.page, size: paging.size};
                this.table.data.forEach((item, index) => {
                    if (item.valid_from) {
                        let date = new Date(item.valid_from);
                        try {
                            this.table.data[index].valid_from = this.DateUtil.getDateString(date, '/', false);
                        } catch (e) {
                            console.log(e)
                        }
                    }
                    if (item.valid_to) {
                        let date = new Date(item.valid_to);
                        try {
                            this.table.data[index].valid_to = this.DateUtil.getDateString(date, '/', false);
                        } catch (e) {
                            console.log(e)
                        }
                    }
                    if(item.creator) {
                        let date = new Date(item.creator.created_at);
                        try {
                            this.table.data[index].creator.created_at = this.DateUtil.getDateString(date, '/', false);
                        } catch (e) {
                            console.log(e)
                        }
                    }
                });
            },
            showModalEdit(item) {
                this.formData = this.$_formData();
                this.showModal = true;
                if (!item) {
                    return
                }
                this.formData.value.platform = item.platform;
                this.formData.value.id = item.id;
                this.formData.value.link = item.action_on_click_target;
                this.formData.value.type = item.type;
                this.formData.value.actionType = item.action_on_click_type;
                this.formData.value.url = item.original_resource_path;
                //this.formData.value.ratio = item.image_ratio;
                this.changeValueAttribute(item.image_ratio.replace(':', '/'), item.image_ratio);
            },
            resetCropper() {
                this.formData.value.file = null;
                this.formData.value.blob = null;
                this.formData.value.url = null;
            },
            validateImage(img, type) {
                let item = null;
                if (this.formData.value.platform == EPlatform.WEB || this.formData.value.platform == EPlatform.WEB_AND_MOBILE) {
                    item = this.validate.web.find((item) => item.name == type);
                } else {
                    item = this.validate.mobile.find((item) => item.name == type);
                }
                let minHeight = item.size.minHeight;
                let minWidth = item.size.minWidth;
                if(img.width < minWidth || img.height < minHeight) {
                    this.formData.value.file = null;
                    this.formData.value.url = null;
                    let error = [];
                    error.push(item.size.guideMsg);
                    this.formData.errors.file = error;
                    this.$refs.imageCropperEl.resetSelectedFile();
                    return false;
                } else {
                    this.formData.errors.file = null;
                }
            },
            onCropperCreated({file, imageUrl, indexImg}) {
                setTimeout(() => {
                    this.$refs.imageCropperEl.val()
                        .then(blob => {
                            if (blob) {
                                let img = new Image();
                                img.src = URL.createObjectURL(file);
                                img.onload = () => {
                                    switch(this.formData.value.type) {
                                        case EBannerType.SHOW_AS_POP_UP_AFTER_LOG_IN:
                                            this.validateImage(img, 'flashscreen');
                                            break;
                                        case EBannerType.MAIN_BANNER_ON_HOME_SCREEN:
                                            this.validateImage(img, 'home');
                                            break;
                                        case EBannerType.PROMOTION:
                                            this.validateImage(img, 'promotion');
                                            break;
                                        case EBannerType.TRADEMARK:
                                            this.validateImage(img, 'trademark');
                                            break;
                                    }
                                }
                                this.formData.value.file = file;
                                this.formData.value.blob = blob;
                                this.formData.value.url = imageUrl;
                            }
                        });
                }, 300)
            },
            cropImageData(data, index) {
                setTimeout(() => {
                    this.$refs.imageCropperEl.val()
                        .then(blob => {
                            if (blob) {
                                this.formData.value.blob = blob;
                            }
                        });
                }, 300)
            },
            changeValueAttribute(name, value) {
                this.cropperAttribute.name = name;
                let val = value.split(':');
                if (val.length == 2) {
                    val = parseInt(val[0]) / parseInt(val[1]);
                    this.cropperAttribute.value = val;
                } else {
                    this.cropperAttribute.value = value;
                }
                this.formData.value.ratio = name;
                // setTimeout(() => this.$nextTick(this.$refs.imageCropperEl.setRatio(this.cropperAttribute, this.formData.value.url, this.formData.value.file), 300));
                this.$refs.imageCropperEl.setRatio(this.cropperAttribute, this.formData.value.url, this.formData.value.file);
            },
            changeType() {
                this.cropperAttribute.name = null;
                this.cropperAttribute.value = null;
                this.formData.value.ratio = null;
            },
            async saveBanner() {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm(this.formData.value.id ? this.$t('confirm.edit') : this.$t('confirm.create'), resolve);
                });
                if (!confirm) {
                    return;
                }

                this.Util.loadingScreen.show();
                this.disable = true;
                let formData = new FormData();
                Object.keys(this.formData.value).forEach((key) => {
                    switch (key) {
                        case 'url':
                            formData.append('file', this.formData.value.file);
                            formData.append('blob', this.formData.value.blob);
                            break;
                        default:
                            if (this.formData.value.[key] == null) {
                                this.formData.value.[key] = ''
                            }
                            formData.append(key, this.formData.value[key]);
                            break;
                    }
                });
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/save`,
                    data: formData,
                    errorModel: this.formData.errors,
                    processData: false,
                    contentType: false,
                }).done(response => {
                    this.Util.showMsg2(response);
                    if (response.error == EErrorCode.ERROR) {
                        return false;
                    }
                    this.getListData(this.pagination);
                    this.showModal = false;
                }).always(() => {
                    this.disable = false;
                    this.Util.loadingScreen.hide();
                });
            },
            changePlatForm() {
                if (this.formData.value.platform == EPlatform.WEB_AND_MOBILE) {
                    this.formData.value.type = EBannerType.PROMOTION;
                    this.changeValueAttribute('16/9', '1.77777777778')
                } else {
                    this.formData.value.type = null;
                }
            },
            async approveBanners() {
                if (this.selectedItems.length == 0) {
                    return;
                }
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm('Bạn có muốn duyệt tất cả banner này không?', resolve);
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

            async rejectBanners() {
                if (this.selectedItems.length == 0) {
                    return;
                }
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm('Bạn có muốn từ chối tất cả banner này không?', resolve);
                });
                if (!confirm) {
                    return;
                }
                this.processing = true;
                this.Util.loadingScreen.show();
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/reject`,
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

            showModalUserDetail(item) {
                this.userItem = item;
                this.isShowModalUserDetail = true;
            },
            showModalShopDetail(item) {
                this.shopItem = item;
                this.isShowModalShopDetail = true;
            },
        }
    }
</script>

<style scoped>

</style>

