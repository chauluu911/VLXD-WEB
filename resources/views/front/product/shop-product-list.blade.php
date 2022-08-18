@extends('front.layout.master')

@php
    $queries = request()->all();
    $filterData = base64_encode(
        json_encode([
            'filter' => $queries,
        ])
    );
@endphp

@section('title')
    Quản lí sản phẩm
@endsection

@push('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/product.css') }}" type="text/css"/>
@endpush

@section('main-id', 'product-list')

@section('body-user')
	<div class="container shop-product-list mt-5">
		<div class="row no-gutters mb-2">
			<div class="col-md-16">
				<shop-tool-bar :shop-id="shopId" current-route-name="{{request()->route()->getName()}}" style="margin: 0px 6px 0px 6px"></shop-tool-bar>
			</div>
			<div class="col-md-32">
				<div class="row">
					<div class="col-md-48">
						<div class="card border-0 mb-1" style="margin: 0px 6px 0px 6px">
							<div class="card-body px-0 pt-2 pb-0">
								<div class="row">
									<div class="col-md-30" v-cloak>
										<p class="font-weight-bold mb-2 pl-3 font-size-16px">DANH SÁCH SẢN PHẨM</p>
										<nav class="nav">
                                            <a
                                                class="nav-link py-0 pb-1 px-0 mx-3"
                                                href="javascript:void(0)"
                                                :class="filter.approvalStatus == EApprovalStatus.WAITING ? 'text-primary font-weight-bold' : 'text-dark'"
                                                :style="filter.approvalStatus == EApprovalStatus.WAITING ? 'border-bottom: 2px solid #ea4335' : ''"
                                                @click="filter.approvalStatus = EApprovalStatus.WAITING, getProductList()"
                                            >
                                                Chờ duyệt
                                            </a>
										  	<a
										  		class="nav-link py-0 pb-1 px-0 mx-3"
										  		href="javascript:void(0)"
										  		:class="filter.approvalStatus == EApprovalStatus.APPROVED ? 'text-primary font-weight-bold' : 'text-dark'"
										  		:style="filter.approvalStatus == EApprovalStatus.APPROVED ? 'border-bottom: 2px solid #ea4335' : ''"
										  		@click="filter.approvalStatus = EApprovalStatus.APPROVED, getProductList()"
										  	>
										  		Đang bán
										  	</a>
										  	<a
										  		class="nav-link py-0 pb-1 px-0 mx-3"
										  		href="javascript:void(0)"
										  		:class="filter.approvalStatus == EApprovalStatus.DENY ? 'text-primary font-weight-bold' : 'text-dark'"
										  		:style="filter.approvalStatus == EApprovalStatus.DENY ? 'border-bottom: 2px solid #ea4335' : ''"
										  		@click="filter.approvalStatus = EApprovalStatus.DENY, getProductList()"
										  	>
										  		Bị từ chối
										  	</a>
										</nav>
									</div>
									@if(auth()->user()->getShop && auth()->user()->getShop->status == App\Enums\EStatus::ACTIVE)
										<template v-show="permission.num_product_remain">
											<div v-if="permission.num_product_remain > 0 || permission.num_product_remain == null" class="col-md-18 my-auto">
												<a href="{{ route('product.create', ['shopId' => $shopId], false) }}" class="btn btn-primary mr-3 float-right text-white">
													<i class="fas fa-plus-square"></i> Đăng bán
												</a>
											</div>
											<div v-else class="col-md-18 my-auto">
												<a href="javascript:void(0)" class="btn btn-primary mr-3 float-right text-white" @click="showModalNotify">
													<i class="fas fa-plus-square"></i> Đăng bán
												</a>
											</div>
										</template>
									@endif
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-48">
						<div v-if="products.total > 0">
							<div id="product-list" class="row card-deck mb-2 mx-0">
								<product-item
                                    v-for="item in products.data"
                                    class-item="shop-product-item"
                                    :display-btn-like="false"
                                    :item-data="item">
                                </product-item>
							</div>
						</div>
						<p v-else class="text-center font-size-16px font-medium">Không có sản phẩm nào được tìm thấy</p>
					</div>
					<div v-if="products.total > 0" class="col-48 d-flex justify-content-center py-2">
						<pagination class="mb-0" :data="products" @pagination-change-page="getProductList" :limit="4"></pagination>
					</div>
				</div>
			</div>
		</div>

		<div class="modal fade p-0" id="modal-notidy" tabindex="-1" role="dialog" aria-labelledby="modal-notidy" aria-hidden="true">
		  	<div class="modal-dialog modal-dialog-centered" role="document">
		    	<div class="modal-content">
		      		<div class="modal-body text-center">
		        		<span class="font-medium font-size-16px">Bạn đã sử dụng hết số lần đăng tin cho phép, vui lòng nâng cấp cửa hàng để tiếp tục đăng tin</span>
		      		</div>
		      		<div class="modal-footer d-block p-1">
		        		<div class="row">
		        			<div class="col-md-24">
		        				<button type="button" data-dismiss="modal" class="btn btn-secondary text-white w-100">Hủy</button>
		        			</div>
		        			<div class="col-md-24">
		        				<a href="{{ route('shop.upgrade', ['shopId' => $shopId], false) }}" class="btn btn-primary text-white w-100">Đồng ý</a>
		        			</div>
		        		</div>
		      		</div>
		    	</div>
		  	</div>
		</div>
	</div>
	<input id="product-data" hidden="" type="text" data-shop-id="{{isset($shopId) ? $shopId : null}}" data-filter="{{isset($filterData) ? $filterData : null}}">
@endsection

@push('app-scripts')
	<script src="{{ mix('/js/front/product/list.js') }}"></script>
@endpush
