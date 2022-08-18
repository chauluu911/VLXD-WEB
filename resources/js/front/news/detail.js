import DateUtil from "../../lib/date-utils";
import EErrorCode from "../../constants/error-code";
import NewsItem from "../components/NewsItem";


const app = new Vue ({
    el:'#news-detail',
    name: 'newsDetail',
    components: {
        NewsItem
    },
    data() {
        return {
            newsDetail: {},
            newsList: {},
            pageSize: 12,
            id: $('#newsDetailData').data('id'),
        }
    },
    created() {
        this.getInfoNews();
        this.getNewsList();
    },
    computed: {
        route() {
            return {
                getNewsDetail: `/news/${this.id}/info`,
                news: '/news/get',
            }
        }
    },
    methods:{
        getNewsList() {

            common.loadingScreen.show();
            common.post({
                url: this.route.news,
                data: {
                    pageSize: this.pageSize,
                    ignoreList: [this.id],
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
        getInfoNews() {
            common.loadingScreen.show();
            common.get({
                url: this.route.getNewsDetail,
            }).done(response => {
                if(response.error != EErrorCode.NO_ERROR) {
                    window.location.assign(response.redirectTo);
                }
                this.newsDetail = response.news;
                let date = new Date(this.newsDetail.createdAt);
                let dateCreated = DateUtil.getDateString(date, '/', false);
                this.newsDetail.createdAt = dateCreated

            }).always(() => {
                common.loadingScreen.hide();
            });
        },
    }


})
