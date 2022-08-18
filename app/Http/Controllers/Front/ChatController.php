<?php

namespace App\Http\Controllers\Front;

use App\Constant\ConfigKey;
use App\Constant\DefaultConfig;
use App\Enums\EAvatarType;
use App\Enums\EConnectStatus;
use App\Enums\ECustomerType;
use App\Enums\EErrorCode;
use App\Enums\EResourceType;
use App\Enums\EStatus;
use App\Enums\EStoreFileType;
use App\Enums\EUserType;
use App\Helpers\ConfigHelper;
use App\Helpers\FileUtility;
use App\Http\Controllers\Controller;
use App\Models\Interest;
use App\Services\Firebase\FirebaseService;
//use App\Services\InterestService;
use App\Services\ShopService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Services\ConversationService;
use App\Services\ConversationMemberService;
use Illuminate\Support\Str;
use Kreait\Firebase\Messaging\CloudMessage;

class ChatController extends Controller {
    private ConversationService $conversationService;
    private ConversationMemberService $conversationMemberService;
    private UserService $userService;
    private ShopService  $shopService;
//    private InterestService $interestService;

    public function __construct(ConversationService $conversationService,
                                UserService $userService,
                                ShopService $shopService,
                                ConversationMemberService $conversationMemberService) {
        $this->conversationService = $conversationService;
        $this->userService = $userService;
        $this->shopService = $shopService;
        $this->conversationMemberService = $conversationMemberService;
//        $this->interestService = $interestService;
    }

    public function showChatView($userId=null, $firstMessage = null) {
        if(!auth()->id()) {
            return redirect()->route('login');
        }

        return view('front.chat.chat',[
            'id' => $userId? $userId : null,
            'firstMessage' => $firstMessage ? $firstMessage : null,
        ]);
    }

    public function getChatConfig() {
        $resourceUrlPath = config('app.resource_url_path');
        $currentUser = auth()->user();

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'admin_name' => 'Admin',
            'resource_url_path' => $resourceUrlPath,
            'current_user' => [
                'id' => $currentUser->id,
                'name' => $currentUser->name,
                'customer_type' => $currentUser->customer_type,
                'avatar_path' => empty($currentUser->avatar_path) ? DefaultConfig::FALLBACK_USER_AVATAR_PATH : get_image_url([
                    'path' => $currentUser->avatar_path,
                    'op' => 'thumbnail',
                    'w' => 300,
                    'h' => 300
                ]),
            ],
        ]);
    }

    public function getUserInfo() {
        $filters = [
            'type' => (int)request('userType'),
            'q' => request('q'),
            'pageSize' => 20,
            'status' => EStatus::ACTIVE,
        ];

        $userList = $this->userService->getByOptions($filters);
        $tmp = $userList->map(function($user) {
            $result = [
                'id' => $user->id,
                'name' => $user->code,
                'phone' => $user->phone,
                'avatar_path' => DefaultConfig::FALLBACK_USER_AVATAR_PATH,
            ];
            if (!empty($user->avatar_path)) {
                $imgOptions = [
                    'op' => 'thumbnail',
                    'w' => 150,
                    'h' => 150,
                ];
                if (Str::startsWith($user->avatar_path, ['http', 'https'])) {
                    $imgOptions['url'] = $user->avatar_path;
                } else {
                    $imgOptions['path'] = $user->avatar_path;
                }
                $result['avatar_path'] = get_image_url($imgOptions);
            }
            return $result;
        });
        $userList->setCollection($tmp);

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data' => $userList
        ]);
    }

    public function getConversationsInfo() {
        $user = auth()->user();
        $options = [
            'for_customer_message_management' => true,
            'user_id' => $user->id
        ];

//        // filter by code
//        if (request()->filled('q')) {
//            if ($user->customer_type !== ECustomerType::SELLER) {
//                $options['buyer_q'] = request('q');
//            } else {
//                $options['seller_q'] = request('q');
//            }
//        }
        // filter by conversation_id or init
        $conversationIdList = request('conversation_id_list', []);
        if (empty($conversationIdList)) {
            $options['pageSize'] = DefaultConfig::DEFAULT_PAGE_SIZE;
        } else {
            $options['conversation_id_in'] = $conversationIdList;
        }

        $conversationList = $this->conversationMemberService->getByOptions($options);

        foreach ($conversationList as $conversation) {
            if($conversation->shop_id){
                $conversation->target = $this->shopService->getById($conversation->shop_id);
            }
            else {
                $conversation->target = $this->userService->getById(($conversation->user_id));
            }

            if (!empty($conversation->target->avatar_path)) {
                $imgOptions = [
                    'op' => 'thumbnail',
                    'w' => 150,
                    'h' => 150,
                ];
                if (Str::startsWith($conversation->target->avatar_path, ['http', 'https'])) {
                    $imgOptions['url'] = $conversation->target->avatar_path;
                } else {
                    $imgOptions['path'] = $conversation->target->avatar_path;
                }
                if($conversation->shop_id && $conversation->target->avatar_type == EAvatarType::VIDEO) {
                    $conversation->target->avatar_path = config('app.resource_url_path') ."/" .$conversation->target->avatar_path;
                } else {
                    $conversation->target->avatar_path = get_image_url($imgOptions);
                }
            }
        }

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data' => $conversationList,
        ]);
    }

    public function approveInterestAndCreateConversation(Interest $interest = null) {
        if (empty($interest)) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'msg' => 'Cuộc trò chuyện không tồn tại',
            ]);
        }

        $newStatus = request('status');

        // process cancel request
        if ($interest->connect_status == EConnectStatus::CANCELED) {
            return response()->json([
                'error' => EErrorCode::NO_ERROR,
                'isCanceled' => true,
            ]);
        }
        if ($newStatus == EConnectStatus::CANCELED) {
            $result = $this->interestService->deleteInterest($interest->id, auth()->id());
            if ($result['error'] !== EErrorCode::NO_ERROR) {
                return response()->json($result);
            }
            return response()->json([
                'error' => EErrorCode::NO_ERROR,
                'isCanceled' => true,
            ]);
        }

        // process approve request
        if ($interest->connect_status == EConnectStatus::CONNECTED) {
            return response()->json([
                'error' => EErrorCode::NO_ERROR,
                'conversationId' => $interest->conversation->id,
            ]);
        }

        $result = $this->interestService->approveInterest($interest->id, auth()->id());
        if ($result['error'] !== EErrorCode::NO_ERROR) {
            return response()->json($result);
        }

        $conversation = $result['interest']->conversation;
        FirebaseService::database()->getReference("conversation/c{$conversation->id}/deleted")->set(false);

        $post = $interest->post;
        foreach ($conversation->members as $member) {
            $user = $member->user;
            FirebaseService::database()->getReference("members/c{$conversation->id}/u{$user->id}")->set([
                'name' => $user->customer_type == ECustomerType::SELLER ? $post->code : "$post->code - $user->code",
                'number_of_unseen_messages' => 0,
                'deleted_conversation' => false
            ]);
        }

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'conversationId' => $conversation->id
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Kreait\Firebase\Exception\DatabaseException
     * @deprecated not use in this project
     */
    public function createConversation() {

        $currentUser = auth()->user();
        $userIdTarget =(int)request('userId');
        $shopId = (int)request('shopId');
        $shopName = request('shopName');
        $data = [
            'shopName' => $shopName,
            'shopId' => $shopId,
            'userIdTarget' => $userIdTarget,
            'currentUserName' => $currentUser->name,
        ];

//        $customer = $this->userService->getById((int)request('customerId'));
//        $adminId = ConfigHelper::get(ConfigKey::ADMIN_USER_ID);
//        $admin = $this->userService->getById($adminId);
//        $conversation = $this->conversationService->saveConversation($customer->name, $admin->name, $customer->id, $adminId);
        $conversation = $this->conversationService->saveConversation($data, $currentUser->id);

        FirebaseService::database()->getReference("conversation/c{$conversation->id}/deleted")->set(false);
        $user_ids = [
            [
                'user_id' => $currentUser->id,
                'name' => $currentUser->name,
            ],
            [
                'user_id' => $userIdTarget,
                'name' => $shopName,
            ]
        ];
        foreach ($user_ids as $item) {
            FirebaseService::database()->getReference("members/c{$conversation->id}/u{$item['user_id']}")->set([
                'name' => $item['name'],
                'number_of_unseen_messages' => 0,
                'deleted_conversation' => false
            ]);
        }

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'conversationId' => $conversation->id
        ]);
    }

    /**
     * @param int $conversationId
     * @return array|\Illuminate\Http\JsonResponse
     * @throws \Kreait\Firebase\Exception\DatabaseException
     * @deprecated not use in this project
     */
    public function assignStaffToConversation(int $conversationId) {
        $data = [
            'conversation_id' => $conversationId,
            'user_id' => request('staffId')
        ];
        $conversation = $this->conversationService->getById($conversationId);
        if (empty($conversation)) {
            return [
                'error' => EErrorCode::ERROR,
                'msg' => __('cuoc tro chuyen k ton tai')
            ];
        }

        // old member
        foreach ($conversation->members as $member) {
            if (!$member->deleted_conversation && $member->user_id == $data['user_id']) {
                return response()->json(['error' => EErrorCode::NO_ERROR]);
            }
        }

        $data['conversation_name'] = $conversation->name;
        try {
            $this->conversationMemberService->saveConversationMember($data);

            FirebaseService::database()->getReference("members/c$conversationId/u{$data['user_id']}")->set([
                'name' => $conversation->name,
                'number_of_unseen_messages' => 0,
                'deleted_conversation' => false
            ]);

            // gán tin nhắn cuối cùng của user là tin nhắn cuối cùng của admin trong conversation
            $adminId = ConfigHelper::get(ConfigKey::ADMIN_USER_ID);
            $lastAdminMessage = FirebaseService::database()->getReference("chats_by_user/u$adminId/_conversation/c$conversationId")->getValue();
            FirebaseService::database()->getReference("chats_by_user/u{$data['user_id']}/_conversation/c$conversationId")->set($lastAdminMessage);
            FirebaseService::database()->getReference("chats_by_user/u{$data['user_id']}/_all_conversation")->set([
                'conversation_id' => $conversationId,
                'from_user_id' => null,
                'last_messages' => Arr::get($lastAdminMessage, 'last_messages', null),
                'last_updated_at' => Arr::get($lastAdminMessage, 'last_messages.timestamp', null),
            ]);

            return response()->json([
                'error' => EErrorCode::NO_ERROR,
                'msg' => __('common/common.save-data-success2'),
            ]);
        } catch (\Exception $e) {
            logger()->error('assign staff into conversation failed', compact('e'));
            return response()->json(['error' => EErrorCode::ERROR, 'msg' => __('common/error.system-error')]);
        }
    }

    /**
     * @param int $conversationId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Kreait\Firebase\Exception\DatabaseException
     * @deprecated not use in this project
     */
    public function deleteStaffFromConversation(int $conversationId) {
        $userId = request('userId');
        try {
            $result = $this->conversationMemberService->deleteConversationMember($userId, $conversationId);

            $database = FirebaseService::database();

            // get member data
            $memberData = $database->getReference("members/c$conversationId/u$userId")->getValue();

            // get conversation data
            $conversationData = $database->getReference("chats_by_user/u$userId/_conversation/c$conversationId")->getValue();
            $conversationData['deleted'] = true;
            $conversationData['deleted__last_updated_at'] = preg_replace('/^1/', '0', $conversationData['deleted__last_updated_at']);

            // update data
            $database->getReference('/')->update([
                "members/c$conversationId/u$userId/deleted_conversation" => true,
                "chats_by_user/u$userId/_conversation/c$conversationId" => $conversationData,
            ]);

            if ($result['error'] === EErrorCode::NO_ERROR) {
                $result['msg'] = __('common/common.delete-data-success', [
                    'objectName' => __('back/staff.objectName'),
                ]);
            }
            return response()->json($result);
        } catch (\Exception $e) {
            logger()->error('delete staff failed', compact('e'));
            return response()->json(['error' => EErrorCode::ERROR, 'msg' => __('common/error.system-error')]);
        }
    }

    public function checkUserPermission(int $conversationId) {
        $conversation = $this->conversationService->getById($conversationId);

        $hasPermission = !empty($conversation) && !empty($conversation->members->where('user_id', auth()->id())->first());

        if ($hasPermission) {
            return response()->json([
                'error' => EErrorCode::NO_ERROR,
                'hasPermission' => true
            ]);
        }

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'hasPermission' => false,
            'msg' => __('back/chat.error.not_allowed')
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @deprecated not use in this project
     */
    public function checkCustomerConversationExists() {
        $adminId = ConfigHelper::get(ConfigKey::ADMIN_USER_ID);
        $customerId = (int)request('customerId');

        $conversation = $this->conversationService->getConversationByUserId([$adminId, $customerId]);
        if (empty($conversation)) {
            return response()->json(false);
        }

        return response()->json($conversation->id);
    }

    public function saveConversationImage() {
        try {
            $imageList = request('image');
            if (!is_array($imageList) || !count($imageList)) {
                return response()->json([
                    'error' => EErrorCode::ERROR,
                ]);
            }
            $result = [];
            foreach ($imageList as $image) {
                $result[] = FileUtility::storeFile(EStoreFileType::CHAT_IMAGE, $image);
            }
            return response()->json([
                'error' => EErrorCode::NO_ERROR,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            logger('Error when conversation image', [
                'e' => $e
            ]);
            return response()->json([
                'error' => EErrorCode::ERROR,
            ]);
        }
    }

    public function saveConversationFile() {
        try {
            $file = request('file');
            if (!$file) {
                return response()->json([
                    'error' => EErrorCode::ERROR,
                ]);
            }
            $result = FileUtility::storeFile(EStoreFileType::CHAT_FILE, $file);
            return response()->json([
                'error' => EErrorCode::NO_ERROR,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            logger('Error when conversation file', [
                'e' => $e
            ]);
            return response()->json([
                'error' => EErrorCode::ERROR,
            ]);
        }
    }

    public function getShopInfo() {

        $ownerUserId = request('ownerUserId');
        if(!$ownerUserId) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'msg' => 'owner userId is required !!!',
            ]);
        }

        $shopInfo = $this->shopService->getByOwnerUserId($ownerUserId);
        if(!$shopInfo) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'msg' => 'invalid shop !!!',
            ]);
        }
        $data = [
            'id' => $shopInfo->id,
            'user_id' => $shopInfo->user_id,
            'name' => $shopInfo->name,
            'name_search' => $shopInfo->name_search,
            'phone' => $shopInfo->phone,
            'avatar_type' => $shopInfo->avatar_type,
        ];
        if(empty($shopInfo->avatar_path)) {
            $data['avatar_path'] = DefaultConfig::FALLBACK_USER_AVATAR_PATH;
        } else if ($shopInfo->avatar_type == EAvatarType::VIDEO) {
            $data['avatar_path'] = config('app.resource_url_path') ."/". $shopInfo->avatar_path;
        } else {
            $data['avatar_path'] = get_image_url([
                'path' => $shopInfo->avatar_path,
                'op' => 'thumbnail',
                'w' => 150,
                'h' => 150
            ]);
        }

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data' => $data
        ]);
    }


    public function sendNewChatNotification(int $conversationId) {
        $conversation = $this->conversationService->getById($conversationId);

        if (!empty($conversation)) {
            $messagingData = [
                'title' => __('common/site.title'),
                'body' => request('chat-content'),
                'sound' => 'default',
                'type' => "10",
            ];
            $messagingNotification = \Kreait\Firebase\Messaging\Notification::fromArray($messagingData);

            $adminId = ConfigHelper::get(ConfigKey::ADMIN_USER_ID);
            $messaging = FirebaseService::messaging();
            foreach ($conversation->members as $member) {
                if ($member->user_id == $adminId || empty($member->user) || $member->user->id == auth()->id()) {
                    continue;
                }
                foreach ($member->user->devices as $device) {
                    if (empty($device->device_token)) {
                        continue;
                    }

                    $message = CloudMessage::withTarget('token', $device->device_token);
                    $message = $message->withData($messagingData)->withNotification($messagingNotification);
                    try {
                        $messaging->send($message);
                    }  catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
                        $device->device_token = null;
                        $device->save();
                    } catch (\Exception $e) {
                        logger('error when send GCM message', ['device' => $device, 'e' => $e]);
                    }
                }
            }
        }

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
        ]);
    }
}
