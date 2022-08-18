<?php

namespace App\Constant;


abstract class CacheKey {
	const HOME_BANNER_MAIN_DESKTOP_LIST = 'home.banner.main.xl.list';
	const HOME_BANNER_MAIN_MOBILE_LIST = 'home.banner.main.xs.list';
	const HOME_BANNER_TRADEMARK_DESKTOP_LIST = 'home.banner.trademark.xl.list';
	const HOME_BANNER_TRADEMARK_MOBILE_LIST = 'home.banner.trademark.xs.list';
	const HOME_BANNER_PROMOTION_LIST = 'home.banner.promotion.list';
	const HOME_CATEGORY_LIST = 'home.category.list';

	const HOME_EXAM_QUESTION_LIST = 'home.exam.question.list.{locale}';
	const HOME_POST_LIST = 'home.post.list.{locale}';
	const HOME_VIDEO_LIST = 'home.video.list';
	const HOME_RANKINGS = 'home.rankings.{timeType}.{areaId}.{unitId}';
	const HOME_BANNER_LIST = 'banner.list.{webScreenType}.{locale}';
	const NOTIFICATION_MAX_SENDING_TRY = 'notification.max_sending_try_per_device_token';
	const COUNTRY_LIST = 'api.country.list';
}
