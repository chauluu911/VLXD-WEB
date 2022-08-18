<?php

namespace App\Http\Controllers\Front;


use App\Constant\ConfigKey;
use App\Enums\ELanguage;
use \App\Http\Controllers\Controller;
use App\Services\ConfigService;

class PolicyController extends Controller {

	private ConfigService $configService;

	public function __construct(ConfigService $configService) {
		$this->configService = $configService;
	}

	public function showAboutUsView() {
		$configKey = ConfigKey::ABOUT_US;
		$config = $this->configService->getByName($configKey);
		return view('front.about-us.about-us', [
			'content' => $config->text_value,
			'title' => 'Giới thiệu',
		]);
	}

	/**
	 * Dùng để lấy thông tin điều khoản
	 * hiển thị điều khoản bằng HTML
	 **/
	public function showPolicyView($policyName) {
		switch ($policyName) {
			case 'terms-and-conditions':
				$policyConfigKey = ConfigKey::TERMS_AND_CONDITIONS;
				$title = 'Điều khoản';
				break;
			case 'resolve-complaints':
				$policyConfigKey = ConfigKey::TRANSPORTATION_POLICY;
				$title = 'Quy trình giải quyết khiếu nại';
				break;
			case 'payment-policy':
				$policyConfigKey = ConfigKey::PAYMENT_POLICY;
				$title = 'Chính sách thanh toán';
				break;
			case 'privacy-policy':
				$policyConfigKey = ConfigKey::PRIVACY_POLICY;
				$title = 'Chính sách bảo mật';
				break;
			case 'buy-policy':
				$policyConfigKey = ConfigKey::BUY_POLICY;
				$title = 'Chính sách mua hàng';
				break;
			case 'user-guide':
				$policyConfigKey = ConfigKey::USER_GUIDE;
				$title = "Hướng dẫn";
				break;
			default:
				return redirect()->route('home');
		}
		$policyContent = $this->configService->getByName($policyConfigKey);
		if (empty($policyContent)) {
			return redirect()->route('home');
		}
		return view('front.policy.policy', [
			'title' => $title,
			'content' => $policyContent->text_value
		]);
	}
}