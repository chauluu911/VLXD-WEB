<?php

namespace App\Http\Controllers\Back;

use App\Constant\DefaultConfig;
use App\Constant\SessionKey;
use App\Enums\EErrorCode;
use App\Helpers\ConfigHelper;
use App\Helpers\ValidatorHelper;
use \App\Http\Controllers\Controller;
use App\Constant\ConfigKey;
use App\Enums\EDateFormat;
use App\Enums\EStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Enums\ELanguage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Requests\Post\SaveInfoRequest;
use App\Services\PostService;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller {
	private PostService $postService;

	public function __construct(PostService $postService) {
		$this->postService = $postService;
	}

	public function getPostList() {
        $tz = session(SessionKey::TIMEZONE);
		$acceptFields = ['q', 'createdAtFrom', 'createdAtTo', 'status'];
		$filters = [
			'admin_post_list' => true,
			'pageSize' => request('pageSize'),
		];

		foreach ($acceptFields as $field) {
			if (!request("filter.$field")) {
				continue;
			}
			if ($field === 'createdAtFrom' || $field === 'createdAtTo') {
                if (Arr::has(request('filter'), $field)) {
                    try {
                        $date = Carbon::createFromFormat(EDateFormat::DEFAULT_DATEPICKER_INPUT_FORMAT_WITH_TZ, request('filter')[$field]." $tz");
                        $filters[$field] = $date;
                    } catch (\Exception $e) {
                    }
                }
            } else {
                $filters[$field] = request("filter.$field");
            }
		}
		$post = $this->postService->getByOptions($filters);
		foreach ($post as $key => $value) {
			$value->contentSub = strip_tags($value->content);
			$value->avatar = !empty($value->avatar_path) ? get_image_url([
				'path' => $value->avatar_path,
				'op' => 'thumbnail',
				'w' => 50,
				'h' => 50,
			]) : DefaultConfig::FALLBACK_USER_AVATAR_PATH;
			$value->strStatus = EStatus::valueToLocalizedName($value->status);
			$value->createdAt =  Carbon::parse($value->created_at)->format(EDateFormat::STANDARD_DATE_FORMAT);
		}
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'data' => $post,
		]);
	}

	public function deletePost() {
		// try {
			$id = request('id');
			$delete = $this->postService->deletePost($id, auth()->id());
			if ($delete['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($delete);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => __('common/common.delete-data-success', [
					'objectName' => __('back/post.object_name')
				])
			]);
		// } catch (\Exception $e) {
		// 	logger()->error('Fail to delete post', [
		// 		'error' =>  $e
		// 	]);
		// 	return response()->json([
		// 		'error' => EErrorCode::ERROR,
		// 		'msg' => __('common/common.there_was_an_error_in_the_processing'),
		// 	]);
		// }
	}

	public function savePost(SaveInfoRequest $request) {
		// try {
			$data = $request->validated();
			$data['avatar'] = request('image');
			$data['id'] = request('id');
			$data['blob'] = request('blob');
			$data['file'] = request('file');
			$result = $this->postService->savePost($data, auth()->id());
			if ($result['error'] !== EErrorCode::NO_ERROR) {
				return response()->json($result);
			}
			return response()->json([
				'error' => EErrorCode::NO_ERROR,
				'msg' => __('common/common.save-data-success', [
					'objectName' => __('back/post.object_name')
				])
			]);
		// } catch (\Exception $e) {
		// 	logger()->error('Fail to save', [
		// 		'error' =>  $e
		// 	]);
		// 	return response()->json([
		// 		'error' => EErrorCode::ERROR,
		// 		'msg' => __('common/common.there_was_an_error_in_the_processing'),
		// 	]);
		// }
	}

	public function getPostInfo($id) {
		$option = [
			'id' => $id,
			'first' => true
		];
		$news = $this->postService->getByOptions($option);
		$data = [
			'image' => !empty($news->avatar_path) ? get_image_url([
				'path' => $news->avatar_path,
				'op' => 'thumbnail',
				'w' => 100,
				'h' => 100,
			]) : DefaultConfig::FALLBACK_USER_AVATAR_PATH,
			'status' => $news->status,
			'id' => $news->id,
			'title' => $news->title,
			'originalAvatarPath' => !empty($news->original_avatar_path) ? config('app.resource_url_path') . '/' . $news->original_avatar_path : DefaultConfig::FALLBACK_IMAGE_PATH,
			'content' => $news->content,
			'statusStr' => EStatus::valueToLocalizedName($news->status),
		];
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'news' => $data,
		]);
	}
}
