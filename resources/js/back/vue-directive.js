import VueShave from "vue-shave";

let seeMoreDirectiveCallback = function (el, binding, vnode) {
	let {
		height = 50,
		expandBtn = '+', // expand button html
		expandBtnClassname = 'expand-btn',
		collapseBtn = '-', // collapse button html
		collapseBtnClassname = 'collapse-btn',
	} = binding.value;

	let expandBtnEl = el.querySelector(`.${expandBtnClassname}`)
	if (expandBtnEl) {
		el.removeChild(expandBtnEl);
	}

	let style = getComputedStyle(el);
	if (parseInt(style.height) > height) {
		let isHide = true;

		let elContentEl = el.querySelector('.see-more-el-content')
		if (!elContentEl) {
			elContentEl = document.createElement('div');
			for (let i = 0; i < el.childNodes.length; i++) {
				elContentEl.appendChild(el.childNodes[i]);
			}
			elContentEl.className = 'see-more-el-content';
			elContentEl.style.overflow = 'hidden';
			elContentEl.style.height = `${height}px`;
			elContentEl.style.position = 'relative';
			el.appendChild(elContentEl);
		}

		expandBtnEl = $(`<div><a href="javascript:void(0)" class="${expandBtnClassname}">${expandBtn}</a></div>`);
		expandBtnEl.on('click', () => {
			if (isHide) {
				elContentEl.style.height = '';
				expandBtnEl.find(`.${expandBtnClassname}`)
					.removeClass(expandBtnClassname)
					.addClass(collapseBtnClassname)
					.text(collapseBtn);
			} else {
				elContentEl.style.height = `${height}px`;
				expandBtnEl.find(`.${collapseBtnClassname}`)
					.removeClass(collapseBtnClassname)
					.addClass(expandBtnClassname)
					.text(expandBtn);
			}
			isHide = !isHide;
		});
		el.appendChild(expandBtnEl.get(0));
	} else {
		el.style.height = '';
		el.style.marginBottom = '';
	}
};
export default function registerDirective(Vue) {
    Vue.use(VueShave, {
        height: 66, // 3 line
        character: '...', // ellipsis character
    });
    Vue.directive('see-more', {
		inserted: seeMoreDirectiveCallback,
	});
}
