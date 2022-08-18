@extends('front.layout.master')

@push('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/order.css') }}" type="text/css"/>
@endpush

@section('main-id', 'order-list')

@section('title')
    Giỏ hàng
@endsection

@section('body-user')
	<div class="container-fluid bg-white">
		<div class="container py-1">
			<span v-if="step == 1" class="font-weight-bold" style="font-size: 20px">GIỎ HÀNG</span>
			<span v-if="step == 2" class="font-weight-bold" style="font-size: 20px">ĐẶT HÀNG</span>
		</div>
	</div>
	<div class="container">
		<template v-if="cart && cart.length > 0">
			<div class="row">
				<div class="col-md-24 mb-3">
					<div v-if="step == 2" class="card mt-2 py-3" style="max-width: 540px;">
						<form>
							<p class="font-weight-bold mb-0 px-2">Thông tin giao nhận</p>
							<div class="row no-gutters">
								<div class="col-md-16 p-2 text-right">
							    	<span class="font-medium">Họ tên</span>
							    </div>
							    <div class="col-md-32 p-2">
							    	<input
							    		class="form-control"
							    		type="text"
							    		placeholder="Họ tên"
							    		v-model="receiverInfo.name"
							    		:class="{'is-invalid': errors.name}"
							    	>
							    	<div v-if="errors.name" class="invalid-feedback font-weight-bold">
								        @{{errors.name[0]}}
								    </div>
							    </div>
							</div>
							<div class="row no-gutters">
								<div class="col-md-16 p-2 text-right">
							    	<span class="font-medium">Số điện thoại</span>
							    </div>
							    <div class="col-md-32 p-2">
							    	<input
							    		class="form-control"
							    		type="text"
							    		placeholder="Số điện thoại"
							    		v-model="receiverInfo.phone"
							    		:class="{'is-invalid': errors.phone}"
							    	>
							    	<div v-if="errors.phone" class="invalid-feedback font-weight-bold">
								        @{{errors.phone[0]}}
								    </div>
							    </div>
							</div>
							<div class="row no-gutters">
								<div class="col-md-16 p-2 text-right">
							    	<span class="font-medium">Địa chỉ</span>
							    </div>
							    <div class="col-md-32 p-2">
							    	<input
							    		class="form-control"
							    		type="text"
							    		placeholder="Địa chỉ"
							    		v-model="receiverInfo.address"
							    		:class="{'is-invalid': errors.address}"
							    	>
							    	<div v-if="errors.address" class="invalid-feedback font-weight-bold">
								        @{{errors.address[0]}}
								    </div>
							    </div>
							</div>
							<div class="row no-gutters">
								<div class="col-md-16 p-2 text-right">
							    	<span class="font-medium">Phương thức thanh toán</span>
							    </div>
							    <div class="col-md-32 p-2">
							    	<select class="form-control" disabled="" v-model="receiverInfo.paymentMethod">
							    		<option value="3">COD</option>
							    	</select>
							    </div>
							</div>
						</form>
					</div>
					<div class="card mt-2" v-for="(item, index) in cart" style="max-width: 540px;" v-cloak>
						<div class="border-bottom p-2" v-if="step == 1">
							<img class="mr-1" alt="Takamart" src="/images/icon/store-mall-directory.svg" width="24px"><a :href="item.url" class="text-black text-decoration-none">@{{item.shopName}}</a>
							<span class="float-right cursor-pointer" style="color: #CCCCCC" @click="deleteDetail(item)">Xóa</span>
						</div>
					  	<div class="row no-gutters" v-for="(detail, detailIndex) in item.orderDetail" :key="detailIndex">
						    <div class="col-md-9 p-2">
						    	<a :href="detail.url">
						    		<div style="width: 84px; height: 84px; background-size: cover" :style="{'background-image': `url(${detail.image})`}"></div>
						    	</a>
						    </div>
						    <div class="col-md-39 p-2">
						      	<div class="card-body p-0 mt-2 mb-4">
						        	<a :href="detail.url" class="card-title font-size-16px text-decoration-none text-black">@{{detail.productName}}</a>
						      	</div>
						      	<div class="card-footer bg-white border-0 p-0">
							      	<div class="row no-gutters">
							      		<div class="col-30">
							      			<span class="font-size-16px">@{{detail.priceStr}}</span>
							      		</div>
							      		<template v-if="step == 1">
							      			<div class="col-14">
								      			<div class="input-group">
												  	<div class="input-group-prepend">
													    <a
													    	href="javascript:void(0)"
													    	class="border px-2 pt-2 text-black"
													    	style="border-radius: 4px 0px 0px 4px"
													    	@click="changeQuantity(index, detailIndex, -1)"
													    >
													    	<span class="material-icons-outlined icon-image-preview pb-2" style="font-size: 14px">remove</span>
													    </a>
												  	</div>
												  	<money :disabled="true" class="form-control bg-white p-0 text-center" id="price" v-model="detail.quantity" style="height: 34px"></money>
												  	<div class="input-group-append">
												  		<a
												  			href="javascript:void(0)"
												  			class="border px-2 pt-2"
												  			style="border-radius: 0px 4px 4px 0px"
												  			@click="changeQuantity(index, detailIndex, 1)"
												  		>
														    <span class="material-icons-outlined icon-image-preview text-primary pb-2" style="font-size: 14px">add</span>
														</a>
												  	</div>
												</div>
								      		</div>
								      		<div class="col-4">
								      			<span
								      				class="material-icons-outlined icon-image-preview mt-1 float-right cursor-pointer"
								      				style="color: #00000061"
								      				@click="deleteDetail(detail)"
								      			>
								      				delete
								      			</span>
								      		</div>
							      		</template>
							      		<template v-else>
							      			<div class="col-18 text-right">
							      				<span class="font-weight-bold text-secondary font-size-16px">x@{{detail.quantity}}</span>
							      			</div>
							      		</template>
							      	</div>
						      	</div>
						    </div>
					  	</div>
					  	<div class="row no-gutters">
					  		<div class="col-md-48 px-2" v-if="step == 2">
						    	<p class="mb-1 font-medium">Ghi chú</p>
								<textarea rows="4" class="form-control" placeholder="Ghi chú đơn hàng (nếu có)" v-model="item.note"></textarea>
								<span class="invalid-feedback d-block font-weight-bold">@{{item.errorNote}}</span>
						    </div>
						    <div class="col-md-48" v-if="step == 2">
						    	<div class="row no-gutters m-2">
									<div class="col-md-24">
										<span class="font-medium">Chi phí vận chuyển</span>
									</div>
									<div class="col-md-24 text-right">
										<span class="font-medium" style="color: #808080bd">Cập nhật sau</span>
									</div>
								</div>
						    </div>
						    <div class="col-md-48" v-if="step == 2">
						    	<div class="row no-gutters mx-2 mb-2 text-primary">
									<div class="col-md-24">
										<span class="font-medium">Tổng tiền</span>
									</div>
									<div class="col-md-24 text-right">
										<span class="font-medium">@{{item.totalStr}}</span>
									</div>
								</div>
						    </div>
					  	</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-24">
					<div class="card mt-2 mb-4" v-cloak style="max-width: 540px">
					  	<div class="row no-gutters px-2 pt-2">
					  		<div class="col-md-24">
					  			<span style="line-height: 2">Tổng cộng:</span>
					  		</div>
					  		<div class="col-md-24 text-right">
					  			<span class="font-weight-bold" style="font-size: 20px">@{{total}}</span>
					  		</div>
					  	</div>
					  	<div class="row no-gutters px-2 pb-2">
					  		<div class="col-md-24">
					  			Nhận tích lũy:
					  		</div>
					  		<div class="col-md-24 text-right">
					  			@{{accumulation}}
					  		</div>
					  	</div>
					  	<div class="row no-gutters p-2">
					  		<div v-if="step == 1 && total != 0" class="col-48">
					  			<a href="javascript:void(0)" class="btn btn-primary w-100" @click="step = 2">Tiến hành đặt hàng</a>
					  		</div>
					  		<div v-if="step == 2" class="col-24 pr-2">
						  		<a href="javascript:void(0)" class="btn btn-outline-primary w-100" @click="step = 1">Quay lại</a>
					  		</div>
					  		<div v-if="step == 2" class="col-24 pl-2">
						  		<a href="javascript:void(0)" class="btn btn-primary w-100" @click="createOrder">Tạo đơn hàng</a>
					  		</div>
					  	</div>
					</div>
				</div>
			</div>
			<div class="modal fade p-0" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static">
			  	<div class="modal-dialog modal-dialog-centered" role="document">
				    <div class="modal-content" style="max-width: 415px">
				      <div class="modal-body p-2">
				        <div class="row">
				        	<div class="col-48 d-flex justify-content-center">
				        		<img src="/images/icon/checked.svg" alt="Takamart" width="150px">
				        	</div>
				        	<div class="col-48 d-flex justify-content-center">
				        		<p class="my-2 font-weight-bold" style="font-size: 20px">Đặt hàng thành công</p>
				        	</div>
				        	<div class="col-48 text-center">
				        		<p class="mb-0">Đơn hàng đã được chuyển đến các cửa hàng tương ứng. Vui</p>
				        		<p class="mb-0">lòng chờ phản hồi từ cửa hàng.</p>
				        		<p class="mb-0"> Cảm ơn quý khách hàng đã tin tưởng dùng</p>
				        	</div>
				        </div>
				      </div>
				      <div class="row no-gutters justify-content-center mb-3">
				        <div class="col-md-24 p-2">
				        	<a href="{{ route('product-list', [], false) }}" class="btn btn-outline-primary w-100">Tiếp tục mua hàng</a>
				        </div>
				        <div class="col-md-24 p-2">
				        	<a href="{{ route('order.list', [], false) }}" class="btn btn-primary w-100">Quản lý đơn mua</a>
				        </div>
				      </div>
				    </div>
			  	</div>
			</div>
		</template>
		<template v-else>
			<div class="row mt-3">
				<div class="col-md-48 text-center">
					<p class="font-medium font-size-16px my-2">Không có sản phẩn nào trong giỏ</p>
				</div>
				<div class="col-md-48 text-center">
					<a href="{{ route('product-list', [], false) }}" class="btn btn-primary">Mua sắm ngay</a>
				</div>
			</div>
		</template>
	</div>
@endsection

@push('app-scripts')
	<script src="{{ mix('/js/front/cart/cart.js') }}"></script>
@endpush
