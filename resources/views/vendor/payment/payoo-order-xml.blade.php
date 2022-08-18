<shops>
	<shop>
		<session>{{ $sessionId }}</session>
		<username>{{ config('app.payoo.businessUsername') }}</username>
		<shop_id>{{ config('app.payoo.shopId') }}</shop_id>
		<shop_title>{{ config('app.payoo.shopTitle') }}</shop_title>
		<shop_domain>{{ config('app.url') }}</shop_domain>
		<shop_back_url>{{ $redirectBackUrl }}</shop_back_url>
		<order_no>{{ $orderCode }}</order_no>
		<order_cash_amount>{{ round($orderAmount) }}</order_cash_amount>
		<order_ship_date>{{ $createdAt }}</order_ship_date>
		<order_ship_days>1</order_ship_days>
		<order_description>{{ urlencode($orderDescription) }}</order_description>
		<validity_time>{{ $paymentDeadline }}</validity_time>
		<notify_url>{{ config('app.url') . route('api.payment-result.payoo', [], false) }}</notify_url>
		<customer>
			<name>{{ $user->name }}</name>
			@if (!empty($user->phone))
				<phone>{{ $user->phone }}</phone>
			@endif
			@if (!empty($user->email))
				<email>{{ $user->email }}</email>
			@endif
		</customer>
	</shop>
</shops>
