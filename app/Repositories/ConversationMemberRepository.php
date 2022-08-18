<?php

namespace App\Repositories;

use App\Models\ConversationMember;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ConversationMemberRepository extends BaseRepository {

	public function __construct(ConversationMember $conversationMember) {
        $this->model = $conversationMember;
    }

    public function getConversationMemberByUserId($conversationId, $userId) {
        return $this->model
            ->where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->get();
    }

    public function getByOptions($options) {
        $result = $this->model
            ->from('conversation_member');
        foreach ($options as $key => $val) {
            switch ($key) {
                case 'q':
//                    $result->where('usr.name_search', 'ilike', "%$val%");
                    break;
                case 'conversation_id_in':
                    $result = $result->whereIn('conversation_id', Arr::get($options, 'conversation_id_in', null));
                    break;
                case 'shop_id':
                    $result->where("shop_id", $val);
                    break;
            }
        }
        if (Arr::get($options, 'ratio')) {
            if (Arr::get($options, 'last_message')) {
                $result->whereNotNull("last_message");
            }
        } else {
            $result = $result->whereIn('conversation_id', function($query) use ($options) {
                $query->from('conversation_member')
                    ->select('conversation_id')
                    ->where('user_id', Arr::get($options, 'user_id', null))
                    ->where('deleted_conversation',false);
            })->where('user_id','!=',Arr::get($options, 'user_id', null));
        }

        return $result->get();
    }

    public function getConversationMemberByConversationId($conversationId) {
        return $this->model
            ->where('conversation_id', $conversationId)
            ->get();
    }
}
