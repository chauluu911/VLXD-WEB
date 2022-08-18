@extends('front.layout.master')

@push('stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/front/shop.css') }}" type="text/css"/>
    <link rel="stylesheet" href="{{ mix('/css/front/product.css') }}" type="text/css"/>
@endpush

@section('main-id', 'shop-info')

@section('title')
    Xem cửa hàng
@endsection

@section('body-user')
	<div class="container-fluid bg-white py-3" v-cloak>
		<div class="container pr-0">
			<div class="row no-gutters text-white p-3" style="background-size: cover"
                :style="`background-image: url('/images/store/level-${shop.level}.jpg')`"
            >
            	<div class="col-md-16">
            		<div class="media">
                        <div v-if="shop.avatarType == EResourceType.IMAGE" class="rounded-pill" style="width: 68px; height: 68px; background-size: cover" :style="{'background-image': `url(${shop.avatar})`}"></div>
                        <span v-else class="b-avatar badge-secondary rounded-circle" style="width: 68px; height: 68px">
                            <span class="shop-avatar">
                                <video autoplay muted loop :src="shop.avatar" style="width: 125px"></video>
                            </span>
                        </span>
                        <div class="media-body ml-3">
                            <p class="font-size-16px mb-0">@{{shop.name}}</p>
                            <div class="row no-gutters">
                                <div class="col-30">
                                    <div>
                                        <span style="color: #FFFFFF99">
                                            ID store:
                                        </span>
                                        <span>
                                            @{{shop.code}}
                                        </span>
                                    </div>
                                    <a class="p-0" style="cursor: pointer;color: #FFFFFF99"
                                       @click="showModalFollower"
                                    >
                                        <span>
                                            Lượt theo dõi:
                                        </span>
                                        <span>
                                            @{{shop.follow}}
                                        </span>
                                    </a>
                                </div>
                                <div class="col-18">
                                	@if(auth()->id() && auth()->user()->getShop && $id != auth()->user()->getShop->id)
                                    	<a v-if="shop.isFollowed == false" class="btn btn-outline-light" @click="followShop(true)">Theo dõi</a>
                                    	<a v-else class="btn btn-outline-light" @click="followShop()">Bỏ theo dõi</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
            	</div>
            	<div class="col-md-8 bg-white mx-auto my-2" style="border-radius: 5px">
            		<p class="mb-1 text-center font-medium text-black mt-2">Cấp cửa hàng</p>
                    <p
                        class="text-center mb-0"
                        style="padding-top: 2px"
                    >
                        <img :src="`/images/star/level-${shop.level}.png`" alt="Cấp độ cửa hàng" class="pb-1" width="20px">
                        <span class="font-medium text-black">@{{shop.levelName}}</span>
                    </p>
            	</div>
            	<div class="col-md-8 bg-white mx-auto my-2" style="border-radius: 5px">
                    <a :href="'/shop/' + shopId + '/review'"
                       style="color: #000000DD"
                    >
                        <p class="mb-0 text-center font-medium text-black mt-2">Đánh giá</p>
                        <div
                            class="text-center mb-0"
                            style="padding-top: 2px"
                        >
                            <div href="" class="d-flex justify-content-center">
                                <star-rating v-if="shop.evaluate" :show-rating="false" :increment="0.1" :star-size="16" :read-only="true" :rating="shop.evaluate.average">
                                </star-rating>
                            </div>
                        </div>
                    </a>

            	</div>
            	<div class="col-md-8 bg-white mx-auto my-2" style="border-radius: 5px">
            		<p class="mb-1 text-center font-medium text-black mt-2">Phản hồi chat</p>
            		<p class="mb-0 text-center font-medium text-black">@{{shop.responseRate}}%</p>
            	</div>
        	</div>
		</div>
	</div>
	<div class="container shop-info" v-cloak>
		<div class="row no-gutters">
			<div class="col-md-48">
				<div class="row">
					<div class="col-md-16">
						<div class="bg-white p-3 my-2">
	            			<p class="mb-1 font-medium">Liên hệ cửa hàng:</p>
	            			@if(auth()->id() && auth()->user()->getShop && $id != auth()->user()->getShop->id || !auth()->id())
		            			<a class="btn border mr-2 contact-shadow mb-2" @click="zaloRedirectTo(shop.zalo)">
		            				<img class="mr-1" src="/images/icon/zalo.svg" alt="zalo" width="24px">Zalo
		            			</a>
		            			<a :href="'tel:' + shop.phone" class="btn border mr-2 contact-shadow mb-2">
	                                <img class="mr-1" src="/images/icon/call.svg" alt="Takamart" width="24px">Gọi điện
	                            </a>
		            			<button class="btn border mr-2 contact-shadow mb-2" @click="redirectToMessage">
		            				<img class="mr-1" src="/images/icon/chat.svg" alt="chat" width="24px">Nhắn tin
		            			</button>
		            		@else
		            			<a href="javascript:void(0)" class="btn border mr-2 contact-shadow mb-2" style="background: #E6E6E6">
		            				<img class="mr-1 rounded-pill" src="/images/icon/zalo-dissable.jpg" alt="zalo" width="24px">Zalo
		            			</a>
		            			<a href="javascript:void(0)" class="btn border mr-2 contact-shadow mb-2" style="background: #E6E6E6">
	                                <img class="mr-1" src="/images/icon/call.svg" alt="Takamart" width="24px">Gọi điện
	                            </a>
		            			<button class="btn border mr-2 contact-shadow mb-2" style="background: #E6E6E6">
		            				<img class="mr-1" src="/images/icon/chat.svg" alt="chat" width="24px">Nhắn tin
		            			</button>
		            		@endif
	            		</div>
						<div class="bg-white p-3 my-2" v-if="video.value.length > 0">
							<p class="mb-1 font-medium">Video</p>
							<div class="position-relative">
                                <div v-if="video.value[video.index].videoType == EVideoType.INTERNAL_VIDEO" class="video-frame">
                                    <video :src="video.value[video.index].path">
                                    </video>
                                </div>

                                <div v-else class="video-frame" >
                                    <img class="img-thumbnail" :src="video.value[video.index].thumbnail">
                                </div>
                                <button class="btn rounded-pill border position-absolute bg-white p-0 btn-pre-video" v-if="video.index > 0" @click="nexrOrPreVideo(-1)">
                                 	<i class="fas fa-chevron-left" style="font-size: 12px"></i>
                                 </button>
                                <button class="btn rounded-pill border position-absolute bg-white p-0 btn-next-video" v-if="video.index < video.value.length - 1" @click="nexrOrPreVideo(1)">
                                	<i class="fas fa-chevron-right" style="font-size: 12px"></i>
                                </button>
                                <div class="position-absolute div-faded" @click="showModal">
                                	<div class="position-relative w-100 h-100">
                                		<img src="/images/icon/play-arrow-white.svg" alt="Takamart" width="35px" class="position-absolute bg-black rounded-pill" style="top: 43%; right: 45%">
                                	</div>
                                </div>
							</div>
						</div>
						<div class="bg-white p-3 my-2">
							<p class="mb-1 font-medium" v-if="imageList.value.length > 0">Hình ảnh</p>
							<div class="row mb-2 no-gutters" v-if="imageList.value.length > 0">
								<div class="col-24 p-1">
									<div
										class="cursor-pointer"
										:style="`background-image: url('${imageList.value[0].path}')`"
										style="width: 100%; height: 0px; padding-bottom: 100%; background-position: center;
										background-repeat: no-repeat; background-size: cover;"
										@click="imageList.index = 0"
										data-toggle="modal" data-target="#modal-image"
									>
									</div>
								</div>
								<div class="col-24">
									<div class="row no-gutters">
										<div
											v-for="(item, index) in imageList.value"
											class="col-24 p-1"
											v-if="index < 5 && index > 0"
										>
											<div
												class="cursor-pointer"
												:class="{'position-relative': index == 4}"
												:style="`background-image: url('${item.path}')`"
												style="width: 100%; height: 0px;padding-bottom: 100%; background-position: center;
												 background-repeat: no-repeat; background-size: cover;"
												data-toggle="modal" data-target="#modal-image"
												@click="imageList.index = index"
											>
												<div v-if="index == 4" class="position-absolute div-faded">
													<div class="position-relative w-100 h-100">
				                                		<span class="position-absolute text-white" style="font-size: 20px; right: 35%; top: 27%">
				                                			+@{{imageList.value.length - 5}}
				                                		</span>
				                                	</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<p class="mb-1 font-medium">Giới thiệu</p>
							<div class="row no-gutters mb-2">
								<div class="col-24">
									<div class="float-left mr-1" style="width: 16px">
										<span
											class="material-icons-outlined icon-image-preview
											font-size-16px text-00000061"
											style="margin-top: 3px"
										>
											how_to_reg
										</span>
									</div>
									<span class="text-00000061">Ngày thanm gia: </span>
								</div>
								<div class="col-24 font-medium text-right">
									<span>@{{shop.createdAt}}</span>
								</div>
							</div>
							<div class="row no-gutters mb-2">
								<div class="col-24">
									<div class="float-left mr-1" style="width: 16px">
										<span
											class="material-icons-outlined icon-image-preview
											font-size-16px text-00000061"
											style="margin-top: 3px"
										>
											email
										</span>
									</div>
									<span class="text-00000061">Email: </span>
								</div>
								<div class="col-24 font-medium text-right">
									<span style="word-break: break-all">@{{shop.email}}</span>
								</div>
							</div>
							<div class="row no-gutters mb-2">
								<div class="col-24">
									<div class="float-left mr-1" style="width: 16px">
										<span
											class="material-icons-outlined icon-image-preview
											font-size-16px text-00000061"
											style="margin-top: 3px"
										>
											facebook
										</span>
									</div>
									<span class="text-00000061">Facebook: </span>
								</div>
								<div class="col-24 font-medium text-right">
									<span style="word-break: break-all">@{{shop.fb}}</span>
								</div>
							</div>
							<div class="row no-gutters mb-2">
								<div class="col-24">
									<div class="float-left mr-1" style="width: 16px">
										<span
											class="material-icons-outlined icon-image-preview
											font-size-16px text-00000061"
											style="margin-top: 3px"
										>
											call
										</span>
									</div>
									<span class="text-00000061">Điện thoại: </span>
								</div>
								<div class="col-24 font-medium text-right">
									<span style="word-break: break-all">@{{shop.phone}}</span>
								</div>
							</div>
							<div class="row no-gutters mb-2">
								<div class="col-24">
									<div class="float-left mr-1" style="width: 16px">
										<span
											class="material-icons-outlined icon-image-preview
											font-size-16px text-00000061"
											style="margin-top: 3px"
										>
											location_on
										</span>
									</div>
									<span class="text-00000061">Địa chỉ: </span>
								</div>
								<div class="col-24 font-medium text-right">
									<p class="mb-1">@{{shop.address}}</p>
								</div>
								<div class="col-md-48">
									<div id="map" style="width: 100%; height: 200px"></div>
								</div>
							</div>
						</div>
					</div>
					<div v-if=" products.data && products.data.length > 0" class="shop-product col-md-32 p-0">
						<div class="row mt-2 py-2">
							<div class="col-md-36" style="line-height: 2">
								<span class="div-title_product">Sản phẩm đang bán</span>
							</div>
							<div class="col-md-12 text-right" v-if="products && products.last_page > 1">
								<span>
									<span class="text-primary">@{{paging}}</span>/@{{products.last_page}}
								</span>
								<button class="btn bg-white ml-3" @click="getProductList(paging - 1)">
									<i class="fas fa-chevron-left"></i>
								</button>
								<button class="btn bg-white" @click="getProductList(paging + 1)">
									<i class="fas fa-chevron-right"></i>
								</button>
							</div>
						</div>
						<div class="row card-deck no-gutters px-2" id="product-list">
							<product-item
								v-for="item in products.data"
								:class-item="'shop-product-item'"
								:item-data="item"
							></product-item>
						</div>
                        <div v-if="products.total > pageSize" class="row no-gutters bg-transparent mx-1 my-3">
                            <div class="col-48 d-flex justify-content-center py-2">
                                <pagination class="mb-0" :data="products" @pagination-change-page="getProductList" ></pagination>
                            </div>
                        </div>
					</div>

					<div v-else class="text-center font-size-16px font-weight-bold col-md-32 pl-2 mt-2">
						<span>Không có sản phẩm nào</span>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade p-0 bd-example-modal-lg" id="modal-video" tabindex="-1" role="dialog" aria-labelledby="modal-video-Title" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		  	<div class="modal-dialog modal-lg" role="document">
		    	<div class="modal-content">
		      		<div class="modal-header">
				        <button type="button" class="close" @click="closeModal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
		      		</div>
				    <div class="modal-body p-0">
				        <div class="row" v-if="show">
				        	<div class="col-md-48">
				        		<div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel" data-interval="false">
								  	<div class="carousel-inner">
								    	<div
								    		class="carousel-item"
								    		:class="{'active': video.index == index}"
								    		v-for="(item, index) in video.value"
								    	>
								      		<template v-if="video.index == index">
								      			<video v-if="item.videoType == EVideoType.INTERNAL_VIDEO" controls preload width="100%" height="100%" :src="item.path">
			                                	</video>
			                                	<iframe v-else width="100%" height="400px" :src="item.path"></iframe>
								      		</template>
								    	</div>
								  	</div>
								</div>
				        	</div>
				        </div>
						<div class="row no-gutters">
							<div class="col-md-48">
								<ol class="carousel-indicators bg-black w-100 m-0">
								    <li
								    	data-target="#carouselExampleIndicators"
								    	v-for="(item, index) in video.value"
								    	:data-slide-to="index"
								    	:class="{'active': video.index == index}"
								    	@click="video.index = index"
								    ></li>
								</ol>
							</div>
						</div>
					</div>
			      	<div class="modal-footer p-1">
			        	<button type="button" class="btn btn-secondary" @click="closeModal">Đóng</button>
			      	</div>
		    	</div>
		  	</div>
		</div>

		<div class="modal fade bd-example-modal-lg"
             id="modal-image"
             tabindex="-1"
             role="dialog"
             aria-labelledby="modal-image-Title"
             aria-hidden="true"
             data-keyboard="false">
		  	<div class="modal-dialog image-modal-dialog" role="document">
                <div class="modal-content w-100 bg-white  mt-2"
                     style="display: flex;justify-content: center;flex-direction: row;border: 0;height: 100%; align-items: center"
                >
                    <div class="row w-100" style="height: 95%;" >
                        <div class="col-md-30 main-image-modal">
                            {{--                                    <div id="carouselExampleIndicators" style="height: 100%"--}}
                            {{--                                         class="carousel slide"--}}
                            {{--                                         data-ride="carousel"--}}
                            {{--                                         data-interval="false">--}}
                            {{--                                        <div class="carousel-inner" style="height: 100%">--}}
                            {{--                                            <div--}}
                            {{--                                                class="carousel-item" style="height: 100%"--}}
                            {{--                                                :class="{'active': imageList.index == index}"--}}
                            {{--                                                v-for="(item, index) in imageList.value"--}}
                            {{--                                            >--}}
                            {{--                                                <div--}}
                            {{--                                                     :style="{'background-image': `url(${item.src})`}"--}}
                            {{--                                                     style="height: 100%;--}}
                            {{--                                                     background-repeat: no-repeat;--}}
                            {{--                                                     background-position: center;--}}
                            {{--                                                     background-size: contain;"--}}
                            {{--                                                >--}}
                            {{--                                                </div>--}}
                            {{--                                            </div>--}}
                            {{--                                        </div>--}}
                            {{--                                    </div>--}}
                            <div v-if="imageList.value.length > 0"
                                 :style="{'background-image': `url(${imageList.value[imageList.index].path})`}"
                                 style="height: 100%;
                                         background-repeat: no-repeat;
                                         background-position: center;
                                         background-size: contain;"
                            >
                            </div>
                            <button class="btn position-absolute p-0 btn-pre-img"
                                    v-if="imageList.index > 0" @click="nextOrPreImg(-1)">
                                <i class="fas fa-chevron-left" style="font-size: 14px; color: white;"></i>
                            </button>
                            <button class="btn position-absolute p-0 btn-next-img"
                                    style="z-index: 999999"
                                    v-if="imageList.index < imageList.value.length - 1" @click="nextOrPreImg(1)">
                                <i class="fas fa-chevron-right" style="font-size: 14px; color: white;"></i>
                            </button>
                        </div>
                        <div class="col-md-18" style="max-height: 100%;">
                            <div class="w-100 border-0 m-0 image-list-modal"
                                 style="margin-top: -4px !important;">
                                <div
                                    v-for="(item,index) in imageList.value"
                                    class="image-item-modal"
                                >
                                    <div class="image-frame-modal"
                                         :class="{'image-frame-modal-active': index === imageList.index}"
                                         @click="imageList.index = index"
                                         :style="{'background-image': `url(${item.path})`}"
                                    >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
		  	</div>
		</div>
        <div class="modal fade p-0" id="modal-follower" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" >
                    <div class="modal-header py-2 bg-primary">
		        		<span class="font-medium font-size-16px w-100 text-white" style="line-height: 2">Người theo dõi</span>
		        		<button type="button" class="btn text-white" data-dismiss="modal" aria-label="Close">
		          			<i class="fas fa-times"></i>
		        		</button>
		      		</div>
                    <div class="modal-body p-2 modal-follower-body" ref="listFollowerEL">
                        <div class="row" >
                            <div class="col-md-24 col-48 m-0 py-2" v-for="follower in followers.listFollower">
                                <div class="d-flex">
                                    <div class="mx-2 rounded-pill"
                                         style="width: 40px; height: 40px;background-size: cover"
                                         :style="{'background-image': `url(${follower.avatar})`}"
                                    >
                                    </div>
                                    <div style="line-height: 40px;max-height: 40px; overflow: hidden">
                                        @{{follower.name}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row m-0 d-flex justify-content-center pb-4"
                        >
                            <i v-if="iconLoad" class="fas fa-spinner fa-spin"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<input id="shopData" hidden="" type="text" data-id="{{isset($id) ? $id : null}}">
	</div>
@endsection

@push('app-scripts')
	<script src="{{ mix('/js/front/shop/shop-info.js') }}"></script>
	<!-- <script async defer src="https://maps.googleapis.com/maps/api/js?key={{config('app.maps_api_key')}}&callback=initGoogleMapSuccess"></script> -->
	<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAE7zocVnayiUt3EeLFosK0OaL9EYV46l8&callback=initGoogleMapSuccess&libraries=places"></script>
	<script type="text/javascript">
		function initGoogleMapSuccess() {
			$(document).trigger('initGoogleMapSuccess');
			$(document).data('initGoogleMapSuccess', true);
		}
	</script>
@endpush
