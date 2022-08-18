<template>
    <span>
        <label v-show="!processing" :class="labelClass" class="d-inline-block cursor-pointer mb-0">
            <b-form-checkbox v-model="checked" @change="onCheckedStatusChanged" :switch="useSwitch" :checked="checkedState" :disabled="disabled" v-show="showCheckbox"></b-form-checkbox>
            <slot></slot>
        </label>
        <i class="fas fa-spinner fa-pulse" v-if="processing"></i>
    </span>
</template>

<script>
    export default {
        data() {
            return {
                checked: this.value
            }
        },
        props: {
            value: {
                type: Boolean,
                default: false
            },
            disabled: {
                type: Boolean,
                default: false
            },
            processing: {
                type: Boolean,
                default: false
            },
            checkedState: Boolean,
            useSwitch: Boolean,
            labelClass: {
                type: String,
            },
            hideCheckbox: Boolean,
        },
        computed: {
            showCheckbox() {
                return (this.useSwitch || !this.processing) && !this.hideCheckbox;
            }
        },
        watch: {
            value(newVal) {
                this.checked = newVal;
            }
        },
        methods: {
            onCheckedStatusChanged(val) {
                this.$emit('input', val);
                this.$emit('change', val);
            }
        }
    }
</script>

<style scoped>

</style>
