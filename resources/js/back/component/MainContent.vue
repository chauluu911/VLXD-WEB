<template>
    <div class="main-content">
        <div class="w-100" v-if="!$router.currentRoute.meta.noHeader">
            <router-view class="main-content__header mb-3" name="header"/>
        </div>
        <router-view @change-header="this.header.title = $event"/>
        <router-view class="main-content__footer" name="footer"/>
    </div>
</template>

<script>
    export default {
        name: "MainContent",
        data() {
            return {
                header: {
                    title: '',
                }
            }
        }
    }
</script>

<style scoped>

</style>
