@extends('front.layout.master')

@prepend('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/news.css') }}" type="text/css"/>
@endprepend

@section('title')
    {{$title ? $title : 'Tin Tức'}}
@endsection

@section('main-id', 'news-detail')


@section('body-user')
    <div v-cloak class="mb-5 pb-5">
        <div>
            <div class="container">
                <div class="my-breadcrumb my-3">
                    <div class="my-breadcrumb-item mr-2">
                        <a href="{{ route('home', [], false) }}">
                            <i class="fa fa-home my-1" aria-hidden="true"></i>
                        </a>
                    </div>
                    <div class="my-breadcrumb-item mr-2"><i class="fa fa-angle-right my-1" aria-hidden="true"></i></div>
                    <div class="my-breadcrumb-item mr-2">
                        <a href="{{ route('news.list', [], false) }}"
                           style="color: #000000DD;text-decoration: none;"
                           class="font-medium"
                        >
                            Tin tức xây dựng
                        </a>
                    </div>
                    <div class="my-breadcrumb-item mr-2"><i class="fa fa-angle-right my-1" aria-hidden="true"></i></div>
                    <div class="my-breadcrumb-item mr-2">
                        <a href="{{ isset($id) ? route('news.detail', ['id'=> $id], false) : '#' }}"
                           style="color: #000000DD;text-decoration: none;"
                           class="font-medium"
                        >
                            @{{ newsDetail.title }}
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-32">
                        <div class="detail-news-title">
                            @{{ newsDetail.title }}
                        </div>
                        <div class="detail-news-created">
                            @{{ newsDetail.createdAt }}
                        </div>
                        <span class="body-news" v-html="newsDetail.content"></span>
                    </div>
                    <div class="col-md-16" id="news-list">
                        <div class="row mr-0"
                             v-for="item in newsList"
                        >
                            <news-item :item-data="item" class="news-item-in-detail"> </news-item>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <input id="newsDetailData" hidden="" type="text"
           data-id="{{isset($id) ? $id : null}}">

@endsection

@push('app-scripts')
    <script src="{{ mix('/js/front/news/detail.js') }}"></script>
@endpush
