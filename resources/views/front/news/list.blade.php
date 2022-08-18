@extends('front.layout.master')

@prepend('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/news.css') }}" type="text/css"/>
@endprepend

@section('title')
    Tin tức
@endsection

@section('main-id', 'news-list')

@section('body-user')
    <div class="container-fluid bg-white" style="box-shadow: 0px 1px 5px #0000001F;">
        <div class="container pl-1 py-3 font-medium"
             style="font-size: 17.5px"
        >
            TIN TỨC XÂY DỰNG
        </div>
    </div>
    <div class="container py-4">
        <div v-if="Object.keys(topNewsList).length > 0" class="row top-news pb-3">
            <div class="col-md-32">
                <div class="img-top-right" v-cloak @click="redirectTo(topNewsList[0].id)"
                     :style="{'background-image': `url(${topNewsList[0].avatarPath})`}"
                >
                    <div class="content-top-right py-2" @click="redirectTo(topNewsList[0].id)" >
                            <span class="news-title-top"
                                  :title="topNewsList[0].title"
                                  v-shave="{height: 50, character: '...'}">
                                @{{ topNewsList[0].title }}
                            </span>
                        <span class="news-created-top">
                                @{{ topNewsList[0].createdAt }}
                            </span>
                    </div>
                </div>
            </div>
            <div class="col-md-16">
                <div class="row">
                    <div class="col-md-48">
                        <div class="img-top-left" v-cloak @click="redirectTo(topNewsList[1].id)"
                             :style="{'background-image': `url(${topNewsList[1].avatarPath})`}"
                        >
                            <div class="content-top-left py-2" @click="redirectTo(topNewsList[1].id)">
                                <span class="news-title-top"
                                      :title="topNewsList[1].title"
                                      v-shave="{height: 50, character: '...'}">
                                    @{{ topNewsList[1].title }}
                                </span>
                                <span class="news-created-top">
                                    @{{ topNewsList[1].createdAt }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-48">
                        <div class="img-top-left" v-cloak @click="redirectTo(topNewsList[2].id)"
                             :style="{'background-image': `url(${topNewsList[2].avatarPath})`}"
                        >
                            <div class="content-top-left py-2" @click="redirectTo(topNewsList[2].id)">
                                <span class="news-title-top"
                                      :title="topNewsList[2].title"
                                      v-shave="{height: 50, character: '...'}">
                                    @{{ topNewsList[2].title }}
                                </span>
                                <span class="news-created-top">
                                    @{{ topNewsList[2].createdAt }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-primary row my-4" style="height: 5px" >

        </div>
        <div class="row bottom-news pt-3" id="news-list">
            <div class="col-md-16 mb-2" v-for="item in newsList">
                <news-item
                    :item-data="item"
                >
                </news-item>
            </div>
        </div>
        <div v-if="dataPaginate.total > pageSize" class="row no-gutters bg-transparent mx-1 my-3">
            <div class="col-48 d-flex justify-content-center py-2">
                <pagination class="mb-0" :data="dataPaginate" @pagination-change-page="getNewsList" ></pagination>
            </div>
        </div>
        <input id="newsListData" hidden="" type="text"
               data-page="{{isset($page) ? $page : null}}">
    </div>
@endsection

@push('app-scripts')
    <script src="{{ mix('/js/front/news/list.js') }}"></script>
@endpush
