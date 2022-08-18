'use strict';

import FeedbackButton from "../components/FeedbackButton";

let viewData = $('#view-data').val();
viewData = !!viewData ? atob(viewData) : '{}';
viewData = JSON.parse(viewData);

const app = new Vue({
	name: 'AdsCreateSuccess',
	el: '#ads-create-success',
	components: { FeedbackButton },
});