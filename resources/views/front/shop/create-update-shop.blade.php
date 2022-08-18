@extends('front.layout.master')

@push('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/shop.css') }}" type="text/css"/>
@endpush

@section('title')
    Thông tin cửa hàng
@endsection

@section('main-id', 'personal-info')

@section('body-user')
	<div class="container" id="create-shop">
		<div class="row no-gutters" v-cloak>
			<div v-if="shopId" class="col-md-16">
				<shop-tool-bar :shop-id="shopId" current-route-name="{{request()->route()->getName()}}" @permission="getPermission" class="mx-1"></shop-tool-bar>
			</div>
			<div class="col-md-32" :class="shopId ? '' : 'm-auto'">
				<div class="card mb-3 mx-1">
					<div class="card-header p-3 bg-white">
						<h5 v-if="!shopId" class="card-title mt-3 mb-0 font-weight-bold">Tạo cửa hàng</h5>
						<h5 v-else class="card-title mb-0 font-weight-bold">Thông tin cửa hàng</h5>
					</div>
					<div class="card-body px-4">
						<div class="row">
							<div class="col-md-48">
								<form>
									<div class="form-group row mt-2 mb-3">
										<label for="name" class="col-md-11 col-form-label mt-4 text-right" style="line-height: 3.5">Ảnh đại diện</label>
										<div class="col-md-37">
											<div class="media">
										  		<div v-if="!selectedFile && formData.value.avatarType == EResourceType.IMAGE && formData.value.avatar"
                                                     class="mr-4"
                                                     :class="permission && permission.avatar.type == 1 ? 'rounded-pill' : ''"
                                                     style="width: 88px; height: 88px; background-size: cover"
                                                     :style="{
                                                     	'background-image': `url(${formData.value.avatar})`,
                                                     	'border-radius': permission && permission.avatar.type == 1 ? '50%' : ''
                                                     }"></div>
                                                <div v-else-if="!selectedFile && formData.value.avatarType == EResourceType.IMAGE"
                                                 class="mr-4"
                                                 :class="permission && permission.avatar.type == 1 ? 'rounded-pill' : ''"
                                                 style="width: 88px; height: 88px; background-size: cover"
                                                 :style="{
                                                 	'background-image': `url(/images/default-user-avatar.png)`,
                                                 	'border-radius': permission && permission.avatar.type == 1 ? '50%' : ''
                                                 }"></div>
                                                <span
                                                	v-if="!selectedFile && formData.value.avatarType == EResourceType.VIDEO"
                                                	class="b-avatar badge-secondary mr-3" style="width: 100px; height: 100px"
                                                	:style="permission && permission.avatar.type == 1 ? 'border-radius: 50%' : ''"
                                                >
                                                 	<span class="shop-avatar">
                                                 		<video
												  		class="mr-4"
												  		autoplay muted loop :src="formData.value.avatar"
												  		style="max-width: 300px">

												  		</video>
                                                 	</span>
                                                </span>
										  		<div class="text-center mr-4" v-else-if="!avatarVideo && avatarImage">
													<img :class="permission && permission.avatar.type == 1 ? 'rounded-pill' : ''" width="88px" height="88px" alt="avatar" :src="avatarImage" />
												</div>
										  		<span v-if="avatarVideo" class="b-avatar badge-secondary mr-3" :class="permission && permission.avatar.type == 1 ? 'rounded-pill' : ''" style="width: 100px; height: 100px">
                                                 	<span class="shop-avatar" :class="permission && permission.avatar.type == 1 ? 'rounded-pill' : ''">
                                                 		<video
												  		class="mr-4"
												  		autoplay muted loop :src="avatarVideo">
												  		</video>
                                                 	</span>
                                                </span>

											  	<div class="media-body m-auto">
											    	<label class="cursor-pointer mb-0 mt-1" for="avatar">
														<i class="fas fa-camera mr-2 my-2 text-primary" style="font-size: 16px"></i>
														<span class="text-primary">
															Chọn file
														</span>
													</label>
													<input type="file" accept="image/*,video/mp4" id="avatar" hidden="" @change="onSelectAvatar">
													<div>
														<span class="text-muted">
															Hỗ trợ định dạng JPG, PNG, JPEG, MP4
														</span>
													</div>
													<div v-if="formData.errors.avatar" class="invalid-feedback d-block font-weight-bold">
										        		@{{formData.errors.avatar[0]}}
										    		</div>
											  	</div>
											</div>
										</div>
									</div>
								  	<div class="form-group row mt-2 mb-3">
								    	<label for="name" class="col-md-11 col-form-label text-right">Tên cửa hàng</label>
								    	<div class="col-md-37">
								      		<input
								      			type="text"
								      			class="form-control"
								      			id="name"
								      			placeholder="Tên cửa hàng"
								      			v-model="formData.value.name"
								      			:class="{'is-invalid': formData.errors.name}"
								      		>
								      		<div v-if="formData.errors.name" class="invalid-feedback font-weight-bold">
										        @{{formData.errors.name[0]}}
										    </div>
								    	</div>
								  	</div>
								  	<div class="form-group row mb-3">
								    	<label for="phone" class="col-md-11 col-form-label text-right">Số điện thoại</label>
								    	<div class="col-md-37">
								      		<input
								      			type="text"
								      			class="form-control"
								      			id="phone"
                                                @blur="removeUnDigitCharacters"
								      			placeholder="Số điện thoại" v-model="formData.value.phone"
								      			:class="{'is-invalid': formData.errors.phone}"
								      		>
								      		<div v-if="formData.errors.phone" class="invalid-feedback font-weight-bold">
										        @{{formData.errors.phone[0]}}
										    </div>
								    	</div>
								  	</div>
								  	<div class="form-group row mb-3">
								    	<label for="identityCode" class="col-md-11 col-form-label text-right pl-0">CMND, CCCD hoặc MST</label>
								    	<div class="col-md-37">
								      		<input
								      			type="text"
								      			class="form-control"
								      			id="identityCode"
								      			placeholder="CMND, CCCD hoặc MST" v-model="formData.value.identityCode"
								      			:class="{'is-invalid': formData.errors.identityCode}"
								      		>
								      		<div v-if="formData.errors.identityCode" class="invalid-feedback font-weight-bold">
										        @{{formData.errors.identityCode[0]}}
										    </div>
								    	</div>
								  	</div>
								  	<div class="form-group row mb-3">
								    	<label for="identityDate" class="col-md-11 col-form-label text-right">Ngày cấp</label>
								    	<div class="col-md-37">
								      		<input
								      			type="date"
								      			class="form-control"
								      			id="identityDate"
								      			placeholder="Ngày cấp" v-model="formData.value.identityDate"
								      			:class="{'is-invalid': formData.errors.identityDate}"
								      		>
								      		<div v-if="formData.errors.identityDate" class="invalid-feedback font-weight-bold">
										        @{{formData.errors.identityDate[0]}}
										    </div>
								    	</div>
								  	</div>
								  	<div class="form-group row mb-3">
								    	<label for="identityPlace" class="col-md-11 col-form-label text-right">Nơi cấp</label>
								    	<div class="col-md-37">
								      		<input
								      			type="text"
								      			class="form-control"
								      			id="identityPlace"
								      			placeholder="Nơi cấp" v-model="formData.value.identityPlace"
								      			:class="{'is-invalid': formData.errors.identityPlace}"
								      		>
								      		<div v-if="formData.errors.identityPlace" class="invalid-feedback font-weight-bold">
										        @{{formData.errors.identityPlace[0]}}
										    </div>
								    	</div>
								  	</div>
								  	<div class="form-group row mb-3">
								    	<label for="inputPassword" class="col-md-11 col-form-label text-right">Tỉnh, thành phố</label>
								    	<div class="col-md-37">
								      		<c-select
												v-model="formData.value.areaProvince"
												:search-route="'/area/all'"
												:custom-request-data="areaFilterData"
												:taggable="false"
												ref="areaFilterEl"
												placeholder="Vui lòng chọn tỉnh, thành phố"
												:class="{'is-invalid': formData.errors.areaId}"
											>
											</c-select>
											<div v-if="formData.errors.areaId" class="invalid-feedback font-weight-bold">
										        @{{formData.errors.areaId[0]}}
										    </div>
								    	</div>
								  	</div>
                                    <div class="form-group row mb-3">
                                        <label for="inputPassword" class="col-md-11 col-form-label text-right">Quận, huyện</label>
                                        <div class="col-md-37">
                                            <c-select
                                                v-model="formData.value.areaDistrict"
                                                :search-route="'/area/all'"
                                                :custom-request-data="districtFilterData"
                                                :taggable="false"
                                                ref="areaFilterEl"
                                                placeholder="Vui lòng chọn quận huyện"
                                            >
                                            </c-select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label for="inputPassword" class="col-md-11 col-form-label text-right">Xã, phường</label>
                                        <div class="col-md-37">
                                            <c-select
                                                v-model="formData.value.areaWard"
                                                :search-route="'/area/all'"
                                                :custom-request-data="wardFilterData"
                                                :taggable="false"
                                                ref="areaFilterEl"
                                                placeholder="Vui lòng chọn xã, phường"
                                            >
                                            </c-select>
                                        </div>
                                    </div>
								  	<div class="form-group row mb-3">
								    	<label for="address" class="col-md-11 col-form-label text-right">Địa chỉ</label>
								    	<div class="col-md-37">
								      		<div class="input-group">
								      			<input
								      				type="text"
								      				v-model="formData.value.address"
								      				class="form-control"
								      				id="address"
								      				placeholder="Địa chỉ"
								      				:class="{'is-invalid': formData.errors.address}"
								      			>
								      			<div class="input-group-prepend">
									      			<a href="javascript:void(0)"
                                                       class="btn btn-primary"
                                                       @click="showMap">Map
                                                    </a>
	  											</div>
									      		<div v-if="formData.errors.address" class="invalid-feedback font-weight-bold">
											        @{{formData.errors.address[0]}}
											    </div>
								      		</div>
								    	</div>
								  	</div>
								  	<div class="form-group row mb-3">
								    	<label for="email" class="col-md-11 col-form-label text-right">Email</label>
								    	<div class="col-md-37">
								      		<input
								      			type="text"
								      			class="form-control"
								      			id="email"
								      			placeholder="Email"
								      			v-model="formData.value.email"
								      			:class="{'is-invalid': formData.errors.email}"
								      		>
								      		<div v-if="formData.errors.email" class="invalid-feedback font-weight-bold">
										        @{{formData.errors.email[0]}}
										    </div>
								    	</div>
								  	</div>
								  	<div class="form-group row mb-3">
								    	<label for="zalo" class="col-md-11 col-form-label text-right">Fanpage (Zalo)</label>
								    	<div class="col-md-37">
								      		<input
								      			type="text"
								      			class="form-control"
								      			id="zalo"
								      			placeholder="link zalo"
								      			v-model="formData.value.zalo"
								      			:class="{'is-invalid': formData.errors.zalo}"
								      		>
								      		<div v-if="formData.errors.zalo" class="invalid-feedback font-weight-bold">
										        @{{formData.errors.zalo[0]}}
										    </div>
								    	</div>
								  	</div>
								  	<div class="form-group row mt-2 mb-3">
								    	<label for="fb" class="col-md-11 col-form-label text-right">Fanpage (Fb)</label>
								    	<div class="col-md-37">
								      		<input
								      			type="text"
								      			class="form-control"
								      			id="fb"
								      			placeholder="Link facebook"
								      			v-model="formData.value.fb"
								      			:class="{'is-invalid': formData.errors.fb}"
								      		>
								      		<div v-if="formData.errors.fb" class="invalid-feedback font-weight-bold">
										        @{{formData.errors.fb[0]}}
										    </div>
										    <a
								      			v-if="!shopId"
								      			class="btn btn-primary mt-4"
								      			href="javascript:void(0)"
								      			@click="saveShop"
								      		>
								      			Tạo cửa hàng
								      		</a>
								      		<a
								      			v-else
								      			class="btn btn-primary mt-4"
								      			href="javascript:void(0)"
								      			@click="saveShop"
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
		    		<input class="form-control" v-model="formData.value.address" type="text" id="pac-input" placeholder="Nhập vị trí">
		      		<div id="map" style="width: 100%;height: 500px"></div>
		    	</div>
		  	</div>
		</div>
		<div class="modal fade p-0" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static">
		  	<div class="modal-dialog modal-dialog-centered" role="document">
			    <div class="modal-content" style="max-width: 415px">
				    <div class="modal-body p-2">
				        <div class="row">
				        	<div class="col-48 d-flex justify-content-center">
				        		<img src="/images/icon/checked.svg" width="150px">
				        	</div>
				        	<div class="col-48 d-flex justify-content-center">
				        		<p class="my-2 font-weight-bold" style="font-size: 20px">Thông báo</p>
				        	</div>
				        	<div class="col-48 text-center">
				        		<p class="mb-0">Cửa hàng của bạn đã được tạo và sẽ được duyệt trong thời gian sớm nhất</p>
				        	</div>
				        </div>
				    </div>
				    <div class="row no-gutters justify-content-center mb-3">
				        <div class="col-md-24 p-2">
				        	<a href="{{ route('home', [], false) }}" class="btn btn-outline-primary w-100">Trang chủ</a>
				        </div>
				    </div>
			    </div>
		  	</div>
		</div>
		<input id="shopData" hidden="" type="text" data-id="{{isset($shopId) ? $shopId : null}}">
	</div>
@endsection

@push('app-scripts')
	<script src="{{ mix('/js/front/shop/create-update-shop.js') }}"></script>
	<!-- <script async defer src="https://maps.googleapis.com/maps/api/js?key={{config('app.maps_api_key')}}&callback=initGoogleMapSuccess"></script> -->
	<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAE7zocVnayiUt3EeLFosK0OaL9EYV46l8&callback=initGoogleMapSuccess&libraries=places"></script>
	<script type="text/javascript">
		function initGoogleMapSuccess() {
			$(document).trigger('initGoogleMapSuccess');
			$(document).data('initGoogleMapSuccess', true);
		}
	</script>
@endpush
