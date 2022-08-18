<?php

namespace App\Constant;

abstract class SessionKey {
    const TIMEZONE = 'timezone';
    const TIMEZONE_OFFSET = 'timezone_offset';
    const LANG = 'lang';
    const VIEW_ALERT_MSG = 'view_alert_msg';
	const REGISTER_CUSTOMER_TYPE = 'register.customer_type';
    const POST_CREATE_FORM_DATA = 'post.create-form-data';

    const OAUTH_USER_INFO = 'auth.oauth.user_info';
    const ROUTE_INTENDED = 'route.intended';
}
