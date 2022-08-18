<?php


namespace App\Services;


use App\Enums\Banner\EBannerActionType;
use App\Enums\EErrorCode;
use App\Enums\EPlatform;
use App\Enums\EStatus;
use App\Helpers\StringUtility;
use App\Models\AppConfig;
use App\Repositories\AppConfigRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Constant\ConfigKey;
use Illuminate\Support\Arr;

class ConfigService {
    public function __construct(AppConfigRepository $appConfigRepository) {
        $this->appConfigRepository = $appConfigRepository;
    }

    public function getByName($configKey) {
        return $this->appConfigRepository->getByName($configKey);
    }

    public function saveData(int $idBonusUserSaleAdmin = null, int $idBonusGroupSaleAdmin = null, int $idBonusGroupSaleGroupOwner = null, int $idWalletFQA = null, array $inputData, int $loggedInUserId) {
        return DB::transaction(function() use ($idBonusUserSaleAdmin, $idBonusGroupSaleAdmin, $idBonusGroupSaleGroupOwner, $idWalletFQA, $inputData, $loggedInUserId) {
            $bonusUserSaleAdmin = $this->appConfigRepository->getById($idBonusUserSaleAdmin);
            $bonusGroupSaleAdmin = $this->appConfigRepository->getById($idBonusGroupSaleAdmin);
            $bonusGroupSaleGroupOwner = $this->appConfigRepository->getById($idBonusGroupSaleGroupOwner);
            $walletFQA = $this->appConfigRepository->getById($idWalletFQA);
            if (empty($bonusUserSaleAdmin) || empty($bonusGroupSaleAdmin) || 
                empty($bonusGroupSaleGroupOwner) || empty($walletFQA)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            //dd($inputData['userAndAdmin'] / 100);
            $bonusUserSaleAdmin->numeric_value = $inputData['userAndAdmin'] / 100;
            $bonusUserSaleAdmin->save();

            $bonusGroupSaleAdmin->numeric_value = $inputData['groupAndAdmin'] / 100;
            $bonusGroupSaleAdmin->save();

            $bonusGroupSaleGroupOwner->numeric_value = $inputData['groupAndHost'] / 100;
            $bonusGroupSaleGroupOwner->save();

            $walletFQA->text_value = $inputData['content'];
            $walletFQA->save(); 
            return ['error' => EErrorCode::NO_ERROR];
        });
    }

    // public function saveScoreData($data, $loggedInUserId) {
    //     return DB::transaction(function() use ($data, $loggedInUserId) {
    //         $config = $this->appConfigRepository->getById(Arr::get($data['data'], 'id'));
    //         $text_value = json_decode($config->text_value);
    //         $text_value->cost = Arr::get($data['data'], 'cost');
    //         $text_value->score = Arr::get($data['data'], 'score');
    //         $config->text_value = json_encode($text_value);
    //         $config->save();
    //     });
    // }
}
