@extends('front.layout.master')

@push('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/product.css') }}" type="text/css"/>
    <style type="text/css">
    	p {
    		margin-bottom: 0px;
    	}
    </style>
@endpush

@section('main-id', 'create-product')

@section('body-user')
	@if(auth()->user()->getShop->status == App\Enums\EStatus::ACTIVE)
		<div class="bg-white container-fluid p-0 border-bottom mb-4">
			<div class="container">
				<div class="row">
					<div class="col-48 font-weight-bold py-2">
						@if(request()->route()->getName() == 'product.edit')
							<span style="font-size: 20px">CẬP NHẬT TIN ĐĂNG / SẢN PHẨM</span>
						@else
							<span style="font-size: 20px"> ĐĂNG TIN / SẢN PHẨM</span>
						@endif
					</div>
				</div>
			</div>
		</div>
		<div class="container" id="create-product" v-cloak>
			<div class="row no-gutters mb-5 justify-content-center">
				<div class="col-md-32 col-sm-48" style="border-bottom: none;">
					<div class="card">
						<div class="card-body p-0">
							<div class="row" style="overflow: hidden;">
								<div class="col-48">
									<div class="progress_bar progress_bar_active" @click="back(1)">
										<div 
											class="div_triangle div_triangle_active"
										></div>
										<div class="m-auto">
											<span class="font-medium mx-2">1. Hình thức đăng</span>
										</div>
									</div>
									<div 
										class="progress_bar"
										:class="{'progress_bar_active': step > 1}"
										@click="back(2)"
									>
										<div 
											class="div_triangle2"
											:style="step >= 1 ? 'border-right: 12px solid white' : 'border-right: 12px solid #e4e4e4'" 
										></div>
										<div class="div_triangle" :class="{'div_triangle_active' : step >= 1}"></div>
										<div class="m-auto">
											<span class="font-medium mx-2">2. Danh mục</span>
										</div>
									</div>
									<div 
										class="progress_bar"
										:class="{'progress_bar_active': step > 3}"
										@click="back(4)"
									>
										<div 
											class="div_triangle2"
											:style="step >= 2 ? 'border-right: 12px solid white' : 'border-right: 12px solid #e4e4e4'" 
										></div>
										<div class="div_triangle" :class="{'div_triangle_active' : step >= 2}"></div>
										<div class="m-auto">
											<span class="font-medium mx-2">3. Thông tin sản phẩm</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="card" style="border-top: none">
						<div class="card-body py-2 px-0">
							<div class="row">
								<template v-if="step == 1">
									<div class="col-48">
										<div class="row no-gutters">
											<div class="col-48 bg-white px-3 py-3">
												<a 
													class="btn border w-100 text-left mb-2"
													:class="formData.type == EProductType.PRODUCT_FOR_SALE ? 'border-primary text-primary' : 'border'"
													@click="nextStep(EProductType.PRODUCT_FOR_SALE)"
												>
													Đăng sản phẩm
												</a>
												<a 
													class="btn border w-100 text-left mb-2"
													:class="formData.type == EProductType.POST ? 'border-primary text-primary' : 'border'"
													@click="nextStep(EProductType.POST)"
												>
													Đăng tin
												</a>
											</div>
										</div>
									</div>
								</template>
								<template v-if="step == 2">
									<div class="col-48">
										<div class="row no-gutters">
											<div class="col-48 bg-white px-3 py-3">
												<a 
													class="btn border w-100 text-left mb-2"
													:class="formData.category.id == item.id ? 'border-primary text-primary' : 'border'"
													v-for="(item, index) in categoryList"
													@click="nextStep(item)"
												>
													@{{item.name}} <i v-if="item.child_categories.length > 0" class="fas fa-chevron-right float-right mt-1 text-muted"></i>
												</a>
											</div>
										</div>
									</div>
								</template>
								<template v-if="step == 3 && childCategoryList.length > 0">
									<div class="col-48">
										<div class="row no-gutters">
											<div class="col-48 bg-white px-3 py-4">
												<a 
													class="btn border w-100 text-left mb-2"
													@click="back(2)"
												>
													Quay lại
												</a>
												<a 
													class="btn border w-100 text-left mb-2"
													:class="formData.category.id == item.id ? 'border-primary text-primary' : 'border'"
													v-for="(item, index) in childCategoryList"
													@click="nextStep(item)"
												>
													@{{item.name}}
												</a>
											</div>
										</div>
									</div>
								</template>
								<template v-if="step == 4">
									<div class="col-48">
										<div class="row no-gutters px-2">
											<div class="col-48 bg-white px-3 py-4">
												<form>
												  	<div class="form-group row">
												    	<div class="col-md-48">
												    		<label class="col-form-label font-medium text-right">
													  			<p class="mb-0">Video</p>
													  			<!-- <span>Bạn có thể đăng tối đa 6 hình với định dạng JPG, JPEG, PNG</span> -->
													  		</label>
													  		<div class="row no-gutters">
												      			<div class="col-md-12" v-if="permission.video_in_product && permission.video_in_product.allow_upload_video">
												      				<label class="px-3 py-1 cursor-pointer text-center" for="input-file"
												      				style="background: #00000014; width: 133px">
														      			<img src="/images/icon/upload.svg" width="16px">	Tải lên
														      		</label>
												      			</div>
												      			<div :class="permission.video_in_product && permission.video_in_product.allow_upload_video ? 'col-md-36' : 'col-md-48'">
												      				<div class="input-group">
												      					<input type="text" class="form-control" id="video" v-model="formData.linkVideo" :class="{'is-invalid': formData.errors.linkVideo}" placeholder="Nhập link">
																		<div class="input-group-prepend">
																		    <a
																		    	class="btn" 
																		    	style="background: #ebebeb"
																		    	@click="addLinkVideo"
																		    >
																		    	Thêm
																		    </a>
																		</div>
																	</div>
												      			</div>
												      			<div class="col-md-48" v-if="permission.video_in_product && permission.video_in_product.allow_upload_video">
														      		<div class="row">
														      			<div class="col-md-24">
														      				<span class="font-medium" style="color: #00000061; font-size: 12px">Hỗ trợ file video MP4, thời lượng @{{permission.video_in_product.upload_time}} giây</span>
														      			</div>
														      			<div class="col-md-24">
														      				<div v-if="formData.errors.linkVideo && formData.linkVideo == null" class="invalid-feedback font-weight-bold d-block text-right">
																		        @{{formData.errors.linkVideo[0]}}
																		    </div>
														      			</div>
														      		</div>
												      			</div>
												      			<!-- <div class="col-md-48">
												      				<iframe class="mt-2 border-0" v-if="!!getYoutubeId" width="100%" height="300px" :src="getYoutubeId" allowfullscreen="true"></iframe>
												      			</div> -->
												      			<div class="col-md-48">
												      				<div v-if="formData.video.length > 0" class="mt-2 mr-2 position-relative float-left" v-for="(item, index) in formData.video">
													    				<video v-if="item.type == 1" width="133px" style="border-radius: 5px; max-height: 75px" :src='item.src'>
													    				</video>
													    				<img v-else width="133px" style="border-radius: 5px; max-height: 75px" :src='item.src'>
													    				<span class="position-absolute cursor-pointer btn-remove" @click="removeOldVideo(index)">
													    					<img src="/images/icon/cancel-24px.svg">
													    				</span>
													    			</div>
													    			<div v-if="videoRender.length > 0" class="mt-2 mr-2 position-relative float-left" v-for="(item, index) in videoRender">
													    				<video v-if="item.type == 'file'" width="133px" style="border-radius: 5px; max-height: 75px" :src='item.src'>
													    				</video>
													    				<img v-else width="133px" style="border-radius: 5px; max-height: 75px" :src='item.src'>
													    				<span class="position-absolute cursor-pointer btn-remove" @click="removeVideo(index)">
													    					<img src="/images/icon/cancel-24px.svg">
													    				</span>
													    			</div>
												      			</div>
												      		</div>
												      		<input class="form-control" type="file" ref="fileVideoInputEl" id="input-file" hidden="" @change="onSelectVideo" :class="{'is-invalid': formData.errors.videoCount}">
												      		<div v-if="formData.errors.videoCount" class="invalid-feedback float-left font-weight-bold">
														        @{{formData.errors.videoCount[0]}}
														    </div>
												    	</div>
												  	</div>
												  	<div class="form-group row">
												    	<div class="col-md-48">
												    		<label class="col-form-label font-medium text-right">
													  			<p class="mb-0">Hình ảnh</p>
													  		</label>
													  		<div class="row">
												      			<div class="col-md-48">
												      				<label class="px-3 py-1 cursor-pointer text-center" for="input-file-1"
												      				style="background: #00000014; width: 133px">
														      			<img src="/images/icon/upload.svg" width="16px">	Tải lên
														      		</label>
														      		<br>
														      		<span class="font-medium" style="color: #00000061; font-size: 12px">Bạn có thể đăng tối đa @{{permission.num_image_in_product}} hình với định dạng JPG, JPEG, PNG</span>
												      			</div>
												      			<div class="col-md-48">
												      				<div 
												      					v-if="formData.image.length > 0" class="mt-2 mr-2 position-relative float-left" v-for="(item, index) in formData.image" 
												      				>
													    				<img width="90px" height="90px" style="border-radius: 5px" :src='item'/>
													    				<span class="position-absolute cursor-pointer btn-remove" @click="removeOldImage(index)">
													    					<img src="/images/icon/cancel-24px.svg">
													    				</span>
													    			</div>
														    		<div 
														    			v-if="imageRender.length > 0" class="mt-2 mr-2 position-relative float-left" v-for="(item, index) in imageRender"
												      				>
													    				<img width="90px" height="90px" style="border-radius: 5px" :src='item'/>
													    				<span class="position-absolute cursor-pointer btn-remove" @click="removeImage(index)">
													    					<img src="/images/icon/cancel-24px.svg">
													    				</span>
													    			</div>
												      			</div>
												      		</div>
												      		<input class="form-control" type="file" ref="fileImageInputEl" id="input-file-1" hidden="" @change="onSelectImage" :class="{'is-invalid': formData.errors.imageCount}" name="files[]" multiple>
												      		<div v-if="formData.errors.imageCount" class="invalid-feedback float-left font-weight-bold">
														        @{{formData.errors.imageCount[0]}}
														    </div>
												    	</div>
												  	</div>
												  	<div class="form-group row">
												    	<div class="col-md-48 mb-2">
												    		<label for="name" class="col-form-label font-medium text-right mb-0">Tiêu đề/ Tên sản phẩm</label>
												    		<input type="text" class="form-control" id="name" v-model="formData.name" :class="{'is-invalid': formData.errors.name}" placeholder="Nhập tên sản phẩm">
												      		<div v-if="formData.errors.name" class="invalid-feedback font-weight-bold">
														        @{{formData.errors.name[0]}}
														    </div>
												    	</div>
												    	<div class="col-md-24 mb-2">
												    		<label for="price" class="col-form-label font-medium text-right mb-0">Giá</label>
												    		<money class="form-control" id="price" v-model="formData.price" :class="{'is-invalid': formData.errors.price}"></money>
												      		<div v-if="formData.errors.price" class="invalid-feedback font-weight-bold">
														        @{{formData.errors.price[0]}}
														    </div>
												    	</div>
												    	<div class="col-md-24 mb-2">
												    		<label for="unit" class="col-form-label font-medium text-right mb-0">Đơn vị</label>
												    		<input type="text" class="form-control" id="unit" v-model="formData.unit" :class="{'is-invalid': formData.errors.unit}" placeholder="cm, mm, kg...">
												      		<div v-if="formData.errors.unit" class="invalid-feedback font-weight-bold">
														        @{{formData.errors.unit[0]}}
														    </div>
												    	</div>
												    	<div v-if="item.attribute_name" class="col-md-24 mb-2" v-for="(item, index) in attribute" :key="index + genkey">
												    		<div class="row">
												    			<div class="col-md-48" v-if="item.value_type == ECategoryValueType.SINGLE">
												    				<label :for="item.attribute_name" class="col-form-label font-medium text-right mb-0">@{{item.attribute_name}}</label>
														      		<template v-if="item.data_type == ECategoryDataType.NUMBER">
														      			<input type="number" class="form-control" :id="item.attribute_name" v-model="formData.category.attribute[index].value" :placeholder="'Nhập ' + item.attribute_name"
														      			:key="index + genkey":class="{'is-invalid': formData.errors.attributeName && formData.errors.attributeName[index] != index}">
														      		</template>

														      		<template v-if="item.data_type == ECategoryDataType.TEXT">
														      			<input type="text" class="form-control" :id="item.attribute_name" v-model="formData.category.attribute[index].value" :placeholder="'Nhập ' + item.attribute_name":key="index + genkey":class="{'is-invalid': formData.errors.attributeName && formData.errors.attributeName[index] != index}">
														      		</template>

														      		<template v-if="item.data_type == ECategoryDataType.DATE">
														      			<input type="date" class="form-control" :id="item.attribute_name" v-model="formData.category.attribute[index].value" :placeholder="'Nhập ' + item.attribute_name":key="index + genkey"
														      			:class="{'is-invalid': formData.errors.attributeName && formData.errors.attributeName[index] != index}">
														      		</template>
														      		<div v-if="formData.errors.attributeName && formData.errors.attributeName[index] != index" class="invalid-feedback font-weight-bold">
																        @{{item.attribute_name}} là bắt buộc
																    </div>
														    	</div>
														    	<div class="col-md-48" v-else>
														    		<label :for="item.attribute_name" class="col-form-label font-medium text-right mb-0">@{{item.attribute_name}}</label>
														    		<a-select
														      			class="form-control"
														      			v-model="formData.category.attribute[index].value"
																		:options="item.data.value"
																		:multiple="true"
																		:searchable="false"
																	    :close-on-select="true"
																	    :show-labels="false"
																	    :placeholder="item.attribute_name"
																	    :class="{'is-invalid': formData.errors.attributeName&& formData.errors.attributeName[index] != index, 'extend-height': formData.category.attribute[index].value.length > 0}" :key="index + genkey"
																	>
																        <template #caret="{toggle}">
																            <span class="multiselect__select custom-caret" @mousedown.prevent.stop="toggle">
																                <open-indicator/>
																            </span>
																        </template>
																	</a-select>
																	<div v-if="formData.errors.attributeName && formData.errors.attributeName[index] != index" class="invalid-feedback font-weight-bold">
																        @{{item.attribute_name}} là bắt buộc
																    </div>
														    	</div>
												    		</div>
												    	</div>
												    	<div class="col-md-48 mb-2">
												    		<label for="description" class="col-form-label font-medium text-right mb-0">Mô tả</label>
												      		<textarea class="form-control" id="description" rows="3" v-model="formData.description" :class="{'is-invalid': formData.errors.description}" placeholder="Nhập mô tả"></textarea>
												      		<div v-if="formData.errors.description" class="invalid-feedback font-weight-bold">
														        @{{formData.errors.description[0]}}
														    </div>
												      		
												      		@if(request()->route()->getName() == 'product.edit')
																<a href="javascript:void(0)" class="mt-3 btn btn-primary float-right" @click="saveProduct">Tiến hành cập nhật</a>
															@else
																<a href="javascript:void(0)" class="mt-3 btn btn-primary float-right" @click="saveProduct">Tiến hành đăng tin</a>
															@endif
												    	</div>
												  	</div>
												</form>
											</div>
										</div>
									</div>
								</template>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-48">
					<a @click="prePage" href="javascript:void(0)"><i class="fas fa-arrow-left mr-2 cursor-pointer"></i> Quay lại</a>
				</div>
			</div>
			<input id="productData" hidden="" type="text" data-code="{{isset($code) ? $code : null}}" 
			data-shop-id="{{isset($shopId) ? $shopId : null}}">
		</div>
	@else
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
	@endif
@endsection

@push('app-scripts')
	<script src="{{ mix('/js/front/product/create.js') }}"></script>
	<script async src="https://www.tiktok.com/embed.js"></script>
@endpush