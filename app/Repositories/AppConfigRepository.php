<?php


namespace App\Repositories;


use App\Enums\EStatus;
use App\Helpers\ConfigHelper;
use App\Models\AppConfig;

class AppConfigRepository extends BaseRepository {
    public function __construct() {
        $this->model = new AppConfig();
    }

    public function getByName($configKey) {
        return $this->model->where('status', EStatus::ACTIVE)
            ->where('name', $configKey)
            ->where('version', 0)
            ->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getConfig() {
        return $this->model->where('version', 0)->get();
    }

    public function updateConfig($data) {
        ConfigHelper::clear();
        foreach ($data as $key => $value) {
            if ($key === '_token') continue;
            $config = $this->getByName($key);
            if (!$config) {
                $config = new AppConfig();
                $config->name = $key;
            }
            is_numeric($value) ? ($config->numeric_value = $value) : ($config->text_value = $value);
            $config->status = EStatus::ACTIVE;
            $config->save();
        }
    }
}
