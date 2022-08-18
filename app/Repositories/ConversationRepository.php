<?php

namespace App\Repositories;

use App\Enums\EStatus;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class ConversationRepository extends BaseRepository {

	public function __construct(Conversation $conversation) {
	    $this->model = $conversation;
    }

    /**
     * @param array $options
     * @return bool|LengthAwarePaginator|Collection|Conversation
     */
    public function getByOptions(array $options) {
        $result = $this->model
            ->from('conversation as c')
            ->select('c.*');

        if (!Arr::hasAny($options, ['status', 'not_status'])) {
			$result->where('c.status', EStatus::ACTIVE);
		}
        foreach ($options as $key => $val) {
            switch ($key) {
                case 'status':
                    $result->where('c.status', $val);
                    break;
                case 'not_status':
                    $result->where('c.status', '!=', $val);
                    break;
                case 'name':
                    $result->where(function($q) use ($val) {
                        $q->where('c.name', 'ilike', "%$val%")
                            ->orWhere('c.name_search', 'ilike', "%$val%");
                    });
                    break;
                case 'id_in':
                    $result->whereIn('c.id', $val);
                    break;
            }
        }

		$orderBy = Arr::get($options,'orderBy', 'created_at');
		$orderDirection = Arr::get($options,'orderDirection', 'desc');
		switch ($orderBy) {
			default:
				$result->orderBy("c.$orderBy", $orderDirection);
				break;
		}

        return parent::getByOption($options, $result);
    }

    public function getConversationByUserId(array $userIds) {
        sort($userIds, SORT_NUMERIC);
        $userIds = implode(',', $userIds);
        $query = <<<sql
            select *
            from conversation as c
            where c.status = 1 and (
                select array_agg(cm.user_id)
                from (select * from conversation_member order by user_id) as cm
                where c.id = cm.conversation_id
                )::int[] @> ARRAY[$userIds]
            sql;
        return $this->model
            ->fromRaw("($query) as c")
            ->first();
    }
}
