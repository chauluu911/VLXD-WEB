<?php

namespace App\Repositories;

use App\Helpers\DateUtility;
use App\Helpers\StringUtility;
use App\Models\ShopLevelConfig;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\EStatus;
use App\Models\Shop;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Enums\EDateFormat;

class ShopLevelConfigRepository extends BaseRepository {

    public function __construct(ShopLevelConfig $shopLevelConfig) {
        $this->model = $shopLevelConfig;
    }
    /**
     * @param array $options
     * @return bool|LengthAwarePaginator|Collection|User
     */
    public function getByOptions(array $options) {
        $result = $this->model
            ->from('shop_level_config as s')
            ->select('s.*');
        if (Arr::get($options, 'upgrade_shop')) {
            $result->leftJoin('subscription_price as sp', 'sp.meta->level', DB::raw('text(s.level)'))->select('s.*', 'sp.price', 'sp.id as subscriptionPriceId');
        }
        foreach ($options as $key => $val) {
            switch ($key) {
                case 'status':
                    $result->where('s.status', $val);
                    break;
                case 'level':
                    $result->where('s.level', $val);
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
