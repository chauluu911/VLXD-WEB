<?php
namespace App\Enums;

abstract class EStoreFileType {
    /*const BANNER_ORIGINAL = 'banner_original';
    const BANNER = 'banner';
    const JOB_RESOURCE = 'job_resource';
    const JOB_LOGO = 'job_logo';
    const COMPANY_RESOURCE = 'company';
    const CHAT_IMAGE = 'chat';
    const MEMBERSHIP_CARD_LEVEL = 'membership_level';
    const VEHICLE_COLOR = 'vehicle_color';
    const VEHICLE_COLOR_ORIGINAL = 'vehicle_color';
    const VEHICLE_DESCRIPTION_DESIGN = 'vehicle_description_design';
    const VEHICLE_DESCRIPTION_TECH = 'vehicle_description_tech';
    const VEHICLE_DESCRIPTION_UTILITY = 'vehicle_description_utility';
    const VEHICLE_DESCRIPTION_SPECS = 'vehicle_description_specs';
    const VEHICLE_PART_DESCRIPTION = 'vehicle_part_description';
    const ORDER_WORK_PROOF = 'order_work_proof';
    const POST_IMAGE = 'post';
    const SERVICE_IMAGE = 'service';*/
    const CHAT_IMAGE = 'chat';
    const CHAT_FILE = 'chat_file';
    const USER_AVATAR = 'user_avatar';
    const ADVERTISE_RESOURCE = 'advertise_resource';
    const POST_RESOURCE = 'post_resource';
    const TEMPORARY = 'temporary';
    const INTEREST_RESOURCE = 'interest_resource';
    const SHOP_AVATAR = 'shop_avatar';
    const CATEGORY_LOGO = 'category_logo';
    const POST_AVATAR = 'post_avatar';
    const POST_BRANCH = 'post_branch';
    const BANNER_ORIGINAL_RESOURCE = 'banner_original_resource';
    const BANNER_RESOURCE = 'banner_resource';
    const PRODUCT_RESOURCE = 'product_resource';
    const SHOP_RESOURCE = 'shop_resource';

    public static function isUseShardedPath($type) {
        return in_array($type, [
			//self::POST_RESOURCE,
            /*self::JOB_RESOURCE,
            self::JOB_LOGO,
            self::COMPANY_RESOURCE,
            self::CHAT_IMAGE,
            self::MEMBERSHIP_CARD_LEVEL,
            self::VEHICLE_COLOR,
            self::VEHICLE_COLOR_ORIGINAL,
            self::VEHICLE_DESCRIPTION_DESIGN,
            self::VEHICLE_DESCRIPTION_TECH,
            self::VEHICLE_DESCRIPTION_UTILITY,
            self::VEHICLE_DESCRIPTION_SPECS,
            self::VEHICLE_PART_DESCRIPTION,
            self::POST_IMAGE,
            self::SERVICE_IMAGE,*/
            self::CHAT_IMAGE,
            self::CHAT_FILE,
        ]);
    }

    public static function hasCustomPathOption($type) {
        return in_array($type, [
        ]);
    }

    public static function isValid($type) {
        return in_array($type, [
            /*self::BANNER_ORIGINAL,
            self::BANNER,
            self::JOB_RESOURCE,
            self::JOB_LOGO,
            self::COMPANY_RESOURCE,
            self::CHAT_IMAGE,
            self::MEMBERSHIP_CARD_LEVEL,
            self::VEHICLE_COLOR,
            self::VEHICLE_COLOR_ORIGINAL,
            self::VEHICLE_DESCRIPTION_DESIGN,
            self::VEHICLE_DESCRIPTION_TECH,
            self::VEHICLE_DESCRIPTION_UTILITY,
            self::VEHICLE_DESCRIPTION_SPECS,
            self::VEHICLE_PART_DESCRIPTION,
            self::POST_IMAGE,
            self::ORDER_WORK_PROOF,*/
            self::CHAT_IMAGE,
            self::CHAT_FILE,
            self::USER_AVATAR,
            self::ADVERTISE_RESOURCE,
            self::POST_RESOURCE,
			self::TEMPORARY,
            self::INTEREST_RESOURCE,
            self::SHOP_AVATAR,
            self::CATEGORY_LOGO,
            self::POST_AVATAR,
            self::BANNER_ORIGINAL_RESOURCE,
            self::BANNER_RESOURCE,
            self::PRODUCT_RESOURCE,
            self::SHOP_RESOURCE,
        ]);
    }
}
