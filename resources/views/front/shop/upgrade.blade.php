@extends('front.layout.master')

@push('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/shop.css') }}" type="text/css"/>
@endpush

@section('main-id', 'upgrade')

@section('title')
    Nâng cấp cửa hàng
@endsection

@section('body-user')
	<div class="container upgrade-shop">
		<p class="mt-2">
			<a class="text-black" href="{{ route('shop.edit', ['shopId' => $shopId], false) }}">
				<i class="fas fa-arrow-left text-primary"></i>
				Quay lại cửa hàng
			</a>
		</p>
		<div class="row bg-white no-gutters mb-3">
			<div class="col-md-48 p-3 border-bottom mb-3">
				<p class="mb-1 font-weight-bold" style="font-size: 25px">Nâng cấp cửa hàng</p>
			</div>
			<div class="col-md-48" v-for="(item, index) in package" v-cloak>
				<div class="row border mt-1 ml-3 mr-3 no-gutters py-2">
					<div class="col-md-2 text-center">
						<img :src="`/images/star/level-${item.level}.png`" width="20px">
					</div>
					<div class="col-md-40 font-medium px-2">
						<p class="mb-1" v-if="item.level == ELevel.LEVEL_2" style="color: #751316DE">@{{item.name}}</p>
						<p class="mb-1" v-else-if="item.level == ELevel.LEVEL_3" style="color: #7969E2DE">@{{item.name}}</p>
						<p class="mb-1" v-else-if="item.level == ELevel.LEVEL_4" style="color: #129C8CDE">@{{item.name}}</p>
						<p class="mb-1" v-else-if="item.level == ELevel.LEVEL_5" style="color: #712E72">@{{item.name}}</p>
						<p class="mb-1" v-else>@{{item.name}}</p>
						<span>@{{item.price}} đ</span>
					</div>
					<div class="col-md-6 text-right">
						<span class="font-medium pr-2" v-if="item.level == {{auth()->user()->getshop->level}}" style="color: #0E98E8">
							Cấp hiện tại
						</span>
						<span class="font-medium pr-2" v-else-if="item.level == {{$levelWaitingApprove}}" style="color: red">
							Cấp chờ duyệt
						</span>
						<div v-else-if="item.level > {{auth()->user()->getshop->level}}" class="custom-control custom-checkbox">
							@if($isUpgrade)
								<input type="radio" name="subscription" class="custom-control-input" :id="'subscription' + index" @click = chooseLevel(item)>
								<label class="custom-control-label" :for="'subscription' + index"></label>
							@else
								<input type="radio" disabled="" name="subscription" class="custom-control-input" :id="'subscription' + index" @click = chooseLevel(item)>
								<label class="custom-control-label" :for="'subscription' + index"></label>
							@endif
						</div>
					</div>
				</div>
				<div class="border-bottom border-left border-right mb-3 ml-3 mr-3 no-gutters py-3 px-3">
					<div v-html="item.description"></div>
				</div>
			</div>
			<div class="custom-control custom-checkbox d-none">
				<input type="radio" name="subscription" class="custom-control-input" checked="true">
				<label class="custom-control-label"></label>
			</div>
			<div class="col-md-48 text-right mb-3 pr-3" v-if="{{auth()->user()->getshop->level}} != ELevel.LEVEL_5">
				@if ($isUpgrade)
					<a class="btn btn-primary" @click="upgradeShop">Tiến hành nâng cấp</a>
				@else
					<a class="btn btn-secondary" href="javascript:void(0)">Tiến hành nâng cấp</a>
				@endif
			</div>
		</div>
		<input id="data" hidden="" type="text" data-id="{{isset($shopId) ? $shopId : null}}" data-package="{{$data}}">
	</div>
@endsection

@push('app-scripts')
	<script src="{{ mix('/js/front/shop/upgrade.js') }}"></script>
@endpush
