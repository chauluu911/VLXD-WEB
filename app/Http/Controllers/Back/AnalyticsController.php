<?php

namespace App\Http\Controllers\Back;


use App\Constant\CacheKey;
use App\Enums\EErrorCode;
use App\Enums\EStatus;
use \App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Analytics;
use Spatie\Analytics\Period;
use App\Services\ShopService;
use App\Services\OrderService;
use App\Enums\ECustomOrderStatusForUser;
use Illuminate\Support\Carbon;
use App\Enums\EDateFormat;
use App\Constant\SessionKey;

class AnalyticsController extends Controller {
	protected ShopService $shopService;
	protected OrderService $orderService;

	public function __construct(ShopService $shopService, OrderService $orderService) {
		$this->shopService = $shopService;
		$this->orderService = $orderService;
	}

	public function analyticsData() {
		$acceptFields = ['createdAtLt', 'createdAtGt', 'all'];
		$options = [];
		$tz = session(SessionKey::TIMEZONE);
		foreach ($acceptFields as $field) {
			if (!request()->filled("filter.$field")) {
				continue;
			}
			$fieldValue = request("filter.$field");
			switch ($field) {
				case 'createdAtGt':
				case 'createdAtLt':
					try {
						$date = Carbon::createFromFormat(EDateFormat::DEFAULT_DATEPICKER_INPUT_FORMAT_WITH_TZ, "$fieldValue $tz");
						$options[Str::snake($field)] = $date;
					} catch (\Exception $e) {}
					continue 2;
			}
			$options[Str::snake($field)] = $fieldValue;
		}
		// if (count($options) == 0) {
		// 	$options['created_at_gt'] = now();
		// 	$options['created_at_lt'] = now();
		// }
		if (isset($options['all']) && $options['all']) {
			$options['created_at_gt'] = Carbon::createFromFormat(EDateFormat::DEFAULT_DATEPICKER_INPUT_FORMAT_WITH_TZ, "01/01/2005 $tz");
		}
		if (!isset($options['created_at_gt'])) {
			$options['created_at_gt'] = Carbon::createFromFormat(EDateFormat::DEFAULT_DATEPICKER_INPUT_FORMAT_WITH_TZ, "01/01/2005 $tz");
		}
		if (!isset($options['created_at_lt'])) {
			$options['created_at_lt'] = now();
		}
		if ($options['created_at_gt']->year < 2005 || $options['created_at_gt']->year < 2005) {
			return response()->json([
            	'error' => EErrorCode::ERROR,
            	'msg' => 'Vui lòng chọn từ năm 2005 trở đi'
	        ]);
		}
		if ($options['created_at_gt'] > $options['created_at_lt']) {
			return response()->json([
            	'error' => EErrorCode::ERROR,
            	'msg' => 'Ngày không hợp lệ'
	        ]);
		}
		$total_visitors = Analytics::fetchTotalVisitorsAndPageViews(Period::create($options['created_at_gt'], $options['created_at_lt']));
		$totalShop = $this->shopService->getByOptions([
			'count' => true,
			'createdAtFrom' => $options['created_at_gt'] ?? null,
			'createdAtTo' => $options['created_at_lt'] ?? null,
			'not_status' => EStatus::WAITING
		]);
		$totalTransaction = $this->orderService->getByOptions([
			'count' => true,
			'custom_order_status_for_user' => ECustomOrderStatusForUser::DELIVERED_SUCCESS,
			"for_admin_payment_list" => true,
			"delivery_status" => "2",
			"type_list" => "order",
			'created_at_gt' => $options['created_at_gt'] ?? null,
			'created_at_lt' => $options['created_at_lt'] ?? null,
		]);
		$totalValue = $this->orderService->getByOptions([
			'total_price' => true,
			'sum' => 'o.total',
			'custom_order_status_for_user' => ECustomOrderStatusForUser::DELIVERED_SUCCESS,
			"for_admin_payment_list" => true,
			"delivery_status" => "2",
			"type_list" => "order",
			'created_at_gt' => $options['created_at_gt'] ?? null,
			'created_at_lt' => $options['created_at_lt'] ?? null,
		]);
		return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data'  => [
            	'googleAnalytics' => $total_visitors,
            	'totalShop' => $totalShop,
            	'totalTransaction' => $totalTransaction,
            	'totalValue' => number_format($totalValue) . ' VNĐ',
            ],
        ]);
	}
}