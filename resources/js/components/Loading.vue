<template>
	<div class="d-flex-center" :class="{'loading-mask': mask}" :style="{'min-height': height}">
        <div class="text-center text-primary my-2">
            <b-spinner class="align-middle"/>
            <strong>{{ $t('common.loading') }}</strong>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ALoading",
        props: {
            height: {
                type: String,
                default: '300px',
            },
            mask: Boolean,
        }
    }
</script>

<style scoped>

</style>
