<?php

namespace App\Repositories;


use App\Models\Post;

class PostResourceRepository extends BaseRepository {

	public function __construct(PostResource $postResource) {
		$this->model = $postResource;
	}

}
