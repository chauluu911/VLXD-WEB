<template>
    <b-row>
        <b-col cols="48">
            <div class="content__inner">
                <b-row class="mb-3 border-bottom border-primary">
                    <b-col style="min-width: 400px;">
                        <div class="d-inline-block h3 mr-4 text-primary">
                            {{ title }}
                        </div>
                    </b-col>
                    <b-col class="text-right mb-3" style="min-width: 300px;">
                        <b-link @click="$router.go(-1)" :disabled="loading || processing" class="text-primary mr-3">
                            {{ $t('common.button.back') }}
                        </b-link>
                        <a-button
                            :icon-class="['mr-lg-2']"
                            variant="primary"
                            class="mr-2"
                            @click="submit"
                            :loading="loadingState"
                        >
                            <template #icon>
                                <f-save-icon size="20" class="mr-lg-2"/>
                            </template>
                            <template #default>
                                {{ $t('common.button.save2', {objectName: $t('object_name').toLowerCase()}) }}
                            </template>
                        </a-button>
                    </b-col>
                </b-row>
                <b-form>
                    <b-row>
                        <b-col md="48">
                            <b-form-group class="font-weight-bold"
                                :label-class="'required'"
                                :label="$t('tab.name')"
                            >
                                <b-form-input class="w-100" v-model="formData.id" required
                                        :placeholder="$t('tab.name')"
                                        :disabled="true"
                                        @change="onInputChange"
                                />
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-form>
            </div>
        </b-col>
    </b-row>
</template>

<script>
    import ECategoryType from "../../../constants/category-type";
    import categoryCreatingFormMessages from '../../../locales/back/category-management-form';
    import FSaveIcon from 'vue-feather-icons/icons/SaveIcon';
    import ECreatingFormStage from '../../../constants/creating-form-stage';
    import {mapState} from "vuex";

    export default {
        name: 'CategoryForm',
        i18n: {
            messages: categoryCreatingFormMessages,
        },
        inject: ['Util', 'StringUtil'],
        components: {
            FSaveIcon,
        },
        data() {
            return {
                ECategoryType,
            };
        },
        computed: {
            
        },
        watch: {
            
        },
        created() {
            
        },
        methods: {
            
        },
    }
</script>
