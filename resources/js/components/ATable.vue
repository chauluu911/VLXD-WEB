<template>
    <div class="table-wrapper">
        <b-table hover :items="items" :fields="fields" :busy="loading" show-empty bordered>
            <template v-slot:table-busy>
                <div class="text-center text-danger my-2">
                    <b-spinner class="align-middle" variant="primary"/>
                    <strong class="text-primary">{{ $t('common.loading') }}</strong>
                </div>
            </template>
            <template v-slot:empty>
                <div class="text-center text-info">
                    <strong>
                        <slot name="nodata">
                            {{ $t('table.empty') }}
                        </slot>
                    </strong>
                </div>
            </template>
            <template v-slot:emptyfiltered>
                <div class="text-center text-info">
                    <strong>
                        <slot name="nodatafiltered">
                            {{ $t('table.empty') }}
                        </slot>
                    </strong>
                </div>
            </template>
            <template v-slot:table-colgroup="scope">
                <col v-for="field in fields"
                     :key="field.key"
                     :style="{ width: field.colWidth }">
            </template>
            <template v-slot:cell(index)="data">
                <span v-if="pagination">{{ data.index + 1 + (pagination.page - 1) * (pagination.size || pagination.sz) }}</span>
                <span v-else>{{ data.index + 1 }}</span>
            </template>
            <template v-for="(_, name) in $scopedSlots" :slot="name" slot-scope="slotData">
                <slot :name="name" v-bind="slotData"/>
            </template>
        </b-table>
    </div>
</template>

<script>
    export default {
        props: {
            items: {
                type: Array,
                default: () => []
            },
            fields: {
                type: Array,
                default: () => []
            },
            pagination: {
                type: Object,
                /*default: () => ({
                    page: 1,
                    size: 10
                })*/
            },
            loading: {
                type: Boolean,
                default: false
            },
        }
    }
</script>

<style scoped>

</style>
