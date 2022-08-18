'use strict';

import EErrorCode from "../../constants/error-code";
import FCameraIcon from 'vue-feather-icons/icons/CameraIcon';
import EGender from "../../constants/gender";
import Profile from "../components/Profile"
import DateUtil from "../../lib/date-utils";

let getNotificationTimeout = null;
const app = new Vue({
	el:'#personal-info',
    components: {
        FCameraIcon,
        EGender,
        Profile
    },
    data() {
        return {
            seeMore: false,
            map: null,
            infoUser: $('#user-info').data('user'),
            isEdit: $('#user-info').data('is-edit'),
            urlPath: $('#user-info').data('url-path'),
            formData: this.$_formData(),
            arrMarker: [],

            EGender,
            selectedFile: null,
        }
    },
    created() {
        $(document).on('initGoogleMapSuccess', () => {
            this.initMap();
        });
        this.initFormData();
    },

    methods: {
        $_formData() {
            return {
                value: {
                    name: null,
                    phone: null,
                    address: null,
                    email: null,
                    dob: null,
                    gender: null,
                    latitude: null,
                    longitude: null,
                    avatar: null,
                    affiliateCode: null,
                },
                errors: {
                    name: null,
                    phone: null,
                    address: null,
                    email: null,
                    dob: null,
                    gender: null,
                }
            };
        },
        initFormData() {
            this.formData.value.affiliateCode = this.infoUser.affiliateCode.code;
            this.formData.value.name = this.infoUser.name;
            this.formData.value.phone = this.infoUser.phone;
            this.formData.value.address = this.infoUser.address;
            this.formData.value.email = this.infoUser.email;
            this.formData.value.dob = this.infoUser.date_of_birth;
            this.formData.value.gender = this.infoUser.gender;
            this.formData.value.latitude = this.infoUser.latitude;
            this.formData.value.longitude = this.infoUser.longitude;
        },
        initMap() {
            let myLatlng = new google.maps.LatLng(this.infoUser.latitude, this.infoUser.longitude);
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
            this.map = new google.maps.Map(document.getElementById('map'),
                mapOptions);

            let input = document.getElementById("address");
            let searchBox = new google.maps.places.SearchBox(input);

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
            infowindow.setContent(this.infoUser.address);
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
            $("#resource").text('');
            if (window.File && window.FileList && window.FileReader) {
                let fileReader = new FileReader();
                let files = e.target.files;
                this.selectedFile = files[0];
                fileReader.onload = (function(e) {
                    $("#resource").append("<img class=\"rounded-pill\" width=\"100px\" height=\"100px\" alt=\"avatar\" src=\"" + e.target.result + "\" />");
                });
                fileReader.readAsDataURL(this.selectedFile);
            }
            // this.saveAvatarUser();
        },
        saveAvatarUser() {
            if (!this.selectedFile) {
                this.selectedFile = '';
                return;
            }
            let formData = new FormData();
            formData.append('id', this.infoUser.id);
            formData.append('avatar', this.selectedFile);

            common.loadingScreen.show('body');

            common.post({
                url: '/profile/personal-info/save',
                data: formData,
                errorModel: this.formData.errors,
            }).done((res) => {
                if (res.error !== EErrorCode.NO_ERROR) {
                    common.showMsg('error', null, res.msg);
                    return;
                }
                window.location.assign(res.profile);
            }).always(() => {
                common.loadingScreen.hide('body');
            });
        },
        saveInfoUser() {
            let formData = new FormData();
            formData.append('id', this.infoUser.id);

            Object.keys(this.formData.value).forEach((key) => {
                switch (key) {
                    case 'avatar':
                        if (this.selectedFile) {
                            formData.append('avatar', this.selectedFile);
                        }
                        break;
                    case 'dob': {
                        let date = new Date(this.formData.value['dob']);
                        formData.append('dob', DateUtil.getDateString(date));
                        break;
                    }
                    default:
                        if (this.formData.value[key]) {
                            formData.append(key, this.formData.value[key]);
                        }
                        break;
                }
            });

            common.loadingScreen.show('body');

            common.post({
                url: '/profile/personal-info/save',
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
        }
    }
});
