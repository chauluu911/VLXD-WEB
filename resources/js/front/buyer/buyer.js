'use strict';

import SubHeader from './SubHeader.vue';
import * as Pagination from 'laravel-vue-pagination';
import EErrorCode from "../../constants/error-code";
import PostItem from '../components/PostItem.vue';
import businessMessage from "../../locales/front/buyer";

let filterData = $('#filter-data').val();
filterData = atob(filterData) || '{}';
filterData = JSON.parse(filterData);

const app = new Vue({
	el:'#sub-header',
    components: {
        SubHeader,
        Pagination,
        PostItem,
    },
    i18n: {
        messages: businessMessage
    },
    data() {
        return {
            filters: filterData.filter,
            pageSize: 10,
            postList: {},
            total: null,
            adsImage: [],
            countries: null,
            categoryList: null,
            currentCountry: {},
            option: {
                orderBy: 'newest',
                name: this.$t('sort.latest-business'),
                orderDirection: 'desc',
            }
        }
    },
    created() {
        this.getPostList();
    },
    methods: {
        changeOrder(val) {
            this.option = val;
            this.getPostList();
        },
        getPostList(page) {
            let data = {
                filter: {
                    ...this.filters
                },
                pageSize: this.pageSize,
                orderBy: this.option.orderBy,
                orderDirection: this.option.orderDirection
            };
            if (page) {
                data.page = page;
            }
            window.history.pushState(null, null, window.stringUtil.getUrlWithQueries(
                window.location.pathname, {...data.filter, page: page})
            );
            common.loadingScreen.show('body');
            return $.ajax({
                url: 'buyer/post/list',
                method: 'POST',
                data: data,
            }).done((res) => {
                this.postList = res.data;
                this.total = this.postList.total;
                this.adsImage = res.adsImage;
                this.currentCountry = res.currentCountry;
            })
            .always(function() {
                common.loadingScreen.hide('body');
            });
        },

        onSearch(val) {
            this.filters = val.data;
            this.getPostList();
        },

        saveMySearch() {
            this.$refs.subHeaderEL.saveMySearch();
        },
    }
});
