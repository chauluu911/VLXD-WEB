<?php

namespace App\Http\Controllers\Front;


use App\Constant\CacheKey;
use App\Constant\DefaultConfig;
use App\Enums\EAvatarType;
use App\Enums\ECustomOrderStatusForUser;
use App\Enums\EDateFormat;
use App\Enums\EDeliveryStatus;
use App\Enums\EDisplayStatus;
use App\Enums\EErrorCode;
use App\Enums\EOrderStatus;
use App\Enums\EPaymentMethod;
use App\Enums\EStatus;
use \App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\UserSaveFeedbackRequest;
use App\Http\Requests\Order\ApproveOrderRequest;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Http\Requests\Order\ReviewOrderRequest;
use App\Models\Country;
use App\Models\Category;
use App\Services\AreaService;
use App\Services\CategoryService;
use App\Services\CountryService;
use App\Services\FeedbackService;
use App\Services\OrderService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\Order;

class OrderController extends Controller {
    private OrderService $orderService;

    public function __construct(OrderService $orderService) {
        $this->orderService = $orderService;
    }

    public function showOrderListView() {
        if (!auth()->id()) {
            return redirect()->route('login');
        }
        return view('front.order.list',['isOrderOfShop' => false]);
    }

    public function showOrderListViewOfShop($shopId =null) {
        if (!auth()->id() || !$shopId)  {
            return redirect()->route('login');
        }
        if (auth()->user()->getShop) {
            $shopIdOfAuthUser = auth()->user()->getShop->id;
        } else {
            $shopIdOfAuthUser = $shopId;
        }

        if ($shopIdOfAuthUser != $shopId) {
            return redirect()->route('shop', ['shopId' => $shopIdOfAuthUser]);
        }

        return view('front.order.list',['isOrderOfShop' => true, 'shopId' =>$shopIdOfAuthUser]);
    }



    public function showOrderDetailView($code=null) {
        if (!auth()->id()) {
            return redirect()->route('login');
        }
        return view('front.order.detail', ['code' => $code,'isOrderOfShop' => false]);
    }

    public function showOrderDetailViewOfShop($shopId = null,$code=null) {
        if (!auth()->id() || !$shopId) {
            return redirect()->route('login');
        }
       if (auth()->user()->getShop) {
            $shopIdOfAuthUser = auth()->user()->getShop->id;
        } else {
            $shopIdOfAuthUser = $shopId;
        }
        if ($shopIdOfAuthUser != $shopId) {
            return redirect()->route('shop', ['shopId' => $shopIdOfAuthUser]);
        }
        return view('front.order.detail',
            ['code' => $code, 'isOrderOfShop' => true, 'shopId' =>$shopIdOfAuthUser]);
    }


    public function getOrderList() {
        $userId = auth()->id();
        if(!$userId) {
            return response()->json([
                'error' => EErrorCode::UNAUTHORIZED,
                'redirectTo' => route('login'),
            ]);
        }
        $options = [
            'pageSize' => request('pageSize'),
            'orderBy' => 'created_at',
            'orderDirection' => 'desc',
            'user_id' => $userId
        ];

        $acceptFields = ['q','getForOrderListOfUser','getForOrderListOfShop',
            'type', 'customOrderStatusForUser','shopId' ];
        $isOrderOfShop = request()->filled('getForOrderListOfShop');
        $isOrderOfUser = request()->filled('getForOrderListOfUser');
        if($isOrderOfShop){
            if(!request()->filled('shopId')) {
                //error shopId absent
            }
            $shopId = (request('shopId'));
            if(auth()->user()->getShop && $shopId != auth()->user()->getShop->id){
                //error can't get order list from another shop
            }
        }

        foreach ($acceptFields as $field) {
            if (!request()->filled($field)) {
                continue;
            }
            $options[Str::snake($field)] = request($field);
        }
        $orders = $this->orderService->getByOptions($options);
        $tmp = $orders->map(function(Order $order) use ($isOrderOfShop, $isOrderOfUser) {
            switch ($order->status) {
                case EOrderStatus::CANCEL_BY_SHOP:
                case EOrderStatus::CANCEL_BY_USER: {
                    $order->status = ECustomOrderStatusForUser::CANCELED;
                    break;
                }
                case EOrderStatus::WAITING: {
                    $order->status = ECustomOrderStatusForUser::WAITING;
                    break;
                }
                case EOrderStatus::CONFIRMED: {
                    switch ($order->delivery_status) {
                        case EDeliveryStatus::WAITING_FOR_APPROVAL :
                            $order->status = ECustomOrderStatusForUser::WAITING_FOR_DELIVERY;
                            break;
                        case EDeliveryStatus::ON_THE_WAY:
                            $order->status = ECustomOrderStatusForUser::ON_THE_WAY;
                            break;
                        case EDeliveryStatus::DELIVERED_SUCCESS:
                            $order->status = ECustomOrderStatusForUser::DELIVERED_SUCCESS;
                            break;
                    }
                    break;
                }
            }
            $order->statusStr = ECustomOrderStatusForUser::valueToLocalizedName($order->status);
            $orderDetails = $order->orderDetails;
            $order->totalProduct = 0;
            $orderDetails->each(function ($item, $key) use ($order) {
                $order->totalProduct =$order->totalProduct + $item->quantity;
            });

            if($order->canceled_by) {
                $order->canceled_by = $order->canceled_by === $order->user_id ? "user" : 'shop';
            }

            if($isOrderOfShop) {
                $order->userInfo = [
                    'name' => $order->user_name,
                    'phone' => $order->user_phone,
                    'avatarPath' => !empty($order->user_avatar) ? get_image_url([
                        'path' => $order->user_avatar,
                        'op' => 'thumbnail',
                        'w' => 130,
                        'h' => 130,
                    ]): DefaultConfig::FALLBACK_USER_AVATAR_PATH,
                ];
            }
            if($isOrderOfUser) {
                $avatarPath = DefaultConfig::FALLBACK_USER_AVATAR_PATH;
                if(!empty($order->shop_avatar)) {
                    if($order->shop_avatar_type === EAvatarType::IMAGE) {
                        $avatarPath = get_image_url([
                            'path' => $order->shop_avatar,
                            'op' => 'thumbnail',
                            'w' => 130,
                            'h' => 130,
                        ]);
                    } else {
                        $avatarPath = config('app.resource_url_path') ."/" .$order->shop_avatar;
                    }
                }
                $order->shopInfo = [
                    'name' => $order->shop_name,
                    'phone' => $order->shop_phone,
                    'avatarPath' => $avatarPath,
                    'avatarType' => $order->shop_avatar_type,
                ];
            }
            return [
                'code' => $order->code,
                'status'=> $order->status,
                'statusStr' => $order->statusStr,
                'shippingFee' => is_null($order->shipping_fee) ? 'chưa cập nhật' :
                    number_format($order->shipping_fee, 0, '.', '.') . ' đ',
                'totalProduct' => $order->totalProduct,
                'userInfo' => $isOrderOfShop ? $order->userInfo : null,
                'shopInfo' => $isOrderOfUser ? $order->shopInfo : null,
                'shopId'=> $order->shop_id,
                'canceledBy' => $order->canceled_by,
                'receiverName' => $order->receiver_name,
                'totalPrice' => number_format($order->total,
                    0, '.', '.') . ' đ',
                'createdAt' => $order->created_at,
                'rated' => $order->rated,
                'isOrderOfShop' => $isOrderOfShop ? true : false,
                'redirectTo' => request()->filled('getForOrderListOfShop') ?
                    route('orderShop.detail',[
                        'code' => $order->code,
                        'shopId' => request('shopId'),
                    ], false) :
                    route('order.detail', [
                    'code' => $order->code
                ], false),
            ];
        });
        $orders->setCollection($tmp);
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'orders' => $orders,
        ]);
    }

    public function getInfoOrder($code = null ) {
        $userId = auth()->id();
        if(!$userId) {
            return response()->json([
                'error' => EErrorCode::UNAUTHORIZED,
                'redirectTo' => route('login'),
            ]);
        }

        $order = $this->orderService->getByCode($code);
        //dd($order);
        if (empty($order)) {
            if(request()->filled('isOrderOfShop')) {
                if (auth()->user()->getShop) {
                    $shopId = auth()->user()->getShop->id;
                }
                return response()->json([
                    'error' => EErrorCode::ERROR,
                    'redirectTo' => route('orderShop.list',['shopId' => $shopId]),
                ]);
            }
            return response()->json([
                'error' => EErrorCode::ERROR,
                'redirectTo' => route('order.list'),
            ]);
        }

        if(request()->filled('isOrderOfShop')) {
            if (auth()->user()->getShop) {
                $shopId = auth()->user()->getShop->id;
            }
            if($order->shop_id != $shopId) {
                return response()->json([
                    'error' => EErrorCode::UNAUTHORIZED,
                    'redirectTo' => route('orderShop.list',['shopId' => $shopId]),
                ]);
            }
        } else {
            $userId = auth()->id();
            if($order->user_id != $userId) {
                return response()->json([
                    'error' => EErrorCode::UNAUTHORIZED,
                    'redirectTo' => route('order.list'),
                ]);
            }
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

        switch ($order->status) {
            case EOrderStatus::CANCEL_BY_SHOP:
            case EOrderStatus::CANCEL_BY_USER: {
                $order->status = ECustomOrderStatusForUser::CANCELED;
                break;
            }
            case EOrderStatus::WAITING: {
                $order->status = ECustomOrderStatusForUser::WAITING;
                break;
            }
            case EOrderStatus::CONFIRMED: {
                switch ($order->delivery_status) {
                    case EDeliveryStatus::WAITING_FOR_APPROVAL :
                        $order->status = ECustomOrderStatusForUser::WAITING_FOR_DELIVERY;
                        break;
                    case EDeliveryStatus::ON_THE_WAY:
                        $order->status = ECustomOrderStatusForUser::ON_THE_WAY;
                        break;
                    case EDeliveryStatus::DELIVERED_SUCCESS:
                        $order->status = ECustomOrderStatusForUser::DELIVERED_SUCCESS;
                        break;
                }
                break;
            }
        }
        $order->statusStr = ECustomOrderStatusForUser::valueToLocalizedName($order->status);

        $data = [
            'code' => $order->code,
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
            'statusStr' => $order->statusStr,
            'shippingFee' => is_null($order->shipping_fee) ? 'chưa cập nhật' :
                number_format($order->shipping_fee, 0, '.', '.') . ' đ',
            'totalPrice' =>number_format($order->total,
                    0, '.', '.') . ' đ',
            'shopName' => $shop->name,
            'productsOfOrder' => $productsOfOrder,
            'rated' => $order->rated,
            'isOrderOfShop' => request()->filled('isOrderOfShop') ? true : false,
        ];

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'order' => $data,
        ]);
    }

    public function approveAndUpdateShippingFeeOrder(ApproveOrderRequest $request) {
        $data = $request->validated();
        $code = $data['code'];
        if (auth()->user()->getShop) {
            $shopId = auth()->user()->getShop->id;
        }
        $shippingFee = $data['shippingFee'];
        $approve = $this->orderService
            ->approveAndUpdateShippingFeeOrder($code,$shopId,$shippingFee, auth()->id());
        if ($approve['error'] !== EErrorCode::NO_ERROR) {
            return response()->json($approve);
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.update-data-success', [
                'objectName' => __('front/order.object_name')
            ])
        ]);
    }

    public function cancelOrder( CancelOrderRequest $request) {
        $data = $request->validated();
        $code = $data['code'];
        $cancelBy = $data['cancelBy'];
        $cancelReason = $data['cancelReason'];
        if (auth()->user()->getShop) {
            $shopId = auth()->user()->getShop->id;
        }
        $shopId = $cancelBy == "shop" ? $shopId : null;

        $cancel = $this->orderService
            ->cancelOrder($code,$shopId,$cancelReason,$cancelBy, auth()->id());
        if ($cancel['error'] !== EErrorCode::NO_ERROR) {
            return response()->json($cancel);
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.cancel-data-success', [
                'objectName' => __('front/order.object_name')
            ])
        ]);
    }

    public function reviewOrder( ReviewOrderRequest $request) {
        $data = $request->validated();
        $review = $this->orderService
            ->reviewOrder($data, auth()->id());
        if ($review['error'] !== EErrorCode::NO_ERROR) {
            return response()->json($review);
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.review-data-success2')
        ]);
    }

    public function approveDeliveryOrder () {
        $code = request('code');
        if (auth()->user()->getShop) {
            $shopId = auth()->user()->getShop->id;
        }
        $approveDelivery = $this->orderService
            ->approveDeliveryOrder($code,$shopId, auth()->id());
        if ($approveDelivery['error'] !== EErrorCode::NO_ERROR) {
            return response()->json($approveDelivery);
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.update-data-success', [
                'objectName' => __('front/order.object_name')
            ])
        ]);
    }

    public function completeOrder () {
        $code = request('code');
        if (auth()->user()->getShop) {
            $shopId = auth()->user()->getShop->id;
        }
        $completeOrder = $this->orderService
            ->completeOrder($code,$shopId, auth()->id());
        if ($completeOrder['error'] !== EErrorCode::NO_ERROR) {
            return response()->json($completeOrder);
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.update-data-success', [
                'objectName' => __('front/order.object_name')
            ])
        ]);
    }


}
