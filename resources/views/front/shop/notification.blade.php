@extends('front.layout.master')

@push('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/shop.css') }}" type="text/css"/>
    <link rel="stylesheet" href="{{ mix('/css/front/product.css') }}" type="text/css"/>
@endpush

@section('main-id', 'notification')

@section('title')
    Tạo thông báo
@endsection

@section('body-user')
	<div class="container shop-notification my-5">
		<div class="row no-gutters" v-cloak>
			<div class="col-md-16">
				<shop-tool-bar :shop-id="shopId" current-route-name="{{request()->route()->getName()}}" class="mx-1"></shop-tool-bar>
			</div>
			<div class="col-md-32">
				<div class="bg-white mx-2 font-medium">
					<p class="font-size-16px p-3 border-bottom">TẠO THÔNG BÁO</p>
					<div class="d-flex">
						<div class="m-auto px-2">
							<span class="mb-0">
								- Tính năng gửi thông báo được áp dụng cho cửa hàng cấp độ VIP.
							</span>
							<br>
							<span class="mb-0">
								- Tính năng gửi thông báo giúp cửa hàng quảng bá hình ảnh, mặt hàng, dịch vụ của mình
							</span>
<!-- 							<br>
							<span class="mb-0">
								- Thông báo được gửi đến tất cả người dùng sau khi đã được chúng tôi duyệt.
							</span>
							<br> -->
							<!-- <span class="mb-0">
								- Thời gian duyệt thông báo 1 ngày kể từ thời gian gửi
							</span> -->
						</div>
					</div>
					<div class="row no-gutters mt-3">
						<div class="col-md-14 text-right px-3">
							<span style="line-height: 2.5">Tiêu đề</span>
						</div>
						<div class="col-md-30">
							<input
								class="form-control w-100"
								v-model="formData.title"
								:class="{'is-invalid': formData.errors.titleVi}"
							>
							<div v-if="formData.errors.titleVi" class="invalid-feedback font-weight-bold">
						        @{{formData.errors.titleVi[0]}}
						    </div>
						</div>
					</div>
					<div class="row no-gutters mt-3">
						<div class="col-md-14 text-right px-3">
							<span style="line-height: 2.5">Nội dung</span>
						</div>
						<div class="col-md-30">
							<textarea
								rows="4"
								class="form-control"
								v-model="formData.content"
								:class="{'is-invalid': formData.errors.contentVi}"
							></textarea>
							<div v-if="formData.errors.contentVi" class="invalid-feedback font-weight-bold">
						        @{{formData.errors.contentVi[0]}}
						    </div>
						</div>
					</div>
					<div class="row no-gutters mt-3">
						<div class="col-md-14 text-right px-3">
							<span style="line-height: 2.5">Thời gian</span>
						</div>
						<div class="col-md-30">
							<div class="row">
								<div class="col-md-24">
									<f-date-time-picker
										id="notification-date-input"
		                                v-model="formData.date"
		                                placeholder="Ngày gửi"
		                                role="normal"
		                                :errors="formData.errors.date ? formData.errors.date[0] : null"
		                                :custom-config="{minDate: date}"
									></f-date-time-picker>
									<div v-if="formData.errors.date" class="invalid-feedback font-weight-bold">
								        @{{formData.errors.date[0]}}
								    </div>
								</div>
								<div class="col-md-24">
									<f-date-time-picker
										id="notification-time-input"
		                                v-model="formData.time"
		                                placeholder="Giờ gửi"
		                                role="time"
		                                :errors="formData.errors.time ? formData.errors.time[0] : null"
		                                :custom-config="{minDate: formData.date === dateString ?  new Date() : date, format: 'HH:mm:ss'}"
									></f-date-time-picker>
									<div v-if="formData.errors.time" class="invalid-feedback font-weight-bold">
								        @{{formData.errors.time[0]}}
								    </div>
								</div>
								<div class="col-md-48 my-4">
									<button class="btn btn-primary text-white" @click="createNotification">Tiến hành tạo thông báo</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<input id="shopData" hidden="" type="text" data-id="{{isset($shopId) ? $shopId : null}}">
	</div>
@endsection

@push('app-scripts')
	<script src="{{ mix('/js/front/shop/notification.js') }}"></script>
@endpush
