<template>
    <div class="fa-lg position-relative">
        <i class="fas fa-search position-absolute" style="top: 25%; left: 5px; color: lightgray"></i>
        <b-form-input :id="id" class="w-100" style="padding-left: 1.7rem" :placeholder="placeholder" :disabled="disabled" :debounce="debounce" :value="value" @update="onInputChanged"></b-form-input>
        <i v-if="searching" class="fas fa-spinner fa-pulse position-absolute" style="top: 25%; right: 5px;"></i>
    </div>
</template>

<script>
    export default {
        props: {
            id: String,
            placeholder: String,
            disabled: Boolean,
            debounce: Number,
            value: String,
            searching: Boolean
        },
        methods: {
            onInputChanged(value) {
                this.$emit('input', value);
            }
        }
    }
</script>

<style scoped>

</style>
