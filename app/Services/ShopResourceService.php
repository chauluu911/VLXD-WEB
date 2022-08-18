<?php

namespace App\Services;

use App\Enums\EErrorCode;
use App\Enums\EResourceType;
use App\Enums\EStatus;
use App\Enums\EStoreFileType;
use App\Helpers\FileUtility;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCategoryAttribute;
use App\Models\ProductResource;
use App\Models\ShopResource;
use App\Repositories\ShopResourceRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ShopResourceService {
    private ShopResourceRepository $shopResourceRepository;

    public function __construct(ShopResourceRepository $shopResourceRepository) {
        $this->shopResourceRepository = $shopResourceRepository;
    }

    public function getByOptions(array $options) {
        return $this->shopResourceRepository->getByOptions($options);
    }

    public function getById($id) {
        return $this->shopResourceRepository->getById($id);
    }

    public function saveResource($data, $loggedInUserId) {
        return DB::transaction(function() use ($data, $loggedInUserId) {


            $newResourceLink = Arr::get($data, 'newResourceLink',null);
            $newResourceType = Arr::get($data, 'newResourceType',null);
            $newResourceFile = Arr::get($data, 'newResourceFile',null);

            $newResource = new ShopResource();
            $relativePath = $newResourceFile ?
                FileUtility::storeFile(EStoreFileType::SHOP_RESOURCE, $newResourceFile) :
                $newResourceLink;
            $newResource->shop_id = Arr::get($data, 'shopId', null);
            $newResource->path_to_resource = $relativePath;
            $fileToDeleteIfError[] = $relativePath;
            $newResource->type = $newResourceType;
            $newResource->status= EStatus::ACTIVE;
            $newResource->created_by = $loggedInUserId;
            $newResource->save();
            return ['error' => EErrorCode::NO_ERROR];
        });
    }

    public function deleteResource($resourceId,$shopId, $loggedInUserId) {
        return DB::transaction(function() use ($resourceId,$shopId, $loggedInUserId) {


            $shopResource = $this->getById($resourceId);
            if (empty($shopResource)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            if ($shopResource->shop_id != $shopId) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            $shopResource->status = EStatus::DELETED;
            $shopResource->save();
            return ['error' => EErrorCode::NO_ERROR];
        });
    }
}
