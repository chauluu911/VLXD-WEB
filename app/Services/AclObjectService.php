<?php

namespace App\Services;

use App\Repositories\AclObjectRepository;
use Illuminate\Database\Eloquent\Collection;

class AclObjectService {

	private $aclObjectRepository;

    public function __construct(AclObjectRepository $aclObjectRepository) {
        $this->aclObjectRepository = $aclObjectRepository;
    }

    public function getByOptions($options = []) {
	    return $this->aclObjectRepository->getByOptions($options);
    }
}
