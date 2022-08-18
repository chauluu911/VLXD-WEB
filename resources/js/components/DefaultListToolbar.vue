<template>
	<div class="default-toolbar row">
        <a v-if="exportable" :href="exportRoute" download class="col-md mt-2 mt-md-0 pt-3" :disabled="disabled" style="max-width: 102px;">
            <div class="row align-items-center">
                <img src="/images/icon/awesome-file-export.svg" class="mr-2">
                <span>{{ $t('export') }}</span>
            </div>
        </a>
        <a v-if="importable" class="col-md mt-2 mt-md-0 pt-3" :disabled="disabled" style="max-width: 152px;">
            <div class="row align-items-center">
                <img src="/images/icon/awesome-file-import-black.svg" class="mr-2">
                <span>{{ $t('import') }}</span>
            </div>
        </a>
        <div class="col-md mt-2 mt-md-0">
            <div class="row">
                <div class="pt-3" v-if="hasFilterLabel">
                    <div class="d-flex align-items-center">
                        <img src="/images/icon/feather-filter.svg" class="mr-2">
                        <span>{{ $t('filter') }}</span>
                    </div>
                </div>
                <div class="col">
                    <div v-for="field in parsedFields" :style="{'max-width': field.maxWidth || 'auto'}"  class="mr-3 mt-2 d-inline-block" :class="{'w-100': field.maxWidth}">
                        <label v-if="field.label" :for="field.input_id" class="mr-2" :inner-html.prop="sp2nbsp(field.label)"/>
                        <a-date-time-picker v-if="field.type === 'date'" v-model="filter[field.name]" @input="$nextTick($_change)" role="normal" :disabled="disabled"
                                            :id="field.input_id" :placeholder="field.placeholder" :title="field.placeholder" :custom-config="field.config"/>
                        <select v-else-if="field.type === 'select'" v-model="filter[field.name]" @change="$_change"
                                class="w-100 form-control custom-select" :title="field.placeholder" :id="field.input_id" :disabled="disabled">
                            <option v-if="field.placeholder" :value="null">{{ field.placeholder }}</option>
                            <option v-for="option in field.options" :value="option.value">{{ option.name }}</option>
                        </select>
                        <input v-else :type="field.type" v-model="filter[field.name]" @keyup.enter="$_change" @change="$_change" :id="field.input_id"
                               class="w-100 form-control" :placeholder="field.placeholder" :title="field.placeholder">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import messages from '../locales/back/default-list-toolbar';
    import StringUtil from '../lib/string-utils';

    export default {
        name: "DefaultListToolbar",
        i18n: {
            messages: messages,
        },
        props: {
            disabled: {
                type: Boolean,
                default: false
            },
            exportable: {
                type: Boolean,
                default: false,
            },
            exportRoute: {
                type: String,
                default: '',
            },
            importable: {
                type: Boolean,
                default: false,
            },
            hasFilterLabel: {
                type: Boolean,
                default: false,
            },
            fields: {
                type: Array,
                /*default: () => {
                    return [
                        {
                            type: 'text',
                            name: 'username',
                            maxWidth: '100px',
                            placeholder: 'Họ tên',
                        },
                        {
                            type: 'number',
                            name: 'amount',
                            maxWidth: '100px',
                            placeholder: 'Số lượng',
                        },
                        {
                            type: 'date',
                            name: 'selectedDate',
                            maxWidth: '100px',
                            placeholder: 'Ngày tạo',
                        },
                        {
                            type: 'date-range',
                            name: 'createAt',
                            maxWidth: '220px',
                            placeholderFrom: 'Ngày tạo từ',
                            placeholderTo: 'Ngày tạo đến',
                            separator: 'đến',
                        },
                        {
                            type: 'select',
                            name: 'status',
                            maxWidth: '100px',
                            placeholder: 'Trạng thái',
                            options: [
                                {
                                    value: -2,
                                    name: 'All'
                                },
                                {
                                    value: -1,
                                    name: 'Deleted'
                                },
                                {
                                    value: 0,
                                    name: 'Pending'
                                },
                                {
                                    value: 1,
                                    name: 'Active'
                                }
                            ]
                        },
                    ]
                }*/
            },
            default: {
                type: Object,
                /*default: () => {
                    return {
                        username: 'abc',
                        amount: 1,
                        selectedDate: '10/06/2019',
                        status: 1,
                    }
                }*/
            },
        },
        data() {
            return {
                formId: Math.random().toString(36).substring(2, 15),
                filter: {}
            }
        },
        computed: {
            parsedFields() {
                return this.fields.map((item) => {
                    if (item.label) {
                        item.input_id = `form-${this.formId}-${item.name}`;
                    }
                    return item;
                });
            }
        },
        methods: {
            sp2nbsp: StringUtil.sp2nbsp,
            $_search() {
                let searchData = Object.assign({}, this.filter);
                Object.keys(searchData).forEach((key) => {
                    if (searchData[key] && typeof searchData[key] === 'string') {
                        searchData[key] = searchData[key].trim();
                    }
                });
                this.$emit('search', searchData);
            },
            $_change() {
                this.$nextTick(() => {
                    let searchData = Object.assign({}, this.filter);
                    Object.keys(searchData).forEach((key) => {
                        if (searchData[key] && typeof searchData[key] === 'string') {
                            searchData[key] = searchData[key].trim();
                        }
                    });
                    this.$emit('change', searchData);
                });
            },
            clearFilter() {
                this.filter = {};
            }
        },
        created() {
            this.fields.forEach((field) => {
                this.filter[field.name] = (!this.default || !this.default[field.name]) ? null : this.default[field.name];
            });
        },
    }
</script>
