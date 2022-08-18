'use strict';

import Chat from "./Conversation.vue";

let viewData = $('#view-data').val();
viewData = !!viewData ? atob(viewData) : '{}';
viewData = JSON.parse(viewData);

let userId = $('#chat-data').val();

const app = new Vue({
	name: 'Chat',
	el: '#chat',
	components: { Chat },
});
