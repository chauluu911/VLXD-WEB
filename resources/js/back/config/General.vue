<template>
    <div>
        <b-row>
            <b-col md="24" cols="48" class="mt-1" >
                <div class="content__inner" style="height: 100%">
                    <b-row>
                        <b-col>
                            <p class="mb-0 font-medium">Điểm tích lũy</p>
                        </b-col>
                        <b-col cols="48" class="d-flex pt-2 pb-3">
                            <div>
                                <b-input-group>
                                    <b-input-group-prepend class="mt-1">
                                        <money
                                            v-model="formData.cost"
                                            v-bind="money"
                                            class="form-control"
                                            style="width: 100px"
                                            :disabled="!isEditScore"
                                            :class="{'is-invalid': formData.errors.cost}"
                                        >
                                        </money>
                                    </b-input-group-prepend>
                                    <b-input-group-prepend class="mt-1">
                                        <b-input-group-text>VND</b-input-group-text>
                                    </b-input-group-prepend >

                                    <span class="px-3 mt-1" style="font-size: 20px"> = </span>

                                    <b-input-group-append class="mt-1">
                                        <money
                                            v-model="formData.point"
                                            v-bind="money"
                                            class="form-control"
                                            style="width: 100px"
                                            :disabled="!isEditScore"
                                            :class="{'is-invalid': formData.errors.point}"
                                        >
                                        </money>
                                        <b-input-group-prepend >
                                            <b-input-group-text>Điểm</b-input-group-text>
                                        </b-input-group-prepend>
                                        <template v-if="isEditScore">
                                            <b-button variant="primary" @click="saveScoreConfig()">
                                                {{ $t('Lưu') }}
                                            </b-button>
                                            <b-button variant="info" @click="isEditScore=false, getWalletData()">
                                                {{ $t('Hủy') }}
                                            </b-button>
                                        </template>
                                        <template v-else>
                                            <b-button variant="primary" @click="isEditScore=true, getWalletData()">
                                                <span>
                                                    <i class="fas fa-edit"/>
                                                </span>
                                            </b-button>
                                        </template>
                                    </b-input-group-append>
                                </b-input-group>
                            </div>
                        </b-col>
                        <b-col>
                            <p class="mb-0 " style="color: #000000; opacity: 0.3">
                                Chú thích: điểm tích luỹ của user nhận được trên
                                tổng tiền đơn hàng dựa theo cấu hình.
                            </p>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col md="48">
                            <div v-if="formData.errors.point && formData.errors.point[0]" class="invalid-feedback d-block ml-2">
                                {{formData.errors.point[0]}}
                            </div>
                            <div v-if="formData.errors.cost && formData.errors.cost[0]" class="invalid-feedback d-block ml-2">
                                {{formData.errors.cost[0]}}
                            </div>
                        </b-col>
                    </b-row>
                </div>
            </b-col>
            <b-col md="24" cols="48" class="mt-1">
                <div class="content__inner" style="height: 100%">
                    <b-row>
                        <b-col>
                            <p class="mb-0 font-medium">Chiết khấu nâng cấp shop</p>
                        </b-col>
                        <b-col cols="48" class="d-flex pt-2 pb-3">
                            <div>
                                <b-input-group>
                                    <b-input-group-prepend class="mt-1">
                                        <money
                                            v-model="formData.commission"
                                            v-bind="money"
                                            class="form-control"
                                            style="width: 100px"
                                            :disabled="!isEditCommission"
                                            :class="{'is-invalid': formData.errors.commission}"
                                        >
                                        </money>
                                    </b-input-group-prepend>
                                    <b-input-group-prepend class="mt-1">
                                        <b-input-group-text>%</b-input-group-text>
                                    </b-input-group-prepend>
                                    <b-input-group-append class="mt-1">
                                        <template v-if="isEditCommission">
                                            <b-button variant="primary" @click="saveCommisionConfig">
                                                {{ $t('Lưu') }}
                                            </b-button>
                                            <b-button variant="info" @click="isEditCommission=false, getCommissionData()">
                                                {{ $t('Hủy') }}
                                            </b-button>
                                        </template>
                                        <template v-else>
                                            <b-button variant="primary" @click="isEditCommission=true, getCommissionData()">
                                                <span>
                                                    <i class="fas fa-edit"/>
                                                </span>
                                            </b-button>
                                        </template>
                                    </b-input-group-append>
                                </b-input-group>
                            </div>
                        </b-col>
                        <b-col>
                            <p class="mb-0 " style="color: #000000; opacity: 0.3">
                                Chú thích: user giới thiệu sẽ nhận được tiền chiết khấu dựa trên tổng
                                tiền nâng cấp shop (quy đổi tiền chiết khấu thành xu).
                            </p>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col md="48">
                            <div v-if="formData.errors.commission && formData.errors.commission[0]" class="invalid-feedback d-block ml-2">
                                {{formData.errors.commission[0]}}
                            </div>
                        </b-col>
                    </b-row>
                </div>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import {mapState} from "vuex";
    import ECustomerType from "../../constants/customer-type";
    import userListManage from "../../locales/back/user/user-list";
    import EErrorCode from "../../constants/error-code";
    import EUserType from "../../constants/user-type";
    import EStatus from "../../constants/status";
    import VMoney from 'v-money';

    export default {
        inject: ['Util', 'StringUtil', 'DateUtil'],
        i18n: {
            messages: userListManage
        },
        components: {
            VMoney
        },
        data() {
            return {
                money: {
                    thousands: ',',
                    precision: 0,
                },
                formData: this.$_formData(),
                isEditScore: false,
                isEditCommission: false,
            }
        },

        created() {
            this.$store.commit('updateFilterFormState', []);
            this.$store.commit('updateQueryFilterState', {
                enable: false,
            });
            this.$store.commit('updateBreadcrumbsState', [
                {
                    text: 'Cấu hình chung',
                    to: { name: 'employee.list' }
                }
            ]);
            this.getWalletData();
            this.getCommissionData();
        },
        methods: {
            $_formData() {
                return {
                    cost: 0,
                    point: 0,
                    commission: 0,
                    errors: {
                        cost: null,
                        point: 0,
                        commission: null,
                    }
                }
            },
            getWalletData() {
                this.formData.errors.cost = null;
                this.formData.errors.point = null;
                this.Util.loadingScreen.show();
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/wallet/search`,
                }).done(response => {
                    if (response.error == EErrorCode.ERROR) {
                        this.Util.showMsg2(response);
                        return false;
                    }
                    if (this.isEditScore == false) {
                        this.formData.cost = response.data.score.cost;
                        this.formData.point = response.data.score.point;
                    }
                })
                .always(() => {
                    this.Util.loadingScreen.hide();
                });
            },
            async saveScoreConfig() {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm('Bạn có muốn thay đổi dữ liệu không?', resolve);
                });
                if (!confirm) {
                    return;
                }
                this.Util.loadingScreen.show();
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/wallet/save`,
                    data: {
                        isEditScore: this.isEditScore,
                        point: this.formData.point,
                        cost: this.formData.cost,
                    },
                    errorModel: this.formData.errors,
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }
                    if (this.isEditScore == true) {
                        this.isEditScore = false;
                    }

                    await this.getWalletData();

                    this.Util.showMsg('success', null, res.msg);
                }).always(() => {
                    this.Util.loadingScreen.hide();
                });
            },
            getCommissionData() {
                this.formData.errors.commission = null;
                this.Util.loadingScreen.show();
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/commission/search`,
                }).done(response => {
                    if (response.error == EErrorCode.ERROR) {
                        this.Util.showMsg2(response);
                        return false;
                    }
                    this.formData.commission = response.data;
                })
                .always(() => {
                    this.Util.loadingScreen.hide();
                });
            },
            async saveCommisionConfig() {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm('Bạn có muốn thay đổi dữ liệu không?', resolve);
                });
                if (!confirm) {
                    return;
                }
                this.Util.loadingScreen.show();
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/commission/save`,
                    data: {
                        commission: this.formData.commission,
                    },
                    errorModel: this.formData.errors,
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }
                    await this.getCommissionData();

                    this.Util.showMsg('success', null, res.msg);
                }).always(() => {
                    this.Util.loadingScreen.hide();
                });
            }
        }
    }
</script>

<style scoped>

</style>
