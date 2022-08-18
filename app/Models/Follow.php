<?php
namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Follow extends BaseModel {
    protected $table = 'follow';
    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
