<template>
    <div class="profile-info p-0">
        <div class="bg-white">
            <div class="media p-3 border-bottom ">
                <a :href="profileInfo.linkToProfile">
                    <div class="m-auto rounded-pill" style="width: 45px; height: 45px; background-size: cover" :style="{'background-image': `url(${profileInfo.avatarPath})`}"></div>
                </a>
                <div class="media-body">
                    <div class="mb-0 mx-2">
                        <div
                            class="p-0 font-size-16px"  style="font-weight: 500"
                        >
                            <a :href="profileInfo.linkToProfile" class="text-black text-decoration-none">{{profileInfo.name}}</a>
                        </div>
                        <div style="font-size: 12px; color : #00000099;">
                            Mã số thẻ: <span class="font-weight-bold"> {{profileInfo.code}} </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="">
                <div class="mt-2 pb-2 d-flex justify-content-between border-bottom">
                    <div class="col-24 border-right">
                        <div style="font-size: 12px; color : #00000099;">
                            Xu hiện tại:
                        </div>
                        <div>
                            <span class="font-weight-bold">{{coin}}</span>
                            <span>xu</span>
                        </div>
                    </div>
                    <div class="col-24">
                        <div style="font-size: 12px; color : #00000099;">
                            Điểm hiện tại:
                        </div>
                        <div>
                            <span class="font-weight-bold">{{point}}</span>
                            <span>điểm</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-bottom bg-white">
                <a :href="profileInfo.linkToOrder"
                   class="dropdown-item media px-3 py-2" >
                    <img
                        src="/images/icon/icon-donmua.svg"
                        width="20px"
                        class="mt-2 mr-2"
                        alt="avatar"
                    >
                    <div class="media-body">
                        <div class="py-2 px-1">
                        <span class="font-medium" :class="{active : profileInfo.currentTab == 'order' }">
                            Đơn mua
                        </span>
                        </div>
                    </div>
                </a>
                <a :href="profileInfo.linkToSavedProduct"
                   class="dropdown-item media px-3 py-2">
                    <img
                        src="/images/icon/icon-sanphamdaluu.svg"
                        width="20px"
                        class="mt-2 mr-2"
                        alt="avatar"
                    >
                    <div class="media-body">
                        <div class="py-2 px-1">
                        <span class="font-medium" :class="{active : profileInfo.currentTab == 'savedProduct' }">
                            Sản phẩm đã lưu
                        </span>
                        </div>
                    </div>
                </a>
                <a :href="profileInfo.linkToDeposit" class="dropdown-item media px-3 py-2">
                    <img
                        src="/images/icon/icon-napxu.svg"
                        width="20px"
                        class="mt-2 mr-2"
                        alt="avatar"
                    >
                    <div class="media-body">
                        <div class="py-2 px-1">
                            <span class="font-medium">Nạp xu</span>
                        </div>
                    </div>
                </a>
                <a :href="profileInfo.linkToPaymentHistory"
                   class="dropdown-item media px-3 py-2">
                    <img
                        src="/images/icon/icon-lichsugiaodich.svg"
                        width="20px"
                        class="mt-2 mr-2"
                        alt="avatar"
                    >
                    <div class="media-body">
                        <div class="py-2 px-1" :class="{active : profileInfo.currentTab == 'paymentHistory' }">
                            <span class="font-medium">Lịch sử giao dịch</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="">
                <a href="/policy/user-guide"
                   class="dropdown-item media px-3 py-2 ">
                    <img
                        src="/images/icon/icon-huongdan.svg"
                        width="20px"
                        class="mt-2 mr-2"
                        alt="avatar"
                    >
                    <div class="media-body">
                        <div class="py-2 px-1">
                        <span class="font-medium">
                            Hướng dẫn
                        </span>
                        </div>
                    </div>
                </a>
                <a :href="profileInfo.linkToChangePassword"
                   class="dropdown-item media px-3 py-2 ">
                    <img
                        src="/images/icon/icon-doimatkhau.svg"
                        width="20px"
                        class="mt-2 mr-2"
                        alt="avatar"
                    >
                    <div class="media-body">
                        <div class="py-2 px-1">
                        <span class="font-medium" :class="{active : profileInfo.currentTab == 'changePassword' }">
                            Đổi mật khẩu
                        </span>
                        </div>
                    </div>
                </a>
                <div class="dropdown-item media mb-3 px-3 py-2">
                    <img
                        src="/images/icon/icon-dangxuat.svg"
                        width="20px"
                        class="mt-2 mr-2"
                        alt="avatar"
                    >
                    <div class="media-body">
                        <div class="py-2 px-1 cursor-pointer" @click="logOut">
                            <span class="font-medium">Đăng xuất</span>
                            <form id="logout-form" :action="profileInfo.linkToLogout" method="POST"
                                  style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import EErrorCode from "../../constants/error-code";

    export default {
        data() {
            return {
                coin: 0,
                point: 0,
            }
        },
        props: {
            profileInfo: {
                default: null,
            },
        },
        created() {
            this.getCoinAndPoint();
        },
        methods: {
            getCoinAndPoint() {
                common.get({
                    url: '/profile/personal-info/coin-and-point',
                }).done(response => {
                    if(response.error === EErrorCode.NO_ERROR) {
                        this.coin = response.coin;
                        this.point = response.point;
                    }
                })
            },
            logOut() {
                common.post({
                    url: '/logout',
                }).always( () => {
                    window.location.reload();
                })
            }
        }
    }
</script>

<style scoped>

@media (max-width: 768px) {
    .profile-info{
        padding: 0 0.5rem !important;
    }
}

</style>
