<template>
	<div>
		<user-list :customer-type="customerType" :user-type="userType"></user-list>
	</div>
</template>

<script>
	import {mapState} from "vuex";
	import UserList from "./UserList";

	export default {
		components: {
			UserList
		},
		props: {
            customerType: {
                type: Number,
                default: null,
            },
            userType: {
                type: Number,
                default: null,
            },
        },
	}
</script>

<style scoped>

</style>
