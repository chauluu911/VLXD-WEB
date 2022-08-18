<template>
    <b-row>
  <!--       <b-col md="48" class="mb-3">
            <b-button-group>
                <b-button
                    class="btn__option"
                    :class="{'btn__option--active': this.$route.meta.module === 'info'}"
                    :to="{name: router.info, params:{userCode: id}}"
                >
                    {{$t('tab.title.user-info')}}
                </b-button>
                <b-button
                    class="btn__option"
                    v-if="customerType != ECustomerType.ADVERTISER"
                    :class="{'btn__option--active': this.$route.meta.module === 'contact'}"
                    :to="{name: router.contact, params:{userCode: id}}"
                >
                    {{$t('tab.title.contact-history')}}
                </b-button>
                <b-button
                    class="btn__option"
                    v-if="customerType == ECustomerType.SELLER"
                    :class="{'btn__option--active': this.$route.meta.module === 'business'}"
                    :to="{name: 'seller.business', params:{userCode: id}}"
                >
                    {{$t('tab.title.post')}}
                </b-button>
                <b-button
                    class="btn__option"
                    v-if="customerType == ECustomerType.ADVERTISER"
                    :class="{'btn__option--active': this.$route.meta.module === 'promo-list'}"
                    :to="{name: 'promo.list', params:{userCode: id}}"
                >
                    {{$t('tab.title.advertise-list')}}
                </b-button>
            </b-button-group>
        </b-col> -->
        <b-col md="48">
            <router-view></router-view>
        </b-col>
    </b-row>
</template>

<script>
    import ECustomerType from "../../constants/customer-type";
    import userDetailMessage from '../../locales/back/user/user-detail'

    export default {
        name: "UserDetail",
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
                id: this.$route.params.userCode,
            }
        },
        computed: {
            router() {
                return {
                    info: this.customerType == ECustomerType.SELLER ? 'seller.info' : 
                        this.customerType == ECustomerType.BUYER ? 'buyer.info' : 'advertiser.info',
                    contact: this.customerType == ECustomerType.SELLER ? 'seller.contact' : 'buyer.contact'
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
