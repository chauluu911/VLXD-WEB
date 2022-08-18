<?php

namespace App\Http\Controllers\Back;

use App\Enums\EApprovalStatus;
use App\Enums\EDeliveryStatus;
use App\Enums\EOrderStatus;
use App\Enums\ETableName;
use App\Constant\SessionKey;
use App\Enums\EDateFormat;
use App\Enums\EErrorCode;
use App\Enums\EPaymentStatus;
use App\Enums\EPaymentMethod;
use App\Enums\EPaymentType;
use App\Enums\ESubscriptionPriceType;
use App\Enums\EStatus;
use App\Helpers\FileUtility;
use \App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Services\OrderService;
use App\Services\ProductService;
use App\Services\UserService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Services\SubscriptionService;
use App\Enums\EWalletType;
use App\Constant\DefaultConfig;
use App\Enums\EGender;

class PaymentController extends Controller {
	protected OrderService $orderService;
	protected SubscriptionService $subscriptionService;
	protected UserService $userService;
	protected ProductService $productService;

    public function __construct(OrderService $orderService,
                                UserService $userService,
                                ProductService $productService,
                                SubscriptionService $subscriptionService) {
        $this->orderService = $orderService;
        $this->userService = $userService;
        $this->productService = $productService;
        $this->subscriptionService = $subscriptionService;
    }

    public function getForPaymentList() {
		$tz = session(SessionKey::TIMEZONE);
		$options = [
			'pageSize' => request('pageSize'),
			'for_admin_payment_list' => true,
		];

		$acceptFields = ['q', 'userId', 'status', 'deliveryStatus', 'paymentStatus', 'paymentType', 'createdAtLt', 'createdAtGt', 'typeList','paymentMethod'];
		foreach ($acceptFields as $field) {
			if (!request()->filled("filter.$field")) {
				continue;
			}
			$fieldValue = request("filter.$field");
			switch ($field) {
				case 'createdAtGt':
				case 'createdAtLt':
					try {
						$date = Carbon::createFromFormat(EDateFormat::DEFAULT_DATEPICKER_INPUT_FORMAT_WITH_TZ, "$fieldValue $tz");
						$options[Str::snake($field)] = $date;
					} catch (\Exception $e) {}
					continue 2;
				case 'paymentStatus':
					if ($fieldValue == EStatus::DELETED) {
						$options['status'] = EStatus::DELETED;
						break;
					}
			}
			$options[Str::snake($field)] = $fieldValue;
		}
		if ($options['type_list'] == ETableName::SUBSCRIPTION) {
			$paymentList = $this->subscriptionService->getByOptions($options);
		}else {
			$paymentList = $this->orderService->getByOptions($options);
		}
//		dd($paymentList);
		$tmp = $paymentList->map(function($item) use ($options) {
			if ($item->status == EStatus::WAITING || $item->status == EStatus::DELETED) {
				$statusStr = EStatus::valueToLocalizedName($item->status);
				$status = $item->status;
			} else {
				$statusStr = EPaymentStatus::valueToLocalizedName($item->payment_status);
				$status = $item->payment_status;
			}
			$user = $this->userService->getById($item->user_id);
			if (isset($user->getShop)) {
				$user->shopId = $user->getShop->id;
				$user->shopName = $user->getShop->name;
			}
			$user->date_of_birth = !empty($user->date_of_birth) ? Carbon::parse($user->date_of_birth)->format(EDateFormat::STANDARD_DATE_FORMAT) : null;
			$score = $user->getWallet->where('type', EWalletType::POINT)->first();
			$coins = $user->getWallet->where('type', EWalletType::INTERNAL_MONEY)->first();
			$user->avatar = !empty($user->avatar_path) ? get_image_url([
				'path' => $user->avatar_path,
				'op' => 'thumbnail',
				'w' => 100,
				'h' => 100,
			]) : DefaultConfig::FALLBACK_USER_AVATAR_PATH;
			$user->strStatus = EStatus::valueToLocalizedName($user->status);
			$user->score = isset($score) ? number_format($score->balance) : 0;
			$user->coins = isset($coins) ? number_format($coins->balance) : 0;
			$user->scoreType = EWalletType::POINT;
			$user->affiliateCode = $user->affiliateCode ? $user->affiliateCode->code : null;
			$user->coinsType = EWalletType::INTERNAL_MONEY;
			$user->createdAt =  get_display_date_for_ajax($user->created_at ?? $user->created_at);
			$user->genderStr = EGender::getNameByValue($user->gender);
			if($user->meta){
			    $meta = (object)$user->meta;
                $user->isFreeSubscriptionOnce = $meta->freeSubscriptionOnce;
            }else{
                $user->isFreeSubscriptionOnce = false;
            }

			// $activitiesTime = $this->userActivitiesLogService->getLastTime($user->id);
			// $user->activities_time = !empty($activitiesTime->created_at) ? Carbon::parse($activitiesTime->created_at)->timezone($timezone)->format(EDateFormat::DATE_FORMAT_HOUR) : null;

			$shopName = null;
			$shop = $user->getShop;
			if ($options['type_list'] == ETableName::SUBSCRIPTION && ($item->type ==
				ESubscriptionPriceType::PACKAGE_PUSH_PRODUCT || $item->type ==
				ESubscriptionPriceType::UPGRADE_SHOP)) {
				$shopName = !empty($shop) ? $shop->name : null;
				if (!empty($shop)) {
					$shop->avatar = !empty($shop->avatar_path) ? get_image_url([
						'path' => $shop->avatar_path,
						'op' => 'thumbnail',
						'w' => 100,
						'h' => 100,
					]) : DefaultConfig::FALLBACK_USER_AVATAR_PATH;
					$shop->strStatus = EStatus::valueToLocalizedName($shop->status,true);
					$shop->strPaymentStatus = EPaymentStatus::valueToLocalizedName($shop->payment_status);
					$shop->createdAt =  get_display_date_for_ajax($shop->created_at ?? $shop->created_at);
				}
			}

			//nếu là lịch sử thanh toán của gói đẩy tin thì lấy thêm về thông tin
            // của sản phẩm được đẩy
            if ($options['type_list'] == ETableName::SUBSCRIPTION && $item->type ==
                    ESubscriptionPriceType::PACKAGE_PUSH_PRODUCT) {
                if($item->table_name === ETableName::PRODUCT && $item->table_id) {
                    $product = $this->productService->getById($item->table_id);
                    $item->product = [
                        'id' => $product->id,
                        'name' => $product->name
                    ];
                }
            }

            //nếu là order thì lấy thêm thông tin của cửa hàng trong order
            $shopOfOrder = [];
            if($options['type_list'] == ETableName::ORDER) {
                $shopOfOrder['avatar'] = $item->shp_avatar ? FileUtility::getFileResourcePath($item->shp_avatar) :
                    DefaultConfig::FALLBACK_IMAGE_PATH;
                $shopOfOrder['code'] = $item->shp_code;
                $shopOfOrder['id'] = $item->shp_id;
                $shopOfOrder['name'] = $item->shp_name;
                $shopOfOrder['level'] = $item->shp_level;
                $shopOfOrder['phone'] = $item->shp_phone;
                $shopOfOrder['email'] = $item->shp_email;
                $shopOfOrder['address'] = $item->shp_address;
                $shopOfOrder['createdAt'] = $item->shp_created_at;
                $shopOfOrder['paymentStatus'] = $item->shp_payment_status;
                $shopOfOrder['strPaymentStatus'] = EPaymentStatus::valueToLocalizedName($item->shp_payment_status);
                $shopOfOrder['status'] = $item->shp_status;
                $shopOfOrder['strStatus'] = EStatus::valueToLocalizedName($item->shp_status);
                $shopOfOrder['fb_page'] = $item->shp_fb_page;
                $shopOfOrder['zalo_page'] = $item->shp_zalo_page;
            }

			$result = [
				'id' => $item->id,
                'paymentMethod' => EPaymentMethod::valueToLocalizedName($item->payment_method),
                'paymentType' => isset($item->type) ? EPaymentType::valueToLocalizedName($item->type) : null,
                'price' => number_format($item->price) . ' VNĐ',
                'createdAt' => $item->created_at,
                'statusStr' => $statusStr,
                'status' => $status,
                'userName' => $user->name,
                'userId' => $user->id,
                'shop' => $shop,
                'shopOfOrder' => $shopOfOrder,
                'user' => $user,
                'shopName' => $shopName,
                'name' => $item->name ? $item->name : null,
                'product' => $item->product ? $item->product : null,
                'code' => $item->code ? $item->code : null,
			];
            if($options['type_list'] == ETableName::ORDER) {
                $result['statusStr'] = EOrderStatus::valueToLocalizedName($item->status);
                $result['paymentStatus'] =  $item->payment_status;
                $result['paymentStatusStr'] = EPaymentStatus::valueToLocalizedName($item->payment_status);
                $result['deliveryStatus'] = $item->delivery_status;
                $result['deliveryStatusStr'] = EDeliveryStatus::valueToLocalizedName($item->delivery_status);
            }
			return $result;
		});
		$paymentList->setCollection($tmp);
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'data' => $paymentList,
		]);
	}

	public function approvePayment() {
		//try {
		    $items = request('items');
			if (empty($items)) {
			    return response()->json([
			        'error' => EErrorCode::ERROR,
                    'msg' => __('back/payment.err.Failed to approve payment, w/e', [
                        'error' => '',
                    ])
                ]);
			}
			$result = $this->subscriptionService->approvePayment($items, auth()->id());
            if ($result['error'] !== EErrorCode::NO_ERROR) {
                return response()->json($result);
            }
            return response()->json([
                'error' => EErrorCode::NO_ERROR,
                'msg' => 'Duyệt thành công'
            ]);
		// } catch (\Exception $e) {
		// 	logger()->error('Fail to approve payment', ['e' => $e]);
  //           return response()->json([
  //               'error' => EErrorCode::ERROR,
  //               'msg' => __('back/payment.err.Failed to approve payment, w/e', [
  //                   'error' => $e->getMessage()
  //               ])
  //           ]);
		// }
	}

	public function deletePayment() {
		//try {
			$id = request('id');
			$typeList = request('typeList');
			if ($typeList == ETableName::SUBSCRIPTION) {
				$delete = $this->subscriptionService->deleteSubscription($id, auth()->id());
			}else {
				$delete = $this->orderService->deleteOrder($id, auth()->id());
			}
			//$delete = $this->userService->deletePayment($id, auth()->id());
			if ($delete['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($delete);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => __('common/common.delete-data-success', [
					'objectName' => __('back/payment.object_name')
				])
			]);
		// } catch (\Exception $e) {
		// 	logger()->error('Fail to delete payment', [
		// 		'error' =>  $e
		// 	]);
		// 	return response()->json([
		// 		'error' => EErrorCode::ERROR,
		// 		'msg' => __('common/common.there_was_an_error_in_the_processing'),
		// 	]);
		// }
	}
}
