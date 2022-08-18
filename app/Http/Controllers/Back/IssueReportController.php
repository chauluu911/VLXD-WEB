<?php

namespace App\Http\Controllers\Back;


use App\Constant\CacheKey;
use App\Enums\ETableName;
use App\Enums\EErrorCode;
use App\Enums\EStatus;
use App\Enums\EDateFormat;
use \App\Http\Controllers\Controller;
use App\Services\IssueReportService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;
use App\Constant\SessionKey;

class IssueReportController extends Controller {
	private IssueReportService $issueReportService;

	public function __construct(IssueReportService $issueReportService) {
		$this->issueReportService = $issueReportService;
	}

	public function getReportList() {
		$timezone = session(SessionKey::TIMEZONE);
		$options = [
			'pageSize' => request('pageSize'),
			'get_for_admin' => true,
		];

		$acceptFields = ['q', 'categoryType', 'createdAtFrom', 'createdAtTo'];

		foreach ($acceptFields as $field) {
			if (!request()->filled("filter.$field")) {
				continue;
			}
			if ($field === 'createdAtFrom' || $field === 'createdAtTo') {
                if (Arr::has(request('filter'), $field)) {
                    try {
                        $date = Carbon::createFromFormat(
                            EDateFormat::DEFAULT_DATEPICKER_INPUT_FORMAT_WITH_TZ,
                            request("filter.$field"). " $timezone",
                        );
                        $options[Str::snake($field)] = $date;
                    } catch (\Exception $e) {
                    }
                }
            } else {
                $options[Str::snake($field)]  = request("filter.$field");
            }
		}
		$options['table_name'] = ETableName::PRODUCT;
		$reportList = $this->issueReportService->getByOptions($options);
		$tmp = $reportList->map(function($item) {
			return [
				'id' => $item->id,
				'productId' => $item->productId,
				'reportType' => $item->categoryName,
				'userName' => $item->userName,
				'userPhone' => $item->userPhone,
				'productName' => $item->productName,
				'createdAt' => $item->created_at,
				'content' => $item->content,
			];
		});
		$reportList->setCollection($tmp);
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'reportList' => $reportList,
		]);
	}
}