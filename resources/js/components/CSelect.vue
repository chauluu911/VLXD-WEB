<template>
    <a-select
        v-if="!useVueSelect"
        :placeholder="placeholder"
        class="form-control"
        :class="{'is-invalid': error, 'extend-height': expansible}"
        :id="id"
        :close-on-select="closeOnSelect"
        :disabled="disabled"
        @search-change="searchItemList"
        :options="filteredOptions"
        label="name"
        v-model="selectedItem"
        :loading="isLoading"
        :internal-search="false"
        :show-labels="false"
        :hide-selected="true"
		:allow-empty="allowEmpty"
        ref="vueSelectEl"
        :multiple="multiple"
        :taggable="taggable"
        @remove="$_onRemove"
    >
        <template #noOptions>
            <slot name="noOptions">
				<span v-if="!getDataOnInit && !searchString">{{ $t('filter.type_to_search') }}</span>
				<span v-else>{{ $t('filter.empty') }}</span>
            </slot>
        </template>
        <template #noResult>
            <slot name="noResult">
				<span v-if="!getDataOnInit && !searchString">{{ $t('filter.type_to_search') }}</span>
				<span v-else>{{ $t('filter.empty') }}</span>
            </slot>
        </template>
        <template #single-label="option">
            <slot name="single-label" v-bind="option">
                {{ option.option.name }}
            </slot>
        </template>
        <template #option="option">
            <slot v-if="!loading" name="option" v-bind="option.option">
                {{ option.option.name }}
            </slot>
            <div v-else class="text-center">
                <b-spinner variant="primary"/>
            </div>
        </template>
        <template #caret="{toggle}">
            <span class="multiselect__select custom-caret" @mousedown.prevent.stop="toggle">
                <open-indicator/>
            </span>
        </template>
    </a-select>
    <d-select
        v-else
        :placeholder="placeholder"
        class="form-control"
        :class="{'is-invalid': error}"
        :input-id="id"
        :filterable="false"
        :clear-search-on-select="clearSearchOnSelect"
        :close-on-select="closeOnSelect"
        :disabled="disabled"
        @search="searchItemList"
        :options="filteredOptions"
        label="name"
        v-model="selectedItem"
        :loading="isLoading"
		:clearable="allowEmpty"
        ref="vueSelectEl"
    >
        <template slot="no-options">
            <slot name="noOptions">
				<span v-if="!getDataOnInit && !searchString">{{ $t('filter.type_to_search') }}</span>
                <span v-else>{{ $t('filter.empty') }}</span>
            </slot>
        </template>
        <template #selected-option="option">
            <slot name="single-label" v-bind="option">
                {{ option.name }}
            </slot>
        </template>
        <template #option="option">
            <slot v-if="!option.loading" name="option" v-bind="option">
                {{ option.name }}
            </slot>
        </template>
        <template #list-footer>
            <div class="text-center" v-if="processing">
                <b-spinner variant="primary"/>
            </div>
            <slot name="afterList" v-else></slot>
        </template>
        <template #spinner="{loading}">
            <div v-if="loading" class="multiselect__spinner"></div>
        </template>
    </d-select>
</template>

<script>
    import ErrorCode from "../constants/error-code";
    import OpenIndicator from 'vue-select/src/components/OpenIndicator.vue';
    import Util from '../lib/common';

    export default {
        name: "CSelect",
        components: {OpenIndicator},
        props: {
            id: String,
            disabled: {
                type: Boolean,
            },
            selectable: {
                type: Boolean,
                default: true,
            },
            customRequestData: {
                type: Object,
                default: () => ({}),
            },
            error: {
                default: false,
            },
            value: {
                default: null,
            },
            clearSearchOnSelect: {
                type: Boolean,
                default: false,
            },
            closeOnSelect: {
                type: Boolean,
                default: true,
            },
            placeholder: {
                type: String,
            },
            searchRoute: {
                type: String,
                required: true,
            },
            dataFilter: {
                type: Function,
                default: (item) => true,
            },
            loading: {
                type: Boolean,
            },
            getDataOnInit: {
                type: Boolean,
                default: true,
            },
            useVueSelect: {
                type: Boolean,
                default: false,
            },
            watchFilterChange: {
                type: Boolean,
                default: true,
            },
			allowEmpty: {
            	type: Boolean,
				default: true,
			},
            multiple: {
                type: Boolean,
                default: false,
            },
            taggable: {
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                page: 1,
                total: 1,
                options: [],
                searchString: '',
                timeoutWaitUserType: null,
                currentRequest: null,
                selectedItem: null,
                processing: false,
                loadingFunc: null,
                expansible: false,
            }
        },
        watch: {
            value(val) {
                if (this.multiple) {
                    this.expansible = true;
                }
                this.selectedItem = val;
            },
            selectedItem(val) {
                if (this.selectable || this.processing) {
                    this.$emit('input', val);
                } else {
                    this.$emit('change', val);
                    this.selectedItem = null;
                }
                if (this.selectedItem == null || this.multiple && this.selectedItem.length == 0) {
                    this.expansible = false;
                }
            },
            customRequestData() {
                if (!this.watchFilterChange) {
                    return;
                }
                this.selectedItem = null;
                this.searchItemList(this.searchString);
            }

        },
        computed: {
            filteredOptions() {
                return this.options.filter(this.dataFilter);
            },
            isLoading() {
                return this.loading || this.processing;
            }
        },
        methods: {
            $_onRemove(val) {
                this.selectedItem.splice(this.selectedItem.indexOf(val), 1);
            },
            $_initVueSelectLoadMore() {
                let scrollCb = (event) => {
                    let el = event.target;
                    if (!this.currentRequest
                        && this.page < this.total
                        && el.scrollTop + el.clientHeight >= el.scrollHeight) {
                        this.searchItemList(this.searchString, this.loadingFunc, this.page + 1);
                        this.$nextTick(() => {
                            $(el).scrollTop($(el)[0].scrollHeight);
                        });
                    }
                };

                if (this.useVueSelect) {
                    let optionListId = $(this.$refs.vueSelectEl.$el).find('.vs__dropdown-toggle').attr('aria-owns');
                    document.addEventListener('scroll', function (event) {
                        if (event.target.id === optionListId) {
                            scrollCb(event);
                        }
                    }, true);
                } else {
                    $(this.$refs.vueSelectEl.$el).find('.multiselect__content-wrapper').on('scroll', scrollCb);
                }
            },
            searchItemList(search = '', loadingFunc, page = 1) {
                if (typeof loadingFunc === 'function') {
                    this.loadingFunc = loadingFunc;
                }
                this.searchString = search;
                this.$emit('search-string-change', this.searchString);

                if (!page || page <= 1) {
                    page = 1;
                }

                this.processing = true;
                if (this.loadingFunc) {
                    this.loadingFunc(true);
                }
                if (this.timeoutWaitUserType) {
                    clearTimeout(this.timeoutWaitUserType);
                }

                this.timeoutWaitUserType = setTimeout(() => {
                    this.timeoutWaitUserType = null;
                    if (this.currentRequest !== null) {
                        this.currentRequest.abort();
                        this.currentRequest = null;
                    }
                    let requestData = {
                        'q': search.trim(),
                        'page': page,
                        ...this.customRequestData,
                    };

                    const apiCaller = this.useFrontApi ? Util.getFront : Util.post;
                    this.currentRequest = apiCaller({
                        url: this.searchRoute,
                        data: requestData,
                    }).done((res) => {
                        if (res.error === ErrorCode.NO_ERROR) {
                            if (page > 1) {
                                this.options.splice(this.options.length - 1, 1);
                                this.options = this.options.concat(res.data.data);
                                this.$nextTick(() => {
                                    let $el = $(`#${this.id} .dropdown-menu`);
                                    if ($el.length) {
                                        $el.scrollTop($el[0].scrollHeight * (this.page - 1) / this.page - $el[0].clientHeight + 50);
                                    }
                                });
                            } else {
                                this.options = [];
                                this.options = res.data.data;
                            }
                            this.page = res.data.current_page;
                            this.total = res.data.last_page;
                        }
                    }).fail(() => {

                    }).always(() => {
                        this.currentRequest = null;
                        if (this.loadingFunc) {
                            this.loadingFunc(false);
                        }
                        this.processing = false;
                    });
                }, 1000);
            },
        },
        mounted() {
        	if (this.getDataOnInit) {
				this.searchItemList('');
			}
            this.$_initVueSelectLoadMore();
        }
    }
</script>

<style scoped>
    .vs__open-indicator {
        display: none;
    }
    .vs--open .vs__open-indicator {
        transform: none;
    }
</style>
