<?php

namespace App\Http\Controllers\Front;

use App\Enums\ECustomOrderStatusForUser;
use App\Enums\EDeliveryStatus;
use App\Enums\EErrorCode;
use App\Enums\EOrderStatus;
use App\Enums\EStatus;
use \App\Http\Controllers\Controller;
use App\Constant\ConfigKey;
use App\Enums\EDateFormat;
use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SubscriptionService;
use App\Services\SubscriptionPriceService;
use App\Services\ProductService;
use App\Constant\ConfigTableName;
use App\Services\ConfigService;
use Illuminate\Support\Facades\Validator;
use App\Enums\Payoo\EPayooPaymentDetailType;
use App\Enums\Payoo\EPayooPaymentMethod;
use App\Enums\Payoo\EPayooPaymentStatus;
use App\Enums\EPaymentMethod;
use App\Enums\EPaymentStatus;
use Illuminate\Support\Carbon;
use App\Services\ShopLevelService;

class PaymentController extends Controller {

	public function __construct(SubscriptionService $subscriptionService,
								ConfigService $configService,
								SubscriptionPriceService $subscriptionPriceService,
								ProductService $productService,
                                ShopLevelService $shopLevelService) {
		$this->subscriptionService = $subscriptionService;
		$this->configService = $configService;
		$this->subscriptionPriceService = $subscriptionPriceService;
		$this->productService = $productService;
        $this->shopLevelService = $shopLevelService;
	}

	public function showDepositView() {
		if (!auth()->id()) {
			return redirect()->route('home');
		}
		$data = $this->configService->getByName(ConfigKey::WALLET_DEPOSIT_GUIDE_AMOUNTS)->text_arr_value;
		$contact = [
			'address' => $this->configService->getByName(ConfigKey::CONTACT_ADDRESS)->text_value,
			'phone' => $this->configService->getByName(ConfigKey::CONTACT_PHONE)->text_value,
			'email' => $this->configService->getByName(ConfigKey::CONTACT_EMAIL)->text_value,
		];
        $adminId = $this->configService->getByName(ConfigKey::USER_ADMIN_ID);
        $price = $this->subscriptionPriceService->getByOptions([
            'type' => 2,
            'orderBy' => 'price',
            'orderDirection' => 'asc',
            'created_by' => (int)$adminId->numeric_value
        ]);
        $bankTranferInfo = $this->configService->getByName(ConfigKey::BANK_TRANSFER_INFO);
        $bankTranferInfo->text_arr_value = preg_replace('/^{/', '[', $bankTranferInfo->text_arr_value);
        $bankTranferInfo->text_arr_value = preg_replace('/}$/', ']', $bankTranferInfo->text_arr_value);
		return view('front.payment.deposit', [
			'depositGuideAmountList' => $price,
            'bankTranferInfo' => json_decode(json_decode($bankTranferInfo->text_arr_value)[0]),
			'contact' => $contact,
		]);
	}

	public function showPaymentHistoryView() {
        if (!auth()->id()) {
            return redirect()->route('login');
        }
        return view('front.profile.payment-history');
    }

	public function saveDepositInfo() {
		if (!auth()->id()) {
			return redirect()->route('home');
		}
        $validation = Validator::make(request()->all(), [
            'amount' => 'required|numeric|min:10000',
            'paymentMethod' => 'required',
            'bankCode' => function($attribute, $value, $fail) {
                if (request('paymentMethod') != EPaymentMethod::PAYMENT_GATEWAY) {
                    return;
                }
                if (empty($value)) {
                    $method = EPayooPaymentMethod::nameToValue(request('payooPaymentMethod'));
                    if ($method === EPayooPaymentMethod::INTERNAL_CARD) {
                        $bankCodeType = __('front/payment.err.bank');
                    } elseif (in_array($method, [
                        EPayooPaymentMethod::INTERNATIONAL_CARD_VN,
                        EPayooPaymentMethod::INTERNATIONAL_CARD_OVERSEA,
                    ])) {
                        $bankCodeType = __('front/payment.err.card_type');
                    }
                    if (isset($bankCodeType)) {
                        $fail(__('validation.custom.pick_required', [
                            'attribute' => $bankCodeType
                        ]));
                    }
                }
            }
        ], [
            'amount.required' => __('validation.custom.pick_required', ['attribute' => __('front/payment.amount')]),
            'amount.numeric' => __('validation.custom.pick_required', ['attribute' => __('front/payment.amount')]),
            'amount.min' => __('validation.custom.pick_required', ['attribute' => __('front/payment.amount')]),
            'paymentMethod.required' => __('validation.custom.pick_required', ['attribute' => __('front/payment.paymentMethod')]),
        ], [
            'amount' => __('front/payment.amount'),
            'paymentMethod' => __('front/payment.payment_method')
        ]);
        if ($validation->fails()) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'errors' => $validation->errors()->messages(),
            ]);
        }

        $data = [
            'table_name' => ConfigTableName::DEPOSIT,
            'amount' => (float)request('amount'),
            'user_id' => auth()->id(),
            'payment_method' => request('paymentMethod'),
            'subscriptionPriceId' => request('subscriptionPriceId'),
            'payooPaymentMethod' => request('payooPaymentMethod'),
            'bankCode' => request('bankCode'),
            'continue' => request('continue'),
        ];
        $data['payment_status'] = EPaymentStatus::WAITING;
        if (request()->filled('orderId')) {
            $data['orderId'] = request('orderId');
        }
        try {
            $result = $this->subscriptionService->saveSubscription($data, auth()->id());
            if ($result['error'] !== EErrorCode::NO_ERROR) {
                return response()->json($result);
            }

            $response = [
                'error' => EErrorCode::NO_ERROR,
                'subscriptionId' => $result['subscription']->id,
                'subscriptionCode' => $result['subscription']->code,
            ];
            if ($data['payment_method'] == EPaymentMethod::PAYMENT_GATEWAY) {
                $response['redirect'] = true;
                $response['redirectTo'] = $result['payment_url'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            logger()->error('Error when save deposit info', compact('e'));
            return response()->json([
                'error' => EErrorCode::ERROR,
                'msg' => __('Something went wrong, please try again'),
            ]);
        }
    }

    public function showPaymentView($type, $tableId = null, $subscriptionPriceId = null) {
    	if (!auth()->id()) {
			return redirect()->route('home');
		}
        $subscription = $this->subscriptionService->getByOptions([
            'table_id' => $tableId,
            'table_name' => $type,
            'user_id' => auth()->id(),
            'subscription_price_id' => $subscriptionPriceId,
            'payment_status' => EPaymentStatus::WAITING,
            'first' => true,
        ]);
        if (!empty($subscription)) {
            return redirect()->route('home');
        }
    	$paymentInfo = [];
    	switch ($type) {
    		case 'product':
    			// $subscription = $this->subscriptionService->getByOptions([
    			// 	'table_id' => $tableId,
    			// 	'table_name' => $type,
    			// 	'user_id' => auth()->id(),
    			// 	'first' => true,
    			// ]);
				$getSubscriptionPrice = $this->subscriptionPriceService->getByOptions([
    				'id' => $subscriptionPriceId,
    				'first' => true,
    			]);

    			$getProductInfo = $this->productService->getByOptions([
    				'id' => $tableId,
    				'first' => true,
    			]);

                $shopLevel = $this->shopLevelService->getByOptions([
                    'shop_id' => $getProductInfo->getshop->id,
                    'status' => EStatus::ACTIVE,
                    'first' => true,
                ]);
                // if ($shopLevel->num_push_product_in_month_remain <= 0) {
                //     return redirect()->route('home');
                // }
    			$paymentInfo = [
    				'tableId' => $tableId,
                    'subscriptionPriceId' => $subscriptionPriceId,
    				'subscriptionPriceName' => $getSubscriptionPrice->name,
    				'detail' => $getSubscriptionPrice->description,
    				'price' => $getSubscriptionPrice->price,
                    'priceStr' => number_format($getSubscriptionPrice->price, 0, '.', '.') . 'đ',
                    'paymentPriceStr' => null,
                    'paymentPrice' => null,
    				'numDay' => $getSubscriptionPrice->num_day,
    				'product' => $getProductInfo->name,
    				'type' => $type,
    				'title' => 'Thanh toán gói ưu tiên'
    			];

    			if (!empty($subscription) && $subscription->payment_status == EPaymentStatus::PAYMENT_RECEIVED) {
                    $canSkipPayment = true;
                }
                if ($canSkipPayment ?? false) {
                    return redirect()->route('home');
                }
    			break;

    		case 'shop':
                $getSubscriptionPrice = $this->subscriptionPriceService->getByOptions([
                    'id' => $subscriptionPriceId,
                    'first' => true,
                ]);
                $shopSubscription = $this->subscriptionService->getByOptions([
                    'table_name' => 'shop',
                    'table_id' => $tableId,
                    'orderBy' => 'id',
                    'orderDirection' => 'desc',
                    'payment_status' => EPaymentStatus::PAYMENT_RECEIVED,
                    'first' => true,
                ]);
                if (empty($shopSubscription)) {
                    $paymentPrice = $getSubscriptionPrice->price;
                } else {
                    $dayValid = now()->startOfDay()->diffInDays(Carbon::parse($shopSubscription->valid_to)->startOfDay());
                    $shopSubscriptionPrice = $this->subscriptionPriceService->getByOptions([
                        'id' => json_decode($shopSubscription->payment_meta)->subscriptionPriceId,
                        'first' => true,
                    ]);
                    $numDay = !empty($shopSubscriptionPrice->numDay) ? $shopSubscriptionPrice->numDay : 0;
                    if ($dayValid == $numDay || $numDay == 0) {
                        $paymentPrice = $getSubscriptionPrice->price;
                    } else {
                        $paymentPrice = $getSubscriptionPrice->price - round((($dayValid * $shopSubscriptionPrice->price) / $numDay) * 100);
                    }
                }
    			$paymentInfo = [
                    'tableId' => $tableId,
                    'subscriptionPriceName' => $getSubscriptionPrice->name,
                    'price' => $getSubscriptionPrice->price,
                    'paymentPrice' => $paymentPrice,
                    'paymentPriceStr' => number_format($paymentPrice, 0, '.', '.') . 'đ',
                    'priceStr' => number_format($getSubscriptionPrice->price, 0, '.', '.') . 'đ',
                    'type' => $type,
                    'title' => 'Thanh toán nâng cấp cửa hàng',
                    'subscriptionPriceId' => $subscriptionPriceId,
                ];
    			break;
    	}

    	$contact = [
			'address' => $this->configService->getByName(ConfigKey::CONTACT_ADDRESS)->text_value,
			'phone' => $this->configService->getByName(ConfigKey::CONTACT_PHONE)->text_value,
			'email' => $this->configService->getByName(ConfigKey::CONTACT_EMAIL)->text_value,
		];
        //$bankTranferInfo = $this->configService->getByName(ConfigKey::WALLET_DEPOSIT_GUIDE_AMOUNTS);
        $bankTranferInfo = $this->configService->getByName(ConfigKey::BANK_TRANSFER_INFO);
        $bankTranferInfo->text_arr_value = preg_replace('/^{/', '[', $bankTranferInfo->text_arr_value);
        $bankTranferInfo->text_arr_value = preg_replace('/}$/', ']', $bankTranferInfo->text_arr_value);
    	return view('front.payment.common', [
			'paymentInfo' => $paymentInfo,
            'bankTranferInfo' => json_decode(json_decode($bankTranferInfo->text_arr_value)[0]),
			'contact' => $contact
		]);
    }

    public function savePaymentInfo($type, $tableId = null, $subscriptionPriceId = null) {
        $subscription = $this->subscriptionService->getByOptions([
            'table_id' => $tableId,
            'table_name' => $type,
            'user_id' => auth()->id(),
            'subscription_price_id' => $subscriptionPriceId,
            'payment_status' => EPaymentStatus::WAITING,
            'first' => true,
        ]);
        if (!empty($subscription)) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'redirectTo' => redirect()->route('home'),
            ]);
        }
        // $validation = Validator::make(request()->all(), [
        //     // 'subscriptionPriceId' => function($attribute, $value, $fail) use ($type) {
        //     //     if ($type !== 'subscription') {
        //     //         return;
        //     //     }
        //     //     $subscriptionPrice = $this->subscriptionPriceService->getById(request('subscriptionPriceId'));
        //     //     if (empty($subscriptionPrice)) {
        //     //         $fail(__('validation.custom.pick_required', [
        //     //             'attribute' => __('front/payment.exam-package')
        //     //         ]));
        //     //     }
        //     // },
        //     'paymentMethod' => 'required',
        //     'bankCode' => function($attribute, $value, $fail) {
        //         if (request('paymentMethod') != EPaymentMethod::PAYMENT_GATEWAY) {
        //             return;
        //         }
        //         if (empty($value)) {
        //             $method = EPayooPaymentMethod::nameToValue(request('payooPaymentMethod'));
        //             if ($method === EPayooPaymentMethod::INTERNAL_CARD) {
        //                 $bankCodeType = __('front/payment.err.bank');
        //             } elseif (in_array($method, [
        //                 EPayooPaymentMethod::INTERNATIONAL_CARD_VN,
        //                 EPayooPaymentMethod::INTERNATIONAL_CARD_OVERSEA,
        //             ])) {
        //                 $bankCodeType = __('front/payment.err.card_type');
        //             }
        //             if (isset($bankCodeType)) {
        //                 $fail(__('validation.custom.pick_required', [
        //                     'attribute' => $bankCodeType
        //                 ]));
        //             }
        //         }
        //     }
        // ], [
        //     'paymentMethod.required' => __('validation.custom.pick_required', ['attribute' => __('front/payment.paymentMethod')]),
        // ], [
        //     'paymentMethod' => __('front/payment.payment_method')
        // ]);
        // if ($validation->fails()) {
        //     return response()->json([
        //         'error' => ErrorCode::SYSTEM_ERROR,
        //         'errors' => $validation->errors()->messages(),
        //     ]);
        // }
        $data = [
            'id' => null,
            'table_name' => null,
            'table_id' => null,
            'validFrom' => null,
            'validTo' => null,
            'user_id' => auth()->id(),
            'payment_method' => request('paymentMethod'),
            'payooPaymentMethod' => request('payooPaymentMethod'),
            'bankCode' => request('bankCode'),
        ];

        $subscription = $this->subscriptionService->getByOptions([
			'table_id' => $tableId,
			'table_name' => $type,
			'user_id' => auth()->id(),
            'null_payment_status' => true,
			'first' => true,
		]);
        if (request()->filled('subscriptionId')) {
            $data['subscriptionId'] = request('subscriptionId');
        }
        switch ($type) {
            case 'product':
                // $subscriptionPrice = $this->subscriptionPriceService->getById(request('subscriptionPriceId'));
                if ($data['payment_method'] == EPaymentMethod::FREE) {
                    $subscriptionPrice = $this->subscriptionPriceService->getById($subscriptionPriceId);
                } else {
                    $subscriptionPrice = $this->subscriptionPriceService->getById(json_decode($subscription->payment_meta)->subscriptionPriceId);
                }
                $data['table_name'] = ConfigTableName::PRODUCT;
                $data['table_id'] = $tableId;
                $data['amount'] = $subscriptionPrice->price;
                $data['validFrom'] = now();
                $data['validTo'] = now()->addDays($subscriptionPrice->num_day);
                $data['subscriptionPriceId'] = $subscriptionPrice->id;
                $data['id'] = !empty($subscription) ? $subscription->id : null;
                $product = $this->productService->getById($tableId);
                break;
            case 'shop':
                $subscriptionPrice = $this->subscriptionPriceService->getById($subscriptionPriceId);
                $data['subscriptionPriceId'] = $subscriptionPrice->id;
            	$data['table_name'] = ConfigTableName::SHOP;
                $data['table_id'] = $tableId;
                $data['amount'] = $subscriptionPrice->price;
                $data['paymentPrice'] = $subscriptionPrice->price;
                $data['validFrom'] = now();
                $data['paymentPrice'] = request('paymentPrice');
                $data['refferalCode'] = request('refferalCode');
                $data['validTo'] = now()->addDays($subscriptionPrice->num_day);
            	break;
        }
        $data['payment_status'] = EPaymentStatus::WAITING;
        try {
            $result = $this->subscriptionService->saveSubscription($data, auth()->id());
            if ($result['error'] !== EErrorCode::NO_ERROR) {
                return response()->json($result);
            }

            $response = [
                'error' => EErrorCode::NO_ERROR,
                'subscriptionId' => $result['subscription']->id,
                'subscriptionCode' => $result['subscription']->code,
                'redirectTo' =>!empty($product) ? route(
                    'shop.product.detail', [
                        'shopId' => $product->getShop->id,
                        'code' => $product->code
                    ], false
                ) : route(
                    'shop.edit', [
                        'shopId' => $tableId,
                    ], false
                )
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            logger()->error('Error when save payment info', compact('e'));
            return response()->json([
                'error' => EErrorCode::ERROR,
                'msg' => __('Đã có lỗi xảy ra, vui lòng thử lại'),
            ]);
        }
    }

    public function getPaymentHistory() {
	    $userId = auth()->id();
        if (!$userId) {
            return response()->json([
                'error' => EErrorCode::UNAUTHORIZED,
                'redirectTo' => route('login'),
            ]);
        }
        $options = [
            'pageSize' => request('pageSize'),
            'status' => EStatus::ACTIVE,
            'user_id' => $userId,
            'type_list' => true,
            'payment_status' => EPaymentStatus::PAYMENT_RECEIVED,
        ];
        $payments = $this->subscriptionService->getByOptions($options);
        $tmp = $payments->map(function(Subscription $payment){
            return [
                'id' => $payment->id,
                'price' => $payment->payment_method == EPaymentMethod::COIN ?
                    '-' . $payment->price / 1000 . ' xu'  :
                    number_format($payment->price,
                        0, '.', '.') . ' đ',
                'createdAt' => $payment->created_at,
                'name' => $payment->name ? $payment->name :
                    'Nạp xu: +' . $payment->price / 1000 . 'xu'
            ];
        });
        $payments->setCollection($tmp);
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'payments' => $payments,
        ]);
    }
}
