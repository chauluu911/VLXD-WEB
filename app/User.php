<?php

namespace App;

use App\Enums\ETableName;
use App\Models\UserInterest;
use App\Traits\DateTimeFix;
use App\Traits\ModelDataParse;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\UserRolePermission;
use App\Models\Wallet;
use App\Services\Firebase\FirebaseService;
use App\Models\Shop;
use App\Models\UserAffiliateCode;
use App\Enums\EStatus;
use App\Models\UserDevice;

class User extends Authenticatable  {
    use Notifiable;
    use DateTimeFix;
    use ModelDataParse;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function aclUserRolePermissions() {
        return $this->hasMany(UserRolePermission::class,'user_id', 'id')
            ->whereNotNull('permission_group_id')
            ->where('status', EStatus::ACTIVE);
    }

    public function hasVerifiedEmail() {
    	return $this->status == EStatus::ACTIVE;
	}

    public function firebaseToken() {
        $uid = $this->id;
        $additionalClaims = [
            //            'role' => $role
        ];
        $customToken = FirebaseService::auth()->createCustomToken((string)$uid, $additionalClaims);
        return (string)$customToken->toString();
    }

	public function markEmailAsVerified() {
    	$this->status = EStatus::ACTIVE;
		// $this->approved_at = Carbon::now();
		// $this->approved_by = $this->id;
		$this->save();
	}

    public function getMetaAttribute($value) {
        return $this->getJsonValue($value);
    }

    public function setMetaAttribute($value) {
        $this->setJsonValue('meta', $value);
    }

    public function getWallet() {
        return $this->hasMany(Wallet::class,'user_id', 'id');
    }

    public function getShop() {
        return $this->hasOne(Shop::class,'user_id', 'id')
            ->where('shop.status', '<>',EStatus::DELETED);
    }

    public function affiliateCode() {
        return $this->hasOne(UserAffiliateCode::class,'user_id', 'id')->select('code');
    }

    public function interestProduct() {
        return $this->hasMany(UserInterest::class, 'user_id','id')
            ->where('table_name','=', ETableName::PRODUCT);
    }

    public function devices() {
        return $this->hasMany(UserDevice::class, 'user_id', 'id')
            ->where('status', EStatus::ACTIVE);
    }
}
