export default {
	vi: {
		object_name: 'thanh toán',
		name_filter: 'Tên người dùng',
		filter: {
			push_post: 'Tên, Sđt cửa hàng, Tên sản phẩm',
			upgrade_pakage: 'Tên, sđt cửa hàng',
			buy_coins: 'Tên, Sđt khách hàng',
			order: 'Mã đơn hàng,Tên người mua, Tên cửa hàng',
            status: {
                'waiting': 'Chờ xác nhận từ người bán',
                'confirmed': 'Đã xác nhận',
                'cancel_by_user': 'Hủy bởi khách hàng',
                'cancel_by_shop': 'Hủy bởi chủ shop',
                'deleted': 'Đã xóa',
            }
		},
		title: 'Thanh toán',
		total_count: 'Tổng số lượt thanh toán',
		table: {
			column: {
				code: 'Mã cửa hàng',
                order_code: 'Mã đơn hàng',
				post_id: 'ID người dùng',
				name: 'Người mua',
				shop_name: 'Tên cửa hàng',
				payment_method: 'Hình thức thanh toán',
				payment_type: 'Loại thanh toán',
                payment_status: 'Thanh toán',
                delivery_status: 'Vận chuyển',
				amount: 'Tiền thanh toán',
				updated_at: 'Thời gian',
                push_post: 'Gói đẩy tin',
                upgrade_pakage: 'Gói nâng cấp',
				product: 'Sản phẩm',
				option: 'Tùy chọn'
			}
		},
		label: {
			payment_type: 'Loại thanh toán',
			payment_status: 'Thanh toán',
            status: 'Trạng thái',
            delivery_status: 'Vận chuyển',
            created_at: 'Ngày tạo',

		},
		confirm: {
			'approve': 'Bạn chắc chắn có muốn duyệt thanh toán này không?'
		},
		approve: 'Duyệt',
		'subscription-price-type': {
			'push-product': 'Giá gói đẩy tin',
			'buy-coins': 'Mua xu',
			'verify-shop': 'Giá gói xác thực shop'
		}
	},
	en: {
	},
}
