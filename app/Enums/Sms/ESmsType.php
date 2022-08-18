<?php


namespace App\Enums\Sms;


class ESmsType {
	/*
	 * Brandname chăm sóc khách hàng
	 * 		Khuyến khích sử dụng loại tin nhắn này.
	 * 		Bạn cần liên hệ nhân viên kinh Doanh
	 * 		hoặc hotline 0901.888.484 để đăng ký Brandname
	 * 		riêng của mình và Brandname test.
	 */
	const BRANDNAME_CSKH = 2;

	/*
	 * Tin nhắn đầu số cố định 10 số
	 * 		chuyên dùng cho chăm sóc khách hang.
	 * 		Bạn cần phải đăng ký mẫu tin nhắn
	 * 		trước với bộ phận kinh doanh để
	 * 		đăng ký và sử dụng
	 */
	const STATIC_PHONE_NUMBER_CSKH = 8;
}