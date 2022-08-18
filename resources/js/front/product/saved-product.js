'use strict';
import ProductItem from "../components/ProductItem";
import Profile from "../components/Profile";
import EErrorCode from "../../constants/error-code";

const app = new Vue({
    el:'#saved-product',
    components: {
        ProductItem,
        Profile
    },
    data() {
        return {
            productList: [],
            pageSize: 12,
            page: 1,
            dataPaginate: {},
        }
    },
    created() {
        this.getProductList();
    },
    computed: {
        route() {
            return {
                product: '/product/get',
            }
        }
    },
    methods: {
        getProductList() {
            common.loadingScreen.show('body');
            common.get({
                url: this.route.product,
                data: {
                    pageSize: this.pageSize,
                    page: this.page,
                    filter: {
                        getForHomepage: true,
                        getSubscription: true,
                        getSavedProduct: true,
                    }
                }
            }).done(response => {
                if (response.error == EErrorCode.ERROR) {
                    return;
                }
                this.productList =this.productList.concat(response.products.data);
                this.page += 1;
                this.dataPaginate = response.products;
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        }
    }
});
