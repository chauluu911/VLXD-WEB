<template>
    <div class="news-item" @click="redirectTo()">
        <div class="news-img mr-2">
            <div class="thumb" :style="{'background-image': `url(${itemData.avatarPath})`}"></div>
        </div>
        <div class="news-content">
            <div class="news-title" :title="itemData.title" v-shave="{height: 45, character: '...'}">
                {{itemData.title}}
            </div>
            <div class="news-created">
                {{itemData.createdAt}}
            </div>
        </div>
    </div>
</template>

<script>

export default {
    props: {
        itemData: {
            default: null,
        },
    },
    methods: {
        redirectTo() {
            window.location.assign(this.itemData.redirectTo);
        }
    }
}
</script>

<style scoped>

</style>
