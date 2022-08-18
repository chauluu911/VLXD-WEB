<?php

namespace App\Services\Firebase;


use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Str;

class FirebaseService {
    public static function firebase() {
        $factory = (new Factory)
            ->withServiceAccount(config('app.google_service_account_json'))
            ->withDatabaseUri(config('app.firebase.databaseURL'));
        return $factory;
    }

    public static function database() {
        return self::firebase()->createDatabase();
    }

    public static function messaging() {
        return self::firebase()->createMessaging();
    }

    public static function auth() {
        return self::firebase()->createAuth();
    }

    public static function storage() {
        return self::firebase()->createStorage();
    }

    public static function remoteConfig() {
        return self::firebase()->createRemoteConfig();
    }

    //region messaging via curl
	/**
	 * @param string $topic
	 * @param array $data
	 * string $data['title']
	 * string $data['content']
	 * string $data['en_title']
	 * string $data['en_content']
	 * int $data['type']
	 */
	public static function sendNotificationToTopic(string $topic, array $data = []) {
		self::sendGCMMessage([
			'title' => Arr::get($data, 'title'),
			'content' => Arr::get($data, 'content'),
			'notificationType' => Arr::get($data, 'type'),
			'targetType' => 'to',
			'target' => "/topics/$topic",
		]);
	}

	/**
	 * @param array $deviceTokens
	 * @param array $data
	 * string $data['title']
	 * string $data['content']
	 * string $data['en_title']
	 * string $data['en_content']
	 * int $data['type']
	 */
	public static function sendNotificationToManyDevices(array $deviceTokens, array $data = []) {
		if (empty($deviceTokens)) {
			return;
		}
		self::sendGCMMessage([
			'title' => Arr::get($data, 'title'),
			'content' => Arr::get($data, 'content'),
			'notificationType' => Arr::get($data, 'type'),
			'targetType' => 'registration_ids',
			'target' => $deviceTokens,
			'data' => Arr::get($data, 'data'),
		]);
	}

	/**
	 * @param int $userId
	 * @param array $data
	 * string $data['title']
	 * string $data['content']
	 * string $data['en_title']
	 * string $data['en_content']
	 * int $data['type']
	 */
	public static function sendNotificationToOneUser(int $userId, array $data = []) {
		$userService = resolve('\App\Services\UserService');
		$user = $userService->getById($userId);
		$deviceTokenList = $user->devices
			->where('device_token', '!=', null)
			->map(function($device) {
				return $device->device_token;
			})
			->toArray();
		self::sendNotificationToManyDevices($deviceTokenList, array_merge([
			'targetType' => 'registration_ids',
			'target' => $deviceTokenList,
		], $data));
	}

	/**
	 * @param $messageData
	 * string $messageData['title']
	 * string $messageData['content']
	 * string $messageData['en_title']
	 * string $messageData['en_content']
	 * string $messageData['notificationType']
	 * string $messageData['targetType'] accept: 'to'(default)|'registration_ids'
	 * string|array<string> $messageData['target'] target content
	 * @return bool
	 */
	public static function sendGCMMessage($messageData) {
		$serverKey = config('app.firebaseServerKey');
		try {
			$notificationData = [
				'title' => Arr::get($messageData, 'title'),
				'body' => html_entity_decode(strip_tags(Arr::get($messageData, 'content'))),
				'sound' => 'default',
				'type' => (string)Arr::get($messageData, 'notificationType'),
				'data' => Arr::get($messageData, 'data'),
				// 'en_title' => Arr::get($messageData, 'en_title'),
				// 'en_content' => html_entity_decode(strip_tags(Arr::get($messageData, 'en_content')))
			];
			$body = [
				Arr::get($messageData, 'targetType', 'to') => Arr::get($messageData, 'target'),
				'notification' => $notificationData,
				'data' => $notificationData,
			];
			$headers = [
				"Authorization: key=$serverKey",
				'Content-Type: application/json',
			];

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
			$plainResult = curl_exec($ch);
			curl_close($ch);

			logger('send notification result', ['result' => $plainResult, 'message' => Arr::get($messageData, 'message.notification')]);
			$result = (array)json_decode($plainResult);

			$token = (string)Arr::get($body, 'to');
			$tokenList = (array)Arr::get($body, 'registration_ids');
			$numOfSuccess = (int)Arr::get($result, 'success');
			if ((count($tokenList) && $numOfSuccess != count($tokenList))
				|| ($token && !$numOfSuccess)) {
				logger('Failed result tokens', [
					'tokens' => Arr::get($body, 'registration_ids'),
					'to' => $token,
				]);

				$notificationService = resolve('\App\Services\NotificationService');
				if ($token && !Str::startsWith($token, '/topics/')) {
					$notificationService->updateGCMSendResult([
						$token => $result
					], Carbon::now());
				} elseif (count($tokenList)) {
					$data = [];
					$results = Arr::get($result, 'results');
					foreach ($tokenList as $index => $token) {
						$data[$token] = (array)Arr::get($results, $index);
					}
					$notificationService->updateGCMSendResult($data, Carbon::now());
				}
			}
		} catch (\Throwable $e) {
			logger()->error('error when send GCM message', ['e' => $e, 'message' => Arr::get($messageData, 'message.notification')]);
		}
		return true;
	}
	//endregion
}
