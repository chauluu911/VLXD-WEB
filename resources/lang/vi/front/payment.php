<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Authentication Language Lines
	|--------------------------------------------------------------------------
	|
	| The following language lines are used during authentication for various
	| messages that we need to display to the user. You are free to modify
	| these language lines according to your application's requirements.
	|
	*/

	'payment_management' => 'Quản lý thanh toán',
	'payment_content' => 'Nội dung thanh toán',
	'date_of_purchase' => 'Ngày mua',
	'payment_method' => 'Hình thức thanh toán',
	'status' => 'Trạng thái',
	'delete' => 'Xóa',

	'document' => 'Tài liệu',
	'post' => 'Tin tức',
	'exercise' => 'Bài tập',
    'subscription' => 'Phí duy trì',
    'deposit_2' => 'Nạp đồng ERA',
    'subscription_table_content' => 'Thanh toán phí duy trì: :month tháng',

	'not_received_yet' => 'Chưa thanh toán',
	'received' => 'Đã thanh toán',
	'approve' => 'Duyệt',
    'deleted' => 'Đã xóa',
	'accept' => 'Đồng ý',
	'internal_money_wallet' => 'Ví tiền nội bộ',
	'point_wallet' => 'Ví điểm',
	'bank_transfer' => 'Chuyển khoản',
	'payment_gateway' => 'Cổng thanh toán',
	'sms' => 'SMS',
	'from_date' => 'Từ ngày',
	'to_date' => 'Đến ngày',
	'title' => 'Tiêu đề',
	'surplus' => 'Số dư',
	'finish' => 'Bạn có chắc chắn muốn xóa lượt thanh toán này ?',
	'common' => [
		'action' => 'Hành động',
	],
	'payment' => [
		'messages' => [
			'do_you_want_delete_this_payment' => 'Bạn có muốn xóa thanh toán này ?',
		],
	],
	'user_payment' => [
		'message_accept_payment_exercise' => 'Bạn muốn thanh toán bài tập này ?',
		'message_accept_payment_news' => 'Bạn muốn thanh toán tin tức này ?',
		'messages_not_money_payment_exercise' => 'Bạn không đủ tiền để mua bài tập này',
		'messages_not_money_payment_news' => 'Bạn không đủ tiền để mua tin tức này',
	],
	'no-payment-method' => 'Không có',

    'deposit' => 'NẠP ĐỒNG ERA VÀO TÀI KHOẢN',
    'choose-amount' => 'Chọn mệnh giá',
    'amount' => 'mệnh giá',
    'choose-exam-package' => 'Chọn gói bài tập',
    'exam-package' => 'gói bài tập',
    'choose-payment-method' => 'Chọn hình thức thanh toán',
    'bank-transfer-description-1' => 'Chuyển khoản đến tài khoản của GDGT. Sau khi nhận được số tiền thanh toán, chúng tôi sẽ thông báo và cập nhật hoàn tất giao dịch cho quý khách.',
    'bank-transfer-description-2' => 'Vui lòng ghi rõ thông tin chuyển khoản',
    'account-holder' => 'Chủ tài khoản',
    'account-number' => 'Số tài khoản',
    'bank-branch' => 'Chi nhánh',
    'method' => [
        'sms' => 'SMS',
        'internal_money_wallet' => 'Thanh toán bằng đồng ERA',
        'point_wallet' => 'Thanh toán bằng điểm',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'payment_gateway' => 'Thanh toán trực tuyến',
        'payoo' => [
            'payoo-account' => 'Thanh toán bằng Ví điện tử Payoo',
            //'cc-payment' => 'Thẻ tín dụng/ghi nợ quốc tế',
            'bank-payment' => 'Thanh toán bằng thẻ/tài khoản ngân hàng (ATM nội địa/quốc tế)',
            // 'pay-at-store' => 'Thanh toán tại Cửa hàng gần nhà',
            // 'qr' => 'Thanh toán bằng cách quét mã QR',
        ]
    ],
    'confirm' => [
        'delete' => 'Bạn có chắc muốn xóa lượt thanh toán này?',
        'approve' => 'Bạn có chắc muốn duyệt lượt thanh toán này?|Bạn có chắc muốn duyệt :count lượt thanh toán này?',
        'deposit' => 'Xác nhận nạp đồng ERA với mệnh giá :amount VNĐ?',
        'buy' => 'Xác nhận mua :kind ":name" với giá :amount?',
        'insufficient_recharge' => 'Số đồng ERA trong tài khoản không đủ. Vui lòng nạp thêm đồng ERA vào tài khoản đề tiếp tục thanh toán',
    ],
    'msg' => [
        'Buy credits success' => 'Nạp đồng ERA thành công, tài khoản của bạn có :amount <img src="/images/icon/coins.svg" height="30px" width="30px" style="padding-bottom: 7px;">',
        'Payment information save success' => 'Thông tin thanh toán đã được lưu',
        'Payment success' => 'Thanh toán thành công.',
        'Payment is reviewing' => 'Thanh toán của bạn đang được duyệt. Chúng tôi sẽ thông báo cho bạn ngay khi thanh toán được duyệt xong.'
    ],
    'err' => [
        'Failed to approve payment, w/e' => 'Duyệt thanh toán thất bại, :error',
        'Failed to delete payment, w/e' => 'Xóa lượt thanh toán thất bại, :error',
        'not_found' => 'Không tìm thấy mã thanh toán. Hãy liên hệ chúng tôi để hỗ trợ.',
        'payment_method_invalid' => 'Hình thức thanh toán không hợp lệ.',
        'subscription_invalid' => 'Gói bài tập không hợp lệ.',
        'store' => 'cửa hàng',
        'bank' => 'ngân hàng',
        'card_type' => 'loại thẻ',
        'Payment failed' => 'Thanh toán thất bại, vui lòng thử lại',
    ],
    'notification_title' => [
        'payment_success' => 'Thanh toán',
        'point' => 'Điểm',
    ],
    'notification' => [
        'payment_success' => ':Kind có mã ":code" của bạn, đã thanh toán thành công.',
        'new_subscription' => "Bạn đã thanh toán thành công :kind :month tháng",
        'subscription_renewal' => 'Bạn đã gia hạn thành công gói bài tập :month tháng',
        'payment_reward' => 'Bạn được cộng :point điểm khi mua :kind với mã ":code"',
        'deposit' => 'Bạn đã nạp tiền vào tài khoản, <span class="text-success">+:amount VNĐ</span>',
    ]
];
