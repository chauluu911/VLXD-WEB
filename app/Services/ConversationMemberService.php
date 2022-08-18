<?php

namespace App\Services;

use App\Enums\EErrorCode;
use App\Models\ConversationMember;
use App\Repositories\ConversationMemberRepository;
use App\Repositories\ConversationRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Enums\ENotificationType;
use App\Jobs\NotifyUserJob;
use App\Enums\ELanguage;

class ConversationMemberService {

	protected ConversationMemberRepository $conversationMemberRepository;
	protected ConversationRepository $conversationRepository;
	protected UserRepository $userRepository;

    public function __construct(ConversationMemberRepository $conversationMemberRepository,
                                ConversationRepository $conversationRepository,
                                UserRepository $userRepository) {
        $this->conversationMemberRepository = $conversationMemberRepository;
        $this->conversationRepository = $conversationRepository;
        $this->userRepository = $userRepository;
    }

    public function getById($id) {
        return $this->conversationMemberRepository->getById($id);
    }

    public function getConversationMemberByUserId($conversationId, $userId) {
        return $this->conversationMemberRepository->getConversationMemberByUserId($conversationId, $userId);
    }

    public function getByOptions($option = []) {
        return $this->conversationMemberRepository->getByOptions($option);
    }


    public function saveConversationMember(array $data, int $currentUserId) {
		$conversationMember = new ConversationMember();
		$conversationMember->conversation_id = Arr::get($data, 'conversation_id');
		$conversationMember->user_id = Arr::get($data, 'user_id');
		$conversationMember->conversation_name = Arr::get($data, 'conversation_name');
        $shopId = Arr::get($data, 'shop_id', null);
        if($shopId) {
            $conversationMember->shop_id = $shopId;
        }
		$conversationMember->save();

		return [
			'error' => EErrorCode::NO_ERROR,
			'conversationMember' => $conversationMember,
		];
    }

    public function deleteConversationMember($userId, $conversationId) {
        return DB::transaction(function() use ($userId, $conversationId) {
            $conversation = $this->conversationRepository->getById($conversationId);
            if (empty($conversation)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('back/customer.error.customer_not_found')];
            }

            $conversationMembers = $this->getConversationMemberByUserId($conversationId, $userId);
            foreach ($conversationMembers as $conversationMember) {
                $conversationMember->deleted_conversation = true;
                $conversationMember->save();
            }

            return ['error' => EErrorCode::NO_ERROR];
        });
    }
}
