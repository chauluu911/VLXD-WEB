<template>
	<div class="d-flex-center text-primary" style="min-height: calc(100vh - 300px); font-size: 85px; line-height: 95px; text-align: center;">
		{{ $t('title') }}
	</div>
</template>

<script>
	import homeMessage from '../../locales/back/home';
	export default {
		name: "Home",
		i18n: {
			messages: homeMessage,
		},
		created() {
			this.$store.commit('updateBreadcrumbsState', []);
			this.$store.commit('updateFilterFormState', []);
			this.$store.commit('updateQueryFilterState', {enable: false});
		},
	}
</script>