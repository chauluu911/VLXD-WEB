<template>
   	<b-row>
		<b-col cols="48">
			<div class="content__inner">
				<div id="map" class="d-none" style="width: 100%;height: 500px"></div>
				<b-row class="pb-2" style="border-bottom: 3px solid gray">
					<b-col cols="24">
						<span class="span__title-detail mr-4">{{action == 'create' ? $t('common.button.add2', {'objectName': $t('employee')}).toUpperCase() : $t('info').toUpperCase()}}</span>
						<div class="btn text-white cursor-default mb-2" v-if="!!status.string" :class="status.value == EStatus.DELETED ? 'bg-danger' : 'bg-primary'">
                            <span>
                                {{ status.string }}
                            </span>
                        </div>
					</b-col>
					<b-col cols="24" v-if="action === 'create'">
						<b-button variant="primary" class="float-right ml-3" @click="updateInfo">
							<i class="far fa-save"></i>
							{{$t('save')}}
						</b-button>
					</b-col>
					<b-col cols="24" v-if="status.value != EStatus.DELETED && action !== 'create'">
						<template v-if="disable">
							<b-button variant="outline-primary" class="float-right ml-3" @click="deleteShop">
								<i class="fas fa-trash-alt"/>
								{{$t('common.tooltip.delete')}}
							</b-button>
							<b-button :to="{name: 'shop.info', params:{shopId: formData.id, action: 'edit'}}" variant="outline-primary" class="float-right">
								<i class="fas fa-edit"/>
								{{$t('common.tooltip.edit')}}
							</b-button>
						</template>
						<template v-else>
							<b-button variant="primary" class="float-right ml-3" @click="updateInfo">
								<i class="far fa-save"></i>
								{{$t('save')}}
							</b-button>
                            <b-link
                                @click="goBack"
								class="float-right btn text-primary">
								<span>{{$t('back')}}</span>
							</b-link>
						</template>
					</b-col>
				</b-row>
				<b-row>
				   <b-col cols="48" class="pt-3">
						<div style="position: relative;">
							<label for="avatar" style="cursor: pointer;">
								<b-avatar variant="primary" v-if="selectedFile == null" :src="formData.avatar" size="100"/>
								<span v-else id="resource"></span>
							</label>
							<input v-if="!disable" type="file" ref="fileInputEl" id="avatar" hidden="" @change="onSelectAvatar">
                            <span
                                :tooltip="{}" :title="$t('common.tooltip.delete')"
                                class="position-absolute cursor-pointer"
                                v-if="selectedFile != null" @click="removeAvatar">
								<i class="fas fa-times" />
							</span>
						</div>
				   </b-col>
				</b-row>
				<b-row>
				   <b-col cols="48" class="pt-3">
						<b-form>
							<b-row>
								<b-col md="16">
									<b-form-group class="font-weight-bold"
										:label-class="'required'"
									  	:label="$t('label.shop-name')"
									  	:state="!!formData.errors.name"
									  	:invalid-feedback="formData.errors.name && formData.errors.name[0]">
										<b-form-input class="w-100" v-model="formData.name" required
											  	:placeholder="$t('placeholder.shop-name')"
											  	:disabled="disable"
											  	:state="formData.errors.name && !formData.errors.name[0]"
											  	@change="onInputChange"
										/>
									</b-form-group>
								</b-col>
								<b-col md="16">
									<b-form-group class="font-weight-bold"
										:label-class="'required'"
									  	:label="$t('label.phone')"
									  	:state="!!formData.errors.phone"
									  	:invalid-feedback="formData.errors.phone && formData.errors.phone[0]">
										<b-form-input class="w-100" v-model="formData.phone" required
											  	:placeholder="$t('placeholder.phone')"
											  	:disabled="disable"
											  	:state="formData.errors.phone && !formData.errors.phone[0]"
											  	@change="onInputChange"
										/>
									</b-form-group>
								</b-col>
								<b-col md="16">
									<b-form-group class="font-weight-bold"
										:label="$t('label.email')"
										:state="!!formData.errors.email"
									  	:invalid-feedback="formData.errors.email && formData.errors.email[0]"
									>
										<b-form-input class="w-100" v-model="formData.email" required
											 	:placeholder="$t('placeholder.email')"
											 	:disabled="disable"
											 	:state="formData.errors.email && !formData.errors.email[0]"
											 	@change="onInputChange"
										/>
									</b-form-group>
								</b-col>
								<b-col md="16">
									<b-form-group class="font-weight-bold"
										:label-class="'required'"
									  	:label="$t('label.province')"
									  	:state="!!formData.errors.areaId"
									  	:invalid-feedback="formData.errors.areaId && formData.errors.areaId[0]">
										<c-select
											v-model="formData.areaProvince"
											:search-route="routes.areaSearch"
											:custom-request-data="areaFilterData"
											:taggable="true"
											ref="areaFilterEl"
											placeholder="Vui lòng chọn tỉnh, thành phố"
											:class="{'is-invalid': formData.errors.areaId}"
										>
										</c-select>
									</b-form-group>
								</b-col>
                                <b-col md="16">
                                    <b-form-group class="font-weight-bold"
                                                  :label="$t('label.district')"
                                                  >
                                        <c-select
                                            v-model="formData.areaDistrict"
                                            :search-route="routes.areaSearch"
                                            :custom-request-data="districtFilterData"
                                            :taggable="true"
                                            ref="areaFilterEl"
                                            placeholder="Vui lòng chọn quận, huyện"
                                        >
                                        </c-select>
                                    </b-form-group>
                                </b-col>
                                <b-col md="16">
                                    <b-form-group class="font-weight-bold"
                                                  :label="$t('label.ward')"
                                                 >
                                        <c-select
                                            v-model="formData.areaWard"
                                            :search-route="routes.areaSearch"
                                            :custom-request-data="wardFilterData"
                                            :taggable="true"
                                            ref="areaFilterEl"
                                            placeholder="Vui lòng chọn xã, phường"
                                        >
                                        </c-select>
                                    </b-form-group>
                                </b-col>
								<b-col md="16">
									<b-form-group
										class="font-weight-bold"
										:label-class="'required'"
										:label="$t('label.address')"
										:state="!!formData.errors.address"
									  	:invalid-feedback="formData.errors.address && formData.errors.address[0]"
									>
										<b-input-group :class="{'is-invalid': !!formData.errors.address}" >
											<b-form-input
												id="address"
												v-model="formData.address" required
												:placeholder="$t('placeholder.address')"
												:disabled="disable"
												:state="formData.errors.address && !formData.errors.address[0]"
												@change="onInputChange"
											/>
											<template v-slot:append>
												<a href="javascript:void(0)" class="btn btn-primary" @click="showMap">Map</a>
										    </template>
										</b-input-group>
									</b-form-group>
								</b-col>
                                <b-col md="16">
                                    <b-form-group class="font-weight-bold"
                                                  :label="$t('label.facebook_page')"
                                                  :state="!!formData.errors.fb"
                                                  :invalid-feedback="formData.errors.fb && formData.errors.fb[0]"
                                    >
                                        <b-form-input class="w-100" v-model="formData.fb" required
                                                      :placeholder="$t('placeholder.facebook_page')"
                                                      :disabled="disable"
                                                      :state="formData.errors.fb && !formData.errors.fb[0]"
                                                      @change="onInputChange"
                                        />
                                    </b-form-group>
                                </b-col>
                                <b-col md="16">
                                    <b-form-group class="font-weight-bold"
                                                  :label="$t('label.zalo_page')"
                                                  :state="!!formData.errors.zalo"
                                                  :invalid-feedback="formData.errors.zalo && formData.errors.zalo[0]"
                                    >
                                        <b-form-input class="w-100" v-model="formData.zalo" required
                                                      :placeholder="$t('placeholder.zalo_page')"
                                                      :disabled="disable"
                                                      :state="formData.errors.zalo && !formData.errors.zalo[0]"
                                                      @change="onInputChange"
                                        />
                                    </b-form-group>
                                </b-col>
                                <b-col md="16">
                                    <b-form-group class="font-weight-bold"
                                                  :label="$t('label.identity_code')"
                                                  :state="!!formData.errors.identityCode"
                                                  :invalid-feedback="formData.errors.zalo && formData.errors.identityCode[0]"
                                    >
                                        <b-form-input class="w-100" v-model="formData.identityCode" required
                                                      :placeholder="$t('placeholder.identity_code')"
                                                      :disabled="disable"
                                                      :state="formData.errors.identityCode && !formData.errors.identityCode[0]"
                                                      @change="onInputChange"
                                        />
                                    </b-form-group>
                                </b-col>
                                <b-col md="16">
                                    <b-form-group class="font-weight-bold"
                                                  :label="$t('label.identity_place')"
                                                  :state="!!formData.errors.identityPlace"
                                                  :invalid-feedback="formData.errors.identityPlace && formData.errors.identityPlace[0]"
                                    >
                                        <b-form-input class="w-100" v-model="formData.identityPlace" required
                                                      :placeholder="$t('placeholder.identity_place')"
                                                      :disabled="disable"
                                                      :state="formData.errors.identityPlace && !formData.errors.identityPlace[0]"
                                                      @change="onInputChange"
                                        />
                                    </b-form-group>
                                </b-col>
                                <b-col md="16">
                                    <b-form-group class="font-weight-bold"
                                                  :label="$t('label.identity_date')"
                                                  :state="!!formData.errors.identityDate"
                                                  :invalid-feedback="formData.errors.identityDate && formData.errors.identityDate[0]">
                                        <a-date-time-picker
                                        	id="date"
                                            :label="$t('label.identity_date')"
                                            v-model="formData.identityDate"
                                            :placeholder="$t('placeholder.identity_date')"
                                            role="normal"
                                            :errors="formData.errors.identityDate ? formData.errors.identityDate[0] : null"
                                            :disabled="disable"
                                            @dp-change="onInputChange"
                                        />
                                    </b-form-group>
								</b-col>
							</b-row>
						</b-form>
				   </b-col>
				</b-row>
			</div>
			<b-modal
	            size=lg
	            v-model="showModal"
	            busy:hide-header-close="processing"
	            hide-footer
	            hide-header
	            body-class="p-0"
	        >
	            <template v-slot:default>
	            	<input id="pac-input" v-model="formData.address" class="form-control" type="text" placeholder="Nhập địa chỉ"/>
			      	<div id="map-modal" style="width: 100%;height: 500px"></div>
	            </template>
	       </b-modal>
		</b-col>
	</b-row>
</template>

<script>
	import {mapState} from "vuex";
	import ECustomerType from "../../constants/customer-type";
	import EUserType from "../../constants/user-type";
	import shopInfoMessage from "../../locales/back/shop/shop-info";
	import EErrorCode from "../../constants/error-code";
	import EStatus from "../../constants/status";
	import EAreaType from "../../constants/area-type";

	export default {
		i18n: {
			messages: shopInfoMessage
		},
		inject: ['Util', 'DateUtil'],
		data() {
			return {
				ECustomerType,
				formData: this.$_formData(),
				disable: this.$route.params.action == 'edit' || this.$route.params.action == 'create' ? false : true,
				selectedFile: null,
                areaFilterData: null,
                districtFilterData: null,
                wardFilterData: null,
				info: null,
				status: {
					value: null,
					string: null
				},
				init: true,
				map: null,
				arrMarker: [],
				action: this.$route.params.action,
				showModal: false,
				EErrorCode,
				EStatus,
				EAreaType
			}
		},
		computed: {
			...mapState(['filterValueState', 'queryFilterState']),
            getBackRoute() {
                return 'shop.list';
            },
	        routes() {
                return {
                    areaSearch: `${this.$route.meta.baseUrl}/area`,
                }
            },
		},
		created() {
			$(document).on('initGoogleSuccess', () => {
	            this.initMap('map');
	        });
			this.$store.commit('updateFilterFormState', []);
			this.$store.commit('updateBreadcrumbsState', [
                {
                    text: this.$t('info'),
                    to: { name: 'shop.info' }
                },
            ]);
            this.$store.commit('updateQueryFilterState', {
                enable: false,
                placeholder: this.$t('filter.shop'),
            });
            this.areaFilterData = {
                pageSize: 15,
                type: EAreaType.PROVINCE
            }
            this.districtFilterData = {
                pageSize: 15,
                type: EAreaType.STATE
            }
            this.wardFilterData = {
                pageSize: 15,
                type: EAreaType.STATE
            }
		},
		mounted() {
			this.getInfoShop();
			if ($(document).data('initGoogleSuccess')) {
				this.initMap('map');
			}
		},
		watch: {
		    filterValueState(val) {
		    	this.$router.push({name: 'shop.list', query: val.value});
		    },
            "formData.areaProvince"() {
                console.log('watch------')
                this.districtFilterData = {
                    pageSize: 15,
                    type: this.formData.areaProvince ? EAreaType.DISTRICT : EAreaType.STATE,
                    parentAreaId: this.formData.areaProvince ? this.formData.areaProvince.id : null
                }
            },
            "formData.areaDistrict"() {
                this.wardFilterData = {
                    pageSize: 15,
                    type: this.formData.areaDistrict ? EAreaType.WARD : EAreaType.STATE,
                    parentAreaId: this.formData.areaDistrict ? this.formData.areaDistrict.id : null,
                }
            },
		},
		beforeRouteUpdate(to, from, next) {
            this.disable = to.params.action == 'edit' || to.params.action == 'create' ? false : true;
            if(this.disable) {
                this.formData = this.$_formData();
                this.getInfoShop();
            }
            next();
        },
		methods: {
			onInputChange() {
				if (this.disable == false) {
					this.Util.askUserWhenLeavePage();
				}
            },
			$_formData() {
				return {
					id: this.$route.params.shopId,
					name: null,
					phone: null,
					email: null,
					address: null,
					avatar: null,
					description: null,
					longitude: null,
					latitude: null,
                    areaProvince: null,
                    areaDistrict: null,
                    areaWard: null,
                    fb: null,
                    zalo: null,
                    identityCode: null,
                    identityPlace: null,
                    identityDate: null,
					errors: {
						name: null,
						phone: null,
						email: null,
						description: null,
						address: null,
						areaId: null,
                        fb: null,
                        zalo: null,
                        identityCode: null,
                    	identityPlace: null,
                    	identityDate: null,
					}
				}
			},
			initMap(mapId) {
	            let myLatlng = new google.maps.LatLng(this.formData.latitude, this.formData.longitude);
	            let mapOptions = {
	                zoom: 15,
	                center: myLatlng,
	                mapTypeId: 'roadmap',
	                zoomControl: false,
	                mapTypeControl: false,
	                scaleControl: false,
	                streetViewControl: false,
	                rotateControl: false,
	                fullscreenControl: false
	            };
	            this.map = new google.maps.Map(document.getElementById(mapId),
	                mapOptions);

	            let input = document.getElementById("address");
	            let searchBox = new google.maps.places.SearchBox(input);

	            searchBox.addListener("places_changed", () => {
	                let places = searchBox.getPlaces();
	                this.formData.latitude = places[0].geometry.location.lat();
	                this.formData.longitude = places[0].geometry.location.lng();
	                this.formData.address = document.getElementById("address").value;
	                if (places.length == 0) {
	                    return;
	                }
	                this.arrMarker.forEach(marker => {
	                    marker.setMap(null);
	                });
	                let bounds = new google.maps.LatLngBounds();
	                places.forEach(place => {
	                    if (!place.geometry) {
	                        console.log("Returned place contains no geometry");
	                        return;
	                    }
	                    // Create a marker for each place.
	                    let marker = new google.maps.Marker({
	                        map: this.map,
	                        title: place.name,
	                        position: place.geometry.location
	                    });
	                    this.arrMarker.push(marker);

	                    let infowindow = new google.maps.InfoWindow;
	                    infowindow.setContent(this.formData.address);
	                    infowindow.open(this.map, marker);

	                    marker.addListener('click', () => {
	                        infowindow.open(this.map, marker);
	                    });
	                });
	            });

	            this.map.addListener("bounds_changed", () => {
	                searchBox.setBounds(this.map.getBounds());
	            });

	            let marker = new google.maps.Marker({
	                position: myLatlng,
	                map: this.map,
	            });

	            this.arrMarker.push(marker);

	            let geocoder = new google.maps.Geocoder;
	            let infowindow = new google.maps.InfoWindow;
	            infowindow.setContent(this.formData.address);
	            infowindow.open(this.map, marker);

	            marker.addListener('click', () => {
	                infowindow.open(this.map, marker);
	            });

	            google.maps.event.addListener(this.map, "click", event => {
	                if (this.isEdit) {
	                    this.geocodeLatLng(geocoder, this.map, infowindow, event.latLng);
	                }
	                return false;
	            });

	            this.$nextTick(() => {
					if (this.showModal) {
						this.autoFilterLocation();
					}
				});
	        },
	        geocodeLatLng(geocoder, map, infowindow, latlng) {
	            this.arrMarker.forEach(marker => {
	                marker.setMap(null);
	            });
	            geocoder.geocode({'location': latlng}, (results, status)=> {
	            if (status === 'OK') {
	                if (results[0]) {
	                        map.setZoom(15);
	                        let marker = new google.maps.Marker({
	                            position: latlng,
	                            map: map
	                        });
	                        this.arrMarker.push(marker);
	                        this.formData.latitude = latlng.lat();
	                        this.formData.longitude = latlng.lng();
	                        infowindow.setContent(results[0].formatted_address);
	                        infowindow.open(map, marker);
	                        this.formData.address = results[0].formatted_address;
	                    } else {
	                        window.alert('No results found');
	                    }
	                } else {
	                    window.alert('Geocoder failed due to: ' + status);
	                }
	            });
	        },
	        autoFilterLocation() {
	        	let input = document.getElementById("pac-input");
	            let searchBox = new google.maps.places.SearchBox(input);
	            this.map.addListener("bounds_changed", () => {
	                searchBox.setBounds(this.map.getBounds());
	            });
	            searchBox.addListener("places_changed", () => {
	                let places = searchBox.getPlaces();
	                this.formData.latitude = places[0].geometry.location.lat();
	                this.formData.longitude = places[0].geometry.location.lng();
	                this.formData.address = document.getElementById("pac-input").value;
	                if (places.length == 0) {
	                    return;
	                }
	                this.arrMarker.forEach(marker => {
	                    marker.setMap(null);
	                });
	                let bounds = new google.maps.LatLngBounds();
	                places.forEach(place => {
	                    if (!place.geometry) {
	                        console.log("Returned place contains no geometry");
	                        return;
	                    }
	                    // Create a marker for each place.
	                    let marker = new google.maps.Marker({
	                        map: this.map,
	                        title: place.name,
	                        position: place.geometry.location
	                    });
	                    this.arrMarker.push(marker);

	                    let infowindow = new google.maps.InfoWindow;
	                    infowindow.setContent(this.formData.address);
	                    infowindow.open(this.map, marker);

	                    marker.addListener('click', () => {
	                        infowindow.open(this.map, marker);
	                    });

	                    if (place.geometry.viewport) {
	                        bounds.union(place.geometry.viewport);
	                    } else {
	                        bounds.extend(place.geometry.location);
	                    }
	                });
	                this.map.fitBounds(bounds);
	            });
	        },
	        showMap() {
	        	this.showModal = true;
	        	this.$nextTick(() => {
					this.initMap('map-modal');
				});
	        },
			onSelectAvatar(e) {
				$("#resource").text('');
	            if (window.File && window.FileList && window.FileReader) {
	            	let fileReader = new FileReader();
	            	let files = e.target.files;
	            	this.selectedFile = files[0];
	            	fileReader.onload = (function(e) {
	            		$("#resource").append("<img alt='avatar' class=\"avatar\" src=\"" + e.target.result + "\"  />");
                    });
	            	fileReader.readAsDataURL(this.selectedFile);
	            }
			},
			removeAvatar() {
				this.selectedFile = null;
				this.$refs.fileInputEl.value = '';
			},
			getInfoShop() {
				this.action = this.$route.params.action;
				this.formData = this.$_formData();
				this.selectedFile = null;
				this.Util.loadingScreen.show();
                this.Util.get({
                    url: `${this.$route.meta.baseUrl}/${this.formData.id}/info`,
                }).done(response => {
                        if (response.error == EErrorCode.ERROR) {
                            this.Util.showMsg2(response);
                            this.$router.push({name: 'shop.list'});
                        }
                        this.formData.name = response.shop.name;
						this.formData.phone = response.shop.phone;
						this.formData.email = response.shop.email;
						this.formData.address = response.shop.address;
						this.formData.avatar = response.shop.avatar;
						this.formData.longitude = response.shop.longitude;
						this.formData.latitude = response.shop.latitude;
						this.formData.description = response.shop.description;
						this.formData.areaProvince = response.shop.areaProvince;
						this.formData.zalo = response.shop.zaloPage;
						this.formData.fb = response.shop.facebookPage;
						this.formData.identityPlace = response.shop.identityPlace;
						this.formData.identityDate = response.shop.identityDate;
						this.formData.identityCode = response.shop.identityCode;
                        let areaDistrictFromServer = {...response.shop.areaDistrict};
                        let areaWardFromServer = {...response.shop.areaWard};
                        this.$nextTick(() => {
                            this.formData.areaDistrict = areaDistrictFromServer;
                            this.$nextTick(() => {
                                this.formData.areaWard = areaWardFromServer;
                            })
                        });
                        console.log(this.formData)
                    }).fail((error) =>{
                    	if (error.status == "404") {
                    		this.$router.push({name: '404'});
                    	}
                    }).always(() => {
                        if (this.$route.params.action === 'edit') {
                        	this.disable = false;
                        }
                        this.Util.loadingScreen.hide();
                    });
			},
			async deleteShop() {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirmDelete(this.$t('object_name'), resolve);
                });
                if (!confirm) {
                    return;
                }

                this.processing = true;
                this.Util.post({
                    url: `/api/back/shop/delete`,
                    data: {
                        id: this.formData.id,
                    },
                }).done(async (res) => {
                    if (res.error) {
                        this.Util.showMsg('error', null, res.msg);
                        return;
                    }
                    this.Util.showMsg('success', null, res.msg);
                    if (this.customerType == ECustomerType.BUYER) {
                    	this.$router.push({name: 'buyer.list'});
                    }else if(this.customerType == ECustomerType.SELLER) {
                    	this.$router.push({name: 'seller.list'});
                    }else if(this.customerType == ECustomerType.ADVERTISER) {
                    	this.$router.push({name: 'advertiser.list'});
                    }else {
                    	this.$router.push({name: 'employee.list'});
                    }
                }).always(() => {
                    this.processing = false;
                });
            },
            async updateInfo() {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirm(this.$route.params.action === 'create' ? this.$t('confirm.create') : this.$t('confirm.edit'), resolve);
                });
                if (!confirm) {
                    return;
                }

				this.Util.loadingScreen.show();
                this.disable = true;
                let formData = new FormData();
                Object.keys(this.formData).forEach((key) => {
                    switch (key) {
                        case 'image':
                            if (this.selectedFile != null) {
                            	formData.append(key, this.selectedFile);
                            }
                            break;
                        case 'dob':
                        	let date = new Date(this.formData[key]);
                            formData.append(key, this.DateUtil.getDateString(date));
                            break;
                        case 'role':
                        	let selectedRoles = this.roles.filter(item => item.enable).map(item => item.id);
                        	if (selectedRoles.length == 0) {
                        		selectedRoles = '';
                        	}else {
                        		selectedRoles.forEach((item, index) => {
                                    formData.append('role[]', item);
                                });
                        	}
                            break;
                        case 'areaProvince':
                        case 'areaDistrict':
                        case 'areaWard':
                            if (this.formData.[key] && this.formData.[key].id) {
                                formData.append('areaId', this.formData.[key].id);
                            }
                            break;
                        default:
                            if (this.formData[key]) {
                                formData.append(key, this.formData[key]);
                                break;
                            }
                    }
                });
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/save`,
                    data: formData,
                    errorModel: this.formData.errors,
                    processData: false,
                    contentType: false,
                }).done(response => {
                        if (response.error == EErrorCode.ERROR) {
                            this.formData.errors = response.msg;
                            return false;
                        }
                        this.Util.showMsg2(response);
                        if (!this.formData.id) {
                        	this.$router.push({name: 'shop.list'});
                        	return;
                        }
                        this.getInfoShop();
                    }).always(() => {
                            this.disable = false;
                            this.Util.loadingScreen.hide();
                        });
			},
            goBack() {
                this.$router.push({name:'shop'})
            },
		}
	}
</script>

<style scoped>

</style>
