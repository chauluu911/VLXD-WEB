<?php

namespace App\Services;

use App\Constant\ConfigTableName;
use App\Enums\EErrorCode;
use App\Enums\EPaymentStatus;
use App\Models\Order;
use App\Repositories\SubscriptionRepository;
use App\Enums\EStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\SubscriptionPriceService;
use App\Enums\EPaymentType;
use App\Enums\EPaymentMethod;
use App\Enums\EWalletType;
use App\Services\UserService;
use App\Services\ShopService;
use App\Services\ShopLevelService;
use App\Models\WalletTransactionLog;
use App\Models\ShopLevel;
use App\Models\ShopLevelConfig;
use App\Enums\EWalletTransactionLogType;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Subscription;
use App\Services\ProductService;
use App\Jobs\NotifyUserJob;
use App\Enums\ENotificationType;
use App\Enums\ESubscriptionPriceType;
use App\Enums\ELanguage;
use App\Enums\EDateFormat;

class SubscriptionService {

	private SubscriptionRepository $subscriptionRepository;
	private SubscriptionPriceService $subscriptionPriceService;
	private UserService $userService;
    private ShopService $shopService;
    private ProductService $productService;
    private ShopLevelService $shopLevelService;

    public function __construct(SubscriptionRepository $subscriptionRepository, SubscriptionPriceService $subscriptionPriceService, UserService $userService,
        ShopService $shopService, ProductService $productService, ShopLevelService $shopLevelService) {
		$this->subscriptionRepository = $subscriptionRepository;
		$this->subscriptionPriceService = $subscriptionPriceService;
		$this->userService = $userService;
        $this->shopService = $shopService;
        $this->productService = $productService;
        $this->shopLevelService = $shopLevelService;
    }

    /**
     * @param $id
     * @return Order
     */
    public function getById($id) {
        return $this->subscriptionRepository->getById($id);
    }

	public function getByOptions(array $options) {
    	return $this->subscriptionRepository->getByOptions($options);
	}

	public function approvePayment($items, $currentUserId) {
        return DB::transaction(function() use ($items, $currentUserId) {
        	foreach ($items as $key) {
        	 	$subscription = $this->getById($key['id']);
        	 	if (empty($subscription)) {
                    return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
                }
                $subscription->payment_status = EPaymentStatus::PAYMENT_RECEIVED;
                $subscription->save();

                $subscriptionPrice = $this->subscriptionPriceService->getById(json_decode($subscription->payment_meta)->subscriptionPriceId);
                if ($subscriptionPrice->type == EPaymentType::BUY_COINS) {
                	$user = $this->userService->getById($subscription->user_id);

                	$userCoin = $user->getWallet->where('type', EWalletType::INTERNAL_MONEY)->first();
                	$userCoin->balance += $subscriptionPrice->price / 1000;
                	$userCoin->updated_at = now();
                	$userCoin->updated_by = $currentUserId;
                    $userCoin->save();

                    $walletTransactionLog = new WalletTransactionLog();
                    $walletTransactionLog->user_id = $subscription->user_id;
                    $walletTransactionLog->wallet_id = $userCoin->id;
                    $walletTransactionLog->type = EWalletTransactionLogType::BUY_COIN;
                	$walletTransactionLog->changed_amount = $subscriptionPrice->price;
                    $walletTransactionLog->created_at = now();
                    $walletTransactionLog->created_by = $currentUserId;
                    $walletTransactionLog->status = EStatus::ACTIVE;
                    $walletTransactionLog->payment_method = $subscription->payment_method;
                    $walletTransactionLog->save();

                    NotifyUserJob::dispatch([$subscription->user_id], [
                        'type' => ENotificationType::APPROVED_PAYMENT_COINS,
                        'title' => [
                            ELanguage::VI => 'Thông báo',
                        ],
                        'content' => [
                            ELanguage::VI => 'Thanh toán mua xu với giá "' . number_format($subscriptionPrice->price) . 'đ" của bạn đã được duyệt',
                        ],
                        'meta' => [
                            'subscriptionPriceId' => json_decode($subscription->payment_meta)->subscriptionPriceId,
                        ],
                        'data' => [
                            'subscriptionPriceId' => json_decode($subscription->payment_meta)->subscriptionPriceId,
                        ]
                    ])->onQueue('pushToDevice');
                } else if($subscriptionPrice->type == EPaymentType::UPGRADE_SHOP) {
                    $shop = $this->shopService->getById($subscription->table_id);
                    $subscriptionPrice = $this->subscriptionPriceService->getById(json_decode($subscription->payment_meta)->subscriptionPriceId);
                    $shop->level = $subscriptionPrice->meta->level;
                    $shop->save();

                    $shopLevel = $this->shopLevelService->getByOptions([
                        'status' => EStatus::ACTIVE,
                        'shop_id' => $shop->id,
                        'first' => true,
                    ]);
                    if (!empty($shopLevel)) {
                        $shopLevel->status = EStatus::DELETED;
                        $shopLevel->save();
                    }

                    $shopLevelWaiting = $this->shopLevelService->getByOptions([
                        'status' => EStatus::WAITING,
                        'shop_id' => $shop->id,
                        'first' => true,
                    ]);

                    if (!empty($shopLevelWaiting)) {
                        $shopLevelWaiting->status = EStatus::ACTIVE;
                        $shopLevelWaiting->save();
                    }

                    $subscription->valid_from = now()->copy();
                    $subscription->valid_to = now()->copy()->addDays($subscriptionPrice->num_day);
                    $subscription->save();

                    $shopConfig = ShopLevelConfig::where('level', $subscriptionPrice->meta->level)->first();
                    NotifyUserJob::dispatch([$shop->user_id], [
                        'type' => ENotificationType::APPROVED_UPGRADE_SHOP,
                        'title' => [
                            ELanguage::VI => 'Thông báo',
                        ],
                        'content' => [
                            ELanguage::VI => 'Yêu cầu nâng cấp shop lên cấp độ "' . $shopConfig->name . '" của bạn đã được duyệt',
                        ],
                        'meta' => [
                            'shopId' => $subscription->table_id,
                        ],
                        'data' => [
                            'shopId' => $subscription->table_id,
                        ]
                    ])->onQueue('pushToDevice');
                    if (!empty(json_decode($subscription->payment_meta)->affiliate_code)) {
                        $refferalCodeOwner = $this->userService->getByOptions([
                            'refferalCode' => strtoupper(json_decode($subscription->payment_meta)->affiliate_code),
                            'first' => true,
                        ]);
                        if (!empty($refferalCodeOwner)) {
                            $userCoin = $refferalCodeOwner->getWallet->where('type', EWalletType::INTERNAL_MONEY)->first();
                            $userCoin->balance += $subscriptionPrice->price / 1000;
                            $userCoin->updated_at = now();
                            $userCoin->updated_by = $currentUserId;
                            $userCoin->save();

                            $walletTransactionLog = new WalletTransactionLog();
                            $walletTransactionLog->user_id = $refferalCodeOwner->id;
                            $walletTransactionLog->wallet_id = $userCoin->id;
                            $walletTransactionLog->type = EWalletTransactionLogType::RECEIVED_COIN_COMMISSION_WHEN_UPDATE_LEVEL_SHOP;
                            $walletTransactionLog->changed_amount = $subscriptionPrice->price;
                            $walletTransactionLog->created_at = now();
                            $walletTransactionLog->status = EStatus::ACTIVE;
                            $walletTransactionLog->created_by = $currentUserId;
                            $walletTransactionLog->payment_method = $subscription->payment_method;
                            $walletTransactionLog->save();

                            $shop = $this->shopService->getById($subscription->table_id);
                            NotifyUserJob::dispatch([$refferalCodeOwner->id], [
                                'type' => ENotificationType::RECEIVED_COMMISSION,
                                'title' => [
                                    ELanguage::VI => 'Thông báo',
                                ],
                                'content' => [
                                    ELanguage::VI => 'Bạn nhận được ' . round($subscriptionPrice->price / 1000) .' xu chiết khấu từ nâng cấp cửa hàng của ' . $shop->name,
                                ],
                                'meta' => [
                                    'subscriptionPriceId' => $subscriptionPrice->id,
                                ],
                                'data' => [
                                    'subscriptionPriceId' => $subscriptionPrice->id,
                                ]
                            ])->onQueue('pushToDevice');
                        }
                    }
                } else if($subscriptionPrice->type == EPaymentType::PUSH_PRODUCT) {
                    $product = $this->productService->getById($subscription->table_id);
                    $product->save();

                    $subscription->valid_from = now()->copy();
                    $subscription->valid_to = now()->copy()->addDays($subscriptionPrice->num_day);
                    $subscription->save();

                    NotifyUserJob::dispatch([$product->created_by], [
                        'type' => ENotificationType::APPROVED_PUSH_PRODUCT,
                        'title' => [
                            ELanguage::VI => 'Thông báo',
                        ],
                        'content' => [
                            ELanguage::VI => 'Yêu cầu đẩy tin với gói "' . $subscriptionPrice->name . '" của bạn đã được duyệt',
                        ],
                        'meta' => [
                            'productId' => $product->id,
                        ],
                        'data' => [
                            'productId' => $product->id,
                        ]
                    ])->onQueue('pushToDevice');
                }
        	}
            return [
                'error' => EErrorCode::NO_ERROR,
            ];
        });
    }

    public function deleteSubscription($id, $loggedInUserId) {
        return DB::transaction(function() use ($loggedInUserId, $id) {
            $subscription = $this->getById($id);
            if (empty($subscription)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            abort_if($subscription->status == EStatus::DELETED, Response::HTTP_NOT_FOUND);
            $subscription->deleted_by = $loggedInUserId;
            $subscription->deleted_at = now();
            $subscription->status = EStatus::DELETED;
            $subscription->save();
            return ['error' => EErrorCode::NO_ERROR];
        });
    }

    public function generateNewCode() {
        do {
            $code = 'GD' . mb_strtoupper(Str::random(5));
        } while (Subscription::where('code', $code)->exists());
        return $code;
    }

    public function saveSubscription($data, $loggedInUserId) {
        return DB::transaction(function() use ($data, $loggedInUserId) {
            if (!empty(Arr::get($data, 'id'))) {
                $subscription = $this->getById(Arr::get($data, 'id'));
            }else {
                $subscription = new Subscription();
                $subscription->created_by = $loggedInUserId;
            }
            if (strtoupper(Arr::get($data, 'refferalCode')) == auth()->user()->affiliateCode->code) {
                return ['error' => EErrorCode::ERROR, 'msg' => '*Không được nhập mã giới thiệu của bạn!'];
            }

            $user = $this->userService->getById($loggedInUserId);
            if (Arr::get($data, 'payment_method') == EPaymentMethod::COIN) {
                $userCoin = $user->getWallet->where('type', EWalletType::INTERNAL_MONEY)->first();
                if ($userCoin->balance < Arr::get($data, 'amount') / 1000) {
                    return ['error' => EErrorCode::ERROR, 'msg' => 'Xu của bạn không đủ'];
                }
                $userCoin->balance -= Arr::get($data, 'amount') / 1000;
                $userCoin->updated_at = now();
                $userCoin->updated_by = $loggedInUserId;
                $userCoin->save();

                $walletTransactionLog = new WalletTransactionLog();
                $walletTransactionLog->user_id = $loggedInUserId;
                $walletTransactionLog->wallet_id = $userCoin->id;
                if (Arr::get($data, 'table_name') == 'shop') {
                    $walletTransactionLog->type = EWalletTransactionLogType::UPDATE_LEVEL_SHOP;
                } else if (Arr::get($data, 'table_name') == 'product') {
                    $walletTransactionLog->type = EWalletTransactionLogType::PUSH_PRODUCT;
                }
                $walletTransactionLog->changed_amount = Arr::get($data, 'amount');
                $walletTransactionLog->created_at = now();
                $walletTransactionLog->status = EStatus::ACTIVE;
                $walletTransactionLog->created_by = $loggedInUserId;
                $walletTransactionLog->payment_method = Arr::get($data, 'payment_method');
                $walletTransactionLog->save();

                $subscription->valid_from = Arr::get($data, 'validFrom');
                $subscription->valid_to = Arr::get($data, 'validTo');

                if (!empty(Arr::get($data, 'refferalCode'))) {
                    $refferalCodeOwner = $this->userService->getByOptions([
                        'refferalCode' => strtoupper(Arr::get($data, 'refferalCode')),
                        'first' => true,
                    ]);
                    if (!empty($refferalCodeOwner)) {
                        $userCoin = $refferalCodeOwner->getWallet->where('type', EWalletType::INTERNAL_MONEY)->first();
                        $userCoin->balance += Arr::get($data, 'paymentPrice') / 1000;
                        $userCoin->updated_at = now();
                        $userCoin->updated_by = $loggedInUserId;
                        $userCoin->save();

                        $walletTransactionLog = new WalletTransactionLog();
                        $walletTransactionLog->user_id = $refferalCodeOwner->id;
                        $walletTransactionLog->wallet_id = $userCoin->id;
                        $walletTransactionLog->type = EWalletTransactionLogType::RECEIVED_COIN_COMMISSION_WHEN_UPDATE_LEVEL_SHOP;
                        $walletTransactionLog->changed_amount = Arr::get($data, 'paymentPrice');
                        $walletTransactionLog->created_at = now();
                        $walletTransactionLog->status = EStatus::ACTIVE;
                        $walletTransactionLog->created_by = $loggedInUserId;
                        $walletTransactionLog->payment_method = Arr::get($data, 'payment_method');
                        $walletTransactionLog->save();

                        $shop = $this->shopService->getById(Arr::get($data, 'table_id'));
                        NotifyUserJob::dispatch([$refferalCodeOwner->id], [
                            'type' => ENotificationType::RECEIVED_COMMISSION,
                            'title' => [
                                ELanguage::VI => 'Thông báo',
                            ],
                            'content' => [
                                ELanguage::VI => 'Bạn nhận được ' . round(Arr::get($data, 'paymentPrice') / 1000) .' xu chiết khấu từ nâng cấp cửa hàng của ' . $shop->name,
                            ],
                            'meta' => [
                                'subscriptionPriceId' => Arr::get($data, 'subscriptionPriceId'),
                            ],
                            'data' => [
                                'subscriptionPriceId' => Arr::get($data, 'subscriptionPriceId'),
                            ]
                        ])->onQueue('pushToDevice');
                    }
                }
            }
            switch (Arr::get($data, 'table_name')) {
                case 'shop':
                    $shop = $this->shopService->getById(Arr::get($data, 'table_id'));
                    $subscriptionPrice = $this->subscriptionPriceService->getById(Arr::get($data, 'subscriptionPriceId'));
                    $shopConfig = ShopLevelConfig::where('level', $subscriptionPrice->meta->level)->first();
                    $shopLevel = new ShopLevel();
                    $shopLevel->shop_id = $shop->id;
                    $shopLevel->num_image_introduce_remain = json_decode($shopConfig->image_introduce)->num_image;
                    // $shopLevel->num_push_product_in_month_remain = $shopConfig->num_push_product_in_month;
                    if (Arr::get($data, 'payment_method') == EPaymentMethod::COIN) {
                        $shop->level = $subscriptionPrice->meta->level;
                        $shop->save();

                        $oldShopLevel = $this->shopLevelService->getByOptions([
                            'status' => EStatus::ACTIVE,
                            'shop_id' => $shop->id,
                            'first' => true,
                        ]);
                        if (!empty($oldShopLevel)) {
                            $oldShopLevel->status = EStatus::DELETED;
                            $oldShopLevel->save();
                        }
                        $shopLevel->status = EStatus::ACTIVE;

                        $subscription->valid_from = now()->copy();
                        $subscription->valid_to = now()->copy()->addDays($subscriptionPrice->num_day);
                    } else {
                        $shopLevel->status = EStatus::WAITING;
                    }
                    $shopLevel->created_at = now();
                    $shopLevel->created_by = $loggedInUserId;
                    $shopLevel->num_product_remain = $shopConfig->num_product;
                    $shopLevel->shop_level_config_id = $shopConfig->id;
                    $shopLevel->num_video_introduce_remain = $shopConfig->video_inproduct;
                    $shopLevel->num_image_in_product_remain = $shopConfig->num_image_in_product;
                    $shopLevel->num_push_product_in_month = str_replace('-', '/', json_encode([
                        'month_in_year' => now()->format(EDateFormat::STANDARD_MONTH_FORMAT),
                        'num_push_product_in_month_remain' => $shopConfig->num_push_product_in_month
                    ]));
                    $shopLevel->save();
                    break;
                case 'product':
                    $subscription->valid_from = Arr::get($data, 'validFrom');
                    $subscription->valid_to = Arr::get($data, 'validTo');
                    if (Arr::get($data, 'payment_method') == EPaymentMethod::FREE) {
                        $product = $this->productService->getByOptions([
                            'id' => Arr::get($data, 'table_id'),
                            'first' => true,
                        ]);

                        $shopLevel = $this->shopLevelService->getByOptions([
                            'shop_id' => $product->getshop->id,
                            'status' => EStatus::ACTIVE,
                            'first' => true,
                        ]);
                        $oldNumPushProduct = !empty($shopLevel->num_push_product_in_month) ? json_decode($shopLevel->num_push_product_in_month) : null;
                        if (!empty($oldNumPushProduct)) {
                            $numRemain = $oldNumPushProduct->num_push_product_in_month_remain - 1;
                            $oldNumPushProduct->num_push_product_in_month_remain = $numRemain;
                            $shopLevel->num_push_product_in_month = json_encode($oldNumPushProduct);
                        }
                        $shopLevel->save();
                    }
                    break;
            }
            if (Arr::get($data, 'table_name') == ConfigTableName::DEPOSIT && empty(Arr::get($data, 'subscriptionPriceId'))) {
                $packageData = [
                    'price' => Arr::get($data, 'amount'),
                    'type' => ESubscriptionPriceType::DEPOSIT
                ];
                $result = $this->subscriptionPriceService->savePackage($packageData, auth()->id());
                Arr::set($data, 'subscriptionPriceId', $result['subscriptionPrice']->id);
            }
            $subscription->code = $this->generateNewCode();
            $subscription->table_id = Arr::get($data, 'table_name') == ConfigTableName::DEPOSIT ? null : Arr::get($data, 'table_id');
            $subscription->table_name = Arr::get($data, 'table_name');
            $subscription->payment_method = Arr::get($data, 'payment_method');
            if (Arr::get($data, 'payment_method') == EPaymentMethod::COIN || Arr::get($data, 'payment_method') == EPaymentMethod::FREE) {
                $subscription->payment_status = EPaymentStatus::PAYMENT_RECEIVED;
            } else {
                $subscription->payment_status = Arr::hasAny($data, 'payment_status') ? Arr::get($data, 'payment_status') : null;
            }
            $subscription->user_id = $loggedInUserId;
            $subscription->status = EStatus::ACTIVE;
            if (!empty(Arr::get($data, 'refferalCode'))) {
                $subscription->payment_meta = !empty(Arr::get($data, 'subscriptionPriceId')) ?
                json_encode([
                    'subscriptionPriceId' => Arr::get($data, 'subscriptionPriceId'),
                    'affiliate_code' => !empty(Arr::get($data, 'refferalCode')) ? Arr::get($data, 'refferalCode') : null,
                ]) : $subscription->payment_meta;
            } else {
                $subscription->payment_meta = !empty(Arr::get($data, 'subscriptionPriceId')) ?
                json_encode([
                    'subscriptionPriceId' => Arr::get($data, 'subscriptionPriceId'),
                ]) : $subscription->payment_meta;
            }
            $subscription->save();
            return ['error' => EErrorCode::NO_ERROR, 'subscription' => $subscription];
        });
    }
}
