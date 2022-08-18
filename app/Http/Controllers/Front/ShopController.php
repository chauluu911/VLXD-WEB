<?php

namespace App\Http\Controllers\Front;

use App\Constant\DefaultConfig;
use App\Enums\EErrorCode;
use App\Enums\ETableName;
use App\Enums\EVideoType;
use App\Helpers\ConfigHelper;
use App\Helpers\StringUtility;
use App\Helpers\ValidatorHelper;
use \App\Http\Controllers\Controller;
use App\Constant\ConfigKey;
use App\Enums\EDateFormat;
use App\Enums\EStatus;
use App\Enums\ELevelName;
use App\Enums\EApprovalStatus;
use App\Enums\ENotificationType;
use App\Enums\ELanguage;
use App\Enums\Banner\EBannerActionType;
use App\Enums\Banner\EBannerType;
use App\Enums\EPlatform;
use App\Enums\ESubscriptionPriceType;
use App\Services\ReviewService;
use App\Services\ShopResourceService;
use App\Services\BannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ConfigService;
use App\Services\ShopService;
use App\Services\NotificationScheduleService;
use App\Http\Requests\Shop\SaveInfoRequest;
use App\Http\Requests\Shop\SaveBannerRequest;
use App\Helpers\FileUtility;
use App\Http\Requests\Notification\SaveNotificationScheduleRequest;
use App\Constant\SessionKey;
use App\Services\ShopLevelConfigService;
use App\Services\ShopLevelService;
use App\Services\FollowService;
use App\Services\ConversationMemberService;
use App\Services\SubscriptionService;
use App\Services\SubscriptionPriceService;
use App\Enums\EPaymentStatus;
use App\Enums\EAreaType;

class ShopController extends Controller {

	public function __construct(ShopService $shopService,
			ShopResourceService $shopResourceService,
			NotificationScheduleService $notificationScheduleService,
			BannerService $bannerService,
			ConfigService $configService,
			ReviewService $reviewService,
			ShopLevelConfigService $shopLevelConfigService,
			ShopLevelService $shopLevelService,
			FollowService $followService,
			ConversationMemberService $conversationMemberService,
			SubscriptionService $subscriptionService,
			SubscriptionPriceService $subscriptionPriceService) {
		$this->shopService = $shopService;
		$this->reviewService = $reviewService;
		$this->shopResourceService = $shopResourceService;
		$this->notificationScheduleService = $notificationScheduleService;
		$this->bannerService = $bannerService;
		$this->configService = $configService;
		$this->shopLevelConfigService = $shopLevelConfigService;
		$this->shopLevelService = $shopLevelService;
		$this->followService = $followService;
		$this->conversationMemberService = $conversationMemberService;
		$this->subscriptionPriceService = $subscriptionPriceService;
		$this->subscriptionService = $subscriptionService;
	}

	public function showShopView($shopId = null) {
		// if (!auth()->id()) {
		// 	return redirect()->route('home');
		// }
		if (empty(auth()->user()->getShop) && empty($shopId)) {
			return redirect()->route('shop.create');
		}else {
			return view('front.shop.shop-info', [
				'id' => $shopId,
			]);
		}
	}

	public function showCreateShopView($shopId = null) {
		if (empty($shopId) && !empty(auth()->user()->getShop) && empty($shopId)) {
			return redirect(
				route(
					'shop.edit', [
						'shopId' => auth()->user()->getShop->id,
					], false
				)
			);
		}
		if (!auth()->id() || !empty(auth()->user()->getShop) && auth()->user()->getShop->id != $shopId) {
			return redirect()->route('login');
		}
		// if (empty(auth()->user()->getShop)) {
		// 	return redirect()->route('shop.create');
		// }
		return view('front.shop.create-update-shop', [
			'shopId' => $shopId,
		]);
	}

    public function showResourceView($shopId = null) {
        if (!auth()->id()) {
            return redirect()->route('login', ['continue'=>url()->current()]);
        }
        if (empty(auth()->user()->getShop)) {
            return redirect()->route('shop.create');
        }
        if (!$shopId) {
            return redirect()->route('home');
        }
        if (auth()->user()->getShop->id != $shopId) {
            return redirect()->route('shop', ['shopId' => $shopId]);
        }
        return view('front.shop.shop-resource', [
            'shopId' => $shopId,
        ]);
    }

    public function getShopResourceList($shopId) {
        if (!auth()->id()) {
            return response()->json([
                'error' => EErrorCode::UNAUTHORIZED,
                'redirectTo' => route('login'),
            ]);
        }

        if (empty(auth()->user()->getShop)) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'redirectTo' => route('shop.create'),
            ]);
        }
        $filters = [
            'shop_id' => $shopId,
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

    public function deleteShopResource($shopId) {
        if (!auth()->id()) {
            return response()->json([
                'error' => EErrorCode::UNAUTHORIZED,
                'redirectTo' => route('login'),
            ]);
        }
        if (empty(auth()->user()->getShop)) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'redirectTo' => route('shop.create'),
            ]);
        }
        if (!$shopId) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'redirectTo' => route('home'),
            ]);
        }
        if (auth()->user()->getShop->id != $shopId) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'redirectTo' => route('shop', ['shopId' => $shopId]),
            ]);
        }
        $resourceId = request('id');
        $result = $this->shopResourceService->deleteResource($resourceId,$shopId, auth()->id());

        if ($result['error'] != EErrorCode::NO_ERROR) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'msg' => __('common/common.there_was_an_error_in_the_processing'),
            ]);
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.delete-data-success', [
                'objectName' => __('front/shop.resource')
            ])
        ]);
    }

    public function saveShopResource($shopId) {
        if (!auth()->id()) {
            return response()->json([
                'error' => EErrorCode::UNAUTHORIZED,
                'redirectTo' => route('login'),
            ]);
        }
        if (empty(auth()->user()->getShop)) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'redirectTo' => route('shop.create'),
            ]);
        }
        if (!$shopId) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'redirectTo' => route('home'),
            ]);
        }
        if (auth()->user()->getShop->id != $shopId) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'redirectTo' => route('shop', ['shopId' => $shopId]),
            ]);
        }
        $data = [
            'newResourceFile' => request('newResourceFile',null),
            'newResourceType' => request('newResourceType', null),
            'newResourceLink' => request('newResourceLink', null),
            'shopId' => auth()->user()->getShop->id,
        ];
        $result = $this->shopResourceService->saveResource($data, auth()->id());

        if ($result['error'] != EErrorCode::NO_ERROR) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'msg' => __('common/common.there_was_an_error_in_the_processing'),
            ]);
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.update-data-success', [
                'objectName' => __('front/shop.resource')
            ])
        ]);
    }

    public function showReviewView($shopId = null) {
        if (!$shopId) {
            return redirect()->route('home');
        }

        return view('front.shop.shop-review', [
            'shopId' => $shopId,
        ]);
    }

    public function getShopReviewList($shopId) {
        $option = [
            'table_id' => $shopId,
            'table_name' => ETableName::SHOP,
            'page' => request('page'),
            'pageSize' => request('pageSize'),
        ];
        if(request('starFilter')) {
            $option['star'] = request('starFilter');
        }
        $reviews = $this->reviewService->getByOptions($option);
        $tmp = $reviews->map(function($review) {
            $user = $review->user;
            return [
                'name' => $user->name,
                'avatar' => empty($user->avatar_path) ? DefaultConfig::FALLBACK_USER_AVATAR_PATH :
                    get_image_url([
                    'path' => $user->avatar_path,
                    'op' => 'thumbnail',
                    'w' => 300,
                    'h' => 300
                ]),
                'content' => $review->content,
                'star' => $review->star,
                'createdAt' => $review->created_at,
            ];
        });
        $reviews->setCollection($tmp);
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'reviews' => $reviews,
        ]);
    }

	public function getInfoShop($shopId = null) {
		$option = [
			'id' => $shopId,
			'first' => true,
			'user_get_info' => true,
		];
		$shop = $this->shopService->getByOptions($option);
		if (!empty($shop->avatar)) {
			$shop->avatar = FileUtility::getFileResourcePath($shop->avatar, DefaultConfig::FALLBACK_IMAGE_PATH);
		} else {
			$shop->avatar = null;
		}
		$shop->levelName = ELevelName::valueToName($shop->level);
		$shop->phoneSub = substr($shop->phone, 0, 4) . 'XXXXXX';
		if (!Str::containsAll($shop->zalo, ['https']) && !empty($shop->zalo)) {
			$shop->zalo = 'https://zalo.me/' . $shop->zalo;
		}
		$shop->createdAt = Carbon::parse($shop->created_at)
            ->format(EDateFormat::STANDARD_DATE_FORMAT);
        $shop->identityDate = Carbon::parse($shop->identity_date)
            ->format(EDateFormat::DEFAULT_DATE_INPUT_FORMAT);
        $area1 = $shop->getArea;
        $shop->areaProvince = $area1;
        $shop->areaDistrict = null;
        $shop->areaWard = null;
        $area2 = null;
        $area3 = null;
        if (!empty($area1) && $area1->parent_area_id && $area1->type != EAreaType::STATE) {
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
		$shop->evaluate = [
			'total' => 0,
			'average' => 0,
		];
		$shop->images = $shop->image;
		$shop->videos = $shop->video;
		$shop->url = route('shop', [
			'shopId' => $shop->id,
		], false);
		if (count($shop->images) > 0) {
			foreach ($shop->image as $key) {
				$key->path = FileUtility::getFileResourcePath($key->path_to_resource,
                    DefaultConfig::FALLBACK_IMAGE_PATH);
			}
		}

		if (count($shop->videos) > 0) {
			foreach ($shop->videos as $key) {
                if(Str::containsAll($key->path_to_resource, ['https','youtu'])) {
                    $key->videoType = EVideoType::YOUTUBE_VIDEO;
                } elseif(Str::containsAll($key->path_to_resource, ['https','tiktok'])) {
                    $key->videoType = EVideoType::TIKTOK_VIDEO;
                    $tiktokResource = StringUtility::getLinkTikTok($key->path_to_resource);
                    $key->path_to_resource = $tiktokResource['src'];
                } else {
                    $key->videoType = EVideoType::INTERNAL_VIDEO;
                }
                $key->path = !Str::contains($key->path_to_resource,['https','http']) ? config('app.resource_url_path') .
                    '/' . $key->path_to_resource : $key->path_to_resource;
			}
		}
		if (count($shop->getEvaluate) > 0) {
			$star = 0;
			foreach ($shop->getEvaluate as $key) {
				$star += $key->star;
			}
			$shop->evaluate = [
				'total' => count($shop->getEvaluate),
				'average' => $star / count($shop->getEvaluate),
			];
		}
		$follow = $this->followService->getByOptions([
			'following_table_name' => ETableName::SHOP,
			'following_table_id' => $shop->id,
			'status' => EStatus::ACTIVE,
			'count' => true
		]);
		if ($follow > 1000) {
			$follow = $follow / 1000 . 'k';
		}
		$shop->follow = $follow;
		$allMessage = $this->conversationMemberService->getByOptions([
			'shop_id' => $shop->id,
			'ratio' => true,
		]);
		$responseMessage = $this->conversationMemberService->getByOptions([
			'shop_id' => $shop->id,
			'ratio' => true,
			'last_message' => true,
		]);
		if (count($allMessage) > 0) {
			$shop->responseRate = round((count($responseMessage) / count($allMessage)) * 100);
		} else {
			$shop->responseRate = 0;
		}
		$checkFollowed = $this->followService->getByOptions([
			'user_id' => auth()->id(),
			'following_table_id' => $shop->id,
			'following_table_name' => ETableName::SHOP,
			'status' => EStatus::ACTIVE
		]);
		$shop->isFollowed = $checkFollowed;
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'shop' => $shop,
		]);
	}

	public function saveShop(SaveInfoRequest $request) {
		//try {
			$data = $request->validated();
			$acceptFields = ['id', 'latitude', 'longitude',
                'areaProvince','areaDistrict','areaWard', 'avatarType', 'identityCode', 'identityDate', 'identityPlace'];
			foreach ($acceptFields as $field) {
				if (!request()->filled("$field")) {
					continue;
				}
				$data[$field] = request("$field");
			}
			$result = $this->shopService->saveShop($data, auth()->id());
			if ($result['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($result);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'redirectToShop' => route(
					'shop.edit', [
						'shopId' => $result['shop']->id,
					], false
				),
				'msg' => __('common/common.save-data-success', [
					'objectName' => __('back/shop.object_name')
				]),
			]);
		// } catch (\Exception $e) {
		// 	logger()->error('Fail to save user', [
		// 		'error' =>  $e
		// 	]);
		// 	return response()->json([
		// 		'error' => EErrorCode::ERROR,
		// 		'msg' => __('common/common.there_was_an_error_in_the_processing'),
		// 	]);
		// }
	}

	public function showProductView() {
		if (!auth()->id()) {
			return redirect()->route('home');
		}
		return view('front.shop.shop-info');
	}

	public function showNotificationView($shopId = null) {
		if (!auth()->id()) {
			return redirect()->route('home');
		}
		return view('front.shop.notification', [
				'shopId' => $shopId,
			]);
	}

	public function saveNotification(SaveNotificationScheduleRequest $request) {
		$request->validated();
		$sendDate = request('date');
		$sendTime = request('time');
		$tz = session(SessionKey::TIMEZONE);
		$scheduleAt = Carbon::createFromFormat('d/m/Y H:i:s e', "$sendDate $sendTime $tz");
		if ($scheduleAt->lt(Carbon::now()->addMinutes(5))) {
			return response()->json([
				'error' => EErrorCode::ERROR,
				'msg' => __('back/notification_schedule.errors.schedule_at_after_now'),
			]);
		}

		$data = [
			'status' => EStatus::PENDING,
			'approval_status' => EApprovalStatus::WAITING,
			'type' => ENotificationType::SYSTEM,
			'target_type' => (int)request('targetType'),
			'title' => request('titleVi'),
			'content' => request('contentVi'),
			'scheduled_at' => $scheduleAt,
			'translations' => [
				ELanguage::VI => [
					'title' => request('titleVi'),
					'content' => request('contentVi'),
				],
			],
			'targetList' => request('targetList'),
			'meta' => [
                'notUserId' => auth()->id()
            ],
		];
		if (request('id')) {
			$data['id'] = request('id');
		}

		$result = $this->notificationScheduleService->saveNotificationSchedule($data, auth()->id());
		if ($result['error'] !== EErrorCode::NO_ERROR) {
			return response()->json($result);
		}
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'msg' => 'Tạo thông báo thành công, Thông báo của bạn sẽ được duyệt trong thời gian sớm nhất',
		]);
	}

	public function showBannerView($shopId = null) {
		if (!auth()->id()) {
			return redirect()->route('home');
		}
		return view('front.shop.banner', [
			'shopId' => $shopId,
		]);
	}

	public function getBanner() {
		$tz = session(SessionKey::TIMEZONE);
		$acceptFields = ['status', 'shopId'];
		$filters = [];

		foreach ($acceptFields as $field) {
			if (!request()->filled("$field")) {
				continue;
			}
			$filters[Str::snake($field)] = request("$field");
		}
		$filters['type'] = EBannerType::MAIN_BANNER_ON_HOME_SCREEN;
		$filters['banner_shop'] = true;
		$banners = $this->bannerService->getByOptions($filters);
		foreach ($banners as $key => $value) {
			$value->valid_date = $value->valid_to ? now()->startOfDay()->diffInDays(Carbon::parse($value->valid_to)->startOfDay()) : null;
			$value->path_to_resource = !empty($value->path_to_resource) ? FileUtility::getFileResourcePath($value->path_to_resource, DefaultConfig::FALLBACK_IMAGE_PATH) : DefaultConfig::FALLBACK_IMAGE_PATH;
			$value->original_resource_path = !empty($value->original_resource_path) ? config('app.resource_url_path') . '/' . $value->original_resource_path : DefaultConfig::FALLBACK_IMAGE_PATH;
			$value->actionOnClick = EBannerActionType::valueToName($value->action_on_click_type);
			$value->createdAt =  Carbon::parse($value->created_at)
            ->format(EDateFormat::DATE_FORMAT);
			$value->typeString = EBannerType::valueToName($value->type);
			$value->statusString = EApprovalStatus::valueToLocalizedName($value->status);
			$value->platformString = EPlatform::valueToName($value->platform);
			$link = json_decode($value->action_on_click_target, false);
			$value->action_on_click_target = !empty($value->action_on_click_target) ? $link->url : null;
		}
		$dataValidateWeb = $this->configService->getByName(ConfigKey::BANNER_IMAGE_SPECS_WEB);
		$dataValidateMobile = $this->configService->getByName(ConfigKey::BANNER_IMAGE_SPECS_MOBILE);
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'banners' => $banners,
			'validate' => [
				'web' => json_decode($dataValidateWeb->text_value),
				'mobile' => json_decode($dataValidateMobile->text_value),
			]
		]);
	}

	public function saveBanner(SaveBannerRequest $request) {
		$request = $request->validated();
		$flatForm = ['web', 'mobile'];
		$acceptFields = ['file', 'blob', 'link', 'type', 'actionType', 'ratio'];
		$data = [];
		foreach ($flatForm as $item) {
			foreach ($acceptFields as $field) {
				if (in_array($field, ['file', 'ratio', 'blob'])) {
					if ($field == 'ratio') {
						$data[$item][$field] = str_replace('/', ':', Arr::get($request, $item . ucfirst($field)));
					} else {
						$data[$item][$field] = Arr::get($request, $item . ucfirst($field));
					}
				} else if (in_array($field, ['link', 'type', 'actionType'])) {
					$data[$item][$field] = Arr::get($request, $field);
				} else {
					$data[$item][$field] = request("$item . ucfirst($field)");
				}
			}
		}
		$data['web']['platform'] = EPlatform::WEB;
		$data['mobile']['platform'] = EPlatform::MOBILE;
		$data['web']['id'] = request('webBannerId');
		$data['mobile']['id'] = request('mobileBannerId');
		$data['web']['edit'] = request('webEdit');
		$data['mobile']['edit'] = request('mobileEdit');
		$data['isEdit'] = request('isEdit');
		if (empty($data['web']['id'])) {
			$data['web']['status'] = EStatus::WAITING;
		}
		if (empty($data['mobile']['id'])) {
			$data['mobile']['status'] = EStatus::WAITING;
		}
		$result = $this->shopService->saveBanner($data, auth()->id(), auth()->user()->getShop->id);
		if ($result['error'] !== EErrorCode::NO_ERROR) {
			return response()->json($result);
		}
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'msg' => __('common/common.save-data-success', [
				'objectName' => __('back/banner.object_name'),
			])
		]);
	}

	public function showUpgradeView($shopId = null) {
		if (!auth()->id()) {
			return redirect()->route('home');
		}
		$data = $this->shopLevelConfigService->getByOptions([
			'upgrade_shop' => true,
			'orderBy' => 'level',
			'orderDirection' => 'asc'
		]);
		foreach ($data as $key) {
			$key->price = number_format($key->price, 0, '.', '.');
			$key->description = str_replace('\n', '<br>', $key->description);
		}
		$subscriptionPrice = $this->subscriptionPriceService->getByOptions([
			'type' => ESubscriptionPriceType::UPGRADE_SHOP
		]);
		$isUpgrade = true;
		$levelWaitingApprove = 0;
		foreach ($subscriptionPrice as $item) {
			$subscription = $this->subscriptionService->getByOptions([
	            'table_id' => $shopId,
	            'table_name' => 'shop',
	            'user_id' => auth()->id(),
	            'subscription_price_id' => $item->id,
	            'payment_status' => EPaymentStatus::WAITING,
	            'exists' => true,
	        ]);
			if ($subscription) {
				$isUpgrade = false;
				$levelWaitingApprove = $item->meta->level;
				break;
			}
		}
		return view('front.shop.upgrade', [
			'shopId' => $shopId,
			'data' => $data,
			'isUpgrade' => $isUpgrade,
			'levelWaitingApprove' => $levelWaitingApprove,
		]);
	}

	public function getDataShopLevel($shopId) {
		$shop = $this->shopService->getByOptions([
			'id' => $shopId,
			'first' => true
		]);
		$shopLevel = $this->shopLevelService->getByOptions([
			'shop_id' => $shopId,
			'status' => EStatus::ACTIVE,
			'first' => true,
		]);
		$shopLevelConfig = $this->shopLevelConfigService->getByOptions([
			'level' => $shop->level,
			'first' => true,
		]);
		if (!empty($shopLevel->num_push_product_in_month)) {
			$numPushProduct = json_decode($shopLevel->num_push_product_in_month);
			$monthInYear = $numPushProduct->month_in_year;
			$currentMonthYear = now()->format(EDateFormat::STANDARD_MONTH_FORMAT);
			if ($monthInYear != str_replace('-', '/', $currentMonthYear)) {
				$updateMonthInYear = $this->shopLevelService->updateMonthInYear($shopLevel->id, $currentMonthYear, $shopLevelConfig->num_push_product_in_month);
				if ($updateMonthInYear['error'] == EErrorCode::NO_ERROR) {
					$shopLevel = $updateMonthInYear['shopLevel'];
				}
			}
		}
		$permission = [
			'avatar' => !empty($shopLevelConfig->avatar) ? json_decode($shopLevelConfig->avatar) : null,
			'num_product_remain' => !empty($shopLevel->num_product_remain) ? $shopLevel->num_product_remain : 0,
			'num_push_product_in_month_remain' => !empty($shopLevel->num_push_product_in_month) ? json_decode($shopLevel->num_push_product_in_month)->num_push_product_in_month_remain : 0,
			'video_in_product' => !empty($shopLevelConfig->video_in_product) ? json_decode($shopLevelConfig->video_in_product) : null,
			'imageIntroduce' => !empty($shopLevelConfig->image_introduce) ? json_decode($shopLevelConfig->image_introduce) : null,
			'banner_in_home' => !empty($shopLevelConfig->banner_in_home) ? json_decode($shopLevelConfig->banner_in_home) : null,
			'videoIntroduce' => !empty($shopLevelConfig->video_introduce) ? json_decode($shopLevelConfig->video_introduce) : null,
			'num_image_in_product' => !empty($shopLevelConfig->num_image_in_product) ? json_decode($shopLevelConfig->num_image_in_product) : null,
			'level' => $shop->level,
			'banner_in_home' => !empty($shopLevelConfig->banner_in_home) ? json_decode($shopLevelConfig->banner_in_home) : null,
			'enable_create_notification' => !empty($shopLevelConfig->enable_create_notification) ? json_decode($shopLevelConfig->enable_create_notification) : null,
		];
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'permission' => $permission,
		]);
	}

	public function followShop($shopId) {
		if (!auth()->id()) {
			return redirect()->route('home');
		}
		$data = [
			'user_id' => auth()->id(),
			'following_table_id' => $shopId,
			'following_table_name' => ETableName::SHOP,
		];
		$result = $this->followService->saveFollow($data, auth()->id());

        if ($result['error'] != EErrorCode::NO_ERROR) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'msg' => __('common/common.there_was_an_error_in_the_processing'),
            ]);
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.update-data-success', [
                'objectName' => __('front/shop.resource')
            ])
        ]);
	}

	public function getListFollower($shopId) {
        $option = [
            'following_table_id' => $shopId,
            'following_table_name' => ETableName::SHOP,
            'page' => request('page'),
            'pageSize' => request('pageSize'),
        ];
        $followers = $this->followService->getByOptions($option);
        $tmp = $followers->map(function($follower) {
            $user = $follower->user;
            return [
                'name' => $user->name,
                'avatar' => empty($user->avatar_path) ? DefaultConfig::FALLBACK_USER_AVATAR_PATH :
                    get_image_url([
                        'path' => $user->avatar_path,
                        'op' => 'thumbnail',
                        'w' => 300,
                        'h' => 300
                    ]),
            ];
        });
        $followers->setCollection($tmp);
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'followers' => $followers,
        ]);
    }

    public function deleteBanner() {
		try {
			$id = request('id');
			$delete = $this->bannerService->deleteBanner($id, auth()->id());
			if ($delete['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($delete);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => __('common/common.delete-data-success', [
					'objectName' => __('back/banner.object_name')
				])
			]);

		} catch (\Exception $e) {
			logger()->error('Fail to delete banner', [
				'error' =>  $e
			]);
			return response()->json([
				'error' => EErrorCode::ERROR,
				'msg' => __('common/common.there_was_an_error_in_the_processing'),
			]);
		}
	}
}
