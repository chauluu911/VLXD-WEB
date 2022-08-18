<?php

return [
	'object_name' => 'thông báo',
	'attributes' => [
		'title' => 'tiêu đề',
		'content' => 'nội dung',
		'schedule_at' => 'thời gian gửi',
		'target_type' => 'đối tượng gửi',
	],
	'errors' => [
		'schedule_at_after_now' => 'Thời gian gửi thông báo phải lớn hơn thời gian hiện tại 5 phút',
	]
];