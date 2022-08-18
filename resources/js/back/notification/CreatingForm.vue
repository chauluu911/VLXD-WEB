<template>
    <b-row>
        <b-col cols="48">
            <div class="content__inner">
                <b-row class="mb-3 border-bottom border-primary">
                    <b-col class="d-flex-center-y mb-2" style="min-width: 400px;">
						<div class="h4 d-inline-block mb-0 mr-4 text-primary">
							<span v-if="$route.params.action === 'edit'">
                                {{ $t('common.title.edit', {objectName: $t('object_name')}) }}
                            </span>
							<span v-else-if="$route.params.action === 'detail'">
                                {{ $t('common.title.detail', {objectName: $t('object_name')}) }}
                            </span>
							<span v-else>
                                {{ $t('common.title.create', {objectName: $t('object_name')}) }}
                            </span>
						</div>
						<div
							v-if="formData.status.value === EStatus.DELETED || formData.status.value === EStatus.ACTIVE"
							class="btn text-white cursor-default"
							:class="[formData.status.value === EStatus.DELETED ? 'bg-danger' : 'bg-primary']"
						>
                            <span v-if="formData.status.value === EStatus.DELETED">
                                {{ $t('constant.status.deleted') }}
                            </span>
							<span v-else>
                                {{ $t('constant.status.sent') }}
                            </span>
						</div>
                    </b-col>
                    <b-col class="text-right mb-2" style="min-width: 300px;">
						<b-link @click="$router.go(-1)" :disabled="loading || processing" class="text-primary mr-3">
							{{ $t('common.button.back') }}
						</b-link>
						<template v-if="!isEditing && formData.status.value === EStatus.WAITING">
							<a-button
								:icon-class="['mr-lg-2']"
								variant="primary"
								class="mr-2"
								:loading="loading || processing"
								:to="{name: $route.name, params: {...$route.params, action: 'edit'}}"
							>
								<template #icon>
									<f-edit-icon size="20" class="mr-lg-2"/>
								</template>
								<template #default>
									{{ $t('common.button.edit2', {objectName: $t('object_name').toLowerCase()}) }}
								</template>
							</a-button>
							<a-button
								:icon-class="['mr-lg-2']"
								variant="outline-danger"
								class="mr-2"
								:loading="loading || processing"
								@click="deleteItem"
							>
								<template #icon>
									<f-trash-icon size="20" class="mr-lg-2"/>
								</template>
								<template #default>
									{{ $t('common.button.delete2', {objectName: $t('object_name').toLowerCase()}) }}
								</template>
							</a-button>
						</template>
						<template v-else-if="isEditing">
							<a-button
								:icon-class="['mr-lg-2']"
								variant="primary"
								class="mr-2"
								@click="submit"
								:loading="loading || processing"
							>
								<template #icon>
									<f-save-icon size="20" class="mr-lg-2"/>
								</template>
								<template #default>
									{{ $t('common.button.save2', {objectName: $t('object_name').toLowerCase()}) }}
								</template>
							</a-button>
						</template>
                    </b-col>
                </b-row>
                <b-form autocomplete="off">
                    <b-row class="mb-1">
                        <b-col
                            :md="sidebarState.active ? 48 : 24"
                            :lg="24"
                        >
                            <b-form-group
                                :label="$t('attribute.schedule_at')"
                                :label-class="isEditing ? 'required' : ''"
                            >
                                <b-row :class="{'is-invalid': formData.date.error || formData.time.error}">
                                    <b-col class="pr-0">
                                        <a-date-time-picker
                                            id="notification-date-input"
                                            v-model="formData.date.value"
                                            :placeholder="$t('placeholder.schedule_date')"
                                            role="normal"
                                            :errors="formData.date.error"
                                            :disabled="!isEditing"
                                            :custom-config="{minDate: DateUtil.today()}"
                                            @dp-change="onInputChange"
                                        />
                                    </b-col>
                                    <b-col class="pl-0">
                                        <a-date-time-picker
                                            id="notification-time-input"
                                            v-model="formData.time.value"
                                            :placeholder="$t('placeholder.schedule_time')"
                                            role="time"
                                            :errors="formData.time.error"
                                            :disabled="!isEditing"
                                            :custom-config="{minDate: formData.date.value === DateUtil.getDateString(DateUtil.today()) ?  new Date() : DateUtil.today(), format: 'HH:mm:ss'}"
                                            @dp-change="onInputChange"
                                        />
                                    </b-col>
                                </b-row>
                                <b-form-invalid-feedback>
                                    {{ formData.date.error || formData.time.error }}
                                </b-form-invalid-feedback>
                            </b-form-group>
                        </b-col>
                        <b-col
                            :md="sidebarState.active ? 48 : 24"
                            :lg="24"
                        >
                            <b-form-group
                                :label="$t('attribute.target')"
                                :label-class="isEditing ? 'required' : ''"
                            >
                                <b-input-group :class="{'is-invalid': formData.targetType.error || formData.targetList.error}">
                                    <b-select
                                        v-model="formData.targetType.value"
                                        :state="formData.targetType.error ? false : null"
                                        @change="onInputChange"
                                        :disabled="!isEditing"
                                        style="height:auto"
                                    >
                                        <option v-for="option in targetTypeList" :value="option.value">
                                            {{ option.name }}
                                        </option>
                                    </b-select>
                                    <c-select
                                        v-if="isEditing"
                                        v-show="hasUserFilter"
                                        :error="formData.targetType.error"
                                        :placeholder="$t('placeholder.user')"
                                        :search-route="routes.userSearch"
                                        :custom-request-data="userFilterRequestData"
                                        :selectable="false"
                                        @change="addUserToTargetList"
                                        :class="{'is-invalid': formData.targetList.error}"
                                    >
                                        <template #option="option">
                                            <div>
                                                {{ option.name }} - {{ option.phone }}
                                            </div>
                                            <div>
                                                {{ option.typeStr }}
                                            </div>
                                        </template>
                                    </c-select>
                                    <div v-if="formData.targetList.error" class="invalid-feedback d-block">
                                        {{formData.targetList.error}}
                                    </div>
                                </b-input-group>
                                <b-form-invalid-feedback>
                                    {{ formData.targetType.error }}
                                </b-form-invalid-feedback>
                            </b-form-group>
                            <div v-show="formData.targetType.value === ENotificationScheduleTargetType.SPECIAL_USER">
                                <b-badge
                                    v-for="(user, index) in formData.targetList.value"
                                    :key="index"
                                    class="bg-disabled position-relative mr-2"
                                    style="font-size: 14px; font-weight: normal; text-align: left; color: black; padding-right: 20px;"
                                >
                                    <i
										v-if="isEditing"
                                        class="fas fa-times cursor-pointer position-absolute"
                                        style="right: 5px; top: 10px;"
                                        @click="removeItem(index)"
                                    />
                                    {{ user.name }} <br>
                                    {{ user.phone }}
                                </b-badge>
                            </div>
                        </b-col>
                    </b-row>
                    <b-row class="mb-4">
                        <b-col cols="48">
                            <b-form-group
                                :label="$t('attribute.title')"
                                :label-class="isEditing ? 'required' : ''"
                            >
                                <b-input
                                    v-model.trim="formData.titleVi.value"
                                    trim
                                    type="text"
                                    :placeholder="$t('placeholder.title')"
                                    :state="formData.titleVi.error ? false : null"
                                    @change="onInputChange"
                                    :disabled="!isEditing"
                                />
                                <b-form-invalid-feedback>
                                    {{ formData.titleVi.error }}
                                </b-form-invalid-feedback>
                            </b-form-group>
                            <b-form-group :label="$t('attribute.content')" label-class="required">
                                <b-form-textarea
                                    v-model="formData.contentVi.value"
                                    :placeholder="$t('placeholder.content')"
                                    rows="5"
                                    :disabled="!isEditing ? true : false"
                                ></b-form-textarea>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-form>
            </div>
        </b-col>
    </b-row>
</template>

<script>
    import getNotificationScheduleFormMessages from '../../locales/back/notification-management-form';
    import CreatingFormMixin from '../mixins/creating-form-mixin';
    import FSaveIcon from 'vue-feather-icons/icons/SaveIcon';
    import FEditIcon from 'vue-feather-icons/icons/EditIcon';
    import FTrashIcon from 'vue-feather-icons/icons/TrashIcon';
    import ECreatingFormStage from '../../constants/creating-form-stage';
    import {mapState} from "vuex";
    import ENotificationScheduleTargetType from "../../constants/notification-target-type";
    import EStatus from "../../constants/status";

    export default {
        name: 'CategoryForm',
        mixins: [CreatingFormMixin],
        i18n: {
            messages: getNotificationScheduleFormMessages,
        },
        inject: ['Util', 'StringUtil', 'DateUtil'],
        components: {
            FSaveIcon,
			FEditIcon,
			FTrashIcon,
        },
        data() {
            return {
                multiLang: false,
				ENotificationScheduleTargetType,
				EStatus,
            };
        },
        computed: {
            ...mapState(['sidebarState']),
            targetTypeList() {
                return [
                    {
                        name: this.$t('constant.target_type.all'),
                        value: ENotificationScheduleTargetType.ALL,
                    },
                    {
                        name: this.$t('constant.target_type.specific'),
                        value: ENotificationScheduleTargetType.SPECIAL_USER,
                    }
                ]
            },
            routes() {
                return {
                    userSearch: `${this.$route.meta.baseUrl}/user`,
                    save: `${this.$route.meta.baseUrl}/save`,
                    info: `${this.$route.meta.baseUrl}/${this.$route.params.notificationScheduleId}/info`,
					delete: `${this.$route.meta.baseUrl}/delete`,
                }
            },
            hasUserFilter() {
                return this.formData.targetType.value === ENotificationScheduleTargetType.SPECIAL_USER;
            },
            userFilterRequestData() {
                return {
                    code_not_in: this.formData.targetList.value.map((item) => item.code),
                    status: EStatus.ACTIVE
                }
            },
        },
        created() {
            this.backRoute = {
                name: 'notification.list',
            };
            this.$store.commit('updateFilterFormState', []);
            this.$store.commit('updateBreadcrumbsState', [
                {
                    text: this.$t('notification_title'),
                    to: {name: 'notification.list'}
                },
            ]);
            if (this.$route.params.notificationScheduleId) {
                this.initEditForm();
            }
        },
		beforeRouteUpdate(to, from, next) {
			if (to.name === from.name) {
				this.stage = this.getFormStage(to);
				this.initEditForm();
			}
			next();
		},
        methods: {
			getFormStage(route) {
				if (!route.params.notificationScheduleId) {
					return ECreatingFormStage.CREATING;
				} else if (route.params.action === 'edit') {
					return ECreatingFormStage.UPDATING;
				} else {
					return ECreatingFormStage.READONLY;
				}
			},
            defaultFormData() {
                return {
                    id: this.$route.params.notificationScheduleId,
                    titleVi: this.getAttributeData(),
                    contentVi: this.getAttributeData(),
                    date: this.getAttributeData(),
                    time: this.getAttributeData(),
                    targetType: this.getAttributeData({value: ENotificationScheduleTargetType.ALL}),
                    targetList: this.getAttributeData({value: []}),

					// readonly
					status: this.getAttributeData(),
                };
            },
            initEditForm() {
                this.processing = true;
                this.Util.get({
                    url: this.routes.info,
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.confirm(
                            res.msg,
                            (confirm) => {
                                if (!confirm) {
                                    this.$router.go(-1);
                                    return;
                                }
                                this.initEditForm();
                            },
                            {
                                okTitle: this.$t('common.button.retry'),
                                cancelTitle: this.$t('common.button.back'),
                            },
                        );
                        return;
                    }
                    this.multiLang = res.data.multiLanguage;
                    this.formData.titleVi.value = res.data.titleVi || '';
                    this.formData.contentVi.value = res.data.contentVi || '';
                    this.formData.targetType.value = res.data.targetType || ENotificationScheduleTargetType.ALL;
                    this.formData.targetList.value = res.data.targetList || [];
                    this.formData.status.value = res.data.status;

                    if (res.data.scheduled_at) {
                        let scheduledAt = new Date(res.data.scheduled_at);
                        this.formData.date.value = this.DateUtil.getDateString(scheduledAt);
                        this.formData.time.value = this.DateUtil.getTimeString(scheduledAt, ':', true);
                    }
                }).always(() => {
                    this.processing = false;
                });
            },
            addUserToTargetList(user) {
                if (!user || typeof user !== 'object') {
                    return;
                }
                let isUserAlreadyInList = !this.formData.targetList.value.every((item) => item.code !== user.code);
                if (!isUserAlreadyInList) {
                    this.formData.targetList.value.push(user);
                }
            },
            getFormData() {
                if (!this.validateValue()) {
                    return null;
                }

                let data = {
                    multiLang: true,
                };
                Object.keys(this.formData).forEach((key) => {
                    switch (key) {
                    	case 'status':
                    		break;
                        case 'id':
                            data[key] = this.formData[key];
                            break;
                        case 'titleVi':
                            if (!this.multiLang) {
                                data[key] = this.formData.titleVi.value;
                                break;
                            }
                            data[key] = this.formData[key].value;
                            break;
                        case 'contentVi':
                            if (!this.multiLang) {
                                data[key] = this.formData.contentVi.value;
                                break;
                            }
                            data[key] = this.formData[key].value;
                            break;
                        case 'targetList':
                            data[key] = this.formData[key].value.map((item) => item.code);
                            break;
                        default:
                            data[key] = this.formData[key].value;
                            break;
                    }
                });
                return data;
            },
            showErrors(errors, msg = null) {
                this.footerMsg = msg;
                Object.keys(this.formData).forEach((key) => {
                    if (key === 'id') {
                        return;
                    }
                    this.formData[key].error = errors[key] && errors[key][0] || null;
                });
            },
			onDeleteSuccess(msg) {
				this.Util.showMsg('success', null, msg, {
					onHidden: () => {
						this.initEditForm();
					}
				});
			},

            removeItem(index) {
                this.formData.targetList.value.splice(index, 1);
            },
        },
    }
</script>
