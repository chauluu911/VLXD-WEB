<?php

namespace App\Models;


use App\User;

class ConversationMember extends BaseModel {
	protected $table = 'conversation_member';
    public $timestamps = false;

    const UPDATED_AT = null;

	public function user() {
		return $this->belongsTo(User::class, 'user_id', 'id');
	}
}
