@php
	if(auth()->user()) {
		$userData = auth()->user();
		$userData->affiliateCode = auth()->user()->affiliateCode;
		$userData->avatarPath = App\Helpers\FileUtility::getFileResourcePath($userData->avatar_path, App\Constant\DefaultConfig::FALLBACK_USER_AVATAR_PATH);
	}
@endphp
@extends('front.layout.master')

@section('title')
    Thông tin cá nhân
@endsection

@section('main-id', 'personal-info')

@section('body-user')
	<div class="container">
		<div class="row my-4">
			<profile class="col-md-16" :profile-info="{
                name: '{{auth()->user()->name}}',
                code: '{{auth()->user()->affiliateCode ? auth()->user()->affiliateCode->code : null}}',
                currentTab: '',
                linkToOrder: '{{ route('order.list', [], false) }}',
                linkToSavedProduct: '{{ route('saved-product-list', [], false) }}',
                linkToPaymentHistory: '{{ route('payment.history', [], false) }}',
                linkToChangePassword: '{{ route('profile.change-password', [], false) }}',
                linkToLogout: '{{ route('logout', [], false) }}',
                linkToDeposit: '{{ route('payment.deposit.view', [], false) }}',
                avatarPath: infoUser.avatarPath,
                linkToProfile: '{{ route('profile', [], false) }}',
                }"
            >
            </profile>
			<div class="col-md-32 personal-info" style="padding: 0px 12px">
				<div class="card mb-3">
					<div class="card-header border-bottom bg-white py-3 px-4">
						<span class="font-size-16px font-medium">Thông tin cá nhân</span>
					</div>
					<div class="card-body px-4">
						<div class="row">
							<div class="col-md-48">
								<form>
									<div class="form-group row mt-3 mb-2">
										<label class="col-md-8 col-form-label mt-4 text-right" style="line-height: 3.5">Ảnh đại diện</label>
										<div class="col-md-40">
											<div class="media">
												<div v-if="!selectedFile" class="m-auto rounded-pill" style="width: 100px; height: 100px; background-size: cover" :style="{'background-image': `url(${infoUser.avatarPath})`}"></div>
												<div class="text-center" v-else>
													<span id="resource"></span>
												</div>
												<div class="media-body mt-3 ml-4">
													<label class="cursor-pointer mb-0 mt-1 text-primary" for="avatar">
														<i class="fas fa-camera mr-2 my-2" style="font-size: 16px"></i>
														<span class="font-weight-bold">
															@lang('front/profile.info.choose_avatar')
														</span>
													</label>
													<input type="file" accept="image/*" id="avatar" hidden="" @change="onSelectAvatar">
													<div>
														<span class="text-muted">
															Hỗ trợ định dạng JPG, PNG, JPEG
														</span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="form-group row mt-3 mb-2">
								    	<label for="affiliateCode" class="col-md-8 col-form-label text-right">Mã số thẻ</label>
								    	<div class="col-md-40">
								      		<input type="text" class="form-control" id="affiliateCode" disabled placeholder="Mã số thẻ" v-model="formData.value.affiliateCode" maxlength="5"
								      		:class="{'is-invalid': formData.errors.affiliateCode}">
								      		<div v-if="formData.errors.affiliateCode" class="invalid-feedback font-weight-bold">
										        @{{formData.errors.affiliateCode[0]}}
										    </div>
								    	</div>
								  	</div>
								  	<div class="form-group row mt-3 mb-2">
								    	<label for="name" class="col-md-8 col-form-label text-right">Họ tên</label>
								    	<div class="col-md-40">
								      		<input type="text" class="form-control" id="nam" placeholder="Họ và tên" v-model="formData.value.name"
								      		:class="{'is-invalid': formData.errors.name}">
								      		<div v-if="formData.errors.name" class="invalid-feedback font-weight-bold">
										        @{{formData.errors.name[0]}}
										    </div>
								    	</div>
								  	</div>
								  	<div class="form-group row mb-2">
								    	<label for="phone" class="col-md-8 col-form-label text-right">Số điện thoại</label>
								    	<div class="col-md-40">
								      		<input type="text" class="form-control" disabled id="phone" placeholder="Số điện thoại" v-model="formData.value.phone" :class="{'is-invalid': formData.errors.phone}">
								      		<div v-if="formData.errors.phone" class="invalid-feedback font-weight-bold">
										        @{{formData.errors.phone[0]}}
										    </div>
								    	</div>
								  	</div>
								  	<div class="form-group row mb-2">
								    	<label for="address" class="col-md-8 col-form-label text-right">Địa chỉ</label>
								    	<div class="col-md-40">
								      		<div class="input-group" :class="{'is-invalid': formData.errors.address}">
								      			<input id="address" type="text" class="form-control" placeholder="Địa chỉ" v-model="formData.value.address":class="{'is-invalid': formData.errors.address}">
									      		<div class="input-group-prepend">
									      			<a href="javascript:void(0)" class="btn btn-primary" @click="showMap">Map</a>
	  											</div>
								      		</div>
								      		<div v-if="formData.errors.address" class="invalid-feedback font-weight-bold">
										        @{{formData.errors.address[0]}}
										    </div>
								    	</div>
								  	</div>
								  	<div class="form-group row mb-2">
								    	<label for="email" class="col-md-8 col-form-label text-right">Email</label>
								    	<div class="col-md-40">
								      		<input type="text" class="form-control" id="email" placeholder="Email" v-model="formData.value.email" :class="{'is-invalid': formData.errors.email}">
								      		<div v-if="formData.errors.email" class="invalid-feedback font-weight-bold">
										        @{{formData.errors.email[0]}}
										    </div>
								    	</div>
								  	</div>
								  	<div class="form-group row mb-2">
								    	<label for="dob" class="col-md-8 col-form-label text-right">Ngày sinh</label>
								    	<div class="col-md-40">
								      		<input type="date" class="form-control" id="dob" placeholder="Ngày sinh" v-model="formData.value.dob"
								      		:class="{'is-invalid': formData.errors.dob}">
								      		<div v-if="formData.errors.dob" class="invalid-feedback font-weight-bold">
										        @{{formData.errors.dob[0]}}
										    </div>
								    	</div>
								  	</div>
								  	<div class="form-group row">
								    	<label for="inputPassword" class="col-md-8 col-form-label text-right">Giới tính</label>
								    	<div class="col-md-40">
								      		<select class="form-control" v-model="formData.value.gender" v-cloak :class="{'is-invalid': formData.errors.gender}">
								      			<option :value="EGender.MALE">@{{EGender.valueToName(EGender.MALE)}}</option>
								      			<option :value="EGender.FEMALE">@{{EGender.valueToName(EGender.FEMALE)}}</option>
								      		</select>
								      		<div v-if="formData.errors.gender" class="invalid-feedback font-weight-bold">
										        @{{formData.errors.gender[0]}}
										    </div>
								      		<a
								      			class="btn btn-primary mt-3"
								      			href="javascript:void(0)"
								      			@click="saveInfoUser"
								      		>
								      			Cập nhật
								      		</a>
								    	</div>
								  	</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade p-0 bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="map-modal">
		  	<div class="modal-dialog modal-lg">
		    	<div class="modal-content">
		    		<input id="pac-input" v-model="formData.value.address" class="form-control" type="text" placeholder="Nhập địa chỉ"/>
		      		<div id="map" style="width: 100%;height: 500px"></div>
		    	</div>
		  	</div>
		</div>
		<input id="user-info" hidden="" type="text" data-user="{{$userData}}" data-is-edit="{{$isEdit}}">
	</div>
@endsection

@push('app-scripts')
	<script src="{{ mix('/js/front/profile/personal-info.js') }}"></script>
	<!-- <script async defer src="https://maps.googleapis.com/maps/api/js?key={{config('app.maps_api_key')}}&callback=initGoogleMapSuccess"></script> -->
	<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAE7zocVnayiUt3EeLFosK0OaL9EYV46l8&callback=initGoogleMapSuccess&libraries=places"></script>
	<script type="text/javascript">
		function initGoogleMapSuccess() {
			$(document).trigger('initGoogleMapSuccess');
			$(document).data('initGoogleMapSuccess', true);
		}

		function openNav() {
				$('.admin-nav').css('display', 'block');
		}
		function closeNav() {
			if (screen.width <= 768) {
				$('.admin-nav').css('display', 'none');
			}
		}
	</script>
@endpush
