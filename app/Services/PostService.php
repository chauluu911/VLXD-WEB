<?php

namespace App\Services;

use App\Constant\DefaultConfig;
use App\Enums\EDateFormat;
use App\Enums\EErrorCode;
use App\Enums\EOtpType;
use App\Enums\EStatus;
use App\Repositories\PostRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\FileUtility;
use App\Enums\EStoreFileType;
use App\Models\Post;


class PostService {
	private PostRepository $postRepository;

    public function __construct(PostRepository $postRepository) {
        $this->postRepository = $postRepository;
    }

    /**
     * @param $userId
     * @return \App\User|\Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function getById($shopId) {
	    return $this->postRepository->getById($shopId);
    }

    public function getByOptions(array $options) {
        return $this->postRepository->getByOptions($options);
    }

    public function savePost($data, $loggedInUserId) {
        $fileToDeleteIfError = [];
        try {
            return DB::transaction(function() use ($data, $loggedInUserId, &$fileToDeleteIfError) {
                $id = Arr::get($data, 'id');
                if ($id) {
                    $post = $this->getById($id);
                    if (empty($post)) {
                        return ['error' => EErrorCode::ERROR,
                        'msg' => __('common/error.invalid-request-data')];
                    }
                    $post->updated_by = $loggedInUserId;
                } else {
                    $post = new Post();
                    $post->created_by = $loggedInUserId;
                    $post->status = EStatus::ACTIVE;
                }
                $post->title = Arr::get($data, 'title');
                $post->content = Arr::get($data, 'content');

                $original_avatar_path = Arr::get($data, 'blob');
                $avatar_path = Arr::get($data, 'file');

                if (!empty($original_avatar_path)) {
                    $relativePath = FileUtility::storeFile(EStoreFileType::POST_AVATAR, $original_avatar_path);
                    FileUtility::removeFiles([$post->avatar_path]);
                    $post->original_avatar_path = $relativePath;
                    $fileToDeleteIfError[] = $relativePath;
                }

                if (!empty($avatar_path)) {
                    $relativePath = FileUtility::storeFile(EStoreFileType::POST_AVATAR, $avatar_path);
                    FileUtility::removeFiles([$post->avatar_path]);
                    $post->avatar_path = $relativePath;
                    $fileToDeleteIfError[] = $relativePath;
                }
                $post->save();

                return [
                	'error' => EErrorCode::NO_ERROR,
					'post' => $post,
				];
            });
        } catch (\Exception $e) {
            FileUtility::removeFiles($fileToDeleteIfError);
            throw $e;
        }
    }

    public function deletePost($id, $loggedInUserId) {
        return DB::transaction(function() use ($id, $loggedInUserId) {
            $post = $this->getById($id);
            if (empty($post)) {
                return ['error' => EErrorCode::ERROR, 'msg' => __('common/error.invalid-request-data')];
            }
            $post->deleted_by = $loggedInUserId;
            $post->deleted_at = Carbon::now();
            $post->status = EStatus::DELETED;
            $post->save();
            return ['error' => EErrorCode::NO_ERROR];
        });
    }
}
