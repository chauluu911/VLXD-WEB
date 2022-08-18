<template>
    <b-button :variant="variant" @click="onClickHandler" :disabled="disabled || loading" :to="to">
        <i class="fas fa-spinner fa-pulse" :class="iconClass" v-if="loading"></i>
        <slot v-else name="icon"></slot>
        <slot></slot>
    </b-button>
</template>

<script>
    export default {
        props: {
            disabled: {
                type: Boolean,
                default: false
            },
            loading: {
                type: Boolean,
                default: false
            },
            iconClass: {
                type: Array,
            },
            variant: {
                type: String,
                default: 'primary',
            },
            to: {
                type: Object,
            }
        },
        methods: {
            onClickHandler(e) {
                this.$emit('click', e);
            }
        }
    }
</script>

<style scoped>

</style>
