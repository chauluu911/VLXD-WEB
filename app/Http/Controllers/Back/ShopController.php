<?php

namespace App\Http\Controllers\Back;

use App\Constant\DefaultConfig;
use App\Constant\SessionKey;
use App\Enums\EErrorCode;
use App\Enums\EOrderStatus;
use App\Enums\EResourceType;
use App\Enums\EVideoType;
use App\Helpers\ConfigHelper;
use App\Helpers\StringUtility;
use App\Helpers\ValidatorHelper;
use \App\Http\Controllers\Controller;
use App\Constant\ConfigKey;
use App\Enums\EDateFormat;
use App\Enums\EStatus;
use App\Enums\EPaymentStatus;
use App\Services\ShopResourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Enums\ELanguage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Requests\Shop\SaveInfoRequest;
use App\Services\ShopService;
use App\Services\OrderService;
use App\Services\UserService;
use App\Enums\EUserType;
use App\Enums\ECustomerType;
use App\Enums\EDeliveryStatus;
use App\Enums\EPaymentMethod;
use App\Helpers\FileUtility;
use Symfony\Component\HttpFoundation\Response;

class ShopController extends Controller {
	private ShopService $shopService;
	private OrderService $orderService;
	private UserService $userService;
	private ShopResourceService $shopResourceService;

	public function __construct(ShopService $shopService,
                                OrderService $orderService,
                                UserService $userService,
                                ShopResourceService $shopResourceService) {
		$this->shopService = $shopService;
		$this->orderService = $orderService;
		$this->userService = $userService;
		$this->shopResourceService = $shopResourceService;
	}

	public function getShopList() {
        $tz = session(SessionKey::TIMEZONE);
		$acceptFields = ['q', 'userId', 'createdAtFrom', 'createdAtTo', 'status', 'payment_status'];
		$filters = [
			'admin_shop_list' => true,
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
                        $date = Carbon::createFromFormat(EDateFormat::DEFAULT_DATEPICKER_INPUT_FORMAT_WITH_TZ, request('filter')[$field]." $tz");
                        $filters[$field] = $date;
                    } catch (\Exception $e) {
                    }
                }
            } else {
                $filters[$field] = request("filter.$field");
            }
		}
		$shops = $this->shopService->getByOptions($filters);
		foreach ($shops as $key => $value) {
			$value->avatar = !empty($value->avatar_path) ? FileUtility::getFileResourcePath($value->avatar_path) : DefaultConfig::FALLBACK_USER_AVATAR_PATH;
			$value->strStatus = EStatus::valueToLocalizedName($value->status,true);
			$value->strPaymentStatus = EPaymentStatus::valueToLocalizedName($value->payment_status);
			$value->createdAt =  Carbon::parse($value->created_at)->format(EDateFormat::STANDARD_DATE_FORMAT);
			$value->identityDate =  Carbon::parse($value->identity_date)->format(EDateFormat::STANDARD_DATE_FORMAT);
		}
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'data' => $shops,
		]);
	}

	public function deleteShop() {
		try {
			$id = request('id');
			$delete = $this->shopService->deleteShop($id, auth()->id());
			if ($delete['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($delete);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => __('common/common.delete-data-success', [
					'objectName' => __('back/shop.object_name')
				])
			]);
		} catch (\Exception $e) {
			logger()->error('Fail to delete shop', [
				'error' =>  $e
			]);
			return response()->json([
				'error' => EErrorCode::ERROR,
				'msg' => __('common/common.there_was_an_error_in_the_processing'),
			]);
		}
	}

	public function approveShop() {
		try {
			$id = request('id');
            $approve = $this->shopService->approveShop($id, auth()->id());
			if ($approve['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($approve);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => __('common/common.save-data-success', [
					'objectName' => __('back/shop.object_name')
				])
			]);
		} catch (\Exception $e) {
			logger()->error('Fail to delete shop', [
				'error' =>  $e
			]);
			return response()->json([
				'error' => EErrorCode::ERROR,
				'msg' => __('common/common.there_was_an_error_in_the_processing'),
			]);
		}
	}

	public function saveShop(SaveInfoRequest $request) {
		//try {
			$data = $request->validated();
			$data['avatar'] = request('image');
			$data['id'] = request('id');
			$data['latitude'] = request('latitude');
			$data['longitude'] = request('longitude');
			if (!$data['id']) {
				$data['status'] = EStatus::ACTIVE;
			}
			if(Arr::get($data, 'identityDate', null)) {
				$data['identityDate'] = str_replace('/', '-', $data['identityDate']);
                $data['identityDate'] = Carbon::parse($data['identityDate'])->format(EDateFormat::DATE_FORMAT_WITHOUT_MICROSECOND);
            }
			$result = $this->shopService->saveShop($data, auth()->id());
			if ($result['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($result);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => __('common/common.save-data-success', [
					'objectName' => __('back/shop.object_name')
				]),
			]);
		// } catch (\Exception $e) {
		// 	logger()->error('Fail to save shop', [
		// 		'error' =>  $e
		// 	]);
		// 	return response()->json([
		// 		'error' => EErrorCode::ERROR,
		// 		'msg' => __('common/common.there_was_an_error_in_the_processing'),
		// 	]);
		// }
	}

	public function getShopInfo($id) {
		$option = [
			'id' => $id,
			'first' => true
		];
		$shop = $this->shopService->getByOptions($option);
        $area1 = $shop->getArea;
        $shop->areaProvince = $area1;
        $shop->areaDistrict = null;
        $shop->areaWard = null;
        $area2 = null;
        $area3 = null;
        if (!empty($area1) && $area1->parent_area_id) {
            $area2 = $area1->parentArea;
            $shop->areaProvince = $area2;
            $shop->areaDistrict = $area1;
            if($area2->parent_area_id) {
                $area3 = $area2->parentArea;
                $shop->areaProvince = $area3;
                $shop->areaDistrict = $area2;
                $shop->areaWard = $area1;
            }
        }
        $area1 = $shop->getArea;
        $shop->areaProvince = $area1;
        $shop->areaDistrict = null;
        $shop->areaWard = null;
        $area2 = null;
        $area3 = null;
        if (!empty($area1) && $area1->parent_area_id) {
            $area2 = $area1->parentArea;
            $shop->areaProvince = $area2;
            $shop->areaDistrict = $area1;
            if($area2->parent_area_id) {
                $area3 = $area2->parentArea;
                $shop->areaProvince = $area3;
                $shop->areaDistrict = $area2;
                $shop->areaWard = $area1;
            }
        }
		$data = [
			'avatar' => !empty($shop->avatar_path) ? get_image_url([
				'path' => $shop->avatar_path,
				'op' => 'thumbnail',
				'w' => 100,
				'h' => 100,
			]) : DefaultConfig::FALLBACK_USER_AVATAR_PATH,
			'name' => $shop->name,
			'phone' => $shop->phone,
			'email' => $shop->email,
			'address' => $shop->address,
			'status' => $shop->status,
			'latitude' => $shop->latitude,
			'longitude' => $shop->longitude,
			'description' => $shop->description,
            'facebookPage' => $shop->fb_page,
            'zaloPage' => $shop->zalo_page,
			'statusStr' => EStatus::valueToLocalizedName($shop->status),
            'areaProvince' => $shop->areaProvince,
            'areaDistrict' => $shop->areaDistrict,
            'areaWard' => $shop->areaWard,
            'identityCode' => $shop->identity_code,
            'identityDate' => Carbon::parse($shop->identity_date)->format(EDateFormat::STANDARD_DATE_FORMAT),
            'identityPlace' => $shop->identity_place,
		];
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'shop' => $data,
		]);
	}

	public function getOrderList($id) {
        $tz = session(SessionKey::TIMEZONE);
		$acceptFields = ['q', 'createdAtFrom', 'createdAtTo', 'status','paymentStatus', 'deliveryStatus'];
		$filters = [
			'admin_get_for_order_list' => true,
			'shop_id' => $id,
			'pageSize' => request('pageSize'),
			'orderBy' => 'id',
            'orderDirection' => 'desc',
		];

		foreach ($acceptFields as $field) {
			if (!request()->filled("filter.$field")) {
				continue;
			}
            $fieldValue = request("filter.$field");
			if ($field === 'createdAtFrom' || $field === 'createdAtTo') {
                if (Arr::has(request('filter'), $field)) {
                    try {
                        $date = Carbon::createFromFormat(EDateFormat::DEFAULT_DATEPICKER_INPUT_FORMAT_WITH_TZ, request('filter')[$field]." $tz");
                        $filters[Str::snake($field)] = $date;
                    } catch (\Exception $e) {
                    }
                }
            } else {
                $filters[Str::snake($field)] = $fieldValue;
            }
		}
		$orders = $this->orderService->getByOptions($filters);
		$tmp = $orders->map(function($order) {
			$user = $this->userService->getById($order->user_id);
			$detail = [
				'receiver' => [
					'name' => $user->receiver_name,
					'phone' => $user->receiver_phone,
				],
				'time' => [
					'confirmedAt' => null,
					'deliveredAt' => null,
					'completedAt' => null,
					'canceledAt' => null,
				],
				'product' => [

				],
			];
			switch ($order->delivery_status) {
				case EDeliveryStatus::CUSTOMER_REFUSED:
					$detail['orderStatus']['canceledAt'] = Carbon::parse($order->canceled_at)->format(EDateFormat::STANDARD_DATE_FORMAT);
				case EDeliveryStatus::DELIVERED_SUCCESS:
					$detail['orderStatus']['completedAt'] = Carbon::parse($order->completed_at)->format(EDateFormat::STANDARD_DATE_FORMAT);
				case EDeliveryStatus::ON_THE_WAY:
					$detail['orderStatus']['deliveredAt'] = Carbon::parse($order->delivered_at)->format(EDateFormat::STANDARD_DATE_FORMAT);
				case EDeliveryStatus::WAITING_FOR_APPROVAL:
					$detail['orderStatus']['confirmedAt'] = Carbon::parse($order->confirmed_at)->format(EDateFormat::STANDARD_DATE_FORMAT);
					break;
			}

            return [
                'orderId' => $order->id,
                'shopId' => $order->shop_id,
                'code' => $order->code,
                'paymentMethod' => EPaymentMethod::valueToLocalizedName($order->payment_method),
                'price' => number_format($order->total) . ' VNĐ',
                'shippingFee' => number_format($order->shipping_fee) . ' VNĐ',
                'createdAt' => get_display_date_for_ajax($order->created_at ?? $order->created_at),
                'receiverName' => $order->receiver_name,
                'receiverPhone' => $order->receiver_phone,
                'buyerName' => $order->user_name,
                'buyerPhone' => $order->user_phone,
                'deliveryAddress' => $order->delivery_address,
                'customerNote' => $order->customer_note,
                'status' => $order->status,
                'statusStr' => EOrderStatus::valueToLocalizedName($order->status),
                'paymentStatus' =>  $order->payment_status,
                'paymentStatusStr' => EPaymentStatus::valueToLocalizedName($order->payment_status),
                'deliveryStatus' => $order->delivery_status,
                'deliveryStatusStr' => EDeliveryStatus::valueToLocalizedName($order->delivery_status),
                'detail' => $detail,
            ];
        });
        $orders->setCollection($tmp);
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'orders' => $orders,
		]);
	}

	public function getOrderDetail($shopId, $id) {
	    $order = $this->orderService->getById($id);
        if (empty($order)) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'msg' => __('common/error.invalid-request-data'),
            ]);
        }
        $shop = $order->shop;
        $user = $order->user;
        $orderDetails = $order->orderDetails;
        $productsOfOrder = $orderDetails->map(function($orderDetail) {
            $product = $orderDetail->product;
            $productImage = $product->image;
            if (count($productImage) > 0) {
                foreach ($productImage as $key) {
                    $key->path = get_image_url([
                        'path' => $key->path_to_resource,
                        'op' => 'thumbnail',
                        'w' => 100,
                        'h' => 100,
                    ]);
                }
            }
            return [
                'productImage' => !empty($productImage->first()) ?
                    config('app.resource_url_path') . '/' . $productImage
                        ->first()->path_to_resource
                    : DefaultConfig::FALLBACK_IMAGE_PATH,
                'quantity' => $orderDetail->quantity,
                'productName' => $product->name,
                'price' => number_format($orderDetail->price,
                        0, '.', '.') . ' đ/' . $orderDetail->unit,
            ];
        });
        $data = [
            'code' => $order->code,
            'orderId' => $order->id,
            'shopId' => $order->shop_id,
            'createdAt' => $order->created_at,
            'confirmedAt' => $order->confirmed_at,
            'deliveredAt' => $order->delivered_at,
            'completedAt' => $order->completed_at,
            'paymentMethod' => EPaymentMethod::valueToLocalizedName($order->payment_method),
            'receiverName' => $order->receiver_name,
            'receiverPhone' => $order->receiver_phone,
            'buyerName' => $user->name,
            'buyerPhone' => $user->phone,
            'deliveryAddress' => $order->delivery_address,
            'customerNote' => $order->customer_note,
            'cancelReason' => $order->canceled_reason,
            'status' => $order->status,
            'statusStr' => EOrderStatus::valueToLocalizedName($order->status),
            'paymentStatus' => $order->payment_status,
            'paymentStatusStr' => EPaymentStatus::valueToLocalizedName($order->payment_status),
            'deliveryStatus' => $order->delivery_status,
            'deliveryStatusStr' => EDeliveryStatus::valueToLocalizedName($order->delivery_status),
            'shippingFee' => is_null($order->shipping_fee) ? 'chưa cập nhật' :
                number_format($order->shipping_fee, 0, '.', '.') . ' đ',
            'totalPrice' =>number_format($order->total,
                    0, '.', '.') . ' đ',
            'shopName' => $shop->name,
            'productsOfOrder' => $productsOfOrder,
        ];

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'order' => $data,
        ]);
    }

	public function deleteOrder() {
		try {
			$id = request('id');
			$delete = $this->orderService->deleteOrder($id, auth()->id());
			if ($delete['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($delete);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => __('common/common.delete-data-success', [
					'objectName' => __('back/payment.object_name')
				])
			]);
		} catch (\Exception $e) {
			logger()->error('Fail to delete payment', [
				'error' =>  $e
			]);
			return response()->json([
				'error' => EErrorCode::ERROR,
				'msg' => __('common/common.there_was_an_error_in_the_processing'),
			]);
		}
	}

    public function getResourceList($id) {
        $tz = session(SessionKey::TIMEZONE);
        $acceptFields = ['q', 'status'];
        $filters = [
            'shop_id' => $id,
            'status' => EStatus::ACTIVE,
            'orderBy' => 'created_at',
            'orderDirection' => 'desc',
        ];

        $resources = $this->shopResourceService->getByOptions($filters);
        $resources = $resources->map(function($resource) {
            if(Str::containsAll($resource->path_to_resource, ['https','youtu'])) {
                $typeVideo = EVideoType::YOUTUBE_VIDEO;
            } elseif(Str::containsAll($resource->path_to_resource, ['https','tiktok'])) {
                $typeVideo = EVideoType::TIKTOK_VIDEO;
                $tiktokResource = StringUtility::getLinkTikTok($resource->path_to_resource);
                $resource->path_to_resource = $tiktokResource['src'];
            } else {
                $typeVideo = EVideoType::INTERNAL_VIDEO;
            }
            return [
                'id' => $resource->id,
                'src' => !Str::contains($resource->path_to_resource,['https','http']) ? config('app.resource_url_path') .
                    '/' . $resource->path_to_resource : $resource->path_to_resource,
                'type' => $resource->type,
                'typeVideo' => $typeVideo,
            ];
        });
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'resources' => $resources,
        ]);
    }
}
