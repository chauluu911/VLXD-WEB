<template>
    <div class="card mb-3 border-0" id="shop-tool-bar">
        <div class="card-body p-0">
            <div class="row no-gutters text-white" style="background-size: cover"
                :style="shop.level ? `background-image: url('/images/store/level-${shop.level}.jpg')` : 'background: linear-gradient(45deg, white, black)'"
            >
                <div class="col-md-48 p-3">
                    <div class="media">
                        <div
                            v-if="shop.avatarType == EResourceType.IMAGE"
                            class="mr-3"
                            style="width: 68px; height: 68px; background-size: cover"
                            :style="{'background-image': `url(${shop.avatar})`}"
                            :class="permission && permission.avatar.type == 1 ? 'rounded-pill' : ''"></div>
                        <span
                            v-else-if="shop.avatarType == EResourceType.VIDEO"
                            class="b-avatar badge-secondary mr-3"
                            style="width: 68px; height: 68px"
                            :class="permission && permission.avatar.type == 1 ? 'rounded-circle' : ''"
                        >
                            <span class="shop-avatar">
                                <video autoplay muted loop :src="shop.avatar" style="width: 125px"></video>
                            </span>
                        </span>
                        <div v-else class="mr-3 rounded-pill" style="width: 68px; height: 68px; background-size: cover" :style="{'background-image': `url('/images/default-image.svg')`}"></div>
                        <div class="media-body">
                            <p class="font-medium font-size-16px mb-0">{{shop.name}}</p>
                            <div class="row no-gutters">
                                <div class="col-md-30">
                                    <div>
                                        <span style="color: #FFFFFF99">
                                            ID store:
                                        </span>
                                        <span style="font-weight: 600">
                                            {{shop.code}}
                                        </span>
                                    </div>
                                    <a class="p-0" style="cursor: pointer;color: #FFFFFF99"
                                    @click="showModalFollower"
                                    >
                                        <span>
                                            Lượt theo dõi:
                                        </span>
                                        <span style="font-weight: 600">
                                            {{shop.follow}}
                                        </span>
                                    </a>
                                </div>
                                <div class="col-md-18" v-if="shop.level < 5 && shop.status == EStatus.ACTIVE">
                                    <a :href="hrefUpgrade" class="btn btn-outline-light">Nâng cấp</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row no-gutters">
                <div class="col-md-16 py-2 border-right border-bottom">
                    <p class="mb-0 text-center font-medium">Cấp cửa hàng</p>
                    <p
                        class="text-center mb-0"
                        style="padding-top: 2px"
                        v-if="shop.level"
                    >
                        <!-- <template v-if="shop.levelName.length > 10">
                            <marquee scrollamount="5">
                            <img :src="`/images/star/level-${shop.level}.png`" class="pb-1" width="20px">
                            <span class="font-medium" :title="shop.levelName">{{shop.levelName}}</span>
                        </marquee>
                        </template>
                        <template v-else> -->
                            <img :src="`/images/star/level-${shop.level}.png`" alt="Takamart" class="pb-1" width="20px">
                            <span class="font-medium" :title="shop.levelName">{{shop.levelName}}</span>
                        <!-- </template> -->
                    </p>
                </div>
                <div class="col-md-16 py-2 border-right border-bottom">
                    <a :href="'/shop/' + shopId + '/review'"
                       style="text-decoration: none!important; color: #000000DD"
                    >
                        <p class="mb-0 text-center font-medium">Đánh giá</p>
                        <div class="d-flex justify-content-center">
                            <star-rating v-if="shop.evaluate" :show-rating="false" :increment="0.1" :star-size="16" :read-only="true" :rating="shop.evaluate.average">
                            </star-rating>
                        </div>
                    </a>
                </div>
                <div class="col-md-16 py-2 border-bottom">
                    <p class="mb-0 text-center font-medium">Phản hồi chat</p>
                    <p class="mb-0 text-center font-medium">{{shop.responseRate}}%</p>
                </div>
            </div>
            <div class="row no-gutters">
                <div class="col-md-48">
                    <a :href="'/shop/' + shopId + '/order'"
                        class="dropdown-item text-decoration-none p-3"
                        :class="{'text-primary': currentRouteName == 'orderShop.list'}"
                    >
                        <img class="rounded-pill mr-2" alt="Takamart" src="/images/icon/shop-order.svg" width="24px">
                        <span class="font-medium">Đơn bán</span>
                    </a>
                </div>
                <div class="col-md-48">
                    <a :href="'/shop/' + shopId + '/product'"
                        class="dropdown-item text-decoration-none p-3"
                        :class="{'text-primary': currentRouteName == 'shop.product'}"
                    >
                        <img class="rounded-pill mr-2" alt="Takamart" src="/images/icon/shop-product.svg" width="24px"
                    >
                        <span class="font-medium">Quản lý sản phẩm</span>
                    </a>
                </div>
                <div class="col-md-48">
                    <a :href="shopId ? '/shop/' + shopId + '/edit' : '/shop/create'"
                        class="dropdown-item text-decoration-none p-3"
                        :class="{'text-primary': currentRouteName == 'shop.edit' || currentRouteName == 'shop.create'}"
                    >
                        <img class="rounded-pill mr-2" alt="Takamart" src="/images/icon/shop-info.svg" width="24px">
                        <span class="font-medium">Thông tin cửa hàng</span>
                    </a>
                </div>
                <div class="col-md-48">
                    <a :href="'/shop/' + shopId + '/resource'"
                       class="dropdown-item text-decoration-none p-3"
                       :class="{'text-primary': currentRouteName == 'shop.resource'}"
                    >
                        <img class="rounded-pill mr-2" alt="Takamart" src="/images/icon/shop-video.svg" width="24px">
                        <span class="font-medium">Hình ảnh cửa hàng</span>
                    </a>
                </div>
                <div class="col-md-48 border-bottom border-top">
                    <a :href="'/shop/' + shopId"
                        class="dropdown-item text-decoration-none p-3"
                        :class="{'text-primary': currentRouteName == 'shop'}"
                    >
                        <span class="font-medium">Xem cửa hàng của tôi</span>
                    </a>
                </div>
                <template v-if="permission && permission.banner_in_home && permission.banner_in_home.allow_upload_banner">
                    <div class="col-md-48 border-bottom">
                        <a
                            :href="'/shop/' + shopId + '/banner'"
                            class="dropdown-item text-decoration-none p-3"
                            :class="{'text-primary': currentRouteName == 'shop.banner'}"
                        >
                            <p class="mb-0 font-medium">Đăng banner quảng cáo lên trang chủ</p>
                            <span style="font-size: 12px">Tính năng đặt biệt từ cửa hàng VIP</span>
                        </a>
                    </div>
                </template>
                <template v-else>
                    <div class="col-md-48 border-bottom">
                        <a
                            href="javascript:void(0)"
                            class="dropdown-item text-decoration-none p-3"
                            @click="showModalUpgrade"
                        >
                            <p class="mb-0 font-medium">Đăng banner quảng cáo lên trang chủ</p>
                            <span style="font-size: 12px">Tính năng đặt biệt từ cửa hàng VIP</span>
                        </a>
                    </div>
                </template>
                <template v-if="permission && permission.enable_create_notification">
                    <div class="col-md-48">
                        <a
                            :href="'/shop/' + shopId + '/notification'"
                            class="dropdown-item text-decoration-none p-3"
                            :class="{'text-primary': currentRouteName == 'shop.notification'}"
                        >
                            <p class="mb-0 font-medium">Gửi thông báo cho người dùng</p>
                            <span style="font-size: 12px">Tính năng đặt biệt từ cửa hàng VIP</span>
                        </a>
                    </div>
                </template>
                <template v-else>
                    <div class="col-md-48">
                        <a
                            href="javascript:void(0)"
                            class="dropdown-item text-decoration-none p-3"
                            @click="showModalUpgrade"
                        >
                            <p class="mb-0 font-medium">Gửi thông báo cho người dùng</p>
                            <span style="font-size: 12px">Tính năng đặt biệt từ cửa hàng VIP</span>
                        </a>
                    </div>
                </template>
            </div>
        </div>
        <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content p-3" style="max-width: 415px">
                    <div class="modal-body p-2">
                        <div class="row">
                            <div class="col-48 text-center">
                                <p class="mb-0 font-size-16px font-weight-bold">Cấp độ của bạn không đủ để thực hiện tính năng này !</p>
                            </div>
                        </div>
                    </div>
                    <div class="row no-gutters">
                        <div class="col-md-24 p-2">
                            <a data-dismiss="modal" class="btn btn-outline-primary w-100">Bỏ qua</a>
                        </div>
                        <div class="col-md-24 p-2">
                            <a :href="'/shop/' + shopId + '/upgrade'" class="btn btn-primary w-100">Nâng cấp shop</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modal-follower" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" >
                    <div class="modal-header" style="background: #F76301 ">
                        <div class="d-flex justify-content-between w-100">
                            <div style="color:white">Người theo dõi</div>
                            <div class="font-size-16px" data-dismiss="modal" aria-label="Close">
                                <i style="color: white; cursor: pointer;" class="fa fa-times" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body p-2 modal-follower-body" ref="listFollowerEL">
                        <div class="row" >
                            <div class="col-md-24 col-48 m-0 py-2" v-for="follower in followers.listFollower">
                                <div class="d-flex">
                                    <div class="mx-2 rounded-pill"
                                         style="width: 40px; height: 40px;background-size: cover"
                                         :style="{'background-image': `url(${follower.avatar})`}"
                                    >
                                    </div>
                                    <div style="line-height: 40px;max-height: 40px; overflow: hidden">
                                        {{follower.name}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row m-0 d-flex justify-content-center pb-4"
                        >
                            <i v-if="iconLoad" class="fas fa-spinner fa-spin"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import EErrorCode from "../../constants/error-code";
    import StarRating from 'vue-star-rating';
    import EResourceType from "../../constants/resource-type";
    import ELevel from "../../constants/level";
    import EStatus from "../../constants/status";

    export default {
        components: {
            StarRating
        },
        data() {
            return {
               shop: {},
               listFollower: [],
               followers: {
                   listFollower: [],
                   page: 1,
                   pageSize: 20,
                   dataPaginate: null,
               },
               permission: null,
               hrefUpgrade: null,
               iconLoad: false,

               EResourceType,
               ELevel,
               EStatus
            }
        },
        props: {
            shopId: {
                default: null,
            },
            currentRouteName: {
                default: null,
            }
        },
        mounted() {
            let listFollowerEL = this.$refs.listFollowerEL;
            $(listFollowerEL).scroll(() => {
                if (listFollowerEL.scrollHeight - listFollowerEL.clientHeight === this.$refs.listFollowerEL.scrollTop && !this.iconLoad) {
                    this.loadMoreFollower();
                }
            });
        },
        created() {
            this.getInfoShop();
            this.getPermissionShop();
            this.getListFollower();
        },
        methods: {
            getInfoShop() {
                common.loadingScreen.show('body');
                common.get({
                    url: `/shop/${this.shopId}/info`,
                }).done(response => {
                    if (response.error == EErrorCode.ERROR) {
                        return;
                    }
                    this.shop = response.shop;
                    this.hrefUpgrade = '/shop/' + this.shopId + '/upgrade';
                }).always(() => {
                    common.loadingScreen.hide('body');
                });
            },

            getPermissionShop() {
                common.loadingScreen.show();
                common.post({
                    url: '/shop/' + this.shopId + '/permission',
                }).done(response => {
                    this.permission = response.permission;
                    this.$emit('permission', this.permission);
                })
                .always(() => {
                    common.loadingScreen.hide();
                });
            },

            getListFollower() {
                this.iconLoad = true;
                common.get({
                    url: '/shop/' + this.shopId + '/follow',
                    data: {
                        page: this.followers.page,
                        pageSize: this.followers.pageSize,
                    }
                }).done(response => {
                    if(response.error !== EErrorCode.NO_ERROR) {
                        common.showMsg2(response);
                        return;
                    }
                    response.followers.data.forEach(follower => {
                        this.followers.listFollower.push(follower);
                    })
                    this.followers.dataPaginate = response.followers;
                }).always(() => {
                    this.iconLoad = false;
                });
                // for(let i=0;i<30;i++){
                //     let follower = {
                //         name:'Nguyễn Văn Hoàng Minh Tuấn' + i,
                //         avatar: 'https://vlxdst.bootech.vn/shop/41/avatar/2011/mu6YV76baPbPqEbcT3YPB4DYSErDev.jpg'
                //     }
                //     this.listFollower.push(follower);
                // }
            },
            loadMoreFollower() {
                if(this.followers.dataPaginate.to < this.followers.dataPaginate.total && this.followers.dataPaginate.to ) {
                    this.followers.page += 1;
                    this.getListFollower();
                }
            },
            showModalUpgrade() {
                $('#exampleModalCenter').modal('show');
            },

            showModalFollower() {
                $('#modal-follower').modal('show');
            },
        }
    }
</script>

<style scoped>

</style>
