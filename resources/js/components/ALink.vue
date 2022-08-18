<template>
    <b-link v-b-tooltip.hover @click.stop.prevent="onClickHandler" :title="title">
        <slot></slot>
        <i class="fas fa-spinner fa-pulse" v-if="loading"></i>
    </b-link>
</template>

<script>
    export default {
        props: {
            title: {
                type: String,
            },
            disabled: {
                type: Boolean,
                default: false
            },
            loading: {
                type: Boolean,
                default: false
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
