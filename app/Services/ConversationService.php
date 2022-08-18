<?php

namespace App\Services;

use App\Enums\EStatus;
use App\Models\Conversation;
use App\Repositories\ConversationRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ConversationService {
	protected ConversationRepository $conversationRepository;
	protected ConversationMemberService $conversationMemberService;

    public function __construct(ConversationRepository $conversationRepository,
                                ConversationMemberService $conversationMemberService) {
		$this->conversationRepository = $conversationRepository;
		$this->conversationMemberService = $conversationMemberService;
    }

    public function getById($id) {
        return $this->conversationRepository->getById($id);
    }

    public function getByOptions(array $options) {
        return $this->conversationRepository->getByOptions($options);
    }

    public function getConversationByUserId(array $userIds) {
        return $this->conversationRepository->getConversationByUserId($userIds);
    }

    public function saveConversation(array $data, int $currentUserId) {
        return DB::transaction(function() use ($data, $currentUserId) {
            $userIdTarget = Arr::get($data, 'userIdTarget');
            $shopName = Arr::get($data, 'shopName');
            $shopId= Arr::get($data, 'shopId');
            $currentUserName= Arr::get($data, 'currentUserName');
            $conversation = new Conversation();
            $conversation->name = $shopName;
            $conversation->status = EStatus::ACTIVE;
            $conversation->save();

            //create 2 conversation member
            //1 for buyer(with shop_id = Null)
            $this->conversationMemberService->saveConversationMember([
                'conversation_id' => $conversation->id,
                'conversation_name' => $shopName,
                'user_id' => $currentUserId,
            ], $currentUserId);

            //1 for seller(with shop_id = seller.shopId)
            $this->conversationMemberService->saveConversationMember([
                'conversation_id' => $conversation->id,
                'conversation_name' => $currentUserName,
                'user_id' => $userIdTarget,
                'shop_id' => $shopId
            ], $currentUserId);
            return $conversation;
        });
    }
}
