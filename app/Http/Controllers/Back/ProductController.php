<?php

namespace App\Http\Controllers\Back;

use App\Constant\DefaultConfig;
use App\Constant\SessionKey;
use App\Enums\EErrorCode;
use App\Enums\EProductStatus;
use App\Enums\EProductType;
use App\Enums\ESubscriptionPriceType;
use App\Enums\EVideoType;
use App\Helpers\ConfigHelper;
use App\Helpers\StringUtility;
use App\Helpers\ValidatorHelper;
use \App\Http\Controllers\Controller;
use App\Constant\ConfigKey;
use App\Enums\EDateFormat;
use App\Enums\EStatus;
use App\Enums\EResourceType;
use App\Enums\EApprovalStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
//use App\Http\Requests\Shop\SaveInfoRequest;
use App\Services\ProductService;
use App\Services\ProductCategoryAttributeService;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Product;
use App\Models\ProductResource;
use App\Models\ProductCategory;
use App\Helpers\FileUtility;
use Illuminate\Support\Facades\URL;

class ProductController extends Controller {
	private ProductService $productService;

	public function __construct(ProductService $productService) {
		$this->productService = $productService;
	}

	public function getProductList() {
        $tz = session(SessionKey::TIMEZONE);
		$acceptFields = ['q', 'shop_id', 'createdAtFrom', 'createdAtTo', 'productStatus', 'id'];
		$filters = [
			'admin_product_list' => true,
			'pageSize' => request('pageSize'),
            'orderBy' => 'created_at',
            'orderDirection' => 'desc',
		];

		foreach ($acceptFields as $field) {
			if (!request()->filled("filter.$field")) {
                continue;
			}

			if ($field === 'createdAtFrom' || $field === 'createdAtTo') {
                if (Arr::has(request('filter'), $field)) {
                    try {
                        $date = Carbon::createFromFormat(
							EDateFormat::DEFAULT_DATEPICKER_INPUT_FORMAT_WITH_TZ,
							request("filter.$field"). "$tz"
						);
                        $filters[$field] = $date;
                    } catch (\Exception $e) {
                    }
                }
            } else if ($field === 'productStatus') {
                switch (request("filter.$field")) {
                    case EProductStatus::ACTIVE:
                        $filters['status'] = EStatus::ACTIVE;
                        $filters['approval_status'] = EApprovalStatus::APPROVED;
                        break;
                    case EProductStatus::WAITING:
                        $filters['status'] = EStatus::ACTIVE;
                        $filters['approval_status'] = EApprovalStatus::WAITING;
                        break;
                    case EProductStatus::REJECTED:
                        $filters['status'] = EStatus::ACTIVE;
                        $filters['approval_status'] = EApprovalStatus::DENY;
                        break;
                    case EProductStatus::DELETED:
                        $filters['status'] = EStatus::DELETED;
                        break;
                    case EProductStatus::WAITING_AND_ACTIVE:
                        $filters['status'] = EStatus::ACTIVE;
                        $filters['get_product_waiting_and_active'] = true;
                        break;

                }
            } else {
                $filters[$field] = request("filter.$field");
            }
		}
		$products = $this->productService->getByOptions($filters);
		$tmp = $products->map(function (Product $product) {
			$image = $product->image;
			$video = $product->video;
			if (count($image) > 0) {
			    $product->thumbnail = get_image_url([
                    'path' => $image[0]->path_to_resource,
                    'op' => 'thumbnail',
                    'w' => 100,
                    'h' => 100,
                ]);
				foreach ($image as $key) {
					$key->path = FileUtility::getFileResourcePath($key->path_to_resource);
				}
			}
			$products['imagelist'] = $product->image;

			if (count($video) > 0) {
				foreach ($video as $value) {
					if (!empty($value->path_to_resource)) {
						$value->path = config('app.resource_url_path') . "/$value->path_to_resource";
					}
				}
			}
			if ($product->status == EStatus::ACTIVE) {
				$product->statusStr = EApprovalStatus::valueToLocalizedName($product->approval_status);
			}else {
				$product->statusStr = EStatus::valueToLocalizedName($product->status);
			}
			return [
				'id' => $product->id,
				'code' => $product->code,
				'name' => $product->name,
				'image' => $product->thumbnail,
				'imagelist' => $product->image,
				'youtubeId' => !empty($value->path_to_resource) ? $value->path_to_resource : null,
				'description' => $product->description,
				'price' => number_format($product->price) . ' VNĐ',
				'weight' => $product->weight,
				'unit' => $product->unit,
				'statusStr' => $product->statusStr,
				'status' => $product->status,
				'approvalStatus' => $product->approval_status,
				'createdAt' => Carbon::parse($product->created_at)->format(EDateFormat::STANDARD_DATE_FORMAT),
				'type' => $product->type,
			];
		});
		$products->setCollection($tmp);
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'data' => $products,
		]);
	}

	public function getProductInfo($productId) {
		$option = [
			'id' => $productId,
			'first' => true
		];
		$product = $this->productService->getByOptions($option);

		$arrImage = [];
		$arrVideo = [];
		if (is_array($product->image) && count($image) > 0) {
			foreach ($image as $key) {
				array_push($arrImage, get_image_url([
					'path' => $key->path_to_resource,
					'op' => 'thumbnail',
					'w' => 100,
					'h' => 100,
				]));
			}
		}
		if (is_array($product->video) && count($video) > 0) {
			foreach ($image as $key => $value) {
				array_push($arrVideo, config('app.resource_url_path') . "/$key->path_to_resource");
			}
		}
		if ($product->status == EStatus::ACTIVE) {
			$product->statusStr = EApprovalStatus::valueToLocalizedName($product->approval_status);
		}else {
			$product->statusStr = EStatus::valueToLocalizedName($product->status);
		}

		$categories = [];
		$productCategory = $product->productCategories;
		if (count($productCategory) > 0) {
			foreach ($productCategory as $key) {
				$data = $key->first()->categories;
				$category = [
					'id' => $data->id,
					'name' => $data->name,
				];
				array_push($categories, $category);
			}
		}
		$attributeOfProduct = $product->productCategoryAttributes;
		if (count($attributeOfProduct) > 0) {
			foreach ($attributeOfProduct as $key) {
				$key->valueName = json_decode($key->value)->value;
			}
		}

		$data = [
			'productId' => $product->id,
			'code' => $product->code,
			'name' => $product->name,
			'image' => $arrImage,
			'video' => $arrVideo,
			'description' => $product->description,
			'price' => number_format($product->price) . ' VNĐ',
			'weight' => $product->weight,
			'unit' => $product->unit,
			'status' => $product->statusStr,
			'categories' => $categories,
			'attribute' => $attributeOfProduct,
			'createdAt' => Carbon::parse($product->created_at)->format(EDateFormat::STANDARD_DATE_FORMAT),
			'type' => $product->type,
		];
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'product' => $data,
		]);
	}

	public function getProductDetailForModal($productId) {
        $filters = [
            'get_all_status' => true,
            'id' => $productId,
            'first' => true,
        ];

        $product = $this->productService->getByOptions($filters);
        if (empty($product)) {
            return response()->json([
                'error' => EErrorCode::NO_ERROR,
                'msg' => __('common/error.invalid-request-data'),
            ]);
        }

        $video = $product->video;
        if (count($video) > 0) {
            foreach ($video as $key) {
                if(Str::containsAll($key->path_to_resource, ['https','youtu'])) {
                    $key->type = EVideoType::YOUTUBE_VIDEO;
                } elseif(Str::containsAll($key->path_to_resource, ['https','tiktok'])) {
                    $key->type = EVideoType::TIKTOK_VIDEO;
                    $tiktokResource = StringUtility::getLinkTikTok($key->path_to_resource);
                    $key->path = $tiktokResource['src'];
                } else {
                    $key->type = EVideoType::INTERNAL_VIDEO;
                    $key->path = config('app.resource_url_path') . "/$key->path_to_resource";
//                    $key->path = FileUtility::getFileResourcePath($key->path_to_resource);
                }
            }
        }
        $data = [
            'productId' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'createdAt' => $product->created_at,
            'image' => null,
            'video' => $video,
            'description' => $product->description,
            'price' => (int)$product->price,
            'weight' => $product->weight,
            'unit' => $product->unit,
            'type' => $product->type,
            'typeStr' => EProductType::valueToName($product->type),
            'attribute' => [],
            'priceStr' => number_format($product->price, 0, '.', '.'),
            'status' => $product->status,
            'statusStr' => EStatus::valueToLocalizedName($product->status),
        ];
        foreach ($product->productCategoryAttributes as $key) {
            $key->attributeName = $key->getCategoryAttribute->attribute_name;
            $key->data = json_decode($key->value);
        }
        $attributeList = $product->productCategoryAttributes
            ->groupBy('category_attribute_id');
        foreach ($attributeList as $index => $value) {
            $attribute = [
                'attributeName' => null,
                'id' => $index,
                'value' => [],
            ];
            foreach ($value as $key) {
                $attribute['attributeName'] = $key->attributeName;
                array_push($attribute['value'], $key->data->value);
            }
            array_push($data['attribute'], $attribute);
        }

        if (count($product->image) > 0) {
            foreach ($product->image as $key) {
                $key->path = FileUtility::getFileResourcePath($key->path_to_resource, DefaultConfig::FALLBACK_IMAGE_PATH);
            }
        }
        $data['image'] = $product->image;

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'product' => $data,
        ]);
    }

	public function deleteProduct() {
		try {
			$id = request('id');
			$delete = $this->productService->deleteProduct($id, auth()->id());
			if ($delete['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($delete);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => __('common/common.delete-data-success', [
					'objectName' => __('back/product.object_name')
				])
			]);
		} catch (\Exception $e) {
			logger()->error('Fail to delete product', [
				'error' =>  $e
			]);
			return response()->json([
				'error' => EErrorCode::ERROR,
				'msg' => __('common/common.there_was_an_error_in_the_processing'),
			]);
		}
	}

	public function approveOrRejectProduct() {
		try {
			$id = request('id');
			$approvalStatus = request('approval_status');
			$result = $this->productService->approveOrRejectProduct($id, $approvalStatus, auth()->id());
			if ($result['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($result);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => $approvalStatus == EApprovalStatus::APPROVED ? __('back/product.msg.approved_success') : __('back/product.msg.reject_success')
			]);
		} catch (\Exception $e) {
			logger()->error('Fail to change approval status', [
				'error' =>  $e
			]);
			return response()->json([
				'error' => EErrorCode::ERROR,
				'msg' => __('common/common.there_was_an_error_in_the_processing'),
			]);
		}
	}
}
