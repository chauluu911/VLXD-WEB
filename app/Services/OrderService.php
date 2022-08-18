<?php

namespace App\Services;

use App\Constant\ConfigTableName;
use App\Enums\EDateFormat;
use App\Enums\EDeliveryStatus;
use App\Enums\EErrorCode;
use App\Enums\EOrderStatus;
use App\Enums\EOrderType;
use App\Enums\EPaymentStatus;
use App\Enums\ETableName;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Enums\EStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use App\Services\OrderDetailService;
use App\Services\ShopService;
use App\Services\UserService;
use App\Enums\ENotificationType;
use App\Jobs\NotifyUserJob;
use App\Enums\ELanguage;
use App\Enums\EWalletTransactionLogType;
use App\Models\WalletTransactionLog;
use App\Enums\EWalletType;
use App\Enums\EPaymentMethod;

class OrderService {
	private OrderRepository $orderRepository;
	private PostService $postService;
	private OrderDetailService $orderDetailService;
	private ReviewService $reviewService;
    private ShopService $shopService;
    private UserService $userService;

    public function __construct(OrderRepository $orderRepository,
								PostService $postService,
								ReviewService $reviewService,
								OrderDetailService $orderDetailService,
                                ShopService $shopService,
                                UserService $userService) {
		$this->orderRepository = $orderRepository;
		$this->postService = $postService;
		$this->orderDetailService = $orderDetailService;
		$this->reviewService = $reviewService;
        $this->shopService = $shopService;
        $this->userService = $userService;
    }

    /**
     * @param $id
     * @return Order
     */
    public function getById($id) {
        return $this->orderRepository->getById($id);
    }

    public function getByCode($code) {
        return $this->orderRepository->getByCode($code);
    }

	public function getByOptions(array $options) {
    	return $this->orderRepository->getByOptions($options);
	}

	public function saveOrder(array $data, int $currentUserId) {
		return DB::transaction(function() use ($data, $currentUserId) {
			$result = [
				'error' => EErrorCode::NO_ERROR,
			];

			// create Order
			$orderResult = DB::transaction(function() use ($data, $currentUserId) {
				if (Arr::has($data, 'orderId')) {
					$order = $this->orderRepository->getById(Arr::get($data, 'orderId'));
				}
				if (empty($order)) {
					$order = new Order();
					$order->user_id = Arr::get($data, 'user_id');
					$order->table_name = Arr::get($data, 'table_name');
					$order->table_id = Arr::get($data, 'table_id');
					$order->status = EOrderStatus::WAITING;
					$order->created_by = $currentUserId;

					// first switch to get code prefix
					switch ($order->table_name) {
						case ConfigTableName::SUBSCRIPTION:
							$codePrefix = 'S';
							break;
					}

					do {
						$code = $codePrefix . strtoupper(Str::random(6));
					} while (!empty($this->orderRepository->getByCode($code)));
					$order->code = $code;
				}

				$order->payment_method = Arr::get($data, 'payment_method');
				$order->payment_status = EPaymentStatus::UNPAID;

				$orderMeta = empty($order->meta) ? [] : (array)$order->meta;

				// second switch to process other col
				switch ($order->table_name) {
					case ConfigTableName::SUBSCRIPTION:
						$order->amount = Arr::get($data, 'amount');
						$order->total = Arr::get($data, 'total');
						$order->discount = Arr::get($data, 'discount');

						if (Arr::has($data, 'postData')) {
							$savePostResult = $this->postService->savePost($data['postData'], $currentUserId);
							if ($savePostResult['error'] !== EErrorCode::NO_ERROR) {
								DB::rollBack();
								return $savePostResult;
							}
							$orderMeta['postId'] = $savePostResult['post']->id;
						}
						break;
				}

				$order->meta = $orderMeta;

				$order->save();
				return [
					'error' => EErrorCode::NO_ERROR,
					'order' => $order,
				];
			});
			if ($orderResult['error'] !== EErrorCode::NO_ERROR) {
				return $orderResult;
			}

			return $result;
		});
	}

	public function deleteOrder($id, $loggedInUserId, $detailId = null) {
        return DB::transaction(function() use ($id, $loggedInUserId, $detailId) {
            $order = $this->getById($id);
            if (empty($order)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            abort_if($order->status == EOrderStatus::DELETED, Response::HTTP_NOT_FOUND);
            $order->deleted_by = $loggedInUserId;
            $order->deleted_at = now();
            $order->status = EOrderStatus::DELETED;
            $order->save();

            if (!empty($detailId)) {
                $deleteDetail = $this->orderDetailService->deleteOrderDetail($detailId, $loggedInUserId, now());
            }

            return ['error' => EErrorCode::NO_ERROR];
        });
    }

    public function generateNewCode() {
        do {
            $code = 'DH' . mb_strtoupper(Str::random(5));
        } while (Order::where('code', $code)->exists());
        return $code;
    }

    public function addToCart($data, $loggedInUserId, $now) {
        return DB::transaction(function() use ($data, $loggedInUserId, $now) {
            $orderDetail = $this->getByOptions([
            	'first' => true,
            	'product_id' => Arr::get($data, 'productId'),
            	'shop_id' => Arr::get($data, 'shopId'),
            	'user_id' => $loggedInUserId,
            	'type' => EOrderType::SHOPPING_CART,
            	'get_detail' => true
            ]);
            if (!empty($orderDetail)) {
            	$detailData = [
            		'orderDetailId' => $orderDetail->orderDetailId,
                    'quantity' => Arr::get($data, 'quantity'),
            	];
            	$saveDetail = $this->orderDetailService->saveOrderDetail($detailData, $loggedInUserId);
                $order = $this->getById($saveDetail['detail']->order_id);
            } else {
            	$order = $this->orderRepository->getByOptions([
                    'first' => true,
                    'shop_id' => Arr::get($data, 'shopId'),
                    'user_id' => $loggedInUserId,
                    'type' => EOrderType::SHOPPING_CART,
                    'status' => EStatus::WAITING
                ]);

                if (empty($order)) {
                    $order = new Order();
                    $order->code = $this->generateNewCode();
                    $order->price = Arr::get($data, 'price');
                    $order->status = EOrderStatus::WAITING;
                    $order->created_by = $loggedInUserId;
                    $order->user_id = $loggedInUserId;
                    $order->price = Arr::get($data, 'price');
                    $order->shop_id = Arr::get($data, 'shopId');
                    $order->type = EOrderType::SHOPPING_CART;
                    $order->save();
                }
            	$detailData = [
            		'productId' => Arr::get($data, 'productId'),
            		'orderId' => $order->id,
            		'price' => Arr::get($data, 'price'),
            		'quantity' => Arr::get($data, 'quantity'),
            		'unit' => Arr::get($data, 'unit'),
            	];
            	$saveDetail = $this->orderDetailService->saveOrderDetail($detailData, $loggedInUserId);
            }
            $orderDetailList = $this->orderDetailService->getByOptions([
                'order_id' => $saveDetail['detail']->order_id,
                'status' => EStatus::ACTIVE
            ]);
            $total = 0;
            foreach ($orderDetailList as $item) {
                $total += $item->total;
            }
            $order->price = $total;
            $order->total = $total;
            $order->save();
        	if ($saveDetail['error'] == EErrorCode::NO_ERROR) {
        		return ['error' => $saveDetail['error']];
        	}
        });
    }

    public function convertCartIntoOrder($data, $receiverInfo, $loggedInUserId) {
        return DB::transaction(function() use ($data, $receiverInfo, $loggedInUserId) {
            foreach ($data as $key) {
                $shop = $this->shopService->getById(Arr::get($key, 'shop_id'));
                $user = $this->userService->getById($loggedInUserId);

                $order = $this->getById(Arr::get($key, 'id'));
                $order->status = EOrderStatus::WAITING;
                $order->updated_by = $loggedInUserId;
                $order->type = EOrderType::ORDER_CREATED;
                $order->created_at = now()->timezone(config('app.timezone'))
                    ->format(EDateFormat::MODEL_DATE_FORMAT);
                $order->receiver_name = Arr::get($receiverInfo, 'name');
                $order->customer_note = Arr::get($key, 'note');
                $order->receiver_phone = Arr::get($receiverInfo, 'phone');
                $order->delivery_address = Arr::get($receiverInfo, 'address');
                $order->payment_method = Arr::get($receiverInfo, 'paymentMethod');
                $order->save();

                NotifyUserJob::dispatch([$shop->user_id], [
                    'type' => ENotificationType::RECEIVED_NOTIFICATION_ORDER_CREATED_BY_USER,
                    'title' => [
                        ELanguage::VI => 'Thông báo',
                    ],
                    'content' => [
                        ELanguage::VI => 'Đơn hàng #' . $order->code . ' của ' . $user->name . ' đang chờ xác nhận',
                    ],
                    'meta' => [
                        'orderId' => (int)$order->id,
                    ],
                    'data' => [
                        'orderId' => (int)$order->id,
                    ],
                ])->onQueue('pushToDevice');
            }
            return ['error' => EErrorCode::NO_ERROR];
        });
    }
    public function approveAndUpdateShippingFeeOrder($code,$shopId,$shippingFee, $loggedInUserId) {
        return DB::transaction(function() use ($code,$shopId,$shippingFee, $loggedInUserId) {
            $order = $this->getByCode($code);
            if (empty($order)) {
                return ['error' => EErrorCode::ERROR,
                    'msg' => __('common/error.invalid-request-data')];
            }
            if($order->shop_id != $shopId || $order->status != EOrderStatus::WAITING) {
                return ['error' => EErrorCode::ERROR,
                    'msg' => __('common/error.invalid-request-data')];
            }
            $order->updated_by = $loggedInUserId;
            $order->confirmed_by = $loggedInUserId;
            $order->status = EOrderStatus::CONFIRMED;
            $order->shipping_fee = $shippingFee;
            $order->total = $order->total + $shippingFee;
            $order->confirmed_at =now()->timezone(config('app.timezone'))
                ->format(EDateFormat::MODEL_DATE_FORMAT);
            $order->save();
            NotifyUserJob::dispatch([$order->user_id], [
                'type' => ENotificationType::APPROVED_ORDER,
                'title' => [
                    ELanguage::VI => 'Thông báo',
                ],
                'content' => [
                    ELanguage::VI => 'Đơn hàng #' . $order->code . ' của bạn đã được duyệt',
                ],
                'meta' => [
                    'orderId' => (int)$order->id,
                ],
                'data' => [
                    'orderId' => (int)$order->id,
                ],
            ])->onQueue('pushToDevice');
            return ['error' => EErrorCode::NO_ERROR];
        });
    }
    public function cancelOrder($code,$shopId,$cancelReason,$cancelBy, $loggedInUserId) {
        return DB::transaction(function() use ($code,$shopId,$cancelReason,$cancelBy, $loggedInUserId) {
            $order = $this->getByCode($code);
            if (empty($order)) {
                return ['error' => EErrorCode::ERROR,
                    'msg' => __('common/error.invalid-request-data')];
            }
            if($cancelBy == "shop") {
                if($order->shop_id != $shopId ||
                    $order->status == EOrderStatus::CANCEL_BY_SHOP ||
                    $order->status == EOrderStatus::CANCEL_BY_USER) {
                    return ['error' => EErrorCode::ERROR,
                        'msg' => __('common/error.invalid-request-data')];
                }
            } else {
                if($order->user_id != $loggedInUserId || $order->status != EOrderStatus::WAITING) {
                    return ['error' => EErrorCode::ERROR,
                        'msg' => __('common/error.invalid-request-data')];
                }
            }

            $order->updated_by = $loggedInUserId;
            $order->canceled_by = $loggedInUserId;
            $order->status = $cancelBy == "shop" ?
                EOrderStatus::CANCEL_BY_SHOP :
                EOrderStatus::CANCEL_BY_USER;
            $order->canceled_reason = $cancelReason;

            $order->canceled_at = now()->timezone(config('app.timezone'))
                ->format(EDateFormat::MODEL_DATE_FORMAT);
            $order->save();

            $shop = $this->shopService->getById($order->shop_id);
            if ($loggedInUserId == $order->user_id) {
                $sendTo = $shop->user_id;
            } else {
                $sendTo = $order->user_id;
            }
            $user = $this->userService->getById($sendTo);
            NotifyUserJob::dispatch([$sendTo], [
                'type' => ENotificationType::APPROVED_ORDER,
                'title' => [
                    ELanguage::VI => 'Thông báo',
                ],
                'content' => [
                    ELanguage::VI => $cancelBy == "shop" ? 'Đơn hàng #' . $order->code . ' của bạn đã bị hủy' : $user->name . ' đã hủy đơn hàng #' . $order->code,
                ],
                'meta' => [
                    'orderId' => (int)$order->id,
                ],
                'data' => [
                    'orderId' => (int)$order->id,
                ],
            ])->onQueue('pushToDevice');

            return ['error' => EErrorCode::NO_ERROR];
        });
    }

    public function reviewOrder($data, $loggedInUserId) {
        return DB::transaction(function() use ($data, $loggedInUserId) {
            $order = $this->getByCode($data['code']);
            if (empty($order) || $order->rated ) {
                return ['error' => EErrorCode::ERROR,
                    'msg' => __('common/error.invalid-request-data')];
            }
            $order->updated_by = $loggedInUserId;
            $order->rated = true;
            $review = $this->reviewService->saveReview([
                'tableId' => $order->shop_id,
                'tableName' => ETableName::SHOP,
                'content' => $data['review'],
                'star' => $data['rating'],
            ],$loggedInUserId);
            $order->save();
            if ($review['error'] == EErrorCode::NO_ERROR) {
                $shop = $order->shop;
                if($shop){
                    $shop->star = $review['evaluate']['average'];
                    $shop->review_count = $review['evaluate']['total'];
                    $shop->save();
                }
                return ['error' => EErrorCode::NO_ERROR];
            }
        });
    }

    public function approveDeliveryOrder($code,$shopId, $loggedInUserId) {
        return DB::transaction(function() use ($code,$shopId, $loggedInUserId) {
            $order = $this->getByCode($code);
            if (empty($order)) {
                return ['error' => EErrorCode::ERROR,
                    'msg' => __('common/error.invalid-request-data')];
            }
            if($order->shop_id != $shopId || $order->status != EOrderStatus::CONFIRMED) {
                return ['error' => EErrorCode::ERROR,
                    'msg' => __('common/error.invalid-request-data')];
            }
            $order->updated_by = $loggedInUserId;
            $order->delivered_by = $loggedInUserId;
            $order->delivery_status = EDeliveryStatus::ON_THE_WAY;
            $order->delivered_at = now()->timezone(config('app.timezone'))
                ->format(EDateFormat::MODEL_DATE_FORMAT);
            $order->save();
            return ['error' => EErrorCode::NO_ERROR];
        });
    }

    public function completeOrder($code,$shopId, $loggedInUserId) {
        return DB::transaction(function() use ($code,$shopId, $loggedInUserId) {
            $order = $this->getByCode($code);
            if (empty($order)) {
                return ['error' => EErrorCode::ERROR,
                    'msg' => __('common/error.invalid-request-data')];
            }
            if($order->shop_id != $shopId ||
                $order->status != EOrderStatus::CONFIRMED ||
                $order->delivery_status != EDeliveryStatus::ON_THE_WAY) {
                return ['error' => EErrorCode::ERROR,
                    'msg' => __('common/error.invalid-request-data')];
            }
            $order->updated_by = $loggedInUserId;
            $order->completed_by = $loggedInUserId;
            $order->delivery_status = EDeliveryStatus::DELIVERED_SUCCESS;
            $order->payment_status = EPaymentStatus::PAYMENT_RECEIVED;
            $order->completed_at = now()->timezone(config('app.timezone'))
                ->format(EDateFormat::MODEL_DATE_FORMAT);
            $order->save();

            $user = $this->userService->getById($order->user_id);

            $userCoin = $user->getWallet->where('type', EWalletType::POINT)->first();
            $userCoin->balance += $order->total / 1000;
            $userCoin->updated_at = now();
            $userCoin->updated_by = $loggedInUserId;
            $userCoin->save();

            $walletTransactionLog = new WalletTransactionLog();
            $walletTransactionLog->user_id = $user->id;
            $walletTransactionLog->wallet_id = $userCoin->id;
            $walletTransactionLog->type = EWalletTransactionLogType::POINT_REWARD_WHEN_BUY_ORDER;
            $walletTransactionLog->changed_amount = $order->total / 1000;
            $walletTransactionLog->created_at = now();
            $walletTransactionLog->status = EStatus::ACTIVE;
            $walletTransactionLog->created_by = $loggedInUserId;
            $walletTransactionLog->payment_method = EPaymentMethod::COD;
            $walletTransactionLog->save();

            NotifyUserJob::dispatch([$order->user_id], [
                'type' => ENotificationType::APPROVED_ORDER,
                'title' => [
                    ELanguage::VI => 'Thông báo',
                ],
                'content' => [
                    ELanguage::VI => 'Bạn đã được cộng ' . round($order->total / 1000) .' điểm tích lũy từ giao dịch đơn hàng ' . $order->code . ' thành công',
                ],
                'meta' => [
                    'orderId' => (int)$order->id,
                ],
                'data' => [
                    'orderId' => (int)$order->id,
                ],
            ])->onQueue('pushToDevice');
            return ['error' => EErrorCode::NO_ERROR];
        });
    }

}
