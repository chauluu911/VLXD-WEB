<template>
    <div class="mb-3 position-relative">
        <div class="input-group">
            <input class="form-control" style="padding-left: 1.7rem" :disabled="disabled" type="text" :placeholder="placeholder" v-model="searchText" @keypress.enter="$_onEnter">
            <div class="input-group-append">
                <button class="btn btn-primary" type="button" :disabled="disabled" @click="$_onEnter">{{ $t('common.button.search') }}</button>
            </div>
        </div>
        <i class="fas fa-search position-absolute" style="top: 30%; left: 5px; color: lightgray; z-index: 3"></i>
    </div>

</template>

<script>
    export default {
        name: "NameFilter",
        props: {
            value: String,
            placeholder: {
                type: String,
                default: '',
            },
            disabled: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                searchText: null,
            }
        },
        watch: {
            searchText(newVal) {
                this.$emit('input', newVal);
            }
        },
        methods: {
            $_onEnter(e) {
                e.target.blur();
                this.$emit('do-search');
            }
        }
    }
</script>

<style scoped>

</style>
