<?php

namespace App\Services;

use App\Constant\ConfigKey;
use App\Enums\EStatus;
use App\Enums\EWalletType;
use App\Models\Wallet;
use App\Repositories\WalletRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService {
    private $walletRepo;
    private $configRepository;

    public function __construct(WalletRepository $walletRepository) {
        $this->walletRepo = $walletRepository;
    }

    public function getWallet($userId, $type = EWalletType::INTERNAL_MONEY) {
        return $this->walletRepo->getWallet($userId, $type);
    }

    public function generateUserWallet($userId, $returnWalletType = EWalletType::INTERNAL_MONEY) {
        DB::transaction(function() use ($userId) {
			$walletType = [
				EWalletType::INTERNAL_MONEY,
				EWalletType::POINT,
			];
			foreach ($walletType as $type) {
				$wallet = $this->getWallet($userId, $type);
				if (isset($wallet)) {
					continue;
				}

				$wallet = new Wallet();
				$wallet->user_id = $userId;
				$wallet->balance = 0;
				$wallet->status = EStatus::ACTIVE;
				$wallet->created_by = $userId;
				$wallet->type = $type;
				$wallet->save();
			}
		});

        return $this->getWallet($userId, $returnWalletType);
    }
}
