<?php

namespace App\Services;

use App\Constant\DefaultConfig;
use App\Enums\EDateFormat;
use App\Enums\EErrorCode;
use App\Enums\EStatus;
use App\Enums\EResourceType;
use App\Enums\ETableName;
use App\Enums\EApprovalStatus;
use App\Enums\ENotificationType;
use App\Models\UserInterest;
use App\Repositories\ProductRepository;
use App\Repositories\UserInterestRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\FileUtility;
use App\Enums\EStoreFileType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCategoryAttribute;
use App\Models\ProductResource;
use App\Models\ItemUrl;
use App\Enums\ELanguage;
use App\Jobs\NotifyUserJob;
use Illuminate\Support\Str;
use App\Models\ShopLevel;
use App\Services\ShopLevelService;
use App\Services\FollowService;

class ProductService {
	private ProductRepository $productRepository;
	private UserInterestRepository $userInterestRepository;
    private ShopLevelService $shopLevelService;
    private FollowService $followService;

    public function __construct(ProductRepository $productRepository,
                                UserInterestRepository $userInterestRepository,
                                ShopLevelService $shopLevelService,
                                FollowService $followService) {
        $this->productRepository = $productRepository;
        $this->userInterestRepository = $userInterestRepository;
        $this->shopLevelService = $shopLevelService;
        $this->followService = $followService;
    }

    /**
     * @param $userId
     * @return \App\User|\Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function getById($id) {
	    return $this->productRepository->getById($id);
    }

    public function getByCode($code) {
        return $this->productRepository->getBycode($code);
    }

    public function getByOptions(array $options) {
        return $this->productRepository->getByOptions($options);
    }

    public function deleteProduct($id, $loggedInUserId) {
        return DB::transaction(function() use ($id, $loggedInUserId) {
            $product = $this->getById($id);
            if (empty($product)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            $product->deleted_by = $loggedInUserId;
            $product->deleted_at = Carbon::now();
            $product->status = EStatus::DELETED;
            $product->save();

            $shopLevel = $this->shopLevelService->getByOptions([
                'shop_id' => $product->getShop->id,
                'status' => EStatus::ACTIVE,
                'first' => true,
            ]);
            if ($shopLevel->num_product_remain) {
                $shopLevel->num_product_remain += 1;
            }
            $shopLevel->save();
            return ['error' => EErrorCode::NO_ERROR];
        });
    }
    public function approveOrRejectProduct($id, $approvalStatus, $loggedInUserId) {
        return DB::transaction(function() use ($id, $approvalStatus, $loggedInUserId) {
            $product = $this->getById($id);
            if (empty($product)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            $product->approved_by = $loggedInUserId;
            $product->approved_at = Carbon::now();
            $product->publish_at = Carbon::now();
            $product->approval_status = $approvalStatus;
            $product->save();
            $shop = $product->getShop;
            NotifyUserJob::dispatch([$shop->user_id], [
                'type' => ENotificationType::APPROVED_PRODUCT,
                'title' => [
                    ELanguage::VI => 'Thông báo',
                ],
                'content' => [
                    ELanguage::VI => $approvalStatus == EApprovalStatus::APPROVED ? 'Sản phẩm ' . "'" . $product->name . "'" . ' của bạn đã được duyệt' : 'Sản phẩm ' . "'" . $product->name . "'" . ' của bạn đã bị từ chối',
                ],
                'meta' => [
                    'productId' => (int)$product->id
                ],
                'data' => [
                    'sellerId' => $shop->user_id,
                    'productId' => $product->id
                ],
            ])->onQueue('pushToDevice');
            if ($approvalStatus == EApprovalStatus::APPROVED) {
                $follow = $this->followService->getByOptions([
                    'following_table_name' => ETableName::SHOP,
                    'following_table_id' => $shop->id
                ]);
                foreach ($follow as $item) {
                    NotifyUserJob::dispatch([$item->user_id], [
                        'type' => ENotificationType::RECEIVED_NOTIFICATION_PRODUCT_CREATED_BY_SHOP,
                        'title' => [
                            ELanguage::VI => 'Thông báo',
                        ],
                        'content' => [
                            ELanguage::VI => $shop->name . ' Vừa đăng sản phẩm mới: ' . '"' . $product->name . '"',
                        ],
                        'meta' => [
                            'productId' => (int)$product->id
                        ],
                        'data' => [
                            'shopId' => $shop->id,
                            'productId' => $product->id
                        ],
                    ])->onQueue('pushToDevice');
                }

            }
            return ['error' => EErrorCode::NO_ERROR];
        });
    }

    public function generateNewCode() {
        do {
            $code = 'SP' . mb_strtoupper(Str::random(5));
        } while (Product::where('code', $code)->exists());
        return $code;
    }

    public function saveProduct($data, $loggedInUserId) {
        return DB::transaction(function() use ($data, $loggedInUserId) {
            if (!empty(Arr::get($data, 'code'))) {
                $product = $this->getBycode(Arr::get($data, 'code'));
                $product->updated_at = now();
                $product->updated_by = $loggedInUserId;

                //không cho phép edit sản phẩm của shop khác
                if(Arr::get($data, 'shopId') != $product->shop_id) {
                    return [
                        'error' => EErrorCode::ERROR,
                        'msg' => __('common/error.invalid-request-data')
                    ];
                }
            } else {
                $shopLevel = $this->shopLevelService->getByOptions([
                    'shop_id' => Arr::get($data, 'shopId'),
                    'status' => EStatus::ACTIVE,
                    'first' => true,
                ]);
                if (!empty($shopLevel->num_product_remain)) {
                    $shopLevel->num_product_remain -= 1;
                }
                $shopLevel->save();

                $product = new Product();
                $product->created_by = $loggedInUserId;
                $product->created_at = now();
                $product->code = $this->generateNewCode();
            }

            $product->name = Arr::get($data, 'name', $product->name);
            $product->status = EStatus::ACTIVE;
            $product->price = Arr::get($data, 'price', $product->price);
            $product->description = Arr::get($data, 'description', $product->description);
            $product->shop_id = Arr::get($data, 'shopId', $product->shop_id);
            $product->type = Arr::get($data, 'type', $product->type);
            $product->approval_status = EStatus::WAITING;
            $product->unit = Arr::get($data, 'unit', $product->unit);
            $product->save();

            if (empty(Arr::get($data, 'code'))) {
                $itemUrl = new ItemUrl();
                $itemUrl->item_type = 'product';
                $itemUrl->item_id = $product->id;
                $itemUrl->path = 'product/' . $product->code . '/detail';
                $itemUrl->created_at = now();
                $itemUrl->created_by = $loggedInUserId;
                $itemUrl->save();
            }

            $productCategory = $product->productCategories;
            if (count($productCategory) > 0) {
                foreach ($productCategory as $key) {
                    if (Arr::get($data, 'childCategoryId')) {
                        $key->category_id = Arr::get($data, 'childCategoryId', $key->category_id);
                    } else {
                        $key->category_id = Arr::get($data, 'categoryId', $key->category_id);
                    }
                    $key->save();
                }
            } else {
                $productCategory = new ProductCategory();
                $productCategory->product_id = $product->id;
                $productCategory->status = EStatus::ACTIVE;
                $productCategory->created_at = now();
                $productCategory->created_by = $loggedInUserId;
                if (Arr::get($data, 'childCategoryId')) {
                    $productCategory->category_id = Arr::get($data, 'childCategoryId', $productCategory->category_id);
                } else {
                    $productCategory->category_id = Arr::get($data, 'categoryId', $productCategory->category_id);
                }
                $productCategory->save();
            }

            $productCategoryAttribute = $product->productCategoryAttributes;
            if (count($productCategoryAttribute) > 0) {
                foreach ($productCategoryAttribute as $key) {
                    $key->status = EStatus::DELETED;
                    $key->deleted_at = now();
                    $key->deleted_by = $loggedInUserId;
                    $key->save();
                }
            }

            for ($i = 0; $i < Arr::get($data, 'numberOfAttribute'); $i++) {
                $arr = explode(',', Arr::get($data, 'attributeName')[$i]);
                for ($j = 0; $j < count($arr); $j++) {
                    $productCategoryAttribute = new ProductCategoryAttribute();
                    $productCategoryAttribute->product_id = $product->id;
                    $productCategoryAttribute->category_attribute_id = Arr::get($data, 'attribute')['id'][$i];
                    $productCategoryAttribute->status = EStatus::ACTIVE;
                    $productCategoryAttribute->value = [
                        'value' => $arr[$j]
                    ];
                    $productCategoryAttribute->save();
                }
            }

            $video = $product->video;
            if (count($video) > 0) {
                foreach ($video as $key) {
                    $key->status = EStatus::DELETED;
                    $key->save();
                }
            }

            $imageProduct = $product->image;
            if (count($imageProduct) > 0) {
                foreach ($imageProduct as $key) {
                    $key->status = EStatus::DELETED;
                    $key->save();
                }
            }
            if (!empty(Arr::get($data, 'oldResource')['image'])) {
                $arrImage = Arr::get($data, 'oldResource')['image'];
                for ($i = 0; $i < count($arrImage); $i++) {
                    $imageProduct = new ProductResource();
                    $imageProduct->product_id = $product->id;
                    $imageProduct->path_to_resource = $arrImage[$i];
                    $imageProduct->type = EResourceType::IMAGE;
                    $imageProduct->status= EStatus::ACTIVE;
                    $imageProduct->created_at = now();
                    $imageProduct->created_by = $loggedInUserId;
                    $imageProduct->save();
                }
            }

            if (!empty(Arr::get($data, 'oldResource')['video'])) {
                $arrVideo = Arr::get($data, 'oldResource')['video'];
                for ($i = 0; $i < count($arrVideo); $i++) {
                    $video = new ProductResource();
                    $video->product_id = $product->id;
                    $video->path_to_resource = $arrVideo[$i];
                    $video->type = EResourceType::VIDEO;
                    $video->status= EStatus::ACTIVE;
                    $video->created_at = now();
                    $video->created_by = $loggedInUserId;
                    $video->save();
                }
            }
            if (!empty(Arr::get($data, 'video'))) {
                for ($i = 0; $i < count(Arr::get($data, 'video')); $i++) {
                    $video = new ProductResource();
                    $video->product_id = $product->id;
                    if (!Str::containsAll(Arr::get($data, 'video')[$i], ['https','youtu']) &&
                        !Str::containsAll(Arr::get($data, 'video')[$i], ['https','tiktok'])) {
                        $relativePath = FileUtility::storeFile(EStoreFileType::PRODUCT_RESOURCE, Arr::get($data, 'video')[$i]);
                        //FileUtility::removeFiles([$imageProduct->path_to_resource]);
                        $video->path_to_resource = $relativePath;
                        $fileToDeleteIfError[] = $relativePath;
                    } else {
                        $video->path_to_resource = Arr::get($data, 'video')[$i];
                    }

                    $video->type = EResourceType::VIDEO;
                    $video->status= EStatus::ACTIVE;
                    $video->created_at = now();
                    $video->created_by = $loggedInUserId;
                    $video->save();
                }
            }

            if (!empty(Arr::get($data, 'image'))) {
                for ($i = 0; $i < count(Arr::get($data, 'image')); $i++) {
                    $imageProduct = new ProductResource();
                    $image = Arr::get($data, 'image')[$i];

                    $relativePath = FileUtility::storeFile(EStoreFileType::PRODUCT_RESOURCE, $image);
                    FileUtility::removeFiles([$imageProduct->path_to_resource]);

                    $imageProduct->product_id = $product->id;
                    $imageProduct->path_to_resource = $relativePath;
                    $fileToDeleteIfError[] = $relativePath;
                    $imageProduct->type = EResourceType::IMAGE;
                    $imageProduct->status= EStatus::ACTIVE;
                    $imageProduct->created_at = now();
                    $imageProduct->created_by = $loggedInUserId;
                    $imageProduct->save();
                }
            }

            return ['error' => EErrorCode::NO_ERROR, 'product' => $product];
        });
    }

    public function toggleInterestProduct($code, $userId, $isInterest) {
        $product = $this->productRepository->getByCode($code);
        if(empty($product)) {
            return [
                'error' => EErrorCode::ERROR,
                'msg' => 'invalid code',
            ];
        }
        //check interest exist
        $didInterestExist = $this->userInterestRepository
            ->didInterestExist($userId,ETableName::PRODUCT, $product->id);
        if($didInterestExist) {
            if($isInterest) {
                return [
                    'error' => EErrorCode::ERROR,
                    'msg' => 'Bạn đã lưu sản phẩm này trước đó',
                ];
            }
            $this->userInterestRepository->deleteInterest($userId,ETableName::PRODUCT, $product->id);
            return [
                'error' => EErrorCode::NO_ERROR,
                'msg' => 'Hủy lưu sản phẩm thành công',
            ];
        }
        if(!$isInterest) {
            return [
                'error' => EErrorCode::ERROR,
                'msg' => 'Hủy lưu sản phẩm không thành công, bạn chưa lưu sản phẩm này',
            ];
        }
        $interest = new UserInterest();
        $interest->user_id = $userId;
        $interest->table_name = ETableName::PRODUCT;
        $interest->table_id = $product->id;
        $interest->created_by = $userId;
        $interest->save();
        return ['error' => EErrorCode::NO_ERROR, 'interest' => $interest];
    }

    public function getBranchShop($shopId) {
        return $this->productRepository->getBranchShop($shopId);
    }
}
