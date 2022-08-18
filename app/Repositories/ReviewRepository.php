<?php

namespace App\Repositories;

use App\Enums\EDateFormat;
use App\Models\Review;
use Illuminate\Support\Arr;

class ReviewRepository extends BaseRepository {
    public function __construct(Review $review) {
        $this->model = $review;
    }

    public function getByOptions(array $options) {
        $result = $this->model
            ->from('review as r')
            ->select('r.*');
        foreach ($options as $key => $val) {
            switch ($key) {
                case 'status':
                case 'id':
                case 'star':
                case 'table_name':
                case 'table_id':
                    $result->where("r.$key", $val);
                    break;
                case 'createdAtFrom':
                    $result->where('sr.created_at', '>=', $val->copy()->timezone(config('app.timezone'))->startOfDay()->format(EDateFormat::MODEL_DATE_FORMAT));
                    break;
                case 'createdAtTo':
                    $result->where('sr.created_at', '<', $val->copy()->timezone(config('app.timezone'))->startOfDay()->addDay()->format(EDateFormat::MODEL_DATE_FORMAT));
            }
        }

        $orderBy = Arr::get($options,'orderBy', 'created_at');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            default:
                $result->orderBy("r.$orderBy", "$orderDirection");
                break;
        }

        return parent::getByOption($options, $result);
    }

    public function getEvaluate($tableName, $tableId) {
        $average = $this->model
            ->from('review as r')
            ->where('table_name', $tableName)
            ->where('table_id', $tableId)
            ->avg('star');
        $total = $this->model
            ->from('review as r')
            ->where('table_name', $tableName)
            ->where('table_id', $tableId)
            ->count('*');
        return [
            'total' => $total,
            'average' => $average,
        ];
    }

}
