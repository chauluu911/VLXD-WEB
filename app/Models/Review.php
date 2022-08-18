<?php
namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Review extends BaseModel {
    protected $table = 'review';

    public function user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
