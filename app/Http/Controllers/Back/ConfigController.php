<?php

namespace App\Http\Controllers\Back;

use App\Enums\EErrorCode;
use App\Constant\ConfigKey;
use App\Helpers\ConfigHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Config\SaveConfigWalletRequest;
use App\Http\Requests\Config\SaveConfigCommissionRequest;
use App\Services\ConfigService;
use App\Http\Requests\Config\SaveConfigRequest;

class ConfigController extends Controller {
    protected $configService;

    public function __construct(ConfigService $configService) {
        $this->configService = $configService;
    }

    public function getWalletData() {
        $score = $this->configService->getByName(ConfigKey::CUMULATIVE_POINTS);
        $coins = $this->configService->getByName(ConfigKey::EXCHANGE_COINS);
        $result = [
            'score' => json_decode($score->text_value),
            'coins' => json_decode($coins->text_value)
        ];

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data' => $result,
        ]);
    }

    public function saveWalletData(SaveConfigWalletRequest $request) {
        $score = $request->validated();
        if ((bool)request('isEditScore') == true) {
            $data = [
                ConfigKey::CUMULATIVE_POINTS => json_encode($score),
            ];
            ConfigHelper::bulkAdd($data);
            $objectName =  __('back/config.score.exchangeValue');
        }
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.save-data-success', [
                'objectName' => $objectName
            ]),
        ]);
    }

    public function getCommissionData() {
        $commission = $this->configService->getByName(ConfigKey::SHOP_LEVEL_COMMISSION);
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data' => $commission->numeric_value * 100,
        ]);
    }

    public function saveCommissionData(SaveConfigCommissionRequest $request) {
        $data = $request->validated();
        $value = [
            ConfigKey::SHOP_LEVEL_COMMISSION => (int)$data['commission'] / 100,
        ];
        ConfigHelper::bulkAdd($value);
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => 'Lưu chiết khấu thành công',
        ]);
    }

    public function getConfigByType() {
        $configName = request('configName');
        $config = $this->configService->getByName($configName);
        $result = [
            'textValue' => !empty($config->text_value) ? $config->text_value : "",
        ];

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'data' => $result,
        ]);
    }

    public function saveConfig(SaveConfigRequest $request) {
        $request->validated();
        $configName = request('configName');
        $data = [
            $configName => request('textValue'),
        ];
        ConfigHelper::bulkAdd($data);
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'msg' => __('common/common.save-data-success2'),
        ]);
    }
}
