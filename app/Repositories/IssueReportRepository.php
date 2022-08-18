<?php

namespace App\Repositories;

use App\Constant\ConfigTableName;
use App\Enums\EDateFormat;
use App\Enums\EStatus;
use App\Enums\ETableName;
use App\Models\IssueReport;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IssueReportRepository extends BaseRepository {

	public function __construct(IssueReport $issueReport) {
		$this->model = $issueReport;
	}

    public function getByOptions(array $options) {
		$result = $this->model
			->from('issue_report as ir')
			->select('ir.*');

		//region filter
		if (Arr::get($options, 'get_for_admin')) {
			$result->select('ir.id', 'p.id as productId', 'p.name as productName',
					'us.name as userName', 'us.phone as userPhone', 'ir.content'
					, 'ir.created_at', 'c.name as categoryName')
				->join('users as us', 'us.id', 'ir.reporter_id')
				->join('product as p', 'p.id', 'ir.table_id')
				->join('category as c', 'c.id', 'ir.issue_type_id');
		}

		if (Arr::hasAny($options, ['status', 'not_status'])) {
			$result->where('ir.status', EStatus::ACTIVE);
		}
		foreach ($options as $key => $val) {
			if (!isset($val)) {
				continue;
			}
			switch ($key) {
				case 'id':
				case 'status':
				case 'created_at_to':
					$result->whereRaw('coalesce(ir.updated_at, ir.created_at) < ?', [
						$val->copy()->timezone(config('app.timezone'))->startOfDay()->addDay()->format(EDateFormat::MODEL_DATE_FORMAT)
					]);
					break;
				case 'created_at_from':
					$result->whereRaw('coalesce(ir.updated_at, ir.created_at) >= ?', [
						$val->copy()->timezone(config('app.timezone'))->startOfDay()->format(EDateFormat::MODEL_DATE_FORMAT)
					]);
					break;
				case 'table_name':
					$result->where('ir.table_name', $val);
					break;
				case 'category_type':
					$result->where('ir.issue_type_id', $val);
					break;
				case 'q':
					$result->where(function($query) use ($val) {
                        $query->orWhere('us.name_search', 'ilike', "%$val%")
                            ->orWhere('us.phone', 'ilike', "%$val%")
                            ->orWhere('p.name_search', 'ilike', "%$val%");
                    });
					break;
			}
		}
		//endregion
        $orderBy = Arr::get($options,'orderBy', 'created_at');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            default:
                $result->orderBy("ir.$orderBy", "$orderDirection");
                break;
        }

		return parent::getByOption($options, $result);
	}
}
