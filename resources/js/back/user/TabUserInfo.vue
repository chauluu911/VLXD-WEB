<template>
    <div class="main-content">
        <span>tab User info</span>
    </div>
</template>

<script>
    export default {
        name: "UserDetail",
        props: {
            userType: {
                type: Number
            },
        },
        data() {
            return {
                header: {
                    title: '',
                }
            }
        },
        mounted() {
            
        },
        // created() {
        //     this.$store.commit('updateBreadcrumbsState', [
        //         {
        //             text: 'Chi tiết',
        //             to: { name: 'home' }
        //         },
        //         {
        //             text: 'ID123123123',
        //             to: { name: 'home' }
        //         }
        //     ]);
        //     this.$store.commit('updateFilterFormState', [
        //         {
        //             type: 'date',
        //             name: 'createdAtFrom',
        //             placeholder: this.$t('placeholder.filter.created_at_from'),
        //             dropleft: true,
        //         },
        //         {
        //             type: 'date',
        //             name: 'createdAtTo',
        //             placeholder: this.$t('placeholder.filter.created_at_to'),
        //             dropleft: true,
        //         },
        //     ]);
        // },
        // watch: {
        //     filterValueState(val) {
        //         console.log(val);
        //     }
        // }
    }
</script>

<style scoped>

</style>
