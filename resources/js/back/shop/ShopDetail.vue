<template>
    <b-row>
        <b-col md="48" class="mb-3">
            <b-button-group>
                <b-button
                    class="btn__option border-0"
                    :class="{'btn__option--active': this.$route.meta.module === 'info'}"
                    :to="{name: router.info, params:{shopId: id, action: 'edit'}}"
                >
                    Thông tin shop
                </b-button>
                <b-button
                    class="btn__option border-0"
                    :class="{'btn__option--active': this.$route.meta.module === 'resource'}"
                    :to="{name: router.resource, params:{shopId: id}}"
                >
                    Hình ảnh và video
                </b-button>
                <b-button
                    class="btn__option border-0"
                    :class="{'btn__option--active': this.$route.meta.module === 'order'}"
                    :to="{name: router.order, params:{shopId: id}}"
                >
                    Quản lý đơn hàng
                </b-button>
            </b-button-group>
        </b-col>
        <b-col md="48">
            <router-view></router-view>
        </b-col>
    </b-row>
</template>

<script>
    import ECustomerType from "../../constants/customer-type";
    import userDetailMessage from '../../locales/back/user/user-detail'

    export default {
        i18n: {
            messages: userDetailMessage
        },
        inject: ['Util'],
        props: {
            customerType: {
                type: Number
            },
        },
        data() {
            return {
                ECustomerType,
                id: this.$route.params.shopId,
            }
        },
        computed: {
            router() {
                return {
                    info: 'shop.info',
                    order: 'shop.order',
                    resource: 'shop.resource',
                }
            },
        },

        created() {
            this.$store.commit('updateFilterFormState', [
                {
                    type: 'date',
                    name: 'createdAtFrom',
                    placeholder: this.$t('placeholder.filter.created_at_from'),
                    dropleft: true,
                },
                {
                    type: 'date',
                    name: 'createdAtTo',
                    placeholder: this.$t('placeholder.filter.created_at_to'),
                    dropleft: true,
                },
            ]);
        },
        watch: {
            filterValueState(val) {
                console.log(val);
            }
        },
        methods: {
        }
    }
</script>

<style scoped>

</style>
