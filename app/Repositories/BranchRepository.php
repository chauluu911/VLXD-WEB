<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Branch;
use App\Models\Shop;
use App\Models\Area;
use Illuminate\Support\Arr;
use App\Enums\EDateFormat;
use App\Enums\EStatus;

class BranchRepository extends BaseRepository
{

    public function __construct(Branch $branch, Shop $shop, Area $area)
    {
        $this->model = $branch;
        $this->model_shop = $shop;
        $this->model_area = $area;
    }
    /**
     * @param array $options
     * @return bool|LengthAwarePaginator|Collection|User
     */

    public function getByOptions(array $options = [])
    {
        $result = $this->model
            ->from('branch as br')
            ->leftJoin('shop as sh', 'sh.id', 'br.shop_id')
            ->where('br.status', EStatus::ACTIVE)
            ->select('br.*', 'sh.name as name_shop');

        //Tìm kiếm
        foreach ($options as  $key => $val) {
            switch ($key) {
                case 'q':
                    $result->where('br.name_search', 'ilike', "%$val%");
                    break;
                case 'createdAtFrom':
                    $result->where('br.updated_at', '>=', $val->copy()->timezone(config('app.timezone'))->startOfDay()->format(EDateFormat::MODEL_DATE_FORMAT));
                    break;
                case 'createdAtTo':
                    $result->where('br.updated_at', '<', $val->copy()->timezone(config('app.timezone'))->startOfDay()->addDay()->format(EDateFormat::MODEL_DATE_FORMAT));
                    break;
            }
        }
        return parent::getByOption($options, $result);
    }

    public function getBranch() {
        return $this->model_area
        ->from('area as ar')
        ->where('ar.type', 1)
        ->select('ar.id', 'ar.name')
        ->get();
    }

    public function getListShop() {
        return $this->model_shop
        ->from('shop as sh')
        ->select('sh.id','sh.name')
        ->get();
    }
}
