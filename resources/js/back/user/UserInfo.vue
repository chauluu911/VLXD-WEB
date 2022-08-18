<template>
   	<b-row>
		<b-col cols="48">
			<div class="content__inner">
				<div id="map" class="d-none" style="width: 100%;height: 500px"></div>
				<b-row class="pb-2" style="border-bottom: 3px solid gray">
					<b-col cols="24">
						<span class="span__title-detail mr-4">{{action == 'create' ? $t('common.button.add2', {'objectName': $t('employee')}).toUpperCase() : $t('tab.title.user-info').toUpperCase()}}</span>
						<div class="btn text-white cursor-default mb-2" v-if="!!status.string" :class="status.value == EStatus.DELETED ? 'bg-danger' : 'bg-primary'">
                            <span>
                                {{ status.string }}
                            </span>
                        </div>
					</b-col>
					<b-col cols="24" v-if="action === 'create'">
						<b-button variant="primary" class="float-right ml-3" @click="updateInfo">
							<i class="far fa-save"></i>
							{{$t('tab.info.edit.save')}}
						</b-button>
						<b-link
                            @click="goBack"
							class="float-right btn">
							<span>{{$t('back')}}</span>
						</b-link>
					</b-col>
					<b-col cols="24" v-else>
						<b-button variant="primary" class="float-right ml-3" @click="updateInfo">
							<i class="far fa-save"></i>
							{{$t('tab.info.edit.save')}}
						</b-button>
						<b-link
                            @click="goBack"
							class="float-right btn">
							<span>{{$t('back')}}</span>
						</b-link>
					</b-col>
				</b-row>
				<b-row>
				   	<b-col cols="48" class="pt-3">
						<div style="position: relative;">
							<label for="avatar" style="cursor: pointer;">
								<b-avatar variant="primary" v-if="selectedFile == null" :src="formData.image" size="100"/>
								<span v-else id="resource"></span>
							</label>
							<input v-if="!disable"
                                   type="file"
                                   accept="image/*"
                                   ref="fileInputEl"
                                   id="avatar" hidden=""
                                   @change="onSelectAvatar"
                            >
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
								<b-col md="16" v-if="this.userType == EUserType.NORMAL_USER">
									<b-form-group class="font-weight-bold"
										:label-class="'required'"
										:label="$t('tab.info.label.code')"
									>
										<b-form-input class="w-100" v-model="formData.code" required
											  	:placeholder="$t('tab.info.label.code')"
											  	:disabled="true"
											  	@change="onInputChange"
										/>
									</b-form-group>
								</b-col>
								<b-col md="16" v-if="this.userType == EUserType.NORMAL_USER">
									<b-form-group class="font-weight-bold"
										:label-class="'required'"
										:label="$t('tab.info.label.affiliate-code')"
										:state="!!formData.errors.affiliateCode"
									  	:invalid-feedback="formData.errors.affiliateCode && formData.errors.affiliateCode[0]"
									>
										<b-form-input
											class="w-100"
											v-model="formData.affiliateCode"
											required
											maxlength="7"
											:state="formData.errors.affiliateCode && !formData.errors.affiliateCode[0]"
											:placeholder="$t('tab.info.label.affiliate-code')"
										  	@change="onInputChange"
                                            @update="onAffiliateCodeUpdate"
										/>
									</b-form-group>
								</b-col>
								<b-col md="16">
									<b-form-group class="font-weight-bold"
										:label-class="'required'"
									  	:label="$t('tab.info.label.name')"
									  	:state="!!formData.errors.name"
									  	:invalid-feedback="formData.errors.name && formData.errors.name[0]">
										<b-form-input class="w-100" v-model="formData.name" required
											  	:placeholder="$t('tab.info.placeholder.name')"
											  	:disabled="disable"
											  	:state="formData.errors.name && !formData.errors.name[0]"
											  	@change="onInputChange"
										/>
									</b-form-group>
								</b-col>
								<b-col md="16">
									<b-form-group class="font-weight-bold"
										:label-class="'required'"
									  	:label="$t('tab.info.label.phone')"
									  	:state="!!formData.errors.phone"
									  	:invalid-feedback="formData.errors.phone && formData.errors.phone[0]">
										<b-form-input class="w-100" v-model="formData.phone" required
											  	:placeholder="$t('tab.info.placeholder.phone')"
											  	:disabled="disable" lazy-formatter :formatter="removeUnDigitCharacters"
											  	:state="formData.errors.phone && !formData.errors.phone[0]"
											  	@change="onInputChange"
										/>
									</b-form-group>
								</b-col>
								<b-col md="16" v-if="this.userType == EUserType.NORMAL_USER">
									<b-form-group class="font-weight-bold"
										:label="$t('tab.info.label.email')"
										:state="!!formData.errors.email"
										:invalid-feedback="formData.errors.email && formData.errors.email[0]"
									>
										<b-form-input class="w-100"
											v-model="formData.email"
											 	:placeholder="$t('tab.info.placeholder.email')"
											 	:disabled="disable"
												:state="formData.errors.email && !formData.errors.email[0]"
											 	@change="onInputChange"
										/>
									</b-form-group>
								</b-col>
								<b-col md="16" v-else>
									<b-form-group class="font-weight-bold"
										:label-class="'required'"
										:label="$t('tab.info.label.email')"
										:state="!!formData.errors.email"
									  	:invalid-feedback="formData.errors.email && formData.errors.email[0]"
									>
										<b-form-input class="w-100" v-model="formData.email" required
											 	:placeholder="$t('tab.info.placeholder.email')"
											 	:disabled="disable"
											 	:state="formData.errors.email && !formData.errors.email[0]"
											 	@change="onInputChange"
										/>
									</b-form-group>
								</b-col>
								<b-col md="16">
									<b-form-group class="font-weight-bold"
									  	:label="$t('tab.info.label.gender')"
									  	:label-class="'required'"
									  	:state="!!formData.errors.gender"
									  	:invalid-feedback="formData.errors.gender && formData.errors.gender[0]">
										<b-form-select class="w-100" type="date" v-model="formData.gender"
										 	required
											:options="genderList"
										 	:disabled="disable"
										 	:state="formData.errors.gender && !formData.errors.gender[0]"
										 	@change="onInputChange"
										>
										</b-form-select>
									</b-form-group>
								</b-col>
                                <b-col md="16">
                                    <b-form-group class="font-weight-bold"
                                                  :label="$t('tab.info.label.dob')"
                                                  :label-class="{required: this.userType == EUserType.INTERNAL_USER}"
                                                  :state="!!formData.errors.dob"
                                                  :invalid-feedback="formData.errors.dob && formData.errors.dob[0]">
                                        <a-date-time-picker
                                            id="notification-date-input"
                                            :label="$t('tab.info.label.dob')"
                                            v-model="formData.dob"
                                            :placeholder="$t('tab.info.placeholder.dob')"
                                            role="normal"
                                            :errors="formData.errors.dob ? formData.errors.dob[0] : null"
                                            :disabled="disable"
                                            @dp-change="onInputChange"
                                        />
                                    </b-form-group>
								</b-col>
								<b-col md="16">
									<b-form-group class="font-weight-bold"
										:label-class="'required'"
										:label="$t('tab.info.label.address')"
										:state="!!formData.errors.address"
									  	:invalid-feedback="formData.errors.address && formData.errors.address[0]"
									>
										<b-input-group :class="{'is-invalid': !!formData.errors.address}" >
											<b-form-input
												id="address"
												v-model="formData.address" required
												:placeholder="$t('tab.info.placeholder.address')"
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
							</b-row>
							<b-row v-if="userType == EUserType.INTERNAL_USER">
								<b-col md="48">
									<b-form-group class="font-weight-bold"
										:label-class="'required'"
										:label="$t('tab.info.label.permission')"
									>
		                                <b-row>
		                                	<b-col md="12" :key="n" v-for="n in Math.ceil(roles.length)">
		                                		<span v-for="item in roles.slice((n - 1), n)" :key="item.id">
			                                        <label>
			                                            <a-checkbox v-model="item.enable" :checked-state="item.enable" :disabled="disable"></a-checkbox>
			                                            {{ item.name }}
			                                        </label>
			                                    </span>
		                                	</b-col>
		                                </b-row>
	                                    <b-form-invalid-feedback class="d-block" v-if="formData.errors.role && formData.errors.role[0]">{{$t('tab.info.message.role')}}</b-form-invalid-feedback>
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
	import userDetailMessage from "../../locales/back/user/user-detail";
	import EErrorCode from "../../constants/error-code";
	import EStatus from "../../constants/status";
	import EGender from "../../constants/gender";

	export default {
		name: "UserDetail",
		i18n: {
			messages: userDetailMessage
		},
		props: {
            customerType: {
                type: Number,
                default: null,
            },
            userType: {
                type: Number,
                required: true,
            },
        },
		inject: ['Util', 'DateUtil'],
		data() {
			return {
				ECustomerType,
				EUserType,
				formData: this.$_formData(),
				disable: this.$route.params.action == 'edit' || this.$route.params.action == 'create' ? false : true,
				selectedFile: null,
				info: null,
				status: {
					value: null,
					string: null
				},
				action: this.$route.params.action,
				showModal: false,
				EErrorCode,
				arrMarker: [],
				roles: [],
				map: null,
				EStatus,
				EGender
			}
		},
		computed: {
			...mapState(['filterValueState', 'queryFilterState']),
			routerName() {
                if (this.userType == EUserType.INTERNAL_USER) {
                    return 'employee.info';
                }else {
                    return 'customer.info';
                }
            },
            getBackRoute() {
                if (this.userType == EUserType.INTERNAL_USER) {
                    return 'employee.list';
                }else {
                    return 'customer.list';
                }
            },
            genderList() {
            	return [
                    {
                        value: EGender.MALE,
                        text: EGender.valueToName(EGender.MALE),
                    },
                    {
                        value: EGender.FEMALE,
                        text: EGender.valueToName(EGender.FEMALE),
                    },
                ]
            }
		},
		created() {
			$(document).on('initGoogleSuccess', () => {
	            this.initMap('map');
	        });
			this.$store.commit('updateBreadcrumbsState', [
                ...(
                    this.userType == EUserType.INTERNAL_USER ? [
                    	{
                        	text: this.$t('tab.title.employee-list'),
                        	to: { name: 'employee.list' }
                    	},
                    ] : []
                ),
                ...(
                    this.userType == EUserType.NORMAL_USER ? [
	                    {
	                        text: this.$t('tab.title.user-info'),
	                        to: { name: 'customer.list' }
	                    },
                    ] : []
                ),
            ]);
            this.$store.commit('updateFilterFormState', [
            	{
                    label: this.$t('constant.status.status'),
                    type: 'select',
                    name: 'status',
                    options: [
                        {
                            name: this.$t('constant.status.active'),
                            value: EStatus.ACTIVE,
                        },
                        {
                            name: this.$t('constant.status.deleted'),
                            value: EStatus.DELETED,
                        },
                    ]
                },
                {
                    type: 'date',
                    name: 'createdAtFrom',
                    placeholder: this.$t('placeholder.filter.created_at_from'),
                    dropleft: true,
                },
                {
                    type: 'date',
                    name: 'createdAtTo',
                    placeholder: this.$t('placeholder.filter.created_at_to'),
                    dropleft: true,
                },
            ]);
            this.$store.commit('updateQueryFilterState', {
                enable: true,
                placeholder: this.$t('filter.user'),
            });
			//this.getCountryList();
			if (this.userType == EUserType.INTERNAL_USER) {
				this.getRolePermissionList();
			}
		},
		mounted() {
			if (this.$route.params.userId) {
				this.getInfoUser();
			}
			if ($(document).data('initGoogleSuccess')) {
				this.initMap('map');
			}
		},
		watch: {
		    filterValueState(val) {
		    	if (this.userType == EUserType.INTERNAL_USER) {
                	this.$router.push({name: 'employee.list', query: val.value});
                }else {
                	this.$router.push({name: 'customer.list', query: val.value});
                }
		    },
		},
		beforeRouteUpdate(to, from, next) {
            this.disable = to.params.action == 'edit' || to.params.action == 'create' ? false : true;
            if(this.disable) {
                this.formData = this.$_formData();
            }
            next();
        },
		methods: {
            removeUnDigitCharacters() {
                let regex = new RegExp(/\D/, 'g');
                return this.formData.phone.replaceAll(regex, '');
            },
			onInputChange() {
				if (this.disable == false) {
					this.Util.askUserWhenLeavePage();
				}
            },
			$_formData() {
				return {
					id: this.$route.params.userId,
					customerType: this.customerType,
					userType: this.userType,
					name: null,
                    code: null,
					phone: null,
					email: null,
					dob: null,
					gender: EGender.MALE,
					address: null,
					image: null,
					role: null,
					latitude: null,
					longitude: null,
					affiliateCode: null,
					errors: {
						name: null,
						phone: null,
						email: null,
						dob: null,
						country: null,
						address: null,
						role: [],
						gender: null,
						affiliateCode: null,
					}
				}
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
			getRolePermissionList() {
				this.Util.loadingScreen.show();
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/permission`,
                }).done(response => {
                    this.roles = response.roleList;
                }).always(() => {
                    this.Util.loadingScreen.hide();
                });
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
			getInfoUser() {
				this.action = this.$route.params.action;
				this.formData = this.$_formData();
				this.selectedFile = null;
				this.Util.loadingScreen.show();
                this.Util.get({
                    url: `${this.$route.meta.baseUrl}/${this.formData.id}/info`,
                    data: {
                    	'type': this.userType,
                    },
                }).done(response => {
                        if (response.error == EErrorCode.ERROR) {
                            this.Util.showMsg2(response);
                            if (this.customerType == ECustomerType.BUYER) {
		                    	this.$router.push({name: 'buyer.list'});
		                    }else if(this.customerType == ECustomerType.SELLER) {
		                    	this.$router.push({name: 'seller.list'});
		                    }else if(this.customerType == ECustomerType.ADVERTISER) {
		                    	this.$router.push({name: 'advertiser.list'});
		                    }else {
		                    	this.$router.push({name: 'employee.list'});
		                    }
                        }
                        this.formData.name = response.user.name;
						this.formData.phone = response.user.phone;
						this.formData.email = response.user.email;
                        this.formData.dob = response.user.dob ?
                            this.DateUtil.getDateString(new Date(response.user.dob)) : null;
                        this.formData.address = response.user.address;
                        this.formData.image = response.user.image;
                        this.formData.gender = response.user.gender;
                        this.formData.longitude = response.user.longitude;
                        this.formData.latitude = response.user.latitude;
                        this.formData.code = response.user.code;
                        this.formData.affiliateCode = response.user.affiliateCode;
                        this.status.value = response.user.status;
                        this.status.string = response.user.statusStr;
                        if (this.userType == EUserType.INTERNAL_USER) {
	                        for (let role of response.user.roleOfUser) {
		                        let roleOfUser = this.roles.find(item => item.id === role.permission_group_id);
                        		roleOfUser.enable = true;
		                    }
                        }
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
            onAffiliateCodeUpdate(input) {
			    this.formData.affiliateCode = input.toUpperCase();
            },
			async deleteUser() {
                let confirm = await new Promise((resolve) => {
                    this.Util.confirmDelete(this.$t('object_name'), resolve);
                });
                if (!confirm) {
                    return;
                }

                this.processing = true;
                this.Util.post({
                    url: `/api/back/seller/delete`,
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
                    this.Util.confirm(this.$route.params.action === 'create' ? this.$t('tab.info.create.confirm') : this.$t('tab.info.edit.confirm'), resolve);
                });
                if (!confirm) {
                    return;
                }

				this.Util.loadingScreen.show();
                this.disable = true;
                let formData = new FormData();
                Object.keys(this.formData).forEach((key) => {
                    switch (key) {
                        case 'country':
                        	if (this.formData[key].value == null) {
                        		this.formData[key].value = '';
                        	}
                        	formData.append(key, this.formData[key].value);
                            break;
                        case 'image':
                            if (this.selectedFile != null) {
                            	formData.append(key, this.selectedFile);
                            }
							break;
                        case 'dob':
                        	if (this.formData[key]) {
                            	formData.append(key, this.formData[key]);
                        	} else {
                                formData.append(key, '');
                            }
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
                        default:
                            if (this.formData[key] == null) {
                            	this.formData[key] = ''
                            }
                            formData.append(key, this.formData[key]);
                            break;
					}
                });
                this.Util.post({
                    url: `${this.$route.meta.baseUrl}/save`,
                    data: formData,
                    errorModel: this.formData.errors,
                    processData: false,
                    contentType: false,
                }).done(response => {
                        if (response.error !== EErrorCode.NO_ERROR) {
                            this.formData.errors = response.msg;
                            return false;
                        }

                        if (this.userType == EUserType.INTERNAL_USER) {
                        	this.$router.push({name: 'employee.list'});
                        	return;
                        } else {
                        	this.$router.push({name: 'customer.list'});
                        }
                    }).always(() => {
                            this.disable = false;
                            this.Util.loadingScreen.hide();
                        });
			},
            goBack() {
                this.$router.go(-1)
            }
		}
	}
</script>

<style scoped>

</style>
