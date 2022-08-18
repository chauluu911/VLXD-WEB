<template>
	<div 
		:style="{'max-width': parsedField.maxWidth || 'auto', 'min-width': parsedField.minWidth || 'auto'}"  class="w-100" 
		:class="{'form-inline': inline}"
	>
		<label v-if="parsedField.label" :for="parsedField.input_id" class="mr-2" :inner-html.prop="sp2nbsp(parsedField.label)"/>
		<a-date-time-picker
			v-if="parsedField.type === 'date'"
			:id="parsedField.input_id"
			v-model="internalValue"
			:config="parsedField.config"
			:placeholder="parsedField.placeholder"
			:title="parsedField.placeholder"
			@dp-hide="$_change"
			v-bind="otherConfigs"
		/>
		<b-select v-else-if="parsedField.type === 'select'"
		        :id="parsedField.input_id"
		        v-model="internalValue"
		        @change="$_change"
		        class="w-100"
		        :title="parsedField.placeholder"
		        :disabled="disabled">
			<option v-if="parsedField.placeholder"
			        :value="null">
				{{ parsedField.placeholder }}
			</option>
			<option v-for="option in parsedField.options"
			        :value="option.value">
				{{ option.name }}
			</option>
		</b-select>
		<input v-else
		       v-model="internalValue"
		       :id="parsedField.input_id"
		       :type="parsedField.type"
		       class="w-100 form-control"
		       :placeholder="parsedField.placeholder"
		       :title="parsedField.placeholder"
		       v-bind="otherConfigs"
		       @keyup.enter="$_change"
		       @change="$_change"
		>
	</div>
</template>

<script>
	import StringUtil from "../lib/string-utils";

	const defaultDatePickerConfig = {
		format: 'DD/MM/YYYY',
		useCurrent: true,
		showClear: true,
		showClose: true,
		icons: {
			clear: 'far fa-calendar-times',
		}
	};

	export default {
		name: "MorphingInput",
		props: {
			field: {
				type: Object,
				required: true,
			},
			disabled: {
				type: Boolean,
				default: false
			},
			inline: {
                type: Boolean,
                default: false,
            },
			value: {
			}
		},
		data() {
			return {
				formId: Math.random().toString(36).substring(2, 15),
				internalValue: this.value,
			};
		},
		computed: {
			otherConfigs() {
				let {input_id, type, label, placeholder, ...otherConfigs} = this.field;
				return otherConfigs;
			},
			parsedField() {
				let item = {
					input_id: `form-${this.formId}-${this.field.name}`,
					...this.field,
				};
				switch (item.type) {
					case 'date':
						if (!item.config) {
							item.config = {};
						}
						item.config = Object.assign(item.config, defaultDatePickerConfig);
						break;
					case 'vSelect':
						if (!item.filterBy) {
							item.filterBy = (option, label, search) => {
								return (option.name || "").toLowerCase().indexOf(search.toLowerCase()) > -1;
							};
						}
						if (!item.getOptionValue) {
							item.getOptionValue = (option) => option.value;
						}
						break;
				}
				return item;
			}
		},
		watch: {
			value(val) {
				this.internalValue = val;
			},
			internalValue(val) {
				this.$emit('input', val);
			}
		},
		methods: {
			sp2nbsp: StringUtil.sp2nbsp,
			$_change() {
				this.$emit('change', this.internalValue);
			},
		}
	}
</script>