<?php

namespace App\Services;

use App\Constant\ConfigTableName;
use App\Repositories\IssueReportRepository;
use App\Enums\EStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use App\Models\IssueReport;
use App\Enums\EErrorCode;

class IssueReportService {

	private IssueReportRepository $issueReportRepository;

    public function __construct(IssueReportRepository $issueReportRepository) {
		$this->issueReportRepository = $issueReportRepository;
    }

    /**
     * @param $id
     * @return Order
     */
    public function getById($id) {
        return $this->issueReportRepository->getById($id);
    }

	public function getByOptions(array $options) {
    	return $this->issueReportRepository->getByOptions($options);
	}

    public function saveReport($data, $now, $loggedInUserId) {
        return DB::transaction(function() use ($data, $now, $loggedInUserId) {
            $report = new IssueReport();
            $report->table_name = ConfigTableName::PRODUCT;
            $report->table_id = Arr::get($data, 'productId', $report->table_id);
            $report->issue_type_id = Arr::get($data, 'reportChoosed', $report->issue_type_id);
            $report->reporter_id = $loggedInUserId;
            $report->status = EStatus::ACTIVE;
            $report->created_at = $now;
            $report->created_by = $loggedInUserId;
            $report->content = Arr::get($data, 'content', $report->content);
            $report->process_status = 0;
            $report->save();
            return ['error' => EErrorCode::NO_ERROR];
        });
    }
}
