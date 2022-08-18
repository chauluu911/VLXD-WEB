<?php

namespace App\Repositories;

use App\Enums\EDateFormat;
use App\Enums\EStatus;
use App\Models\Follow;
use Illuminate\Support\Arr;

class FollowRepository extends BaseRepository {
    public function __construct(Follow $follow) {
        $this->model = $follow;
    }

    public function getByOptions(array $options) {
        $result = $this->model
            ->from('follow as f')
            ->select('f.*');
        if (empty(Arr::get($options, 'status', null))) {
            $result->where('f.status', EStatus::ACTIVE);
        }
        foreach ($options as $key => $val) {
            switch ($key) {
                case 'status':
                case 'user_id':
                case 'following_table_id':
                case 'following_table_name':
                case 'id':
                    $result->where("f.$key", $val);
                    break;
            }
        }

        $orderBy = Arr::get($options,'orderBy', 'created_at');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            default:
                $result->orderBy("f.$orderBy", "$orderDirection");
                break;
        }

        return parent::getByOption($options, $result);
    }
}
