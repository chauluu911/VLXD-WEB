<?php
namespace App\Models;


class Conversation extends BaseModel {
	protected $table = 'conversation';
	const UPDATED_AT = null;

	public function members() {
	    return $this->hasMany(ConversationMember::class, 'conversation_id', 'id')
            ->where('deleted_conversation', '!=', true);
    }

    public function interest() {
		return $this->hasOne(Interest::class, 'id', 'interest_id');
	}
}
