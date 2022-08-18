<?php

namespace App\Http\Controllers\Front;
use App\Constant\DefaultConfig;
use App\Constant\SessionKey;
use App\Enums\ECustomOrderStatusForUser;
use App\Enums\EDeliveryStatus;
use App\Enums\EErrorCode;
use App\Enums\EOrderStatus;
use App\Enums\EPaymentMethod;
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
use App\Models\Post;
use App\Helpers\FileUtility;

class NewsController extends Controller {
    private PostService $postService;

    public function __construct(PostService $postService) {
        $this->postService = $postService;
    }

    public function showNewsListView() {
        $page = request()->query('page');
        return view('front.news.list',['page' => $page]);
    }

    public function showNewsDetailView($id = null) {
        $news = $this->postService->getById($id);
        return view('front.news.detail', ['id' => $id,
            'title' => !empty($news) ? $news->title : null,
        ]);
    }

    public function getNewsForHeader(Request $request) {
        $options = [
            'pageSize' => $request->get('pageSize'),
            'status' => EStatus::ACTIVE,
        ];
        $news = $this->postService->getByOptions($options)
            ->map(function(Post $item) {
                $result = $item->only('id', 'title', 'content', 'avatarPath', 'created_at');
                if (!empty($item->avatar_path)) {
                    $result['avatarPath'] = FileUtility::getFileResourcePath($item->avatar_path);
                } else {
                    $result['avatarPath'] = DefaultConfig::FALLBACK_IMAGE_PATH;
                }
                $result['href'] = route('news.detail', [
                    'id' => $item->id,
                ], false);
                $result['createdAt'] = $item->created_at->format(EDateFormat::STANDARD_DATE_FORMAT);
                return $result;
            });
        $numberOfNews = $this->postService->getByOptions(['count' => true]);
        return response()->json([
            'news' => $news,
            'numberOfNews' => $numberOfNews,
        ]);
    }

    public function getNewsList(Request $request) {
        $pageSize = $request->get('pageSize');
        $ignoreList = $request->get('ignoreList');
        $option = [
            'pageSize' => $pageSize,
            'status' => EStatus::ACTIVE,
            'orderBy' => 'created_at',
            'orderDirection' => 'desc',
        ];
        if($ignoreList) {
            $option ['ignore'] = $ignoreList;
        }
        $news = $this->postService->getByOptions($option);
        $tmp = $news
            ->map(function(Post $item) {
                $result = $item->only('id', 'title', 'content','avatarPath');
                if (!empty($item->avatar_path)) {
                    $result['avatarPath'] = FileUtility::getFileResourcePath($item->avatar_path);
                } else {
                    $result['avatarPath'] = DefaultConfig::FALLBACK_IMAGE_PATH;
                }
                $result['redirectTo'] = route('news.detail', ['id' => $item->id ]);
                $result['createdAt'] = $item->created_at;
                return $result;
            });
        $news->setCollection($tmp);
        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'news' => $news,
        ]);
    }

    public function getNewsInfo($id = null ) {
        $news = $this->postService->getById($id);
        if (empty($news)) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'redirectTo' => route('news.list'),
            ]);
        }
        if($news->status == EStatus::DELETED) {
            return response()->json([
                'error' => EErrorCode::ERROR,
                'redirectTo' => route('news.list'),
            ]);
        }

        $data = [
            'createdAt' => $news->created_at,
            'avatarPath' => empty($news->avatar_path) ? DefaultConfig::FALLBACK_IMAGE_PATH :
                FileUtility::getFileResourcePath($news->avatar_path),
            'title' => $news->title,
            'content' => $news->content,
        ];

        return response()->json([
            'error' => EErrorCode::NO_ERROR,
            'news' => $data,
        ]);
    }
}
