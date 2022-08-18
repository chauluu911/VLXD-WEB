'use strict';


import NewsItem from "../components/NewsItem";
import * as Pagination from 'laravel-vue-pagination';
import DateUtil from "../../lib/date-utils"

const app = new Vue({
    el:'#news-list',
    name: 'newsList',
    components: {
        Pagination,
        NewsItem
    },
    data() {
        return {
            newsList: {},
            topNewsList: {},
            pageSize:12,
            numberOfTopNew: 3,
            dataPaginate: {},
            page: $('#newsListData').data('page'),
        }
    },
    async created() {
        await this.getTopNews();
        this.getNewsList();
        window.addEventListener('popstate',() => {
            let page = stringUtil.getUrlQueries(window.location.href, 'page');
            this.getNewsList(page, false);
        })
    },
    computed: {
        route() {
            return {
                news: '/news/get',
            }
        }
    },
    methods: {
        redirectTo(newsId) {
            let news = this.topNewsList.find(e => e.id == newsId)
            if( news) {
                window.location.assign(news.redirectTo);
            }
        },
        getNewsList(paging, pushState = true) {
            if(pushState) {
                window.history.pushState(null, null, stringUtil.getUrlWithQueries(
                    window.location.pathname, {...this.filter, page: paging ? paging : this.page || 1})
                );
            }
            common.loadingScreen.show();
            common.post({
                url: this.route.news,
                data: {
                    pageSize: this.pageSize,
                    page: paging ? paging : this.page || 1,
                    ignoreList: this.topNewsList.map(news => news.id),
                }
            }).done(response => {
                this.newsList = response.news.data;
                this.dataPaginate = response.news;
                this.newsList.forEach((news,index) => {
                    let date = new Date(news.createdAt);
                    let dateCreated = DateUtil.getDateString(date, '/', false);
                    this.newsList[index].createdAt = dateCreated;
                })
            })
            .always(() => {
                common.loadingScreen.hide();
            });
        },

        getTopNews() {
            common.loadingScreen.show();
            return common.post({
                    url: this.route.news,
                    data: {
                        pageSize: this.numberOfTopNew,
                        page: 1,
                    }
                }).done(response => {
                    this.topNewsList = response.news.data;
                    this.topNewsList.forEach((news,index) => {
                        let date = new Date(news.createdAt);
                        let dateCreated = DateUtil.getDateString(date, '/', false);
                        this.topNewsList[index].createdAt = dateCreated;
                    })
                }).always(() => {
                    common.loadingScreen.hide();
                });
        },

    }
});
