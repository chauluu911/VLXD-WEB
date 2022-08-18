import AButton from "../components/AButton";
import AButtonDelete from "../components/AButtonDelete";
import Multiselect from 'vue-multiselect'
import vSelect from 'vue-select';
import ATable from "../components/ATable";
import ASearchableInput from "../components/ASearchableInput";
import ADateTimePicker from "../components/ADateTimePicker";
import ACheckbox from "../components/ACheckbox";
import Paging from "../components/Paging";
import NameFilter from "../components/NameFilter";
import DefaultListToolbar from "../components/DefaultListToolbar";
import Loading from "../components/Loading";
import CKEditor from '@ckeditor/ckeditor5-vue';
import CKDocument from "../components/CKDocument";
import CSelect from "../components/CSelect";
import ALink from "../components/ALink";
import VMoney from "v-money";
import MorphingInput from "../components/MorphingInput";

export default function registerComponent(Vue) {
    Vue.component('a-select', Multiselect);
    Vue.component('d-select', vSelect);
    Vue.component('c-select', CSelect);
    Vue.component('a-link', ALink);
    Vue.component('a-button', AButton);
    Vue.component('a-button-delete', AButtonDelete);
    Vue.component('a-table', ATable);
    Vue.component('a-searchable-input', ASearchableInput);
    Vue.component('a-date-time-picker', ADateTimePicker);
    Vue.component('a-checkbox', ACheckbox);
    Vue.component('paging', Paging);
    Vue.component('name-filter', NameFilter);
    Vue.component('default-list-toolbar', DefaultListToolbar);
    Vue.component('morphing-input', MorphingInput);
    Vue.component('loading', Loading);
    Vue.use(VMoney, {precision: 0});
    Vue.use(CKEditor);
    Vue.component('ck-document', CKDocument);
}
