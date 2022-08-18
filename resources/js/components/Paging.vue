<template>
    <div class="pagination-wrapper">
        <b-pagination v-model="chosenPage" @change="onChangePageHandler" :total-rows="total" :per-page="chosenPageSize" :limit="numberOfShowingPage + 2" :disabled="disabled"/>

        <div v-if="total > 0" class="page-size-selector">
            <label class="mt-2 mr-2 ml-3" @click="$refs.pageSizeSelectEl.click()">{{ $t('common.pagination.label.showPageSize') }}:</label>
            <select name="quantity" v-model="chosenPageSize" class="custom-select" style="width: 75px;" ref="pageSizeSelectEl" :disabled="disabled">
                <option v-for="sz in pageSizes" :value="sz">{{ sz }}</option>
            </select>
        </div>
    </div>
</template>

<script>
    import { BPagination } from 'bootstrap-vue';
    export default {
        components: {
            BPagination,
        },
        data() {
            return {
                chosenPageSize: this.initPageSize,
                chosenPage: 1,
            }
        },
        props: {
            total: {
                type: Number,
                default: 0
            },
            pageSizes: {
                type: Array,
                default: () => ([10, 30, 50, 100, 500])
            },
            initPageSize: {
                type: Number,
                default: 10
            },
            numberOfShowingPage: {
                type: Number,
                default: 3
            },
            disabled: {
                type: Boolean,
                default: false
            }
        },
        watch: {
            chosenPageSize(newVal, oldVal) {
                this.$emit('page-change', {size: newVal, page: 1});
            }
        },
        methods: {
            onChangePageHandler(page) {
                this.$emit('page-change', {page: page, size: this.chosenPageSize});
            },
            setPage(paging, willEmitEvent = true) {
                if (paging.page) {
                    this.chosenPage = paging.page;
                }
                if (paging.size) {
                    this.chosenPageSize = paging.size;
                }
                if (willEmitEvent) {
                    this.onChangePageHandler(this.chosenPage);
                }
            }
        }
    }
</script>

<style scoped>  

</style>
