<template>
	<div>
		<shop-list :user-type="userType"></shop-list>
	</div>
</template>

<script>
	import {mapState} from "vuex";
	import ShopList from "./ShopList";

	export default {
		components: {
			ShopList
		},
		props: {
            userType: {
                type: Number,
                default: null,
            },
        },
	}
</script>

<style scoped>

</style>
