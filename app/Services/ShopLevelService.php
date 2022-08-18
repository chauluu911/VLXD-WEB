<?php

namespace App\Services;

use App\Constant\DefaultConfig;
use App\Enums\EDateFormat;
use App\Enums\EErrorCode;
use App\Enums\EStatus;
use App\Repositories\ShopLevelRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\FileUtility;
use App\Enums\EStoreFileType;


class ShopLevelService {
    private ShopLevelRepository $shopLevelRepository;

    public function __construct(ShopLevelRepository $shopLevelRepository) {
        $this->shopLevelRepository = $shopLevelRepository;
    }

    public function getByOptions(array $options) {
        return $this->shopLevelRepository->getByOptions($options);
    }

    public function getById($id) {
	    return $this->shopLevelRepository->getById($id);
    }

    public function updateMonthInYear($id, $currentMonthYear, $newNumberPushProduct) {
        return DB::transaction(function() use ($id, $currentMonthYear, $newNumberPushProduct) {
            $shopLevel = $this->getById($id);
            
            $shopLevelMonthYear = json_decode($shopLevel->num_push_product_in_month);
            $shopLevelMonthYear->month_in_year = $currentMonthYear;
            $shopLevelMonthYear->num_push_product_in_month_remain = $newNumberPushProduct;
            
            $shopLevel->num_push_product_in_month = str_replace('-', '/', json_encode($shopLevelMonthYear));
            $shopLevel->save();
            return ['error' => EErrorCode::NO_ERROR, 'shopLevel' => $shopLevel];
        });
    }
}
