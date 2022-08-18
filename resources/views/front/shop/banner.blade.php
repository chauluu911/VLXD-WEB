@extends('front.layout.master')

@push('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/shop.css') }}" type="text/css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.4.3/cropper.min.css" integrity="sha256-d2pK8EVd0fI3O9Y+/PYWrCfAZ9hyNvInLoUuD7qmWC8=" crossorigin="anonymous"/>
@endpush
@section('main-id', 'banner')

@section('title')
    Đăng banner quảng cáo
@endsection

@section('body-user')
	<div class="container shop-banner my-5">
		<div class="row no-gutters" v-cloak>
			<div class="col-md-16">
				<shop-tool-bar :shop-id="shopId" current-route-name="{{request()->route()->getName()}}" class="mx-1"></shop-tool-bar>
			</div>
			<div class="col-md-32">
				<div class="bg-white font-medium p-3 shop-banner-content mx-2">
					<p class="font-size-16px mb-2">BANNER QUẢNG CÁO</p>
					<div class="row">
						<div class="col-md-38">
							<select
								v-model="status"
								class="form-control"
								style="width: 155px"
								@change="getBanner"
							>
								<option :value="null">Tất cả</option>
								<option value="0">Chờ duyệt</option>
								<option value="1">Đã duyệt</option>
								<option value="2">Bị từ chối</option>
							</select>
						</div>
						<div class="col-md-10 text-right">
							<button class="btn btn-primary text-white" @click="showModalCropper()">Tạo banner</button>
						</div>
					</div>
				</div>
				<div class="m-1">
					<div class="row no-gutters">
						<div v-for="item in banners" class="col-md-24 div-banner">
							<div
								class="position-relative image-banner"
								:style="`background-image: url('${item.path_to_resource}')`"
								@click="showModalCropper(item)"
							>
								<div style="padding-top: 48%">
									<p
										class="mb-0 text-info font-medium px-3 py-2"
										style="background: #000000B3; border-radius: 0 0 5px 5px"
									>
										@{{item.statusString}}
										<template v-if="item.status == EStatus.ACTIVE">
											<span v-if="item.valid_date > 0"> - còn @{{item.valid_date}} ngày hiển thị</span>
											<span v-else> 	- Hết hạn</span>
										</template>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade p-0" id="modal-cropper" tabindex="-1" role="dialog" aria-hidden="true">
		  	<div class="modal-dialog modal-lg" role="document">
		    	<div class="modal-content">
			      	<div class="modal-header py-2 bg-primary">
		        		<span class="font-medium font-size-16px w-100 text-white" style="line-height: 2" v-if="!formData.isEdit">Tạo banner</span>
		        		<span class="font-medium font-size-16px w-100 text-white" style="line-height: 2" v-else-if="formData.status == EStatus.WAITING">Chỉnh sửa banner</span>
		        		<span class="font-medium font-size-16px w-100 text-white" style="line-height: 2" v-else>Chi tiết banner</span>
		        		<button type="button" class="btn text-white" data-dismiss="modal" aria-label="Close">
		          			<i class="fas fa-times"></i>
		        		</button>
		      		</div>
			      	<div class="modal-body">
			        	<div class="row">
			        		<div class="col-md-48" v-if="formData.isEdit">
			        			<button class="btn btn-primary float-right" @click="deleteBanner">Xóa banner</button>
			        		</div>
			        		<div
			        			class="col-md-48 px-4 mb-3"
			        			v-show="!formData.web.edit && !formData.mobile.edit && !formData.isEdit || formData.isEdit && formData.web.edit"
			        		>
			        			<p class="mb-2">Banner web:</p>
			        			<cropper-image
		                            ref="imageWebCropperEl"
		                            @cropper-created="onCropperWebCreated"
		                            @cropper-reset="resetCropperWeb"
		                            @crop-image="cropImageWeb"
		                            :image-url="formData.web.url"
		                            :aspect-ratios="[{name: webRatio.name, value: webRatio.value}]"
		                            :disable-size="true"
		                            :disabled="formData.status == EStatus.WAITING || formData.isEdit == false ? false : true"
		                        >
			                        <template #text-drop-image>
			                            <div
			                                class="p-5 cursor-pointer"
			                            >
											<img src="/images/default-image.svg" width="24px">
											Tỉ lệ banner khả dụng 16:5
			                            </div>
			                        </template>
		                        </cropper-image>
		                        <div v-if="formData.errors.webFile" class="invalid-feedback font-weight-bold d-block">
							        @{{formData.errors.webFile[0]}}
							    </div>
			        		</div>
			        		<div
			        			class="col-md-48 px-4"
			        			v-show="!formData.web.edit && !formData.mobile.edit && !formData.isEdit || formData.isEdit && formData.mobile.edit"
			        		>
			        			<p class="mb-2">Banner mobile:</p>
			        			<cropper-image
			        				class="w-100"
		                            ref="imageMobileCropperEl"
		                            @cropper-created="onCropperMobileCreated"
		                            @cropper-reset="resetCropperMobile"
		                            @crop-image="cropImageMobile"
		                            :image-url="formData.mobile.url"
		                            :aspect-ratios="[{name: mobileRatio.name, value: mobileRatio.value}]"
		                            :disable-size="true"
		                            :disabled="formData.status == EStatus.WAITING || formData.isEdit == false ? false : true"
		                        >
			                        <template #text-drop-image>
			                            <div
			                                class="p-5 cursor-pointer"
			                            >
			                                <img src="/images/default-image.svg" width="24px">
											Tỉ lệ banner khả dụng 16:9
			                            </div>
			                        </template>
		                        </cropper-image>
		                        <div v-if="formData.errors.mobileFile" class="invalid-feedback font-weight-bold d-block">
							        @{{formData.errors.mobileFile[0]}}
							    </div>
			        		</div>
			        		<template v-if="formData.status == null || formData.status == EStatus.WAITING">
			        			<div class="col-md-48 mt-3">
				        			<p class="mb-2 px-2">Hành động khi click vào banner:</p>
				        			<div class="row px-2">
				        				<div class="col-md-12">
						        			<label class="container font-medium" @click="changeAction(EBannerActionType.DO_NOTHING)">Không làm gì cả
											  	<input v-if="formData.actionType == EBannerActionType.DO_NOTHING" type="radio" name="radio" checked>
											  	<input v-else type="radio" name="radio">
											  	<span class="checkmark"></span>
											</label>
				        				</div>
				        				<div class="col-md-14">
											<label class="container font-medium" @click="changeAction(EBannerActionType.OPEN_WEBSITE)">Đường dẫn liên kết
											  	<input v-if="formData.actionType == EBannerActionType.OPEN_WEBSITE" type="radio" name="radio" checked>
											  	<input v-else type="radio" name="radio">
											  	<span class="checkmark"></span>
											</label>
				        				</div>
				        			</div>
				        		</div>
				        		<div class="col-md-48">
				        			<input
				        				type="text"
				        				class="form-control w-50"
				        				placeholder="Đường dẫn liên kết"
				        				:disabled="formData.actionType == EBannerActionType.DO_NOTHING ? true : false"
				        				v-model="formData.link"
				        				:class="{'is-invalid': formData.errors.link}"
				        			>
				        			<div v-if="formData.errors.link" class="invalid-feedback font-weight-bold">
								        @{{formData.errors.link[0]}}
								    </div>
				        		</div>
			        		</template>
			        	</div>
			      	</div>
	      			<template v-if="formData.status == null || formData.status == EStatus.WAITING">
	      				<div class="modal-footer">
			        		<button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
			        		<button v-if="formData.status == EStatus.WAITING || formData.isEdit == false" type="button" class="btn btn-primary" @click="saveBanner">Lưu</button>
			        	</div>
		        	</template>
		        	<template v-else>
		        		<div class="modal-footer d-block">
			        		<p class="mb-1">
			        			<span class="font-medium" style="color: #00000061">Hành động:
			        			</span><a v-if="formData.link" :href="formData.link"></a>
			        			<span v-else>Không làm gì cả</span>
			        		</p>
			        		<p class="mb-1">
			        			<span class="font-medium" style="color: #00000061">Thời gian tạo: </span><span>@{{formData.createdAt}}</span>
			        		</p>
			        		<p class="mb-1">
			        			<span class="font-medium" style="color: #00000061">Trạng thái: </span><span class="text-info">
			        				@{{formData.statusString}}
			        			</span>
			        		</p>
			        	</div>
		        	</template>
		      		</div>
		    	</div>
		  	</div>
		</div>
		<input id="shopData" hidden="" type="text" data-id="{{isset($shopId) ? $shopId : null}}" data-banner-id="{{request('bannerId', null)}}">
	</div>
@endsection

@push('app-scripts')
	<script src="{{ mix('/js/front/shop/banner.js') }}"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.min.js" integrity="sha256-EuV9YMxdV2Es4m9Q11L6t42ajVDj1x+6NZH4U1F+Jvw=" crossorigin="anonymous"></script>
@endpush
