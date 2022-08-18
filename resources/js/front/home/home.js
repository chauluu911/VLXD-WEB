'use strict';
import ProductItem from "../components/ProductItem";
import EErrorCode from "../../constants/error-code";

let viewData = $('#product-category').data('category');
viewData = atob(viewData) || '{}';
viewData = JSON.parse(viewData);
let shopId = null;
shopId = $('#shop-info') ? $('#shop-info').data('shopid') : null;

const app = new Vue({
	el:'#home',
    components: {
        ProductItem,
    },
    data() {
        return {
            windowWidth: null,
            displayPromotionBanner: true,
            productList: [],
            pageSize: 18,
            page: 1,
            dataPaginate: {},
            initLoading: true,
            height: null,
            category: viewData,
            shopId: shopId,
            optionSearchProduct: {
                approvalStatus: 1,
                minPrice: 0,
                maxPrice: '100000000',
                orderBy: 'created_at',
                orderDirection: 'desc',
                page: 1,
            }
        }
    },
    created() {
        this.getProductList();
        this.windowWidth = window.innerWidth;
        window.onresize = () => {
            this.windowWidth = window.innerWidth;
        }
    },
    updated() {
        this.category.forEach((item, index) => {
            if ($('#category-name' + index).height() > 30) {
                this.height = 'height: 52px !important';
            }
        })
    },
    computed: {
	    route() {
            return {
                product: '/product/get',
            }
        }
    },
    methods: {
        categoryIndexChange(index) {
            console.log(index);
        },
        getProductList() {
            if (this.initLoading) {
                this.initLoading = false;
                common.loadingScreen.show('#product-list');
            } else {
                common.loadingScreen.show('body');
            }
            common.get({
                url: this.route.product,
                data: {
                    pageSize: this.pageSize,
                    page: this.page,
                    filter: {
                        getForHomepage: true,
                        getSubscription: true,
                    }
                }
            }).done(response => {
                if(response.error != EErrorCode.NO_ERROR) {
                    common.showMsg2(response);
                    return false;
                }
                this.productList =this.productList.concat(response.products.data);
                this.page += 1;
                this.dataPaginate = response.products;
            }).always(() => {
                common.loadingScreen.hide('#product-list');
                common.loadingScreen.hide('body');
            });
        },
        hidePromotionBanner() {
            this.displayPromotionBanner = false;
        },
        redirecToProduct(categoryId) {
            window.location.assign(
                stringUtil.getUrlWithQueries('/product', {categoryId:  categoryId, ...this.optionSearchProduct})
            );
        }
    }
});
