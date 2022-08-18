'use strict';

import EErrorCode from "../../constants/error-code";
import ProductItem from '../components/ProductItem.vue';
import EApprovalStatus from "../../constants/approval-status";
import * as Pagination from 'laravel-vue-pagination';
import EAreaType from "../../constants/area-type";
import ShopToolBar from '../components/ShopToolBar.vue';
import Dropdown from 'bp-vuejs-dropdown';
import 'vue-slick-carousel/dist/vue-slick-carousel.css';
import 'vue-slick-carousel/dist/vue-slick-carousel-theme.css';
import VueSlickCarousel from 'vue-slick-carousel';

let viewData = $('#product-data').data('filter');
viewData = atob(viewData) || '{}';
viewData = JSON.parse(viewData);
let shopId = $('#product-data').data('shopId');
const app = new Vue({
    el:'#product-list',
    components: {
        Pagination,
        ProductItem,
        ShopToolBar,
        Dropdown,
        VueSlickCarousel
    },
    data() {
        return {
            settings: {
                "infinite": true,
                "initialSlide": 2,
                "speed": 500,
                "slidesToShow": 6,
                "slidesToScroll": 1,
                "swipeToSlide": true,
                "draggable": false,
                "responsive": [
                    {
                        "breakpoint": 1024,
                        "settings": {
                            "slidesToShow": 3,
                            "slidesToScroll": 3,
                            "infinite": true,
                        }
                    },
                    {
                        "breakpoint": 600,
                        "settings": {
                            "slidesToShow": 2,
                            "slidesToScroll": 2,
                            "initialSlide": 2
                        }
                    },
                    {
                        "breakpoint": 480,
                        "settings": {
                            "slidesToShow": 2,
                            "slidesToScroll": 1
                        }
                    }
                ],
            },
            query: viewData,
            EApprovalStatus,
            EAreaType,

            products: {},
            areaName: null,
            shopId: shopId,

            // priceInput: {
            //     range: [0, 1000000000],
            //     min: 0,
            //     max: 1500000000,
            //     enableCross: false,
            //     step: 100000,
            //     useKeyboard: true,
            // },
            filter: {
                ...this.$_filter(),
            },
            pageSize: 10,
            page: viewData.filter && viewData.filter.page ? viewData.filter.page : 1,
            category: {
                list: {},
                childLevel1: [],
                childLevel2: [],
            },
            attribute: {},
            categoryChoosed: {
                id: null,
                name: null,
                chooseLevel2: false,
                step: 1,
            },
            areaChoosed: this.$_areaChoosed(),
            attributeChoosed: [],
            areaList: null,
            areaModal: {
                districtList: null,
                wardList: null,
            },
            isShowWaiting: this.shopId ? true : false,
            height: null,
            permission: {
                num_product_remain: null,
                num_video_introduce_remain: 0,
                num_image_in_product: 0
            },
            money: {
                thousands: '.',
                precision: 0,
            },
            pushState: false,
        }
    },
    created() {
        $(window).on("popstate", function(e) {
            // window.location.reload();
            this.getCategory();
            this.getAreaList();
        });

        let area = JSON.parse(window.localStorage.getItem('areaChoosed'));
        if(area && area.province.id) {
            this.areaChoosed = area;
            if(!area.ward.id) {
                this.query.filter.areaId = area.district.id ? area.district.id : area.province.id;
                this.filter.areaId = area.district.id ? area.district.id : area.province.id;
            } else {
                this.query.filter.areaId = area.ward.id
                this.filter.areaId = area.ward.id
            }
        }

    },
    mounted() {
        // this.filter.minPrice = this.priceInput.range[0];
        // this.filter.maxPrice = this.priceInput.range[1];
        if (this.shopId) {
            this.getPermissionShop();
        } else {
            this.getCategory();
            this.getAreaList();
        }
        if(!viewData.filter.categoryId) {
            this.getProductList();
        }
    },
    updated() {
        let number = 0;
        this.category.childLevel1.forEach((item, index) => {
            let height = $('#child-name' + index).height();
            if (height > 22) {
                if (height / 22 > number) {
                    number = height / 22;
                }
            }
        })
        if (number > 0) {
            this.height = 'height: ' + (66 + 14 * number) + 'px';
        }
    },
    filters: {
        shortName(string) {
            if (string && string.length > 22) {
                return window.stringUtil.shortenText(string, 22) + '...';
            } else {
                return string;
            }
        }
    },
    watch: {
        // "priceInput.range"() {
        //     this.filter.minPrice = this.priceInput.range[0];
        //     this.filter.maxPrice = this.priceInput.range[1];
        // },
        // "filter.provinceId"(val) {
        //     if (val) {
        //         this.areaChoosed.ward.id = null;
        //         this.areaChoosed.district.id = null;
        //         this.areaChoosed.name = this.areaChoosed.province.name;
        //         this.filter.areaId = this.filter.provinceId;
        //         this.filter.provinceId = null;
        //     }
        //     this.getProductList();
        // },
        // "category.list"() {

        // },
    },
    computed: {
        route() {
            return {
                productShop: '/shop/' + this.shopId + '/product/get',
                product: '/product/get'
            }
        },
        // formatRange() {
        //     return {
        //         minPrice: window.stringUtil.formatMoney(this.filter.minPrice, 0, '.', '.', ''),
        //         maxPrice: window.stringUtil.formatMoney(this.filter.maxPrice, 0, '.', '.', ''),
        //     }
        // }
    },
    methods: {
        getPermissionShop() {
            common.loadingScreen.show();
            common.post({
                url: '/shop/' + this.shopId + '/permission',
            }).done(response => {
                this.permission = response.permission;
            })
            .always(() => {
                common.loadingScreen.hide();
            });
        },
        $_areaChoosed() {
            return {
                name: '',
                province: {
                    id: null,
                    name: null
                },
                district: {
                    id: null,
                    name: null
                },
                ward: {
                    id: null,
                    name: null
                },
            }
        },
        $_filter() {
            let approvalStatus;
            if(shopId) {
                approvalStatus = viewData.filter && viewData.filter.approvalStatus ?
                    viewData.filter.approvalStatus : EApprovalStatus.WAITING
            } else {
                approvalStatus =  viewData.filter && viewData.filter.approvalStatus ?
                    viewData.filter.approvalStatus : EApprovalStatus.APPROVED;
            }
            return {
                approvalStatus: approvalStatus,
                minPrice: viewData.filter && viewData.filter.minPrice ?
                    viewData.filter.minPrice : 0,
                maxPrice: viewData.filter && viewData.filter.maxPrice ?
                    viewData.filter.maxPrice : 100000000,
                categoryId: viewData.filter && viewData.filter.categoryId ?
                    viewData.filter.categoryId : null,
                parentCategoryId: viewData.filter && viewData.filter.parentCategoryId ?
                    viewData.filter.parentCategoryId : null,
                areaId: viewData.filter && viewData.filter.areaId ? viewData.filter.areaId : null,
                provinceId: null,
                districtId: null,
                wardId: null,
                attribute:[],
                orderBy: viewData.filter && viewData.filter.orderBy ?
                    viewData.filter.orderBy : 'created_at',
                orderDirection: viewData.filter && viewData.filter.orderDirection ?
                    viewData.filter.orderDirection : 'desc',
                q: viewData.filter && viewData.filter.qOriginal ?
                    viewData.filter.qOriginal : null,
            }
        },
        getCategory() {
            common.loadingScreen.show();
            common.post({
                url: '/category',
            }).done(response => {
                this.category.list = response.data;
                let category;
                if (viewData) {
                    this.pushState = false;
                    if (viewData.filter.parentCategoryId) {
                        category = this.category.list.find((item) =>
                            item.child_categories.find((item2) => item2.id == viewData.filter.parentCategoryId));
                        let itemChildLevel1;
                        if (!category) {
                            category = this.category.list.find((item) => item.id ==
                            viewData.filter.parentCategoryId);
                            itemChildLevel1 = category.child_categories.find((item) => item.id ==
                            viewData.filter.categoryId);
                        } else {
                            itemChildLevel1 = category.child_categories.find((item) => item.id ==
                            viewData.filter.parentCategoryId);
                        }
                        this.attribute = category.attribute;
                        if (this.attribute) {
                            for (let i = 0; i < this.attribute.length; i++) {
                                this.attributeChoosed.push({
                                    id: null,
                                    value: [],
                                });
                            }
                        }
                        this.categoryChoosed.id = category.id;
                        this.categoryChoosed.name = category.name;
                        if (itemChildLevel1) {
                            this.category.childLevel1 = category.child_categories;
                            this.category.childLevel2 = itemChildLevel1.child_categories;
                            let itemChildLevel2 = itemChildLevel1.child_categories.find((item2) =>
                                item2.id == viewData.filter.categoryId);
                            if (itemChildLevel2) {
                                this.chooseCategory(itemChildLevel2, false, 2);
                            } else {
                                this.chooseCategory(itemChildLevel1, false, 1);
                            }
                        }else {
                            this.chooseCategory(category);
                        }
                    } else if (viewData.filter.categoryId) {
                        category = this.category.list.find((item) => item.id == viewData.filter.categoryId);
                        this.attribute = category.attribute;
                        this.chooseCategory(category);
                    }
                    if (viewData.filter.attributeId != null) {
                        let attributeId = viewData.filter.attributeId.split(',');
                        let attributeValue;
                        if (viewData.filter.attributeValue != null) {
                            attributeValue = viewData.filter.attributeValue.split(',');
                        }
                        for (let i = 0; i < attributeId.length; i++) {
                            this.attributeChoosed[i].id = attributeId[i];
                            if (attributeValue[i]) {
                                this.attributeChoosed[i].value.push(attributeValue[i]);
                            }
                        }
                        this.filter.attribute = this.attributeChoosed;
                        this.getProductList();
                    }
                    $('#input-search').val(viewData.filter.qOriginal);
                }
            })
            .always(() => {
                common.loadingScreen.hide();
            });
        },
        getProductList(paging) {
            if (!this.shopId) {
                if (this.pushState) {
                    let attributeId = [];
                    let attributeValue = [];
                    this.filter.attribute.forEach((item) => {
                        attributeId.push(item.id);
                        attributeValue.push(item.value);
                    });
                    window.history.pushState(null, null, stringUtil.getUrlWithQueries(
                        window.location.pathname, {...this.filter, page: paging ? paging : this.page,
                            attributeId: attributeId, attributeValue: attributeValue})
                    );
                    // window.location.assign(
                    //     stringUtil.getUrlWithQueries('/product', {...this.filter, page: paging ? paging : this.page,
                    //         attributeId: attributeId, attributeValue: attributeValue})
                    // );
                }
            } else {
                window.history.pushState(null, null, stringUtil.getUrlWithQueries(
                    window.location.pathname, {page: paging ? paging : this.page,
                    approvalStatus: this.filter.approvalStatus,
                    })
                );
            }
            this.products = {};
            //isShowWaiting chỉ sử dụng trong trường hợp lấy list product ở lúc search
            //Khi chọn khu vực ở những khu vực có khu vực con thì không show màn hình waiting
            //Nếu có shopId thì luôn show màn hình waiting khi get product
            //Khi có chuyển trang thì show màn hình waiting
            if (this.isShowWaiting || this.shopId || paging) {
                common.loadingScreen.show('body');
            }
            common.get({
                url: this.shopId ? this.route.productShop : this.route.product,
                data: {
                    filter: this.shopId ? {
                        approvalStatus: this.filter.approvalStatus,
                        getForShop: true,
                        shopId: this.shopId,
                    } : this.filter,
                    pageSize: this.shopId ? 20 : this.pageSize,
                    page: paging ? paging : this.page,
                }
            }).done(response => {
                if (response.error != EErrorCode.NO_ERROR) {
                    common.showMsg('error', null, response.msg);
                    return;
                }
                this.products = response.products;
                this.filter.provinceId = null;
                this.filter.districtId = null;
                this.filter.wardId = null;
            }).always(() => {
                this.pushState = true;
                this.isShowWaiting = false;
                common.loadingScreen.hide('body');
            });
        },
        getAreaList() {
            if (this.areaName == null) {
                common.loadingScreen.show();
            }
            common.post({
                url: '/area/all',
                data: {
                    type: EAreaType.PROVINCE,
                    q: this.areaName ? this.areaName : '',
                }
            }).done(response => {
                this.areaList = response.data;
                if (this.areaList) {
                    if (!this.query.filter.areaId) {
                        this.filter.areaId = null;
                        this.areaChoosed = this.$_areaChoosed();
                    } else {
                        if(this.areaChoosed.province.id) {
                            let item = this.areaList.find((province) => province.id == this.areaChoosed.province.id);
                            this.areaModal.districtList = item.district ? item.district : [];
                        }
                        if(this.areaChoosed.district.id && this.areaModal.districtList) {
                            let item = this.areaModal.districtList.find((district) => district.id == this.areaChoosed.district.id);
                            this.areaModal.wardList = item.child_areas;
                        }
                    }
                }
                // if (!this.areaName) {
                //     this.getProductList();
                // }
            })
            .always(() => {
                common.loadingScreen.hide();
                if (viewData.filter.q == null &&
                    this.filter.areaId == null &&
                    !window.localStorage.getItem('areaChoosed')) {
                    $('#areaModal').modal('show');
                }
            });
        },
        chooseCategory(item, allCategory = false, childLevel = null) {
            this.isShowWaiting = false;
            if (!item) {
                if (allCategory) {
                    this.filter.categoryId = null;
                    this.category.childLevel1 = [];
                    this.attribute = {};
                    this.filter.attribute = [];
                    this.categoryChoosed.id = null;
                }
                if (childLevel == 2) {
                    if (this.filter.parentCategoryId && this.categoryChoosed.chooseLevel2) {
                        this.filter.categoryId = this.filter.parentCategoryId;
                        let item = this.category.childLevel1.find((item) => item.id == this.filter.categoryId);
                        this.filter.parentCategoryId = item.parent_category_id;
                    }
                    this.categoryChoosed.chooseLevel2 = false;
                } else {
                    this.category.childLevel2 = [];
                    if (this.filter.parentCategoryId) {
                        this.filter.categoryId = this.filter.parentCategoryId;
                        this.filter.parentCategoryId = null;
                    }
                }
                $('#categoryModal').modal('hide');
                this.isShowWaiting = true;
            } else {
                if (!item.parent_category_id) {
                    this.category.childLevel1 = [];
                    this.$nextTick(() => {
                        this.categoryChoosed.id = item.id;
                        this.categoryChoosed.name = item.name;
                        this.category.childLevel1 = item.child_categories;
                        this.category.childLevel2 = [];
                    });
                }
                if (childLevel && childLevel == 1) {
                    this.categoryChoosed.chooseLevel2 = false;
                    this.category.childLevel2 = item.child_categories;
                    if (item.child_categories.length == 0) {
                        $('#categoryModal').modal('hide');
                    }
                    this.isShowWaiting = true;
                } else if (childLevel == 2) {
                    this.categoryChoosed.chooseLevel2 = true;
                    $('#categoryModal').modal('hide');
                    this.isShowWaiting = true;
                }
                if (item.attribute) {
                    this.attribute = item.attribute;
                }
                if (this.attribute) {
                    for (let i = 0; i < this.attribute.length; i++) {
                        this.attributeChoosed.push({
                            id: null,
                            value: [],
                        });
                    }
                }
                this.filter.categoryId = item.id;
                this.filter.parentCategoryId = item.parent_category_id;
            }
            this.$nextTick(() => {
                if (this.category.childLevel1.length == 0) {
                    $('#categoryModal').modal('hide');
                }
            });
            this.getProductList();
        },
        chooseProvince(isModal,item) {
            if (!item) {
                this.filter.areaId = null;
                this.areaChoosed = this.$_areaChoosed();
                this.isShowWaiting = true;
                $('#areaModal').modal('hide');
                this.getProductList();
                window.localStorage.setItem('areaChoosed', JSON.stringify(this.areaChoosed));
                return;
            }
            this.areaChoosed.province.id = item.id;
            this.areaChoosed.province.name = item.name;
            this.filter.provinceId = item.id;
            if (isModal && item.district) {
                this.areaModal.districtList = item.district;
            } else {
                this.isShowWaiting = true;
                $('#areaModal').modal('hide');
            }

            this.areaChoosed.ward.id = null;
            this.areaChoosed.district.id = null;
            this.areaChoosed.name = this.areaChoosed.province.name;
            this.filter.areaId = this.filter.provinceId;
            this.filter.provinceId = null;
            this.getProductList();
            window.localStorage.setItem('areaChoosed', JSON.stringify(this.areaChoosed));
        },
        chooseDistrict(isModal, item) {
            this.areaChoosed.district.id = item.id;
            this.areaChoosed.district.name = item.name;
            this.filter.districtId = item.id;
            if (isModal) {
                this.filter.areaId = item.id;
                this.areaChoosed.name = item.name + ', ' + this.areaChoosed.province.name;
                if (item.child_areas.length > 0) {
                    this.areaModal.wardList = item.child_areas;
                } else {
                    this.isShowWaiting = true;
                    $('#areaModal').modal('hide');
                }
                this.getProductList();
                window.localStorage.setItem('areaChoosed', JSON.stringify(this.areaChoosed));

            }
        },
        chooseWard(isModal, item) {
            this.areaChoosed.ward.id = item.id;
            this.areaChoosed.ward.name = item.name;
            this.filter.wardId = item.id;
            if (isModal) {
                this.filter.areaId = item.id;
                this.areaChoosed.name = item.name + ', ' + this.areaChoosed.district.name + ', ' + this.areaChoosed.province.name;
                this.isShowWaiting = true;
                $('#areaModal').modal('hide');
                this.getProductList();
                window.localStorage.setItem('areaChoosed', JSON.stringify(this.areaChoosed));
            }
        },
        chooseAttribute(item, index) {
            if (!item) {
               this.attributeChoosed[index].value = [];
            } else {
                if (item.id) {
                    if (this.attributeChoosed[index].id == item.id) {
                        this.attributeChoosed[index].id = null;
                        this.attributeChoosed[index].value = [];
                        this.getProductList();
                        return;
                    }
                    this.attributeChoosed[index].id = item.id;
                    // this.attributeChoosed.id.push(item.category_id);
                    // this.filter.attributeId = this.attribute.id;
                    // this.filter.attributeName = this.attribute.attribute_name;
                } else {
                    let inx = this.attributeChoosed[index].value.indexOf(item);
                    if (inx != -1) {
                        this.attributeChoosed[index].value.splice(inx, 1);
                        this.getProductList();
                        return;
                    }
                    this.attributeChoosed[index].value.push(item);
                }
            }
            this.filter.attribute = this.attributeChoosed;
            this.getProductList();
        },
        resetFilter() {
            this.isShowWaiting = true;
            this.filter = {
                ...this.$_filter(),
            };
            this.attribute = {};
            this.categoryChoosed = {
                id: null,
                name: null,
                chooseLevel2: false,
                step: 1,
            };
            this.filter.categoryId = null;
            this.filter.parentCategoryId = null;
            this.category.childLevel1 = [];
            this.category.childLevel2 = [];

            this.filter.areaId = null;
            this.areaChoosed = this.$_areaChoosed();
            this.areaModal.districtList = null;
            this.areaModal.wardList = null;
            this.attributeChoosed = [];
            this.getProductList();
            window.localStorage.setItem('areaChoosed', JSON.stringify(this.areaChoosed));
        },
        preArea() {
            if (this.areaModal.wardList) {
                this.areaModal.wardList = null;
                this.filter.areaId = this.areaChoosed.district.id;
                this.areaChoosed.name = this.areaChoosed.district.name + ', ' + this.areaChoosed.province.name;
                this.areaChoosed.ward.id = null;
                this.areaChoosed.ward.name = null;
            } else if (this.areaModal.districtList){
                this.areaModal.districtList = null;
                this.filter.areaId = this.areaChoosed.province.id;
                this.areaChoosed.name = this.areaChoosed.province.name;
                this.areaChoosed.district.id = null;
                this.areaChoosed.district.name = null;
            }
            this.getProductList();
            window.localStorage.setItem('areaChoosed', JSON.stringify(this.areaChoosed));
        },
        preCategory() {
            this.categoryChoosed.step --;
            if (this.category.childLevel2.length > 0) {
                this.category.childLevel2 = [];
                this.filter.categoryId = this.filter.parentCategoryId;
                this.filter.parentCategoryId = null;
                this.categoryChoosed.chooseLevel2 = false;
            }
            else if (this.category.childLevel1.length > 0) {
                this.category.childLevel1 = [];
                this.filter.categoryId = null;
                this.filter.parentCategoryId = null;
                this.categoryChoosed.id = null;
                this.categoryChoosed.name = null;
                this.filter.categoryId = null;
            }
            this.getProductList();
        },
        showModalCategory() {
            this.categoryChoosed.step = 1;
            $('#categoryModal').modal('show');
        },
        showModalArea() {
            $('#areaModal').modal('show');
        },
        chooseCategoryLevel(item, allCategory, childLevel) {
            if (this.filter.categoryId == item.id || this.filter.parentCategoryId == item.id) {
                this.chooseCategory(null, false);
            } else {
                this.chooseCategory(item, allCategory, childLevel);
            }
        },
        showModalNotify() {
            $('#modal-notidy').modal('show');
        }
    }
});
