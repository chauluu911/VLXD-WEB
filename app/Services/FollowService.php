<?php

namespace App\Services;

use App\Constant\ConfigTableName;
use App\Enums\EErrorCode;
use App\Enums\EStatus;
use App\Enums\EStoreFileType;
use App\Models\Follow;
use App\Repositories\FollowRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Jobs\NotifyUserJob;
use App\Enums\ENotificationType;
use App\Services\UserService;
use App\Services\ShopService;
use App\Enums\ELanguage;

class FollowService {
    private FollowRepository $followRepository;
    private UserService $userService;
    private ShopService $shopService;

    public function __construct(FollowRepository $followRepository, UserService $userService, ShopService $shopService) {
        $this->followRepository = $followRepository;
        $this->userService = $userService;
        $this->shopService = $shopService;
    }

    public function getByOptions(array $options) {
        return $this->followRepository->getByOptions($options);
    }

    public function saveFollow(array $data, int $currentUserId) {
        return DB::transaction(function() use ($data, $currentUserId) {
            $follow = $this->getByOptions([
                'user_id' => Arr::get($data, 'user_id'),
                'following_table_id' => Arr::get($data, 'following_table_id'),
                'following_table_name' => Arr::get($data, 'following_table_name'),
                'first' => true,
                'status' => EStatus::ACTIVE
            ]);
            $user = $this->userService->getById(Arr::get($data, 'user_id'));
            if (!empty($follow)) {
                $follow->status = EStatus::DELETED;
                $follow->save();
            } else {
                $follow = new Follow();
                $follow->user_id = Arr::get($data, 'user_id');
                $follow->following_table_id = Arr::get($data, 'following_table_id');
                $follow->following_table_name = Arr::get($data, 'following_table_name');
                $follow->created_at = now();
                $follow->created_by = $currentUserId;
                $follow->status = EStatus::ACTIVE;
                $follow->save();
                
                $shop = $this->shopService->getById(Arr::get($data, 'following_table_id'));
                NotifyUserJob::dispatch([$shop->user_id], [
                    'type' => ENotificationType::FOLLOW,
                    'title' => [
                        ELanguage::VI => 'Thông báo',
                    ],
                    'content' => [
                        ELanguage::VI => 'Người dùng ' . $user->name . ' đã theo dõi shop của bạn',
                    ],
                    'meta' => [
                        'shopId' => (int)$shop->id
                    ],
                    'data' => [
                        'userId' => Arr::get($data, 'user_id'),
                        'shopId' => $shop->id
                    ],
                ])->onQueue('pushToDevice');
            }
            return ['error' => EErrorCode::NO_ERROR];
        });
    }
}
