<template>
    <b-link @click="onClickHandler" :disabled="disabled" :title="title" v-b-tooltip.hover>
        <i class="far fa-trash-alt" v-show="!deleting"></i>
        <i class="fas fa-spinner fa-pulse" v-if="deleting"></i>
    </b-link>
</template>

<script>
    export default {
        props: {
            disabled: {
                type: Boolean,
                default: false
            },
            title: String,
            deleting: {
                type: Boolean,
                default: false
            },
        },
        methods: {
            onClickHandler(e) {
                this.$emit('click');
            }
        }
    }
</script>

<style scoped>

</style>
