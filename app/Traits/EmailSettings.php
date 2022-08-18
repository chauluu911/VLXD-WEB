<?php

namespace App\Traits;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

trait EmailSettings {
	public function setVerifyEmailTemplate($optCodeService) {
		VerifyEmail::toMailUsing(function ($notifiable, $verificationUrl) use ($optCodeService) {
			$otp_code = $optCodeService->removeOldOTPAndGenerateANewOne($notifiable->getKey());

			return (new MailMessage)->subject(__('Verify Email Address'))->view(
				'email-template.email-verification', ['action' => $verificationUrl, 'otp_code' => $otp_code]
			);
		});
	}
}