const MetaStackMixin = {
    data() {
        return {
            metaStack: {},
        }
    },
    watch: {
        $route() {
            let res = {};
            !this.$router || this.$router.currentRoute.matched.forEach((route) => {
                res = Object.assign(res, route.meta);
            });
            this.metaStack = res;
        }
    },
    created() {
        !this.$router || this.$router.currentRoute.matched.forEach((route) => {
            this.metaStack = Object.assign(this.metaStack, route.meta);
        });
    },
};
export default MetaStackMixin;
