<template>
	<div class="youtube-wrapper">
		<iframe :src="`https://www.youtube.com/embed/:youtubeId?autoplay=${autoplay ? 1 : 0}&disablekb=1&rel=0&showinfo=0&enablejsapi=1&version=3&playerapiid=ytplayer`.replace(':youtubeId', youtubeId)" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
	</div>
</template>

<script>
export default {
	name: "YoutubeEmbed",
	props: {
		youtubeId: {
			type: String,
			required: true,
		},
		autoplay: {
			type: Boolean,
			default: true,
		}
	}
}
</script>

<style scoped>

</style>