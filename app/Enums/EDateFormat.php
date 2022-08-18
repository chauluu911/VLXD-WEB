<?php
namespace App\Enums;


abstract class EDateFormat {
	const MODEL_DATE_FORMAT = 'Y-m-d H:i:s.u O';
	const DATE_FORMAT_WITHOUT_MICROSECOND = 'Y-m-d H:i:s O';
    const STANDARD_DATE_FORMAT = 'd/m/Y';
    const STANDARD_MONTH_FORMAT = 'm-Y';
    const STANDARD_DATE_FORMAT_WITH_TZ = 'd/m/Y e';
    const STANDARD_DATE_FORMAT2 = '!d/m/Y';
	const STANDARD_DATE_TIME_FORMAT = 'Y-m-d H:i';

	const LOCALIZE_DATE_FORMAT = '%d-%b-%Y'; // use with Carbon::formatLocalized, reference: http://www.php.net/strftime
	const LOCALIZE_DATE_TIME_FORMAT = '%R, %d-%b-%Y';

	const FORMAT_DATE_JS = 'dd/mm/yyyy';
	const FORMAT_DATE_TIME_JS = 'd/m/Y, H:i';
	const FORMAT_TIME_FORMAT = 'H:i';
	const FORMAT_YEAR_MONTH_DATE = 'Y-m-d';

	const FORMAT_TIME_DATE = 'H:i:s - d/m/Y';
	const DEFAULT_DATE_INPUT_FORMAT = 'Y-m-d';
	const DEFAULT_DATE_INPUT_FORMAT_WITH_TZ = 'Y-m-d e';
	const DEFAULT_DATEPICKER_INPUT_FORMAT_WITH_TZ = 'd/m/Y e';
	const DATE_FORMAT_HOUR = 'd/m/Y H:i';
	const DATE_FORMAT = 'H:i, d/m/Y';
}
