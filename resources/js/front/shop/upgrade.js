'use strict';

import EStatus from "../../constants/status";
import ELevel from "../../constants/level";
import EErrorCode from "../../constants/error-code";

const app = new Vue({
    el:'#upgrade',
    data() {
        return {
            ELevel,
            item: null,
            shopId: $('#data').data('id'),
            package: $('#data').data('package'),
        }
    },

    methods: {
    	chooseLevel(item) {
    		this.item = item;
    	},
    	upgradeShop() {
            if (this.item == null) {
                window.common.showMsg('error', null, 'Vui lòng chọn cấp độ nâng cấp');
                return false;
            }
    		window.location.assign('/payment/shop/' + this.shopId + '/' + this.item.subscriptionPriceId);
    	}
    }
});
