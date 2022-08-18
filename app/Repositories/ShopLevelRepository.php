<?php

namespace App\Repositories;

use App\Helpers\DateUtility;
use App\Helpers\StringUtility;
use App\Models\ShopLevel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\EStatus;
use App\Models\Shop;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Enums\EDateFormat;

class ShopLevelRepository extends BaseRepository {

    public function __construct(ShopLevel $shopLevel) {
        $this->model = $shopLevel;
    }
    /**
     * @param array $options
     * @return bool|LengthAwarePaginator|Collection|User
     */
    public function getByOptions(array $options) {
        $result = $this->model
            ->from('shop_level as s')
            ->select('s.*');

        foreach ($options as $key => $val) {
            switch ($key) {
                case 'status':
                    $result->where('s.status', $val);
                    break;
                case 'shop_id':
                    $result->where('s.shop_id', $val);
                    break;
            }
        }

        $orderBy = Arr::get($options,'orderBy', 'created_at');
        $orderDirection = Arr::get($options,'orderDirection', 'desc');
        switch ($orderBy) {
            default:
                $result->orderBy("s.$orderBy", "$orderDirection");
                break;
        }

        return parent::getByOption($options, $result);
    }

}
