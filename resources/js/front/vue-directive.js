import VueShave from "vue-shave";

export default function registerDirective(Vue) {
    Vue.use(VueShave, {
        height: 66, // 3 line
        character: '...', // ellipsis character
    });
}
