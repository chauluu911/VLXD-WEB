<template>
    <b-row>
        <b-col cols="48">
            <div class="content__inner">
                <b-row class="pb-2" style="border-bottom: 3px solid gray">
                    <!-- {{routes.areaSearch}} -->
                    <b-col cols="24">
                        <span class="span__title-detail mr-4">{{
                            action == "create"
                                ? $t("", {
                                      objectName: $t("object_name")
                                  }).toUpperCase()
                                : $t("info").toUpperCase()
                        }}</span>
                    </b-col>
                    <b-col cols="24">
                        <template>
                            <b-button
                                variant="primary"
                                class="float-right ml-3"
                                @click="updateInfo"
                            >
                                <i class="far fa-save"></i>
                                {{ $t("save") }}
                            </b-button>
                            <b-link @click="goBack" class="float-right btn">
                                <span>{{ $t("back") }}</span>
                            </b-link>
                        </template>
                    </b-col>
                </b-row>

                <b-row>
                    <b-col cols="48" class="pt-3">
                        <b-form>
                            <b-row>
                                <b-col md="16">
                                    <b-form-group
                                        class="font-weight-bold"
                                        :label-class="'required'"
                                        :label="$t('shop')"
                                    >
                                        <c-select

                                            id="shop"
                                            v-model="formData.shopId"
                                            :search-route="'/api/shop/all'"
                                            :placeholder="$t('shop')"
                                        >
                                        </c-select>
                                        <div
                                            v-if="
                                                formData.errors.shopId !=
                                                    null &&
                                                    formData.errors.shopId[0]
                                            "
                                            class="font-weight-bold invalid-feedback d-block"
                                        >
                                            {{ formData.errors.shopId[0] }}
                                        </div>
                                    </b-form-group>
                                </b-col>
                                <b-col md="16">
                                    <b-form-group
                                        class="font-weight-bold"
                                        :label-class="'required'"
                                        :label="$t('branch_name')"
                                        :state="!!formData.errors.name"
                                        :invalid-feedback="
                                            formData.errors.name &&
                                                formData.errors.name[0]
                                        "
                                    >
                                        <b-form-input
                                            class="w-100"
                                            v-model.trim="formData.name"
                                            required
                                            :placeholder="$t('branch_name')"
                                            :state="
                                                formData.errors.name &&
                                                    !formData.errors.name[0]
                                            "
                                            :disabled="disable"
                                            @change="onInputChange"
                                        />
                                    </b-form-group>
                                </b-col>
                                <b-col md="16">
                                    <b-form-group
                                        class="font-weight-bold"
                                        :label-class="'required'"
                                        :label="$t('phone')"
                                        :state="!!formData.errors.phone1"
                                        :invalid-feedback="
                                            formData.errors.phone1 &&
                                                formData.errors.phone1[0]
                                        "
                                    >
                                        <b-form-input
                                            class="w-100"
                                            v-model.trim="formData.phone1"
                                            required
                                            :placeholder="$t('phone')"
                                            :disabled="disable"
                                            :state="
                                                formData.errors.phone1 &&
                                                    !formData.errors.phone1[0]
                                            "
                                            @change="onInputChange"
                                        />
                                    </b-form-group>
                                </b-col>
                            </b-row>
                            <b-row>
                                <b-col md="16">
                                    <b-form-group
                                        class="font-weight-bold"
                                        :label-class="'required'"
                                        :label="$t('address')"
                                        :state="!!formData.errors.address"
                                        :invalid-feedback="
                                            formData.errors.address &&
                                                formData.errors.address[0]
                                        "
                                    >
                                        <b-form-input
                                            class="w-100"
                                            v-model.trim="formData.address"
                                            required
                                            :placeholder="$t('address')"
                                            :disabled="disable"
                                            :state="
                                                formData.errors.address &&
                                                    !formData.errors.address[0]
                                            "
                                            @change="onInputChange"
                                        />
                                    </b-form-group>
                                </b-col>
                                <b-col md="16">
                                    <b-form-group
                                        class="font-weight-bold"
                                        :label-class="'required'"
                                        :label="$t('province')"
                                        :state="!!formData.errors.areaProvince"
                                        :invalid-feedback="
                                            formData.errors.areaProvince &&
                                                formData.errors.areaProvince[0]
                                        "
                                    >
                                        <c-select
                                         v-bind:class="{'error-boarder' : formData.errors.areaProvince != null}"
                                            v-model="formData.areaProvince"
                                            :search-route="'/api/area/all'"
                                            :custom-request-data="
                                                areaFilterData
                                            "
                                            :taggable="false"
                                            ref="areaFilterEl"
                                            placeholder="Vui lòng chọn tỉnh, thành phố"
                                        >
                                        </c-select>
                                        <div
                                            v-if="
                                                formData.errors.areaProvince !=
                                                    null &&
                                                    formData.errors
                                                        .areaProvince[0]
                                            "
                                            class="font-weight-bold invalid-feedback d-block"
                                        >
                                            {{
                                                formData.errors.areaProvince[0]
                                            }}
                                        </div>
                                    </b-form-group>
                                </b-col>
                                <b-col md="16">
                                    <b-form-group
                                        class="font-weight-bold"
                                        :label-class="'required'"
                                        :label="$t('district')"
                                        :state="!!formData.errors.areaProvince"
                                        :invalid-feedback="
                                            formData.errors.areaDistrict &&
                                                formData.errors.areaDistrict[0]
                                        "
                                    >
                                        <c-select
                                            v-bind:class="{'error-boarder' : formData.errors.areaDistrict != null}"
                                            v-model="formData.areaDistrict"
                                            :search-route="'/api/area/all'"
                                            :custom-request-data="
                                                districtFilterData
                                            "
                                            :taggable="false"
                                            ref="areaFilterEl"
                                            placeholder="Vui lòng chọn quận huyện"
                                        >
                                        </c-select>
                                        <div
                                            v-if="
                                                formData.errors.areaDistrict !=
                                                    null &&
                                                    formData.errors
                                                        .areaDistrict[0]
                                            "
                                            class="font-weight-bold invalid-feedback d-block"
                                        >
                                            {{
                                                formData.errors.areaDistrict[0]
                                            }}
                                        </div>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                            <b-row>
                                <b-col md="16">
                                    <b-form-group
                                        class="font-weight-bold"
                                        :label="$t('ward')"
                                        :label-class="'required'"
                                        :state="!!formData.errors.areaWard"
                                        :invalid-feedback="
                                            formData.errors.areaWard &&
                                                formData.errors.areaWard[0]
                                        "
                                    >
                                        <c-select
                                        v-bind:class="{'error-boarder' : formData.errors.areaWard != null}"
                                            v-model="formData.areaWard"
                                            :search-route="'/api/area/all'"
                                            :custom-request-data="
                                                wardFilterData
                                            "
                                            :taggable="false"
                                            ref="areaFilterEl"
                                            placeholder="Vui lòng chọn xã, phường"
                                        >
                                        </c-select>
                                        <div
                                            v-if="
                                                formData.errors.areaWard !=
                                                    null &&
                                                    formData.errors.areaWard[0]
                                            "
                                            class="font-weight-bold invalid-feedback d-block"
                                        >
                                            {{ formData.errors.areaWard[0] }}
                                        </div>
                                    </b-form-group>
                                </b-col>
                                <b-col md="16">
                                    <b-form-group
                                        class="font-weight-bold"
                                        :label="$t('longitude')"
                                    >
                                        <b-form-input
                                            class="w-100"
                                            v-model.trim="formData.longitude"
                                            required
                                            :placeholder="$t('longitude')"
                                            :disabled="disable"
                                            @change="onInputChange"
                                        />
                                    </b-form-group>
                                </b-col>
                                <b-col md="16">
                                    <b-form-group
                                        class="font-weight-bold"
                                        :label="$t('latitude')"
                                    >
                                        <b-form-input
                                            class="w-100"
                                            v-model.trim="formData.latitude"
                                            required
                                            :placeholder="$t('latitude')"
                                            :disabled="disable"
                                            @change="onInputChange"
                                        />
                                    </b-form-group>
                                </b-col>
                            </b-row>
                            <b-row>
                                <b-col md="48">
                                    <b-form-group

                                        :label="$t('description')"
                                    >
                                        <ck-document
                                            v-model="formData.description"
                                            class="w-100"
                                            :ck-class="['border border-top-0']"
                                            :ck-style="{ height: '500px' }"
                                            :placeholder="$t('description')"
                                        ></ck-document>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                        </b-form>
                    </b-col>
                </b-row>
            </div>
        </b-col>
    </b-row>
</template>

<script>
import { mapState } from "vuex";
import branchInfoMessage from "../../locales/back/branch/branch-info";
import EErrorCode from "../../constants/error-code";
import EStatus from "../../constants/status";
import CropperImage from "../../../js/components/ImageCropper";
import EAreaType from "../../constants/area-type";

export default {
    i18n: {
        messages: branchInfoMessage
    },
    components: {
        CropperImage
    },
    inject: ["Util", "DateUtil"],
    data() {
        return {
            wardFilterData: null,
            districtFilterData: null,
            areaFilterData: null,
            formData: this.$_formData(),
            disable:
                this.$route.params.action == "edit" ||
                this.$route.params.action == "create"
                    ? false
                    : true,
            info: null,
            action: this.$route.params.action,
            EAreaType,
            EErrorCode,
            EStatus,
            img: {
                file: null,
                blob: null,
                url: null
            },
            genKey: 0
        };
    },
    computed: {
        ...mapState(["filterValueState", "queryFilterState"]),
        routerName() {
            return "branch.info";
        },
        getBackRoute() {
            return "branch.list";
        }
    },
    created() {
        this.$store.commit("updateBreadcrumbsState", [
            {
                text: this.$t("branch-list"),
                to: { name: "branch.list" }
            }
        ]);
        this.$store.commit("updateFilterFormState", [
            {
                type: "date",
                name: "createdAtFrom",
                placeholder: this.$t("created_at_from"),
                dropleft: true
            },
            {
                type: "date",
                name: "createdAtTo",
                placeholder: this.$t("created_at_to"),
                dropleft: true
            }
        ]);
        this.$store.commit("updateQueryFilterState", {
            enable: true,
            placeholder: this.$t("name_shop")
        });
        this.areaFilterData = {
            pageSize: 15,
            type: EAreaType.PROVINCE
        };
        this.districtFilterData = {
            pageSize: 15,
            type: EAreaType.STATE
        };
        this.wardFilterData = {
            pageSize: 15,
            type: EAreaType.STATE
        };
    },
    watch: {
        filterValueState(val) {
            this.$router.push({ name: "branch.list", query: val.value });
        },
        "formData.areaProvince"() {
            this.districtFilterData = {
                pageSize: 15,
                type: this.formData.areaProvince
                    ? EAreaType.DISTRICT
                    : EAreaType.STATE,
                parentAreaId: this.formData.areaProvince
                    ? this.formData.areaProvince.id
                    : null
            };
        },
        "formData.areaDistrict"() {
            this.wardFilterData = {
                pageSize: 15,
                type: this.formData.areaDistrict
                    ? EAreaType.WARD
                    : EAreaType.STATE,
                parentAreaId: this.formData.areaDistrict
                    ? this.formData.areaDistrict.id
                    : null
            };
        }
    },
    beforeRouteUpdate(to, from, next) {
        this.disable =
            to.params.action == "edit" || to.params.action == "create"
                ? false
                : true;
        if (this.disable) {
            this.formData = this.$_formData();
            this.getInfoBranch();
        }
        next();
    },

    methods: {
        onInputChange() {
            if (this.disable == false) {
                this.Util.askUserWhenLeavePage();
            }
        },
        resetCropper() {
            this.img = {
                file: null,
                blob: null,
                url: null
            };
        },
        onCropperCreated({ file, imageUrl, indexImg }) {
            setTimeout(() => {
                this.$refs.imageCropperEl.val().then(blob => {
                    if (blob) {
                        this.img.file = file;
                        this.img.blob = blob;
                        this.img.url = imageUrl;
                    }
                });
            }, 300);
        },
        $_formData() {
            return {
                id: this.$route.params.branchId,
                shopId: null,
                name: "",
                phone1: "",
                address: "",
                longitude: "",
                latitude: "",
                description: "",
                areaDistrict: null,
                areaWard: null,
                areaProvince: null,
                image: null,

                errors: {
                    shopId: null,
                    name: null,
                    phone1: null,
                    address: null,
                    longitude: null,
                    latitude: null,
                    description: null,
                    areaDistrict: null,
                    areaWard: null,
                    areaProvince: null,
                    blob: null
                }
            };
        },
        getInfoBranch() {
            this.action = this.$route.params.action;
            this.formData = this.$_formData();
            this.Util.loadingScreen.show();
            this.Util.get({
                url: `${this.$route.meta.baseUrl}/${this.formData.id}/info`,
            })
                .done(response => {
                    if(response.error === EErrorCode.NO_ERROR) {
                        this.formData.name = response.branch.name
                        this.formData.phone1 = response.branch.phone1
                        this.formData.address = response.branch.address
                        this.formData.description = response.branch.description
                        this.longitude = response.branch.longitude
                        this.latitude = response.branch.latitude
                    } else {
                        this.Util.showMsg2(response);
                        this.$router.push({ name: "branch.list" });
                    }
                })
                .fail(error => {
                    if (error.status == "404") {
                        this.$router.push({ name: "404" });
                    }
                })
                .always(() => {
                    if (this.$route.params.action === "edit") {
                        this.disable = false;
                    }
                    this.Util.loadingScreen.hide();
                });
        },
        async updateInfo() {
            let confirm = await new Promise(resolve => {
                this.Util.confirm(
                    this.$route.params.action === "create"
                        ? this.$t("create")
                        : this.$t("edit"),
                    resolve
                );
            });
            if (!confirm) {
                return;
            }

            this.Util.loadingScreen.show();
            this.disable = true;

            let formData = new FormData();

            if (this.formData.shopId != null) {
                formData.append("shopId", this.formData.shopId.id);
            }
            formData.append("name", this.formData.name);
            formData.append("phone1", this.formData.phone1);
            formData.append("address", this.formData.address);
            formData.append("description", this.formData.description);
            if (this.formData.areaProvince != null) {
                formData.append("areaProvince", this.formData.areaProvince.id);
            }
            if (this.formData.areaDistrict != null) {
                formData.append("areaDistrict", this.formData.areaDistrict.id);
            }
            if (this.formData.areaWard != null) {
                formData.append("areaWard", this.formData.areaWard.id);
            }

            this.Util.post({
                url: `${this.$route.meta.baseUrl}/save`,
                data: formData,
                errorModel: this.formData.errors,
                processData: false,
                contentType: false
            })
                .done(response => {
                    if (response.EErrorCode === 2) {
                        this.formData.errors = response.mgs;
                        return false;
                    }
                    this.Util.showMsg2(response);
                    if (!this.formData.id) {
                        this.$router.push({ name: "branch.list" });
                        return;
                    }
                })
                .always(() => {
                    this.disable = false;
                    this.Util.loadingScreen.hide();
                });
        },
        goBack() {
            this.$router.go(-1);
        }
    }
};
</script>

<style scoped>
.error-boarder {
    border-color: red;
}
</style>
