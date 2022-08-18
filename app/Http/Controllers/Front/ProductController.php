<?php

namespace App\Http\Controllers\Front;

use App\Constant\DefaultConfig;
use App\Enums\EErrorCode;
use App\Helpers\ConfigHelper;
use App\Helpers\ValidatorHelper;
use \App\Http\Controllers\Controller;
use App\Constant\ConfigKey;
use App\Enums\EDateFormat;
use App\Enums\ECategoryType;
use App\Enums\EStatus;
use App\Enums\EApprovalStatus;
use App\Enums\EProductType;
use App\Enums\ESubscriptionPriceType;
use App\Models\UserInterest;
use App\Services\AreaService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ProductService;
use App\Services\SubscriptionPriceService;
use App\Services\SubscriptionService;
use App\Services\OrderService;
use App\Http\Requests\Product\SaveInfoRequest;
use App\Http\Requests\Product\SaveReportRequest;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Helpers\FileUtility;
use App\Constant\ConfigTableName;
use App\Services\ConfigService;
use App\Services\CategoryService;
use App\Services\IssueReportService;
use App\Helpers\StringUtility;
use App\Enums\EVideoType;
use App\Constant\SessionKey;

class ProductController extends Controller {

	protected ProductService $productService;
	protected SubscriptionPriceService $subscriptionPriceService;
	protected OrderService $orderService;
	protected SubscriptionService $subscriptionService;
	protected ConfigService $configService;
	protected CategoryService $categoryService;
	protected IssueReportService $issueReportService;
	protected AreaService $areaService;

	public function __construct(ProductService $productService,
                                SubscriptionPriceService $subscriptionPriceService,
                            	OrderService $orderService,
                            	SubscriptionService $subscriptionService,
                            	ConfigService $configService,
                            	CategoryService $categoryService,
                            	AreaService $areaService,
                            	IssueReportService $issueReportService) {
		$this->productService = $productService;
		$this->subscriptionPriceService = $subscriptionPriceService;
		$this->orderService = $orderService;
		$this->subscriptionService = $subscriptionService;
		$this->configService = $configService;
		$this->categoryService = $categoryService;
		$this->areaService = $areaService;
		$this->issueReportService = $issueReportService;
	}

	public function showProductOfShopView($shopId = null) {
		if (!auth()->id() || (auth()->user()->getShop && auth()->user()->getShop->id != $shopId)) {
			return redirect()->route('home');
		}
		return view('front.product.shop-product-list', [
			'shopId' => $shopId,
		]);
	}

	public function showCreateProductView($shopId = null, $code = null) {
		if (!auth()->id()) {
			return redirect()->route('home');
		}
		return view('front.product.create', [
			'shopId' => $shopId,
			'code' => $code,
		]);
	}

	public function getProductList() {
		$acceptFields = ['q', 'shopId', 'getForHomepage', 'getSubscription','getSavedProduct',
            'approvalStatus', 'categoryId', 'orderBy', 'orderDirection', 'notId', 'attribute', 'status', 'minPrice', 'maxPrice'];
		$filters = [
			'pageSize' => request('pageSize'),
			'page' => (int)request('page'),
		];
        if (request('filter.getSavedProduct') || request('filter.getForHomepage')) {
            $filters['approval_status'] = EApprovalStatus::APPROVED;
        }
		foreach ($acceptFields as $field) {
			if (!request()->filled("filter.$field")) {
                continue;
            }
            if ($field == 'orderBy' || $field == 'orderDirection') {
            	$filters[$field] = request("filter.$field");
            } else {
            	$filters[Str::snake($field)] = request("filter.$field");
            }
		}
		if (request('filter.getForShop') && empty($filters['shop_id'])) {
			$filters['shop_id'] = auth()->user()->getShop->id;
		}
		if (request("filter.areaId")) {
            //lấy về toàn bộ id của các child area thuộc về area_id trong filter
            $area = $this->areaService->getById(intval(request("filter.areaId")));
            $allAreaChild = $area->allChildAreas;
            $areaIdFilterList = [];
            array_push($areaIdFilterList,$area->id);
            if(count($allAreaChild) > 0) {
                foreach ($allAreaChild as $areaChild1) {
                    array_push($areaIdFilterList, $areaChild1->id);
                    if(count($areaChild1->allChildAreas) > 0) {
                        foreach ($areaChild1->allChildAreas as $areaChild2) {
                            array_push($areaIdFilterList, $areaChild2->id);
                        }
                    }
                }
            }
            $filters['area_id'] = $areaIdFilterList;
		}
//        $isCheckSaved = false;
//        $interestProductOfUser = null;
		if(auth()->id()) {
		    $filters['user_id'] = auth()->id();
//            $isCheckSaved = true;
//            $interestProductOfUser = auth()->user()->interestProduct;
        }
        $filters['status'] = EStatus::ACTIVE;
        if (!empty($filters['approval_status']) && $filters['approval_status'] == EApprovalStatus::DENY && !empty($filters['shop_id'])) {
        	$filters['orderBy'] = 'approved_at';
        	$filters['orderDirection'] = 'desc';
        }
        $filters['get_for_user'] = true;

		$products = $this->productService->getByOptions($filters);
		$tmp = $products->map(function(Product $product) {
			$image = $product->image;
			if ($product->status == EStatus::ACTIVE) {
				$product->statusStr = EApprovalStatus::valueToLocalizedName($product->approval_status);
			}else {
				$product->statusStr = EStatus::valueToLocalizedName($product->status);
			}
			//$productCategory = $product->categories;
			if (!empty(request('filter.getForShop')) && !empty(auth()->user()->getShop) && $product->getShop->id == auth()->user()->getShop->id) {
				$redirectTo = route('shop.product.detail', [
					'shopId' => $product->getShop->id,
					'code' => $product->code
				], false);
			} else {
				$redirectTo = route('product.detail', [
					'code' => $product->code
				], false);
			}
			$area= [];
			$lowestLevelArea = $product->getShop->getArea;
			if(!empty($lowestLevelArea)) {
			    array_push($area, $lowestLevelArea);
			    $nextLevelArea = $lowestLevelArea->parentArea;
			    if(!empty($nextLevelArea)) {
			        array_push($area, $nextLevelArea);
			        $highestLevelArea = $nextLevelArea->parentArea;
			        if(!empty($highestLevelArea)) {
			            array_push($area, $highestLevelArea);
                    }
                }
            }
			return [
				'productId' => $product->id,
				'code' => $product->code,
				'name' => $product->name,
				'image' => !empty($image->first()) ?
                    FileUtility::getFileResourcePath($image->first()->path_to_resource) :
                    DefaultConfig::FALLBACK_IMAGE_PATH,
				'description' => $product->description,
				'price' => number_format($product->price, 0, '.', '.') . ' đ',
				'weight' => $product->weight,
				'unit' => $product->unit,
                'isSaved' => $product->savedId,
				'status' => $product->statusStr,
				'createdAt' => Carbon::parse($product->created_at)
                    ->format(EDateFormat::STANDARD_DATE_FORMAT),
				'type' => $product->type,
				'area' => $area,
				'prioritize' => $product->getSubscription,
				'redirectTo' => $redirectTo,
                'shop_id' => $product->shop_id,
			];
		});
		$products->setCollection($tmp);
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'products' => $products,
		]);
	}

	public function saveProduct(SaveInfoRequest $request) {
		$data = $request->validated();
		$data['numberOfAttribute'] = request('numberOfAttribute');
		$data['shopId'] = auth()->user()->getShop->id;
		$data['image'] = request('image');
		$data['oldResource'] = request('oldResource');
		$data['video'] = request('video');
		$data['code'] = request('code');
		$result = $this->productService->saveProduct($data, auth()->id());
		if (!empty($result['product'])) {
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'redirectTo' => route(
					'shop.product.detail', [
						'shopId' => auth()->user()->getShop->id,
						'code' => $result['product']->code,
					], false
				),
				'msg' => !empty(request('code')) ? 'Cập nhật tin đăng thành công, vui lòng chờ admin duyệt' : 'Đăng tin thành công, vui lòng đợi admin duyệt'
			]);
		}
		return response()->json([
			'error' => EErrorCode::ERROR,
			'msg' => __('common/common.there_was_an_error_in_the_processing'),
		]);
	}

	public function getInfoProduct() {
		$filters = [
            //'shop_id' => auth()->user()->getShop->id,
            'get_all_status' => true,
            'code' => request('code'),
            'first' => true,
		];
		$product = $this->productService->getByOptions($filters);
		if (empty($product)) {
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'redirectTo' => route('home'),
			]);
		}

		$shop = $product->getShop;
		$video = $product->video;
		if (count($video) > 0) {
			foreach ($video as $key) {
				if(Str::containsAll($key->path_to_resource, ['https','youtu'])) {
	                $key->typeVideo = EVideoType::YOUTUBE_VIDEO;
	            } elseif(Str::containsAll($key->path_to_resource, ['https','tiktok'])) {
	                $key->typeVideo = EVideoType::TIKTOK_VIDEO;
	                $tiktokResource = StringUtility::getLinkTikTok($key->path_to_resource);
	                $key->path = $tiktokResource['src'];
	            } else {
	                $key->typeVideo = EVideoType::INTERNAL_VIDEO;
//	                $key->path = FileUtility::getFileResourcePath($key->path_to_resource);
	                $key->path = config('app.resource_url_path') . '/' . $key->path_to_resource;
	            }
			}
		}
		if (auth()->id()) {
			$savedProduct = $this->productService->getByOptions([
				'get_saved_product' => true,
				'first' => true,
				'id' => $product->id,
				'user_id' => auth()->id()
			]);
		}
		$category = $product->productCategories[0]->categories;
		$arr = [$category];
		$breadcrumb = $this->getParentCategory($category, $arr);
		$data = [
			'productId' => $product->id,
			'code' => $product->code,
			'name' => $product->name,
			'image' => null,
			'video' => $video,
			'description' => $product->description,
			'price' => (int)$product->price,
			'weight' => $product->weight,
			'unit' => $product->unit,
			'type' => $product->type,
			'typeStr' => EProductType::valueToName($product->type),
			'category' => $category,
			'breadcrumb' => $breadcrumb,
			'attribute' => [],
			// 'priceStr' => number_format($product->price, 0, '.', '.') . ' đ/' . $product->unit,
			'priceStr' => number_format($product->price, 0, '.', '.'),
			// 'area' => $shop->address . ', ' . $product->getShop->getArea->name,
			'area' => $shop->address,
			'latitude' => $shop->latitude,
			'longitude' => $shop->longitude,
			'subscription' => null,
			'status' => $product->status,
			'statusStr' => EStatus::valueToLocalizedName($product->status),
			'approvalStatus' => $product->approval_status,
			'approvalStatusStr' => $product->approval_status == EApprovalStatus::APPROVED ? 'Đang bán' : EApprovalStatus::valueToLocalizedName($product->approval_status),
			'shop' => [
				'id' => $shop->id,
			],
			'isAddToCart' => !(auth()->id() && !empty(auth()->user()->getShop) && $product->getShop->id == auth()->user()->getShop->id),
			'isSaved' => !empty($savedProduct) ? $savedProduct->savedId : null,
			'createdAt' => Carbon::parse($product->created_at)->format(EDateFormat::STANDARD_DATE_FORMAT),
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

		if ((bool)request('getSubscription')) {
			$data['subscriptionPrices'] = $this->subscriptionPriceService->getByOptions([
				'type' => ESubscriptionPriceType::PACKAGE_PUSH_PRODUCT,
				'orderBy' => 'num_day',
				'orderDirection' => 'asc',
			]);
			foreach ($data['subscriptionPrices'] as $key) {
				$key->price = number_format($key->price, 0, '.', '.') . ' đ';
			}
			if (!empty($product->getSubscription)) {
				$subscriptionPrices = $this->subscriptionPriceService->getByOptions([
					'id' => json_decode($product->getSubscription->payment_meta)->subscriptionPriceId,
					'first' => true,
				]);
				$data['subscription'] = [
					'valid_from' => get_display_date_for_ajax($product->getSubscription->valid_from),
					'valid_to' => get_display_date_for_ajax($product->getSubscription->valid_to),
					'valid_date' => Carbon::parse(now())->startOfDay()->diffInDays(Carbon::parse($product->getSubscription->valid_to)->startOfDay()),
					'name' => $subscriptionPrices->name,
					'description' => $subscriptionPrices->description,
					'paymentStatus' => $product->getSubscription->payment_status,
					'price' => number_format($subscriptionPrices->price, 0, '.', '.') . ' đ'
				];
			}
		}
		$data['packagePushProductWaiting'] = $product->packagePushProductWaiting ?
			json_decode($product->packagePushProductWaiting->payment_meta)->subscriptionPriceId : null;
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'products' => $data,
		]);
	}

	public function showProductDetailOfShopView($shopId = null, $code = null) {
		if (!auth()->id() || (auth()->user()->getShop && auth()->user()->getShop->id != $shopId)) {
			return redirect()->route('home');
		}
		if (request()->route()->getName() == 'shop.product.detail') {
			$productOfShop = 'true';
            // dd($productOfShop);
		} else {
			$productOfShop = 'false';
		}
		$filters = [
			'code' => $code,
            'first' => true,
            'get_all_status' => true,
		];
		$product = $this->productService->getByOptions($filters);

		//không cho phép vào chi tiết sản phẩm của shop khác với quyền chủ shop
		if($product->getShop->id !== auth()->user()->getShop->id) {
            return redirect()->route('home');
        }
		$data = [
			'url' => config('app.url') . '/' . request()->path(),
			'title' => $product->name,
			'image' => !empty($product->image->first()) ? FileUtility::getFileResourcePath($product->image->first(), DefaultConfig::FALLBACK_IMAGE_PATH) : null,
			'description' => $product->description,
		];
        // dd($productOfShop);
		return view('front.product.detail', [
			'code' => $code,
			'productOfShop' => $productOfShop,
			'shareData' => $data,
			'shopId' => $shopId,
		]);
	}

	public function showProductDetailView($code = null) {
		session()->put(SessionKey::ROUTE_INTENDED, url()->current());
		if (request()->route()->getName() == 'shop.product.detail') {
			$productOfShop = 'true';
		} else {
			$productOfShop = 'false';
		}
		$filters = [
			'code' => $code,
            'first' => true,
		];
		$product = $this->productService->getByOptions($filters);
        //lấy danh sách chi nhánh cửa hàng của shop
        $branchShop = $this->productService->getBranchShop($product->shop_id);
        // dd($branchShop);
		if ((auth()->id() && !empty(auth()->user()->getShop) && $product->getShop->id == auth()->user()->getShop->id)) {
			return redirect()->route('shop.product.detail', [
				'shopId' => $product->getShop->id,
				'code' => $product->code
			]);
		}
		if (empty($product)) {
			return redirect()->route('home');
		}
		$data = [
			'url' => config('app.url') . '/' . request()->path(),
			'title' => $product->name,
			'image' => !empty($product->image->first()) ? FileUtility::getFileResourcePath($product->image->first()->path_to_resource, DefaultConfig::FALLBACK_IMAGE_PATH) : null,
			'description' => $product->description,
		];
		$questionsConfig = $this->configService->getByName(ConfigKey::QUESTIONS_ASK);
		$questions = !empty($questionsConfig) ? explode(',', str_replace(['{', '}', '"'], '', $questionsConfig->text_arr_value)) : [];
		$reportlist = $this->categoryService->getByOptions([
			'type' => ECategoryType::ISSUE_REPORT
		]);
		foreach ($reportlist as $item) {
			$item->childCategory = $item->childCategories;
		}
		return view('front.product.detail', [
			'code' => $code,
			'productOfShop' => $productOfShop,
			'shareData' => $data,
			'questions' => $questions,
			'reportlist' => $reportlist,
            'branchShop' => $branchShop
		]);
	}

	public function getParentCategory($category, array &$breadcrumb) {
		if (!empty($category->parent_category_id)) {
			array_push($breadcrumb, $category->parentCategories[0]);
			$this->getParentCategory($category->parentCategories[0], $breadcrumb);
		}
		return array_reverse($breadcrumb);
	}

	public function showProductView() {
		return view('front.product.list');
	}


	public function addToCart() {
		if (!auth()->id()) {
			return response()->json([
				'error' => EErrorCode::ERROR,
				'redirectTo' => route('login'),
			]);
		}
		$data = request()->all();
		$result = $this->orderService->addToCart($data, auth()->id(), now());
		if ($result['error'] == EErrorCode::NO_ERROR) {
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => 'Thêm vào giỏ hàng thành công',
				'redirectTo' => route('cart', [], false),
			]);
		}
		return response()->json([
			'error' => EErrorCode::ERROR,
			'msg' => __('common/common.there_was_an_error_in_the_processing'),
		]);
	}

	public function savePayment() {
		$data = [
			'id' => null,
			'table_id' => request('productId'),
			'table_name' => ConfigTableName::PRODUCT,
			'payment_status' => null,
			'subscriptionPriceId' => request('subscriptionPriceId')
		];
		$subscriptionPrice = $this->subscriptionPriceService->getById(Arr::get($data, 'subscriptionPriceId'));
		// $data['validFrom'] = now()->copy();
		// $data['validTo'] = now()->copy()->addDay($subscriptionPrice->num_day);
		$subscription = $this->subscriptionService->getByOptions([
			'table_id' => request('productId'),
			'table_name' => ConfigTableName::PRODUCT,
			'user_id' => auth()->id(),
			'null_payment_status' => true,
			'first' => true,
		]);
		if (!empty($subscription)) {
			$data['id'] = $subscription->id;
		}
		$saveSubscription = $this->subscriptionService->saveSubscription($data, auth()->id());
		if ($saveSubscription['error'] == EErrorCode::NO_ERROR) {
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'subscriptionPriceId' => request('subscriptionPriceId')
			]);
		}
	}

    public function showSavedProductView() {
         if (!auth()->id()) {
         	return redirect()->route('login');
         }
        return view('front.product.saved-product');
    }

    public function interestProduct() {
        if (!auth()->id()) {
            return response()->json([
                'error' => EErrorCode::UNAUTHORIZED,
                'redirectTo' => route('login'),
            ]);
        }

        if(!request()->filled("code")){
            return response()->json([
                'error' => EErrorCode::ERROR,
                'msg' => 'no code',
            ]);
        }
        $code = request('code');
        $userId = auth()->user()->id;
        $interest = $this->productService->toggleInterestProduct($code, $userId, true);
        if ($interest['error'] !== EErrorCode::NO_ERROR) {
            return response()->json($interest);
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => 'Lưu sản phẩm thành công',
        ]);

    }

    public function unInterestProduct() {
        if (!auth()->id()) {
            return response()->json([
                'error' => EErrorCode::UNAUTHORIZED,
                'redirectTo' => route('login'),
            ]);
        }

        if(!request()->filled("code")){
            return response()->json([
                'error' => EErrorCode::ERROR,
                'msg' => 'no code',
            ]);
        }
        $code = request('code');
        $userId = auth()->user()->id;
        $interest = $this->productService->toggleInterestProduct($code,$userId,false);
        if ($interest['error'] !== EErrorCode::NO_ERROR) {
            return response()->json($interest);
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => 'Hủy lưu sản phẩm thành công',
        ]);

    }

    public function reportProduct(SaveReportRequest $request) {
    	if (!auth()->id()) {
			return redirect()->route('home');
		}
    	$data = $request->validated();
    	$data['productId'] = request('productId');
    	$saveReport = $this->issueReportService->saveReport($data, now(), auth()->id());
    	if ($saveReport['error'] == EErrorCode::NO_ERROR) {
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => 'Báo cáo thành công'
			]);
		}
    }

    public function deleteProduct() {
		try {
			$id = request('id');
			$shopId = request('shopId');
			$delete = $this->productService->deleteProduct($id, auth()->id());
			if ($delete['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($delete);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'redirectTo' => route('shop.product', [
					'shopId' => $shopId,
				]),
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
}
