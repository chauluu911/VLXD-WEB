<?php

namespace App\Repositories;

use App\Enums\EStatus;
use App\Enums\EWalletType;
use App\Models\Wallet;

class WalletRepository extends BaseRepository {

    public function __construct(Wallet $wallet) {
        $this->model = $wallet;
    }

    public function getWallet($userId, $type = EWalletType::INTERNAL_MONEY) {
        return $this->model
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('status', EStatus::ACTIVE)
            ->first();
    }
}
