'use strict';

import EErrorCode from "../../constants/error-code";
import EAreaType from "../../constants/area-type";
import ShopToolBar from '../components/ShopToolBar.vue';
import EResourceType from "../../constants/resource-type";
import EStatus from "../../constants/status";

let getNotificationTimeout = null;
const app = new Vue({
	el:'#create-shop',
    components: {
        ShopToolBar
    },
    data() {
        return {
            seeMore: false,
            map: null,
            shopId: $('#shopData').data('id'),
            areaFilterData: null,
            districtFilterData: null,
            wardFilterData: null,
            formData: this.$_formData(),
            arrMarker: [],
            shop: {},
            initialize: true,
            EAreaType,
            EResourceType,
            selectedFile: null,
            EStatus,
            avatarVideo: null,
            avatarImage: null,
            permission: null,
        }
    },
    computed: {
        route() {
            return {
                save: '/shop/save'
            }
        }
    },
    watch: {
	    "formData.value.areaProvince"() {
            this.districtFilterData = {
                pageSize: 15,
                type: this.formData.value.areaProvince ? EAreaType.DISTRICT : -1,
                parentAreaId: this.formData.value.areaProvince ? this.formData.value.areaProvince.id : null
            }
        },
        "formData.value.areaDistrict"() {
            this.wardFilterData = {
                pageSize: 15,
                type: this.formData.value.areaDistrict ? EAreaType.WARD : -1,
                parentAreaId: this.formData.value.areaDistrict ? this.formData.value.areaDistrict.id : null,
            }
        },
    },
    created() {
        try {
            $(document).on('initGoogleMapSuccess', () => {
                this.initMap();
            });
        } catch(e) {
            window.location.reload();
        }
        if (this.shopId) {
            this.getInfoShop();
        }
        this.areaFilterData = {
            pageSize: 15,
            type: EAreaType.PROVINCE
        }
        this.districtFilterData = {
            pageSize: 15,
            type: -1
        }
        this.wardFilterData = {
            pageSize: 15,
            type: -1
        }
    },
    methods: {
        removeUnDigitCharacters() {
            let regex = new RegExp(/\D/, 'g');
            this.formData.value.phone = this.formData.value.phone.replaceAll(regex, '');
        },
        getInfoShop() {
            // this.initialize = false;
            common.loadingScreen.show('body');
            common.get({
                url: `/shop/${this.shopId}/info`,
                data: {
                    id: this.shopId,
                }
            }).done( response => {
                if (response.error == EErrorCode.ERROR) {
                    return;
                }
                this.formData.value = response.shop;
                let areaDistrictFromServer = {...response.shop.areaDistrict};
                let areaWardFromServer = {...response.shop.areaWard};
                this.$nextTick(() => {
                    this.formData.value.areaDistrict = areaDistrictFromServer;
                    this.$nextTick(() => {
                        this.formData.value.areaWard = areaWardFromServer;
                    })
                });
                this.initMap();
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        $_formData() {
            return {
                value: {
                    name: null,
                    phone: null,
                    address: null,
                    email: null,
                    dob: null,
                    gender: null,
                    latitude: 10.777788619129023,
                    longitude: 106.69668674468994,
                    areaProvince: null,
                    areaDistrict: null,
                    areaWard: null,
                    avatar: null,
                    avatarType: EResourceType.IMAGE,
                    fb: null,
                    zalo: null,
                    identityCode: null,
                    identityDate: null,
                    identityPlace: null,
                },
                errors: this.$_errors(),
            };
        },
        $_errors() {
            return {
                name: null,
                phone: null,
                address: null,
                email: null,
                dob: null,
                areaId: null,
                fb: null,
                zalo: null,
                avatar: null,
            };
        },
        initMap() {
            let myLatlng = new google.maps.LatLng(this.formData.value.latitude, this.formData.value.longitude);
            let mapOptions = {
                zoom: 15,
                center: myLatlng,
                mapTypeId: 'roadmap',
            };
            this.map = new google.maps.Map(document.getElementById('map'),
                mapOptions);

            let input = document.getElementById("address");
            let searchBox = new google.maps.places.SearchBox(input);

            //update latlng marker khi user nhập text vào nhưng không chọn option trên search box
            input.onblur = () => {
                let autocompleteService = new google.maps.places.AutocompleteService();
                autocompleteService.getPlacePredictions({
                    input: input.value,
                    bounds: this.map.getBounds()
                }, (predictions, status) => {
                    if (status == google.maps.places.PlacesServiceStatus.OK && !!predictions[0].place_id) {
                        let request = {
                            placeId: predictions[0].place_id,
                            fields: ['name', 'rating', 'geometry']
                        };
                        let placeService = new google.maps.places.PlacesService(this.map);
                        placeService.getDetails(request, (place, status) => {
                            if (status == google.maps.places.PlacesServiceStatus.OK) {
                                this.formData.value.latitude = place.geometry.location.lat();
                                this.formData.value.longitude = place.geometry.location.lng();
                                let myLatlng = new google.maps.LatLng(place.geometry.location.lat(),
                                    place.geometry.location.lng());
                                let mapOptions = {
                                    zoom: 15,
                                    center: myLatlng,
                                    mapTypeId: 'roadmap',
                                };
                                this.map = new google.maps.Map(document.getElementById('map'),
                                    mapOptions);
                                this.arrMarker.forEach(marker => {
                                    marker.setMap(null);
                                });
                                let marker = new google.maps.Marker({
                                    map: this.map,
                                    title: place.name,
                                    position: place.geometry.location
                                });
                                this.arrMarker.push(marker);

                                if (this.formData.value.address) {
                                    let infowindow = new google.maps.InfoWindow;
                                    infowindow.setContent(this.formData.value.address);
                                    infowindow.open(this.map, marker);
                                }

                                marker.addListener('click', () => {
                                    if (this.formData.value.address) {
                                        infowindow.open(this.map, marker);
                                    }
                                });
                            }
                        });
                    } else {
                        this.formData.value.latitude = null;
                        this.formData.value.longitude = null;
                    }
                });
            }

            searchBox.addListener("places_changed", () => {
                let places = searchBox.getPlaces();
                this.formData.value.latitude = places[0].geometry.location.lat();
                this.formData.value.longitude = places[0].geometry.location.lng();
                this.formData.value.address = document.getElementById("address").value;
                if (places.length == 0) {
                    return;
                }
                this.arrMarker.forEach(marker => {
                    marker.setMap(null);
                });
                let bounds = new google.maps.LatLngBounds();
                places.forEach(place => {
                    if (!place.geometry) {
                        return;
                    }
                    // Create a marker for each place.
                    let marker = new google.maps.Marker({
                        map: this.map,
                        title: place.name,
                        position: place.geometry.location
                    });
                    this.arrMarker.push(marker);

                    if (this.formData.value.address) {
                        let infowindow = new google.maps.InfoWindow;
                        infowindow.setContent(this.formData.value.address);
                        infowindow.open(this.map, marker);
                    }

                    marker.addListener('click', () => {
                        if (this.formData.value.address) {
                            infowindow.open(this.map, marker);
                        }
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

            if (this.formData.value.address) {
                infowindow.setContent(this.formData.value.address);
                infowindow.open(this.map, marker);
                marker.addListener('click', () => {
                    infowindow.open(this.map, marker);
                });
            }

            google.maps.event.addListener(this.map, "click", event => {
                if (this.isEdit) {
                    this.geocodeLatLng(geocoder, this.map, infowindow, event.latLng);
                }
                return false;
            });
        },
        geocodeLatLng(geocoder, map, infowindow, latlng) {
            for (let i = 0; i < this.arrMarker.length; i++) {
                this.arrMarker[i].setMap(null);
            }
            geocoder.geocode({'location': latlng}, (results, status)=> {
            if (status === 'OK') {
                if (results[0]) {
                        map.setZoom(15);
                        let marker = new google.maps.Marker({
                            position: latlng,
                            map: map
                        });
                        this.arrMarker.push(marker);
                        this.formData.value.latitude = latlng.lat();
                        this.formData.value.longitude = latlng.lng();
                        infowindow.setContent(results[0].formatted_address);
                        infowindow.open(map, marker);
                        this.formData.value.address = results[0].formatted_address;
                    } else {
                        window.alert('No results found');
                    }
                } else {
                    window.alert('Geocoder failed due to: ' + status);
                }
            });
        },
        onSelectAvatar(e) {

            if (window.File && window.FileList && window.FileReader) {
                let fileReader = new FileReader();
                let files = e.target.files;
                if(files.length === 0) {
                    return false;
                }
                if (files[0].type != 'image/jpeg' && files[0].type != 'image/png' &&
                    files[0].type != 'image/jpg' && files[0].type != 'video/mp4') {
                    bootbox.alert('Hình không hợp lệ');
                    return false;
                }
                $("#resource").text('');
                this.selectedFile = files[0];
                if (files[0].type != 'video/mp4') {
                    fileReader.onload = ((e) => {
                        this.avatarVideo = null;
                        this.formData.value.avatarType = EResourceType.IMAGE;
                        this.avatarImage = e.target.result;
                        //$("#resource").append("<img class=\"rounded-pill\" width=\"88px\" height=\"88px\" alt=\"avatar\" src=\"" + e.target.result + "\" />");
                    });
                } else {
                    this.formData.value.avatarType = EResourceType.VIDEO;
                    let durationOfVideo;
                    let video = document.createElement('video');
                    video.preload = 'metadata';
                    video.onloadedmetadata = () => {
                        window.URL.revokeObjectURL(video.src);
                        durationOfVideo = video.duration;
                        if (durationOfVideo > 60) {
                            this.selectedFile = null;
                            bootbox.alert('Video vượt quá 60 giây');
                        } else {
                            //$("#resource").append("<video autoplay muted loop id=\"video-shop\" class=\"\" width=\"100%\" alt=\"avatar\" src=\"" + video.src + "\" style=\"max-width: 300px\" />");
                            this.avatarImage = null;
                            this.avatarVideo = video.src;
                        }
                    }
                    video.src = URL.createObjectURL(this.selectedFile);
                }
                fileReader.readAsDataURL(this.selectedFile);
            }
            // if (this.shopId) {
            //     this.saveAvatarShop();
            // }
        },
        saveAvatarShop() {
            if (!this.selectedFile) {
                this.selectedFile = '';
                return;
            }
            let formData = new FormData();
            formData.append('id', this.shopId);
            formData.append('avatar', this.selectedFile);
            formData.append('avatarType', this.formData.value.avatarType);

            common.loadingScreen.show('body');

            common.post({
                url: this.route.save,
                data: formData,
                errorModel: this.formData.errors,
            }).done((res) => {
                if (res.error !== EErrorCode.NO_ERROR) {
                    common.showMsg('error', null, res.msg);
                    return;
                }
                window.location.reload();
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        saveShop() {
            this.formData.errors = this.$_errors();
            let formData = new FormData();
            if (this.shopId) {
                formData.append('id', this.shopId);
            }
            Object.keys(this.formData.value).forEach((key) => {
                switch (key) {
                    case 'avatar':
                        if (this.selectedFile) {
                            formData.append('avatar', this.selectedFile);
                        } else if (this.formData.value[key]) {
                            formData.append('avatar', this.formData.value[key]);
                        }
                        break;
                    case 'areaProvince':
                    case 'areaDistrict':
                    case 'areaWard':
                        if (this.formData.value[key] && this.formData.value[key].id) {
                            formData.append('areaId', this.formData.value[key].id);
                        }
                        break;
                    default:
                        if (this.formData.value[key]) {
                            formData.append(key, this.formData.value[key]);
                        }
                        break;
                }
            });

            common.loadingScreen.show('body');

            common.post({
                url: this.route.save,
                data: formData,
                errorModel: this.formData.errors,
            }).done((res) => {
                if (res.error !== EErrorCode.NO_ERROR) {
                    this.formData.errors = res.msg
                    return;
                }
                if (this.shopId) {
                    window.location.assign(res.redirectToShop);
                } else {
                    $('#exampleModalCenter').modal('show');
                }

            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        showMap() {
            $('#map-modal').modal('show');
            //let map = this.map;
            let input = document.getElementById("pac-input");
            let searchBox = new google.maps.places.SearchBox(input);
            this.map.addListener("bounds_changed", () => {
                searchBox.setBounds(this.map.getBounds());
            });
            searchBox.addListener("places_changed", () => {
                let places = searchBox.getPlaces();
                this.formData.value.latitude = places[0].geometry.location.lat();
                this.formData.value.longitude = places[0].geometry.location.lng();
                this.formData.value.address = document.getElementById("pac-input").value;
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
                    infowindow.setContent(this.formData.value.address);
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
        getPermission(permission) {
            this.permission = permission;
        }
    }
});
