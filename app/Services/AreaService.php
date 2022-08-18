<?php

namespace App\Services;

use App\Repositories\AreaRepository;
use App\Enums\EAreaType;
use App\Models\Area;
use Illuminate\Support\Arr;

class AreaService {

	private AreaRepository $areaRepository;

    public function __construct(AreaRepository $areaRepository) {
        $this->areaRepository = $areaRepository;
    }

	/**
	 * @param int $id
	 * @return \App\Models\Area
	 */
	public function getById(int $id) {
		return $this->areaRepository->getById($id);
	}

    public function getByOptions(array $options) {
        return $this->areaRepository->getByOptions($options);
    }

	public function getAreaForAreaFilter(array $options) {
    	return $this->areaRepository->getAreaForAreaFilter($options);
	}

	public function processArea(?Area $area, $option, array &$result = []) {
        $allAreaId = [];
        if (empty($result)) {
            $result = [
                'province' => (object)[],
                'district' => (object)[],
                'ward' => (object)[],
            ];
        }
        if (empty($area)) {
            return $result;
        }
        switch ($area->type) {
            case EAreaType::PROVINCE:
                Arr::set($result, 'province', [
                    'id' => $area->id,
                    'name' => $area->name,
                ]);
                foreach ($area->$option as $item) {
                    $this->processArea($item, $option, $result);
                }
                //array_push($allAreaId, $area->id);
                break;
            case EAreaType::DISTRICT:
                Arr::set($result, 'district', [
                    'id' => $area->id,
                    'name' => $area->name,
                ]);
                foreach ($area->$option as $item) {
                    $this->processArea($item, $option, $result);
                }
                //array_push($allAreaId, $area->id);
                break;
            case EAreaType::WARD:
                Arr::set($result, 'ward', [
                    'id' => $area->id,
                    'name' => $area->name,
                ]);
                foreach ($area->$option as $item) {
                    $this->processArea($item, $option, $result);
                }
                //array_push($allAreaId, $area->id);
                break;
        }
        return [
            'result' => $result,
            // 'allAreaId' => $allAreaId,
        ];
    }
}
