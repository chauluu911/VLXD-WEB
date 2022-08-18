<template>
    <div>
        <b-row>
            <b-col cols="48">
                <div class="content__inner">
                    <a-table :items="table.data" :fields="fields"
                             :loading="loading" :pagination="pagination">
                        <template v-slot:cell(action)="data">
                            <b-link
                                v-if="data.item.status !== EStatus.DELETED"
                                :title="$t('common.tooltip.edit')"
                                class="no-decoration"
                                @click="$refs.createFormModalEl.showForm(data.item)"
                            >
                                <i class="text-primary fas fa-edit"/>
                            </b-link>
                        </template>
                        <template v-slot:cell(videoIntroduce) = 'data'>
                             <span class="text-info cursor-pointer"
                                   @click="showModalVideoIntroduceDetail(data.item.videoIntroduce)">
                                        chi tiết
                             </span>
                        </template>
                        <template v-slot:cell(imageIntroduce) = 'data'>
                             <span class="text-info cursor-pointer"
                                   @click="showModalImageIntroduceDetail(data.item.imageIntroduce)">
                                        chi tiết
                             </span>
                        </template>
                        <template v-slot:cell(avatar) = 'data'>
                            <span class="text-info cursor-pointer"
                                  @click="showModalAvatarDetail(data.item.avatar)">
                                        chi tiết
                             </span>
                        </template>
                        <template v-slot:cell(videoInProduct) = 'data'>
                            <span class="text-info cursor-pointer"
                                  @click="showModalVideoInProductDetail(data.item.videoInProduct)">
                                        chi tiết
                             </span>
                        </template>
                        <template v-slot:cell(bannerInHome) = 'data'>
                            <span class="text-info cursor-pointer"
                                  @click="showModalBannerInHomeDetail(data.item.bannerInHome)">
                                        chi tiết
                             </span>
                        </template>
                        <template v-slot:cell(numProduct) = 'data'>
                            <span class="text-align-cente" v-if="data.item.numProduct == null">
                                Không giới hạn
                            </span>
                            <span class="text-align-cente" v-else>
                                {{data.item.numProduct}}
                             </span>
                        </template>
                        <template v-slot:cell(priorityShowSearchProduct)="data">
                            <a-checkbox v-model="data.item.priorityShowSearchProduct"
                                        :checked-state="data.item.priorityShowSearchProduct"
                                        disabled>
                            </a-checkbox>
                        </template>
                        <template v-slot:cell(enableCreateNotification)="data">
                            <a-checkbox v-model="data.item.enableCreateNotification"
                                        :checked-state="data.item.enableCreateNotification"
                                        disabled>
                            </a-checkbox>
                        </template>
                    </a-table>
                    <paging @page-change="onPageChangeHandler"
                            :total="table.total"
                            :disabled="loading" ref="pagingEl"/>
                </div>
            </b-col>
        </b-row>
        <b-modal
            size="md"
            v-model="showModalVideoIntroduce"
            no-close-on-backdrop
            hide-footer
            :title="$t('table.video-introduce')"
            header-class=""
            @hidden=""
        >
            <template v-slot:default>
                <div>
                    <label>
                        {{$t('modal.allow-upload-video')}}
                        <a-checkbox v-model="item.allow_upload_video"
                                    :checked-state="item.allow_upload_video"
                                    disabled>
                        </a-checkbox>
                    </label>
                </div>
                <div>
                    {{$t('modal.max-upload-time')}} : {{item.upload_time}}s
                </div>
                <div>
                    {{$t('modal.num-video')}}: {{item.num_video}}
                </div>
            </template>
        </b-modal>
        <b-modal
            size="md"
            v-model="showModalVideoInProduct"
            no-close-on-backdrop
            hide-footer
            :title="$t('table.video-in-product')"
            header-class=""
            @hidden=""
        >
            <template v-slot:default>
                <div>
                    <label>
                        {{$t('modal.allow-upload-video')}}
                        <a-checkbox v-model="item.allow_upload_video"
                                    :checked-state="item.allow_upload_video"
                                    disabled>
                        </a-checkbox>
                    </label>
                </div>
                <div>
                    {{$t('modal.max-upload-time')}} : {{item.upload_time}}s
                </div>
            </template>
        </b-modal>
        <b-modal
            size="md"
            v-model="showModalAvatar"
            no-close-on-backdrop
            hide-footer
            :title="$t('table.avatar')"
            header-class=""
            @hidden=""
        >
            <template v-slot:default>
                <div>
                    <label>
                        {{$t('modal.allow-upload-video')}}
                        <a-checkbox v-model="item.allow_upload_video"
                                    :checked-state="item.allow_upload_video"
                                    disabled>
                        </a-checkbox>
                    </label>
                </div>
                <div>
                    {{$t('modal.max-upload-time')}} : {{item.upload_time}}s
                </div>
                <div>
                    {{$t('modal.type-of-avatar')}}: {{item.type == 1 ?
                    $t('modal.avatar-type.circle') :
                    $t('modal.avatar-type.square')
                    }}
                </div>
            </template>
        </b-modal>
        <b-modal
            size="md"
            v-model="showModalBannerInHome"
            no-close-on-backdrop
            hide-footer
            :title="$t('table.post-banner')"
            header-class=""
            @hidden=""
        >
            <template v-slot:default>
                <div>
                    <label>
                        {{$t('modal.allow-upload-banner')}}
                        <a-checkbox v-model="item.allow_upload_banner "
                                    :checked-state="item.allow_upload_banner"
                                    disabled>
                        </a-checkbox>
                    </label>
                </div>
                <div>
                    {{$t('modal.num-day-show')}} : {{item.num_day_show}}
                </div>
            </template>
        </b-modal>
        <b-modal
            size="md"
            v-model="showModalImageIntroduce"
            no-close-on-backdrop
            hide-footer
            :title="$t('table.image-introduce')"
            header-class=""
            @hidden=""
        >
            <template v-slot:default>
                <div>
                    <label>
                        {{$t('modal.allow-upload-image')}}
                        <a-checkbox v-model="item.allow_upload_banner "
                                    :checked-state="item.allow_upload_image"
                                    disabled>
                        </a-checkbox>
                    </label>
                </div>
                <div>
                    {{$t('modal.num-image')}} : {{item.num_image}}
                </div>
            </template>
        </b-modal>
        <creating-form ref="createFormModalEl" @save-success="getListData({page: 1, size: sz})"/>
    </div>
</template>

<script>
import {mapState} from "vuex";
import shopLevelListManage from "../../../locales/back/config/shop-level";
import EErrorCode from "../../../constants/error-code";
import EStatus from "../../../constants/status";
import listMixin from "../../mixins/list-mixin";
import FPlusIcon from 'vue-feather-icons/icons/PlusIcon';
import CreatingForm from "./CreatingForm";

export default {
    inject: ['Util', 'StringUtil', 'DateUtil'],
    mixins: [listMixin],
    i18n: {
        messages: shopLevelListManage
    },
    components: {
        FPlusIcon,
        CreatingForm
    },
    data() {
        return {
            EStatus,
            item: {},
            showModalVideoIntroduce: false,
            showModalVideoInProduct:false,
            showModalBannerInHome: false,
            showModalImageIntroduce:false,
            showModalAvatar: false,
        }
    },
    computed: {
        ...mapState(['queryFilterState']),
        fields() {
            return [
                {label: this.$t('table.column.no'), key: 'index',
                    class: 'text-center align-middle', colWidth: '5%'},
                {label: this.$t('table.level'), key: 'name', thClass: 'text-center align-middle',
                    tdClass: 'col-text', colWidth: '12%'},
                {label: this.$t('table.num-product'), key: 'numProduct',
                    class: 'text-center align-middle', colWidth: '9%'},
                {label: this.$t('table.num-image-in-product'), key: 'numImageInProduct',
                    class: 'text-center align-middle', colWidth: '9%'},
                {label: this.$t('table.video-introduce'), key: 'videoIntroduce',
                    class: 'text-center align-middle', colWidth: '9%'},
                {label: this.$t('table.image-introduce'), key: 'imageIntroduce',
                    class: 'text-center align-middle', colWidth: '9%'},
                {label: this.$t('table.num-push-product-in-month'), key: 'numPushProductInMonth',
                    class: 'text-center align-middle', colWidth: '5%'},
                {label: this.$t('table.priority-show-search-product'), key: 'priorityShowSearchProduct',
                    class: 'text-center align-middle', colWidth: '9%'},
                {label: this.$t('table.create-notification'), key: 'enableCreateNotification',
                    class: 'text-center align-middle', colWidth: '9%'},
                {label: this.$t('table.post-banner'), key: 'bannerInHome',
                    class: 'text-center align-middle', colWidth: '9%'},
                {label: this.$t('table.avatar'), key: 'avatar',
                    class: 'text-center align-middle', colWidth: '9%'},
                {label: this.$t('table.video-in-product'), key: 'videoInProduct',
                    class: 'text-center align-middle', colWidth: '9%'},
                {label: this.$t('table.option'), key: 'action',
                    class: 'text-center align-middle', colWidth: '5%'},
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
                text: 'Cấu hình cấp độ cửa hàng',
                to: { name: 'config.shop-level' }
            }
        ]);
        //this.getData();
        this.$store.commit('updateFilterValueState', {
            q: this.$route.query.q,
        });
    },
    methods: {
        showModalVideoIntroduceDetail(item) {
            this.item = item;
            this.showModalVideoIntroduce = true;
        },
        showModalVideoInProductDetail(item) {
            this.item = item;
            this.showModalVideoInProduct = true;
        },
        showModalBannerInHomeDetail(item) {
            this.item = item;
            this.showModalBannerInHome = true;
        },
        showModalImageIntroduceDetail(item) {
            this.item = item;
            this.showModalImageIntroduce = true;
        },
        showModalAvatarDetail(item) {
            this.item = item;
            this.showModalAvatar = true;
        },
    }
}
</script>

<style scoped>

</style>
