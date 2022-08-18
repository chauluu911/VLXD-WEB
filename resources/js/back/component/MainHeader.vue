<template>
    <div class="row bg-white mb-3">
        <div class="col-48 py-2 header-top-content">
            <div class="pr-2">
                <div class="float-right" style="max-width: 200px;">
                    <div class="row">
                        <div class="col" style="margin: 6px 0">
                            <!-- <div class="limited-1-row" style="max-width: 120px;" v-shave="{height: 22}">{{ authState.name }}</div> -->
                            <div style="width: 150px;">
                                <b-dropdown size="md" variant="link" no-caret>
                                    <template v-slot:button-content class="">
                                        <i class="fa fa-caret-down"></i>
                                    </template>
                                    <b-dropdown-item @click="$refs.changePasswordModalEl.show()">
                                        {{$t('change-password')}}
                                    </b-dropdown-item>
                                    <b-dropdown-item @click="logout">
                                        Logout
                                    </b-dropdown-item>
                                </b-dropdown>
                                {{ authState.name }}
                            </div>
                        </div>
                        <div class="col p-0" style="max-width: 50px">
                            <b-avatar
                                variant="primary"
                                :text="(authState.name || '').slice(0, 2)"
                                :src="authState.avatar_path"
                                size="40px"
                                class="border border-primary"
                            ></b-avatar>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-48 py-2">
            <div class="row">
                <div class="col">
                    <b-breadcrumb class="header-breadcrumbs">
                        <b-breadcrumb-item :to="{name: 'home'}" class="text-primary">
                            <f-home-icon style="position: relative; bottom: 3px;"/>
                        </b-breadcrumb-item>
                        <b-breadcrumb-item v-for="item in breadcrumbsState"
                                           :key="item.text"
                                           :to="item.to"
                                           class="text-primary">
                            {{ item.text }}
                        </b-breadcrumb-item>
                    </b-breadcrumb>
                </div>
                <div class="col-48 col-lg d-flex-center-y justify-content-end header-filter-form">
                    <div class="d-inline-block">
                        <div class="form-inline">
                            <b-input-group v-if="queryFilterState.enable" class="mr-2">
                                <b-form-input class="border-primary" :placeholder="queryFilterState.placeholder || $t('placeholder.user_info')"
                                              v-model="filters.q"
                                              @keypress.enter="submitFilterForm"
                                />
                                <b-input-group-append>
                                    <a-button
                                        :loading="loadingState"
                                        :icon-class="['mr-lg-2']"
                                        @click="submitFilterForm">
                                        <template #icon>
                                            <f-search-icon size="20" class="mr-lg-2"/>
                                        </template>
                                        <template #default>
                                            <span class="d-none d-lg-inline">{{ $t('common.button.search') }}</span>
                                        </template>
                                    </a-button>
                                </b-input-group-append>
                            </b-input-group>
                            <template v-if="filterFormState.hasForm">
                                <morphing-input
                                    v-if="filterFormState.form.length === 1"
                                    v-model="filters[filterFormState.form[0].name]"
                                    :field="filterFormState.form[0]"
                                    inline
                                    style="width: 100px"
                                />
                                <template v-else>
                                    <a-button :icon-class="['mr-lg-2']" @click="$refs.filterModalEl.show()"
                                    style="width: 100px"
                                >
                                        <template #icon>
                                            <f-filter-icon size="20" class="mr-lg-2"/>
                                        </template>
                                        <template #default>
                                            <span class="d-none d-lg-inline">{{ $t('common.button.filter') }}</span>
                                        </template>
                                    </a-button>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <b-modal
            ref="filterModalEl"
            size="md"
            :title="$t('common.button.filter')"
            body-class="py-0"
            header-class="border-bottom-0"
            footer-class="border-top-0"
        >
            <template #default>
                <b-form>
                    <morphing-input
                        v-for="field in filterFormState.form"
                        v-model="filters[field.name]"
                        :key="field.name"
                        :field="{...field, maxWidth: '100%'}"
                        class="mb-3"
                    />
                </b-form>
            </template>
            <template #modal-footer="{ hide }">
                <a-button class="btn-primary" :loading="processing" @click="submitFilterForm()">
                    {{ $t('common.button.search') }}
                </a-button>
            </template>
        </b-modal>
        <b-modal
            ref="changePasswordModalEl"
            size="md"
            no-close-on-backdrop
            :hide-header-close="processing"
            :title="$t('change-password')"
            footer-class="border-0"
            @hidden="closeModal"
        >
            <template v-slot:default>
                <b-form>
                    <b-row>
                        <b-col md="48">
                            <b-form-group class="font-weight-bold"
                              :label="$t('form.label.old-password')"
                              :state="!!modalData.errors.oldPassword"
                              :invalid-feedback="modalData.errors.oldPassword && modalData.errors.oldPassword[0]">
                                <b-form-input class="w-100" type="password" v-model="modalData.oldPassword" required
                                      :placeholder="$t('form.placeholder.old-password')"
                                      :disabled="processing"
                                      :state="modalData.errors.oldPassword && !modalData.errors.oldPassword[0]"></b-form-input>
                            </b-form-group>
                            <b-form-group class="font-weight-bold"
                              :label="$t('form.label.new-password')"
                              :state="!!modalData.errors.newPassword"
                              :invalid-feedback="modalData.errors.newPassword && modalData.errors.newPassword[0]">
                                <b-form-input class="w-100" type="password" v-model="modalData.newPassword" required
                                      :placeholder="$t('form.placeholder.new-password')"
                                      :disabled="processing"
                                      :state="modalData.errors.newPassword && !modalData.errors.newPassword[0]"></b-form-input>
                            </b-form-group>
                            <b-form-group class="font-weight-bold"
                              :label="$t('form.label.re-enter-new-password')"
                              :state="!!modalData.errors.confirmPassword"
                              :invalid-feedback="modalData.errors.confirmPassword && modalData.errors.confirmPassword[0]">
                                <b-form-input class="w-100" type="password" v-model="modalData.confirmPassword" required
                                      :placeholder="$t('form.placeholder.re-enter-new-password')"
                                      :disabled="processing"
                                      :state="modalData.errors.confirmPassword && !modalData.errors.confirmPassword[0]"></b-form-input>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-form>
            </template>
            <template v-slot:modal-footer="{ hide }">
                <b-button variant="outline-primary" :disabled="processing" @click="hide('forget')">{{ $t('common.button.cancel') }}</b-button>
                <a-button class="btn-primary" :loading="processing" @click="submitChangePassword()">
                    {{ $t('common.button.update') }}
                </a-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
    import FHomeIcon from 'vue-feather-icons/icons/HomeIcon';
    import FSearchIcon from 'vue-feather-icons/icons/SearchIcon';
    import FFilterIcon from 'vue-feather-icons/icons/FilterIcon';
    import defaultListToolbarMessage from '../../locales/back/default-list-toolbar';
    import {mapState} from "vuex";
    import changePasswordMessage from '../../locales/back/change-password'
    import EErrorCode from "../../constants/error-code"

    export default {
        name: "MainHeader",
        i18n: {
            messages: _.merge(defaultListToolbarMessage, changePasswordMessage)
        },
        inject: ['Util'],
        components: {
            FHomeIcon,
            FSearchIcon,
            FFilterIcon
        },
        props: {
            title: {
                type: String
            },
            titleTransKey: {
                type: String,
                default: 'title'
            },
        },
        data() {
            return {
                filters: {
                    q: null,
                },
                processing: false,
                modalData: this.$_modalData(),
                EErrorCode,
            };
        },
        computed: {
            ...mapState(['authState', 'filterFormState', 'filterValueState', 'breadcrumbsState', 'queryFilterState', 'loadingState']),
        },
        watch: {
            breadcrumbsState(val) {
                if (val && val.length) {
                    val = [
                        {text: 'Admin'},
                        ...val,
                    ];
                }
                $('title').text(!val || !val.length ? $('title').data('defaultTitle') : val.map((item) => item.text).join(' - '));
            },
            filterValueState(val) {
                if (!val.timestamp) {
                    return;
                }
                this.filters = val.value;
            }
        },
        methods: {
            submitFilterForm() {
                this.$store.commit('updateFilterValueState', this.filters);
                this.$refs.filterModalEl.hide();
            },
            $_modalData() {
                return {
                    oldPassword: null,
                    newPassword: null,
                    confirmPassword: null,
                    errors: {
                        oldPassword: null,
                        newPassword: null,
                        confirmPassword: null,
                    }
                };
            },
            logout() {
                $.get({
                    url: '/back/logout',
                }).always(() => {
                    window.location.reload();
                });
            },
            closeModal() {
                this.modalData = this.$_modalData();
            },
            submitChangePassword() {
                this.Util.loadingScreen.show();
                this.processing = true;
                this.Util.post({
                    url: `${this.appConfig.baseApiUrl}/change-password`,
                    data: this.modalData,
                    errorModel: this.modalData.errors,
                }).done(response => {
                    if (response.error !== EErrorCode.NO_ERROR) {
                        this.modalData.errors = response.msg;
                        return false;
                    }
                    this.$refs.changePasswordModalEl.hide();
                    this.Util.showMsg2(response);
                }).always(() => {
                    this.processing = false;
                    this.Util.loadingScreen.hide();
                });
            }
        }
    }
</script>

<style scoped>

</style>
