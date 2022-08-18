<?php
namespace App\Models;

use App\Enums\EDateFormat;
use App\Traits\DateTimeFix;
use App\Traits\ModelDataParse;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model {
	protected $dateFormat = EDateFormat::MODEL_DATE_FORMAT;
	protected $translationClass = null;

	use DateTimeFix;
	use ModelDataParse;

	public function getMetaAttribute($value) {
		return $this->getJsonValue($value);
	}

	public function setMetaAttribute($value) {
		$this->setJsonValue('meta', $value);
	}

	public function translations() {
		return $this->hasMany($this->translationClass, "{$this->table}_id", 'id');
	}

	public function getTranslation($languageCode, $key, $default = null) {
		if (empty($this->translationClass)) {
			return $default;
		}
		$translation = $this->translations->where('language_code', $languageCode)->first();
		if (empty($translation)) {
			return $default;
		}
		return $translation->{$key} ?? $default;
	}
}
