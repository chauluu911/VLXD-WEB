<?php

namespace App\Http\Controllers\Front;

use App\Constant\DefaultConfig;
use App\Enums\EErrorCode;
use App\Helpers\ConfigHelper;
use App\Helpers\ValidatorHelper;
use \App\Http\Controllers\Controller;
use App\Constant\ConfigKey;
use App\Enums\EDateFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;
use App\Services\OrderService;
use App\Services\OrderDetailService;
use App\Services\ProductService;
use App\Services\ShopService;
use App\Services\ConfigService;
use App\Http\Requests\Order\SaveInfoRequest;
use App\Enums\EOrderType;
use App\Helpers\FileUtility;
use App\Enums\EStatus;

class CartController extends Controller {

	public function __construct(OrderService $orderService,
								ProductService $productService,
								ShopService $shopService,
								OrderDetailService $orderDetailService,
								ConfigService $configService) {
		$this->orderService = $orderService;
		$this->productService = $productService;
		$this->shopService = $shopService;
		$this->orderDetailService = $orderDetailService;
		$this->configService = $configService;
	}

	public function showCartView() {
		if (!auth()->id()) {
			return redirect()->route('login');
		}
		return view('front.cart.list');
	}

	public function getCartOfUser() {
		$option = [
			'get_cart' => true,
			'type' => EOrderType::SHOPPING_CART,
			'user_id' => auth()->id(),
			'status' => EStatus::WAITING,
		];
		$cart = $this->orderService->getByOptions($option);
		$totalQuantity = 0;
		foreach ($cart as $key) {
			$key->note = null;
			$key->errorNote = null;
			$key->orderDetail = $this->orderDetailService->getByOptions([
				'order_id' => $key->id,
				'status' => EStatus::ACTIVE,
			]);
			$shop = $this->shopService->getByOptions([
				'id' => $key->shop_id,
				'first' => true,
			]);
			$key->shopName = $shop->name;
			$key->url = route('shop', [
				'shopId' => $shop->id,
			]);
			foreach ($key->orderDetail as $value) {
				$totalQuantity += $value->quantity;
				$product = $this->productService->getByOptions([
					'id' => $value->product_id,
					'first' => true,
				]);
				if (!empty($product)) {
					$value->productName = $product->name;
					$image = $product->image->first();
					$value->image = $image ? FileUtility::getFileResourcePath($image->path_to_resource, DefaultConfig::FALLBACK_IMAGE_PATH) : DefaultConfig::FALLBACK_IMAGE_PATH;
					$value->priceStr = !empty($value->price) ? number_format($value->price, 0, '.', '.') . ' đ/' . $product->unit : 0;
					$value->url = route('product.detail', [
						'code' => $product->code
					]);
				}
			}
			$key->totalStr = !empty($key->total) ? number_format($key->total, 0, '.', '.') . ' đ' : 0;
		}
		$score = $this->configService->getByName(ConfigKey::CUMULATIVE_POINTS);
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'cart' => $cart,
			'score' => json_decode($score->text_value),
			'info' => [
				'name' => auth()->user()->name,
				'phone' => auth()->user()->phone,
				'address' => auth()->user()->address,
			],
			'quantity' => $totalQuantity
		]);
	}

	public function deleteOrderDetail() {
		$data = request('data');
		if (!empty(Arr::get($data, 'shop_id'))) {
            $orderDetail = Arr::get($data, 'orderDetail');
            foreach ($orderDetail as $key) {
                $detail = $this->orderService->getByOptions([
                	'order_detail_id' => Arr::get($key, 'id'),
                	'get_detail' => true,
                	'first' => true
                ]);
                $deleteOrder = $this->orderService->deleteOrder($detail->order_id, auth()->id(), Arr::get($key, 'id'));
            }
        } else {
        	$result = $this->orderDetailService->deleteOrderDetail(Arr::get($data, 'id'), auth()->id(), now());
        	if (!empty($result['orderId'])) {
        		$order = $this->orderService->getByOptions([
        			'id' => $result['orderId'],
        			'get_detail' => true,
        		]);
        		if (count($order) == 0) {
        			$deleteOrder = $this->orderService->deleteOrder($result['orderId'], auth()->id());
        		}
        	}
        }
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'msg' => 'Xóa sản phẩm thành công'
		]);
	}

	public function createOrder(SaveInfoRequest $request) {
		$receiverInfo = $request->validated();
		$data = request('cart');
		$result = $this->orderService->convertCartIntoOrder($data, $receiverInfo, auth()->id());
		if ($result['error'] == EErrorCode::NO_ERROR) {
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => 'Tạo đơn hàng thành công',
			]);
		}
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'msg' => __('common/common.there_was_an_error_in_the_processing'),
		]);
	}
}
