<template>
    <b-input-group :class="errors && errors.length > 0 ? 'is-invalid' : 'is-valid'">
        <date-time-picker
			:id="id"
			ref="dateTimePickerEl"
			v-model="selectedDate"
			class="form-control border-right-0"
			:class="errors == null ? '' : (errors.length > 0 ? 'is-invalid' : 'is-valid')"
			:placeholder="placeholder"
			:config="config"
			:disabled="disabled"
			@dp-hide="$emit('dp-hide', $event)"
			@dp-change="$emit('dp-change', $event)"
		/>
        <b-input-group-append>
            <label
				:for="id"
				class="input-group-text border-left-0 bg-white mb-0"
				:class="{'bg-disabled': disabled}"
			>
                <i
                    class="cursor-pointer"
                    :class="[role === 'time' ? 'far fa-clock' : 'far fa-calendar-alt']"
                    style="font-size: 18px; line-height: 18px"
                />
            </label>
        </b-input-group-append>
    </b-input-group>
</template>

<script>
    import DateTimePicker from 'vue-bootstrap-datetimepicker';

    const dateTimePickerConfig = {
        format: 'DD/MM/YYYY',
        showClear: true,
        showClose: true,
        keepOpen: false,
        //maxDate: this.role === 'birthday' ? maxDate : false,
        icons: {
            clear: 'far fa-calendar-times',
        },
    };

    export default {
        inject: ['DateUtil'],
        components: { DateTimePicker },
        props: {
            id: String,
            value: String,
            // normal|birthday|time
            role: {
                type: String,
                default: 'normal'
            },
            placeholder: {
                type: String,
                default: '--/--/----',
            },
            customConfig: {
                type: Object,
                default: () => ({})
            },
            errors: {
                validator: prop => typeof prop === 'string' || prop === null,
                default: null
            },
            disabled: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                selectedDate: this.value,
            }
        },
        computed: {
            config() {
                let config = Object.assign({}, dateTimePickerConfig);
                Object.keys(this.customConfig || {}).forEach((key) => {
                    config[key] = this.customConfig[key];
                });
                switch (this.role) {
                    case 'normal':
                        config.useCurrent = false;
                        break;
                    case 'birthday':
                        let maxDate = this.DateUtil.addYears(-15, this.DateUtil.today());
                        config.maxDate = maxDate;
                        // config.defaultDate = maxDate;
                        break;
                }
                return config;
            }
        },
        watch: {
			value(newVal) {
				if (typeof newVal === 'string' && newVal) {
					this.selectedDate = newVal;
				}
			},
            selectedDate(newVal) {
                this.$emit('input', newVal);
            },
            disabled(newVal) {
                if (newVal) {
                    this.$refs.dateTimePickerEl.dp.disable();
                } else {
                    this.$refs.dateTimePickerEl.dp.enable();
                }
            }
        },
        /*methods: {
            onPickDate(e) {
                console.log(e);
            }
        }*/
    }
</script>

<style scoped>

</style>
