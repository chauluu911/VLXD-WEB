<?php

return [
    'gender' => [
        'other' => 'Khác',
        'male' => 'Nam',
        'female' => 'Nữ'
    ],
	'category' => [
		'class_1' => 'Danh mục',
		'class_2' => 'Danh mục con',
		'class_3' => 'Danh mục con 2',
		'ads' => 'Quảng cáo',
	],
	'status' => [
		'approved' => 'Đã duyệt',
        'suspend' => 'Từ chối',
		'active' => 'Hoạt động',
		'hide' => 'Ẩn',
		'deleted' => 'Đã xóa',
		'waiting' => 'Đang chờ',
		'pending' => 'Chờ duyệt',
	],
	'sell_status' => [
		'selling' => 'Đang bán',
		'sold' => 'Đã bán',
	],
	'payment_status' => [
		'paid' => 'Đã thanh toán',
		'unpaid' => 'Chưa thanh toán',
	],
    'order_status' =>[
        'deleted' => 'Đã xóa'  ,
        'cancel_by_user' => 'Hủy bởi khách hàng',
        'cancel_by_shop' => 'Hủy bởi chủ shop',
        'waiting'=> 'Đang chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
    ],
    'custom_order_status_for_user' =>[
        'canceled' => 'Đã hủy'  ,
        'waiting_for_delivery' => 'Chờ giao hàng',
        'on_the_way' => 'Đang giao',
        'delivery_success' => 'Giao thành công',
        'waiting'=> 'Chờ xác nhận',
    ],
    'product_status' => [
        'rejected' => 'Từ chối'  ,
        'deleted' => 'Đã xóa',
        'waiting' => 'Chờ duyệt',
        'active' => 'Hoạt động',
        'waiting_and_active'=> 'Chờ duyệt, Hoạt động',
    ],
	'display_status' => [
		'hidden' => 'Ẩn',
		'showing' => 'Hiển thị',
	],
	'user_type' => [
		'admin' => 'Admin',
		'staff' => 'Nhân viên',
		'user' => 'Khách hàng',
	],
	'customer_type' => [
		'seller' => 'Người bán',
		'buyer' => 'Người mua',
		'advertiser' => 'Người quảng cáo',
	],
	'target_type' => [
		'all_customer' => 'Tất cả',
		'specific_customer' => 'Chỉ định',
	],
	'notification_status' => [
		'sent' => 'Đã gửi',
	],
];
