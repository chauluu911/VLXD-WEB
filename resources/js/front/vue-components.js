import Multiselect from 'vue-multiselect'
import vSelect from 'vue-select';
import ADateTimePicker from "../components/ADateTimePicker";
import CSelect from "../components/CSelect";
import VMoney from "v-money";
import CKEditor from '@ckeditor/ckeditor5-vue';
import CKDocument from "../components/CKDocument";

export default function registerComponent(Vue) {
    Vue.component('a-select', Multiselect);
    Vue.component('d-select', vSelect);
    Vue.use(CKEditor);
    Vue.component('ck-document', CKDocument);
    Vue.component('c-select', {
    	extends: CSelect,
    	props: {
    		useFrontApi: {
            	type: Boolean,
				default: true,
			}
    	}
    });
    Vue.component('a-date-time-picker', ADateTimePicker);
    Vue.use(VMoney, {precision: 0});
}
