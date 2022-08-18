<template>
    <form :class="[item.name]" :action="checkoutUrl" method="POST">
        <input type="hidden" name="OrdersForPayoo" :value="orderForPayoo.xml" />
        <input type="hidden" name="CheckSum" :value="orderForPayoo.checksum" />
        <input type="hidden" name="pm" :value="item.name">
        <input type="hidden" name="bc" v-model="bankCode">
        <a @click="submitPayoo(item.name)">
            <div>{{ $t(`purchase.radio.${item.name}`) }}</div>
            <div v-if="orderForPayoo.fee > 0">
                {{ $t(`purchase.radio.${item.name}-fee`) }} (+{{ orderForPayoo[item.name].fee }} = {{ orderForPayoo[item.name].total_vi }})
            </div>
            <div v-else>
                {{ orderForPayoo[item.name].total_vi }}
            </div>
        </a>
        <div v-if="item.icons && item.icons.length" class="payoo-icons-list">
            <a v-for="icon in item.icons" @click="submitPayoo(item.name, icon.code)"
               class="payoo-icon" :class="[`payoo-icon-${icon.code}`]"></a>
        </div>
    </form>
</template>

<script>
    export default {
        name: "PayooForm",
        props: {
            checkoutUrl: {
                type: String,
                required: true,
            },
            item: {
                type: Object,
                required: true,
            },
            orderForPayoo: {
                type: Object,
                required: true,
            }
        },
        data() {
            return {
                bankCode: null,
            }
        },
        methods: {
            submitPayoo(paymentMethod, bankCode = null) {
                this.bankCode = bankCode;
                this.$nextTick(() => {
                    $(`form.${paymentMethod}`).submit();
                    common.loading.show('body');
                });
            }
        }
    }
</script>
