<?php


namespace App\Services;


use App\Enums\EErrorCode;
use App\Enums\ECategoryDataType;
use App\Enums\EStatus;
use App\Helpers\StringUtility;
use App\Repositories\ProductCategoryAttributeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class ProductCategoryAttributeService {
    public function __construct(ProductCategoryAttributeRepository $productCategoryAttributeRepository) {
        $this->productCategoryAttributeRepository = $productCategoryAttributeRepository;
    }

    public function getById($id) {
        return $this->productCategoryAttributeRepository->getById($id);
    }

    public function getByOptions($options) {
        return $this->productCategoryAttributeRepository->getByOptions($options);
    }
}
