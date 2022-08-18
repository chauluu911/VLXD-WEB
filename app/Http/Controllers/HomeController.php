<?php

namespace App\Http\Controllers;

use App\Constant\ConfigKey;
use App\Constant\SessionKey;
use App\Enums\EDisplayStatus;
use App\Enums\EErrorCode;
use App\Enums\ELanguage;
use App\Enums\EStatus;
use App\Enums\EUserType;
use App\Services\BannerService;
use App\Services\CategoryService;
use App\Services\ConfigService;
use App\Services\UserService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller {
	protected ConfigService $configService;
	protected UserService $userService;
	protected CategoryService $categoryService;
	protected BannerService $bannerService;

    public function __construct(ConfigService $configService,
								UserService $userService,
								CategoryService $categoryService,
								BannerService $bannerService) {
        $this->configService = $configService;
        $this->userService = $userService;
        $this->categoryService = $categoryService;
        $this->bannerService = $bannerService;
    }

    public function redirectToHome() {
    	return redirect()->route('home');
	}

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {
		$categoryList = $this->categoryService->getCategoryListForHome();
		$bannerList = $this->bannerService->getBannerListForHome();
        return view('front.home.home', [
        	'categoryList' => $categoryList,
			'bannerList' => $bannerList,
		]);
    }

	public function switchLocale(string $locale = ELanguage::EN) {
        if (!ELanguage::isSupportedLanguage($locale)) {
            $locale = ELanguage::EN;
        }
        session([SessionKey::LANG => $locale]);
        $this->userService->saveLanguageCode($locale);
        return redirect(request()->header("referer"));
	}

    public function indexBackend() {
        return view('back.index');
    }

    public function authorizeCheck() {
        $toUrl = request('toUrl');
        if ($toUrl === '/' || $toUrl === '/no-permission') {
            return 1;
        }

        $user = auth()->user();
        if ($user->type == EUserType::ADMIN) {
            return 1;
        }
        foreach ($user->aclUserRolePermissions as $permission) {
            $uriPaths = $permission->permissionGroup->uri_path;
            foreach ($uriPaths as $path) {
                // if ($path === "/back$toUrl" || $path === "/api$toUrl" || $path === $toUrl) {
                //     return 1;
                // }
                foreach (explode('/', $path) as $key) {
                    if (in_array($key, explode('/', $toUrl))) {
                        return 1;
                    }
                }

            }
        }
        abort(Response::HTTP_UNAUTHORIZED);
    }

	public function getAuthInfo() {
		$user = auth()->user();
		$authInfo = [
			'id' => $user->id,
			'name' => $user->name,
			'avatar_path' => null,
			'type' =>  EUserType::getLocalizedName($user->type),
		];
		if (!empty($user->avatar_path)) {
			$authInfo['avatar_path'] = get_image_url([
				'path' => $user->avatar_path,
				'op' => 'thumbnail',
				'w' => 300,
				'h' => 300
			]);
		}
		return response()->json([
			'error' => EErrorCode::NO_ERROR,
			'data' => $authInfo
		]);
	}
    public function getTime() {
        return microtime(true) * 1000;
    }

    public function getShop() {
        dd('qưe');
    }
}
