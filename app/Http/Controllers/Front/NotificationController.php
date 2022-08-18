<?php

namespace App\Http\Controllers\Front;
use App\Enums\ELanguage;
use App\Enums\EStatus;
use \App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Enums\EErrorCode;
use App\Enums\EDateFormat;
use App\Enums\ENotificationType;
use Illuminate\Support\Facades\Validator;
use App\Services\NotificationService;
use App\Services\ProductService;
use App\Services\ShopService;
use App\Services\OrderService;
use App\Constant\ConfigTableName;
use App\Enums\EOrderStatus;
use App\Enums\EDeliveryStatus;

class NotificationController extends Controller {
	protected NotificationService $notificationService;
    protected ShopService $shopService;
    protected OrderService $orderService;

	public function __construct(NotificationService $notificationService, ProductService $productService, ShopService $shopService, OrderService $orderService) {
		$this->notificationService = $notificationService;
        $this->productService = $productService;
        $this->shopService = $shopService;
        $this->orderService = $orderService;
	}

	public function getNotificationForUser(Request $request) {
        $pageSize = $request->get('pageSize');
        $userId = auth()->id();
        $notifications = $this->notificationService->getNotificationForUser($userId, $pageSize)
        	->map(function(Notification $item) {
        		$result = $item->only('id', 'title', 'content', 'type', 'created_at', 'is_seen', 'meta');
                if (!empty($item->created_at)) {
                    $result['created_at'] = $item->created_at->format(EDateFormat::STANDARD_DATE_FORMAT);
                }
                switch ($result['type']) {
                	case ENotificationType::SYSTEM:
                    case ENotificationType::APPROVED_PAYMENT_COINS:
                    case ENotificationType::FOLLOW:
                    case ENotificationType::RECEIVED_COMMISSION:
                        $result['href'] = null;
                        break;
                    case ENotificationType::APPROVED_PUSH_PRODUCT:
                    case ENotificationType::RECEIVED_NOTIFICATION_PRODUCT_CREATED_BY_SHOP:
                	case ENotificationType::APPROVED_PRODUCT:
                        $product = $this->productService->getById($item->meta->productId);
                		if (!empty($product) && !empty(auth()->user()->getShop)) {
                            $result['href'] = route('shop.product.detail', [
                                'shopId' => auth()->user()->getShop->id,
                                'code' => $product->code
                            ], false);
                        } else {
                            $result['href'] = null;
                        }
                		break;
                    case ENotificationType::APPROVED_UPGRADE_SHOP:
                    case ENotificationType::APPROVED_SHOP:
                        $shop = $this->shopService->getById($item->meta->shopId);
                        if (!empty($shop) && $shop->status != EStatus::DELETED) {
                            $result['href'] = route('shop.edit', [
                                'shopId' => $shop->id,
                            ], false);
                        } else {
                            $result['href'] = null;
                        }
                        break;
                    case ENotificationType::APPROVED_BANNER:
                        $shop = $this->shopService->getById($item->meta->shopId);
                        if (!empty($shop) && $shop->status != EStatus::DELETED) {
                            $result['href'] = route('shop.banner', [
                                'shopId' => $shop->id,
                            ], false);
                            $result['bannerId'] = $item->meta->bannerId;
                        } else {
                            $result['href'] = null;
                        }
                        break;
                    case ENotificationType::APPROVED_ORDER:
                        $order = $this->orderService->getByOptions([
                            'id' => $item->meta->orderId,
                            'first' => true
                        ]);
                        if (!empty($order)) {
                            if ($order->status == EOrderStatus::CANCEL_BY_USER) {
                                $result['href'] = route('orderShop.detail', [
                                    'code' => $order->code,
                                    'shopId' => $order->shop_id
                                ], false);
                            } else if ($order->delivery_status != EDeliveryStatus::DELIVERED_SUCCESS) {
                                $result['href'] = route('order.detail', [
                                    'code' => $order->code,
                                ], false);
                            }
                        } else {
                            $result['href'] = null;
                        }
                        break;
                    case ENotificationType::RECEIVED_NOTIFICATION_ORDER_CREATED_BY_USER:
                        $order = $this->orderService->getByOptions([
                            'id' => $item->meta->orderId,
                            'first' => true
                        ]);
                        if (!empty($order)) {
                            $result['href'] = route('orderShop.detail', [
                                'code' => $order->code,
                                'shopId' => $order->shop_id
                            ], false);
                        } else {
                            $result['href'] = null;
                        }
                        break;
                    case ENotificationType::MESSAGE_NOTIFICATION:
                        $result['href'] = route('chat', [], false);
                        break;
                }
                return $result;
        	});
        $numberNotSeen = $this->notificationService->getNotificationNumberNotSeen($userId);
        $numberNotification = $this->notificationService->getNumberOfNotification($userId);
        return response()->json([
        	'notification' => $notifications,
        	'numberNotification' => $numberNotification,
            'numberNotSeen' => $numberNotSeen
        ]);
	}

	public function seenNotification(Request $request) {
		$notifications = $request->get('notificationId');
		$notification = $this->notificationService->seenNotification($notifications, auth()->id());
		if (!empty($notification)) {
			return \Response::json(['error' => EErrorCode::NO_ERROR, 'message' => __('common.success')]);
		}
		return \Response::json(['error' => EErrorCode::SYSTEM_ERROR, 'message' => 'Error !!']);
	}

	//mark all notification
	// public function markAllNotification() {
	// 	$markAll = $this->notificationService->markAllNotification(auth()->id());
	// 	if ($markAll) {
	// 		return \Response::json(['markAll' => $markAll]);
	// 	}
	// }
}
