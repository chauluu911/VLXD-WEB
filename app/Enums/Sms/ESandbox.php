<?php


namespace App\Enums\Sms;


class ESandbox {
	// Không thử nghiệm, gửi tin đi thật
	const ACTUAL_SEND = 0;

	// Thử nghiệm (tin không đi mà chỉ tạo ra tin nhắn)
	const TEST_SEND = 1;
}