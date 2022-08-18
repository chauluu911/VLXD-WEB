<template>
    <div>
        <div class="google-map" ref="googleMap" style="height: 250px"></div>
    </div>
</template>

<script>
    import GoogleMapsApiLoader from 'google-maps-api-loader';

    export default {
        props: {
            mapConfig: Object,
            apiKey: String,
            address: String
        },

        data() {
            return {
                google: null,
                map: null
            }
        },

        watch: {
            address: function (newVal) {
                if (!newVal || newVal.trim() === '') {
                    return;
                }
                this.geocodeAddress(this.geocoder, this.map);
            },
            'mapConfig.center.lat'(newVal) {
                let position = {lat: newVal, lng: this.mapConfig.center.lng};
                this.map.setCenter(position);
                this.marker.setPosition(position);
            },
            'mapConfig.center.lng'(newVal) {
                let position = {lat: this.mapConfig.center.lat, lng: newVal};
                this.map.setCenter(position);
                this.marker.setPosition(position);
            }
        },

        async mounted() {
            const googleMapApi = await GoogleMapsApiLoader({
                apiKey: this.apiKey
            });
            this.google = googleMapApi;
            this.initializeMap();
        },

        methods: {
            initializeMap() {
                const mapContainer = this.$refs.googleMap;
                this.map = new this.google.maps.Map(
                    mapContainer, this.mapConfig
                );
                this.geocoder = new this.google.maps.Geocoder();

                this.marker = new this.google.maps.Marker({
                    map: this.map,
                    position: this.map.getCenter()
                });

                this.google.maps.event.addListener(this.map, 'click', event => {
                    this.marker.setPosition(event.latLng);
                    this.$emit('click', {lat: event.latLng.lat(), lng: event.latLng.lng()})
                });
            },
            geocodeAddress(geocoder, resultsMap) {
                this.$emit('search-address-status-changed', 1);
                geocoder.geocode({'address': this.address, region: 'vn'}, (results, status) => {
                    if (status === 'OK') {
                        let center = results[0].geometry.location;
                        resultsMap.setCenter(center);
                        /*let marker = new this.google.maps.Marker({
                            map: resultsMap,
                            position: center
                        });*/
                        this.marker.setPosition(center);
                        this.$emit('center-changed', {lat: center.lat(), lng: center.lng()});
                        this.$emit('search-address-status-changed', 0);
                    } else {
                        alert('Geocode was not successful for the following reason: ' + status);
                    }
                });
            }
        }
    }
</script>

<style scoped>

</style>
