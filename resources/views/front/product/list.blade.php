@extends('front.layout.master')

@php
	$queries = request()->all();
	$filterData = base64_encode(
        json_encode([
            'filter' => $queries,
        ])
    );
@endphp

@push('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/product.css') }}" type="text/css"/>
@endpush

@section('main-id', 'product-list')

@section('body-user')
	<div class="container-fluid bg-white py-3">
		<div class="container">
			<div class="row" v-cloak>
				<div class="col-24">
					<span
						class="font-weight-bold font-size-16px cursor-pointer"
						@click="showModalCategory"
					>
						<span v-if="!filter.categoryId">Tất cả danh mục</span><span v-else>@{{categoryChoosed.name}}</span> <i class="fas fa-chevron-down"></i>
					</span>
				</div>
				<div class="col-24 text-right">
					<span
						class="font-medium cursor-pointer"
						@click="showModalArea"
					>
						<span v-if="!filter.areaId">Toàn quốc</span><span v-else>@{{areaChoosed.name}}</span> <i class="fas fa-map-marker-alt text-primary ml-2"></i>
					</span>
				</div>
			</div>
		</div>
	</div>
	<div class="container" id="product-list" v-cloak>
		<div class="row" v-if="category.childLevel1.length > 0">
			<div class="col-md-48 mt-3">
				<vue-slick-carousel
			    	v-bind="settings"
			    	style="max-width: 1110px"
			    >
			      	<div
			      		class="div-logo bg-white cursor-pointer position-relative"
			      		v-for="(child, index) in category.childLevel1"
			      		:class="child.id == filter.categoryId || child.id == filter.parentCategoryId ?
			      			'div-logo-active' : ''"
			      		@click="isShowWaiting = true, chooseCategoryLevel(child, false, 1)"
			      	>
			      		<div :style="height">
			      			<div class="logo-category">
				      			<div
				      				class="logo"
				      				:style="{'background-image': `url(${child.logo_full_path})`}"
				      			>
				      			</div>
				      		</div>
				      		<div :id="'child-name' + index" class="child_category text-center position-absolute w-100 px-2">
				      			<span>@{{child.name}}</span>
				      		</div>
			      		</div>
			      	</div>
			    </vue-slick-carousel>
			</div>
			<div class="col-md-48" v-if="category.childLevel2.length > 0">
				<a
					href="javascript:void(0)"
					class="item-category-level2 bg-white py-1 px-2 btn mb-2 mr-2"
					:class="!categoryChoosed.chooseLevel2 ? 'btn-outline-primary text-primary' : ''"
					@click="isShowWaiting = true, chooseCategory(null, false, 2)"
				>
					Tất cả
				</a>
				<a
					href="javascript:void(0)"
					class="bg-white item-category-level2 py-1 px-2 mr-2 btn mb-2"
					v-for="(item, index) in category.childLevel2"
					:class="{'btn-outline-primary text-primary': item.id == filter.categoryId && filter.parentCategoryId}"
					@click="isShowWaiting = true, chooseCategory(item, false, 2)"
				>
					@{{item.name}}
				</a>
			</div>
		</div>
		<div class="row no-gutters">
			<div class="col-md-8 filter">
				<div class="mb-2">
					<p v-if="attribute.length > 0" class="mb-1 font-weight-bold">
						<span>Lọc theo</span>
					</p>
					<template v-for="(item, index) in attribute">
					  	<p
					  		class="mb-0 py-2 border-bottom font-medium"
					  		:data-toggle="attributeChoosed[index].id ? 'collapse' : ''"
					  		:data-target="'#collapseExample' + index"
					  		role="button"
					  		aria-expanded="true"
					  		:aria-controls="'collapseExample' + index"
					  		@click="isShowWaiting = true, chooseAttribute(item, index)"
					  		v-if="item.enable_filter"
					  	>
					    	<span class="">@{{item.attribute_name}}</span>
					    	<i
					    		v-if="item.data"
					    		class="mr-2 float-right"
					    		:class="attributeChoosed[index].id == attribute[index].id ?
					    		'fas fa-chevron-up' : 'fas fa-chevron-down'"
					    		style="font-size: 12px; line-height: 2"
					    		:data-toggle="attributeChoosed[index].id ? 'collapse' : ''"
					  			:data-target="'#collapseExample' + index"
								:aria-controls="'collapseExample' + index"
					    	></i>
					  	</p>
					  	<div
							class="collapse show"
							v-if="attributeChoosed[index].id == item.id"
						>
							<template v-if="item.data">
								<p
						    		class="cursor-pointer my-2"
						    		:class="{'text-primary font-medium': attributeChoosed.length > 0 && attributeChoosed[index].value.length == 0}"
						    		@click="isShowWaiting = true, chooseAttribute('', index)"
						    	>
						    		Tất cả
						    	</p>
						    	<p
							  		class="my-2 cursor-pointer"
							  		:class="{'text-primary font-medium': attributeChoosed.length > 0 && attributeChoosed[index].value.indexOf(attribute) != -1}"
							  		v-for="(attribute, ind) in item.data.value"
							  		@click="isShowWaiting = true, chooseAttribute(attribute, index)"
							  	>
							  		@{{attribute}}
							  	</p>
							</template>
						</div>
						<div
							v-else
							class="collapse"
							:id="'collapseExample' + index"
						>
							<template v-if="item.data">
								<p
						    		class="cursor-pointer mb-1 ml-3"
						    		:class="{'font-medium': attributeChoosed.length > 0 && attributeChoosed[index].value.length == 0}"
						    		@click="isShowWaiting = true, chooseAttribute('', index)"
						    	>
						    		Tất cả
						    	</p>
						    	<p
							  		class="my-1 ml-3 cursor-pointer"
							  		:class="{'font-medium': attributeChoosed.length > 0 && attributeChoosed[index].value.indexOf(attribute) != -1}"
							  		v-for="(attribute, ind) in item.data.value"
							  		@click="isShowWaiting = true, chooseAttribute(attribute, index)"
							  	>
							  		@{{attribute}}
							  	</p>
							</template>
						</div>
					</template>
				</div>
				<p class="mt-4 mb-3 font-weight-bold">
					<span>Khoảng Giá</span>
				</p>
				<div class="row no-gutters border-bottom mb-1 pb-3">
					<div class="col-22">
						<!-- <input v-if="filter.minPrice" type="text" v-model="filter.minPrice" placeholder="Từ" maxlength="10" class="form-control border-0 px-1"> -->
						<money
                            v-model="filter.minPrice"
                            v-bind="money"
                            class="form-control border-0 px-1"
                        >
                        </money>
					</div>
					<div class="col-4 text-center" style="line-height: 2">
						<span>-</span>
					</div>
					<div class="col-22">
						<!-- <input v-if="filter.maxPrice" type="text" v-model="filter.maxPrice" placeholder="Đến" maxlength="10" class="form-control border-0 px-1"> -->
						<money
                            v-model="filter.maxPrice"
                            v-bind="money"
                            class="form-control border-0 px-1"
                        >
                        </money>
					</div>
					<!-- <input type="text" v-model="filter.minPrice" placeholder="Từ" style="width: 82.5px" maxlength="10" class="border-0 px-1">
					<span>-</span>
					<input type="text" v-model="filter.maxPrice" placeholder="Đến" style="width: 82.5px" maxlength="10" class="border-0 px-1"> -->
					<a class="btn btn-primary w-100 mt-2" href="javascript:void(0)" @click="isShowWaiting = true, getProductList()">Áp dụng</a>
				</div>

				<p class="mt-4 mb-3 font-weight-bold">Sắp xếp</p>
				<p
		    		class="cursor-pointer mb-2"
		    		:class="{'text-primary font-medium': filter.orderBy === 'created_at' && filter.orderDirection === 'desc'}"
		    		@click="isShowWaiting = true, filter.orderBy = 'created_at', filter.orderDirection = 'desc', getProductList()"
		    	>
		    		Tin đăng mới nhất
		    	</p>
		    	<p
		    		class="cursor-pointer mb-2"
		    		:class="{'text-primary font-medium': filter.orderBy === 'created_at' && filter.orderDirection === 'asc'}"
		    		@click="isShowWaiting = true, filter.orderBy = 'created_at', filter.orderDirection = 'asc', getProductList()"
		    	>
		    		Tin đăng cũ nhất
		    	</p>
		    	<p
		    		class="cursor-pointer mb-2"
		    		:class="{'text-primary font-medium': filter.orderBy === 'price' && filter.orderDirection === 'asc'}"
		    		@click="isShowWaiting = true, filter.orderBy = 'price', filter.orderDirection = 'asc', getProductList()"
		    	>
		    		Giá từ thấp đến cao
		    	</p>
		    	<p
		    		class="cursor-pointer mb-2"
		    		:class="{'text-primary font-medium': filter.orderBy === 'price' && filter.orderDirection === 'desc'}"
		    		@click="isShowWaiting = true, filter.orderBy = 'price', filter.orderDirection = 'desc', getProductList()"
		    	>
		    		Giá từ cao đến thấp
		    	</p>
		    	<a class="btn btn-primary w-100 my-3" @click="resetFilter">Mặc định</a>
			</div>
			<div class="col-md-40 my-3">
				<div class="row">
					<div v-if="products.total > 0" class="col-48 mb-3">
						<span class="ml-2">Có <span class="font-weight-bold">@{{products.total}}</span> kết quả được tìm thấy</span>
					</div>
					<div v-else class="col-48 text-center font-size-16px font-weight-bold">
						<span>Không có sản phẩm nào được tìm thấy</span>
					</div>
				</div>
				<div class="row card-deck mx-0">
					<product-item
						v-for="(item, index) in products.data"
						:class-item="'product-item'"
						:item-data="item"
						:display-btn-like="true"
                        :display-lowest-level-area="true"
						:key='index'
					></product-item>
				</div>
				<div v-if="products.total > 10" class="row no-gutters bg-white mx-1 my-3">
					<div class="col-48 d-flex justify-content-center py-2">
						<pagination class="mb-0" :data="products" @pagination-change-page="getProductList" :limit="4"></pagination>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade p-0" id="areaModal" tabindex="-1" role="dialog" aria-labelledby="areaModalLabel" aria-hidden="true">
		  	<div class="modal-dialog modal-dialog-centered">
		    	<div class="modal-content">
		      		<div class="modal-header py-2 bg-primary">
		        		<span class="font-medium font-size-16px w-100 text-white" style="line-height: 2">
                            Chọn khu vực
                        </span>
		        		<button type="button" class="btn text-white" data-dismiss="modal" aria-label="Close">
		          			<i class="fas fa-times"></i>
		        		</button>
		      		</div>
		      		<div class="modal-body mb-4" style="max-height: 400px; overflow: auto;">
		      			<div class="row no-gutters">
		      				<div v-if="!areaModal.districtList" class="col-md-48 px-2 pb-3">
		      					<div class="input-group mb-2">
								 	<input type="text" v-model="areaName" placeholder="Tìm kiếm tỉnh, thành phố" class="form-control border-right-0" @input="getAreaList" style="border: 1px solid rgba(0, 0, 0, 0.08)">
									<div class="input-group-append p-2" style="border-bottom-right-radius: 4px; border-top-right-radius: 4px; border: 1px solid rgba(0, 0, 0, 0.08)">
								    	<i class="fas fa-search text-black-50 cursor-pointer" @click="getAreaList"></i>
									</div>
								</div>
		      				</div>
			        		<div class="col-md-24 px-2" v-if="!areaModal.districtList && !areaName ">
			        			<button
				        			class="btn w-100 mb-2 font-medium"
				        			:class="filter.areaId == null ? 'btn-outline-primary' : ''"
				        			@click="chooseProvince(true, null)"
				        			style="height: 40px"
				        			:style="filter.areaId != null ? 'background:linear-gradient(0deg, rgba(0, 0, 0, 0.04), rgba(0, 0, 0, 0.04)), #FFFFFF' : ''"
				        		>
				        			Toàn quốc
				        		</button>
			        		</div>

                            <template v-if="!areaModal.districtList">
                                <div
                                    class="col-md-24 px-2"
                                    v-for="item in areaList"
                                >
                                    <button
                                        class="btn w-100 mb-2 font-medium"
                                        :class="item.id == areaChoosed.province.id ? 'btn-outline-primary' : ''"
                                        @click="chooseProvince(true, item)"
                                        style="height: 40px"
                                        :style="item.id != areaChoosed.province.id ? 'background:linear-gradient(0deg, rgba(0, 0, 0, 0.04), rgba(0, 0, 0, 0.04)), #FFFFFF' : ''"
                                    >
                                        @{{item.name}}
                                        <i v-if="item.district" class="fas fa-chevron-right float-right mt-1"></i>
                                    </button>
                                </div>
                            </template>


                            <template v-if="areaModal.districtList && !areaModal.wardList">
                                <div
                                    class="col-md-24 px-2"
                                    v-for="district in areaModal.districtList"
                                >
                                    <button
                                        class="btn w-100 mb-2 font-medium"
                                        :class="district.id == areaChoosed.district.id ? 'btn-outline-primary' : ''"
                                        @click="chooseDistrict(true, district)"
                                        style="height: 40px"
                                        :style="district.id != areaChoosed.district.id ? 'background:linear-gradient(0deg, rgba(0, 0, 0, 0.04), rgba(0, 0, 0, 0.04)), #FFFFFF' : ''"
                                    >
                                        @{{district.name}}
                                        <i v-if="district.child_areas.length > 0" class="fas fa-chevron-right float-right mt-1"></i>
                                    </button>
                                </div>
                            </template>

                            <template v-if="areaModal.wardList">
                                <div
                                    class="col-md-24 px-2"
                                    v-for="ward in areaModal.wardList"
                                >
                                    <button
                                        class="btn w-100 mb-2 font-medium"
                                        :class="ward.id == areaChoosed.ward.id ? 'btn-outline-primary' : ''"
                                        @click="chooseWard(true, ward)"
                                        style="height: 40px"
                                        :style="ward.id != areaChoosed.ward.id ? 'background:linear-gradient(0deg, rgba(0, 0, 0, 0.04), rgba(0, 0, 0, 0.04)), #FFFFFF' : ''"
                                    >
                                        @{{ward.name}}
                                    </button>
                                </div>
                            </template>

		      			</div>
		      		</div>
		      		<div class="modal-footer d-block" v-if="areaModal.districtList">
		        		<span class="text-primary cursor-pointer" @click="preArea">
		        			<i class="fas fa-chevron-left mr-1" style="font-size: 12px"></i> Quay lại
		        		</span>
		      		</div>
		    	</div>
		  	</div>
		</div>
		<div class="modal fade p-0" id="categoryModal" tabindex="-1" role="dialog" aria-labelledby="areaModalLabel" aria-hidden="true">
		  	<div class="modal-dialog modal-dialog-centered" role="document">
		    	<div class="modal-content">
		      		<div class="modal-header py-2 bg-primary">
		        		<span class="font-medium font-size-16px w-100 text-white" style="line-height: 2">Chọn danh mục</span>
		        		<button type="button" class="btn" data-dismiss="modal" aria-label="Close">
		          			<i class="fas fa-times text-white"></i>
		        		</button>
		      		</div>
		      		<div class="modal-body mb-4" style="max-height: 400px; overflow: auto;">
		      			<div class="row no-gutters">
		      				<template v-if="categoryChoosed.step == 1">
		      					<div class="col-md-48 px-2">
				        			<button
					        			class="btn w-100 mb-2 font-medium text-left"
					        			:class="filter.categoryId == null ? 'btn-outline-primary' : ''"
					        			@click="isShowWaiting = true, chooseCategory(null, true)"
					        			style="height: 40px"
					        			:style="filter.categoryId != null ? 'background:linear-gradient(0deg, rgba(0, 0, 0, 0.04), rgba(0, 0, 0, 0.04)), #FFFFFF' : ''"
					        		>
					        			Tất cả danh mục
					        		</button>
				        		</div>

				        		<div
				        			class="col-md-48 px-2"
				        			v-for="(item, index) in category.list"
					        	>
				        			<button
				        				:title="item.name"
					        			class="btn w-100 mb-2 font-medium text-left"
					        			:class="{'btn-outline-primary': item.id == categoryChoosed.id}"
							    		@click="categoryChoosed.step = 2, chooseCategory(item)"
					        			style="height: 40px"
					        			:style="item.id != categoryChoosed.id ? 'background:linear-gradient(0deg, rgba(0, 0, 0, 0.04), rgba(0, 0, 0, 0.04)), #FFFFFF' : ''"
					        		>
					        			@{{item.name}}
					        			<i v-if="item.child_categories.length > 0" class="fas fa-chevron-right float-right mt-1"></i>
					        		</button>
				        		</div>
		      				</template>

			        		<template v-else-if="categoryChoosed.step == 2 && category.childLevel1.length > 0">
			        			<div class="col-md-48 px-2">
				        			<button
					        			class="btn w-100 mb-2 font-medium text-left"
					        			:class="!filter.parentCategoryId ? 'btn-outline-primary' : ''"
					        			@click="chooseCategory(null, false)"
					        			style="height: 40px"
					        			:style="filter.parentCategoryId ? 'background:linear-gradient(0deg, rgba(0, 0, 0, 0.04), rgba(0, 0, 0, 0.04)), #FFFFFF' : ''"
					        		>
					        			Tất cả
					        		</button>
				        		</div>
			        			<div
				        			class="col-md-48 px-2"
				        			v-for="(child, index) in category.childLevel1"
					        	>
				        			<button
				        				:title="child.name"
					        			class="btn w-100 mb-2 font-medium text-left"
					        			:class="{'btn-outline-primary': child.id == filter.categoryId && filter.parentCategoryId}"
							    		@click="categoryChoosed.step = 3, chooseCategory(child, false, 1)"
					        			style="height: 40px"
					        			:style="child.id != filter.categoryId ? 'background:linear-gradient(0deg, rgba(0, 0, 0, 0.04), rgba(0, 0, 0, 0.04)), #FFFFFF' : ''"
					        		>
					        			@{{child.name}}
					        			<i v-if="child.child_categories.length > 0" class="fas fa-chevron-right float-right mt-1"></i>
					        		</button>
				        		</div>
			        		</template>

			        		<template v-else-if="categoryChoosed.step == 3 && category.childLevel2.length > 0">
			        			<div class="col-md-48 px-2">
				        			<button
					        			class="btn w-100 mb-2 font-medium text-left"
					        			:class="!categoryChoosed.chooseLevel2 ? 'btn-outline-primary' : ''"
					        			@click="isShowWaiting = true, chooseCategory(null, false, 2)"
					        			style="height: 40px"
					        			:style="categoryChoosed.chooseLevel2 ? 'background:linear-gradient(0deg, rgba(0, 0, 0, 0.04), rgba(0, 0, 0, 0.04)), #FFFFFF' : ''"
					        		>
					        			Tất cả
					        		</button>
				        		</div>
			        			<div
				        			class="col-md-48 px-2"
				        			v-for="(child2, index) in category.childLevel2"
					        	>
				        			<button
				        				:title="child2.name"
					        			class="btn w-100 mb-2 font-medium text-left"
					        			:class="{'btn-outline-primary': child2.id == filter.categoryId && filter.parentCategoryId}"
							    		@click="isShowWaiting = true, chooseCategory(child2, false, 2)"
					        			style="height: 40px"
					        			:style="child2.id != filter.categoryId ? 'background:linear-gradient(0deg, rgba(0, 0, 0, 0.04), rgba(0, 0, 0, 0.04)), #FFFFFF' : ''"
					        		>
					        			@{{child2.name}}
					        		</button>
				        		</div>
			        		</template>
		      			</div>
		      		</div>
		      		<div class="modal-footer d-block" v-if="categoryChoosed.step > 1">
		        		<span class="text-primary cursor-pointer" @click="preCategory">
		        			<i class="fas fa-chevron-left mr-1" style="font-size: 12px"></i> Quay lại
		        		</span>
		      		</div>
		    	</div>
		  	</div>
		</div>
		<input id="product-data" hidden="" type="text" data-filter="{{isset($filterData) ? $filterData : null}}">
	</div>
@endsection

@push('app-scripts')
	<script src="{{ mix('/js/front/product/list.js') }}"></script>
@endpush
