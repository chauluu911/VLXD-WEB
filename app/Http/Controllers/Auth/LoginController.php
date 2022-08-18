<?php

namespace App\Http\Controllers\Auth;

use App\Enums\EErrorCode;
use App\Enums\EUserType;
use App\Enums\EStatus;
use App\Helpers\ValidatorHelper;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;
use App\Constant\SessionKey;

class LoginController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/back';

    protected $decayMinutes = 3;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showBackendLoginForm() {
        return view('back.auth.login');
    }

    public function showLoginForm() {
        return view('front.login.login');
    }

    public function username(Request $request = null)
    {
        if($request){
            if(request()->filled("phone")){
                return 'phone';
            }
        }
        return 'email';
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username($request) => 'required|string',
            'password' => 'required|string',
        ]);
    }

    protected function credentials(Request $request) {
        $input = $request->only($this->username($request), 'email', 'password');
         if($request->ajax()) {
			 $input = [
                 'phone' => $input['phone'],
                 'password' => $input['password'],
             ];
         }
		$input['status'] = [
			EStatus::ACTIVE,
			EStatus::SUSPENDED,
			EStatus::WAITING
		];
        return $input;
    }

    /**
     * The user has been authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user) {
        $token = Str::random(80);
        $user->forceFill([
            'api_token' => hash('sha256', $token),
        ])->save();
        if (request()->ajax()) {
            if ($user->type == EUserType::INTERNAL_USER || $user->type == EUserType::ADMIN ||  $user->status === EStatus::DELETED) {
                return $this->sendFailedLoginResponse($request);
            }
            if (session()->has(SessionKey::ROUTE_INTENDED)) {
                $redirectTo = session()->get(SessionKey::ROUTE_INTENDED);
                session()->forget(SessionKey::ROUTE_INTENDED);
            } else {
                $redirectTo = url()->previous();
            }
            return response()->json([
                'error' => EErrorCode::NO_ERROR,
                'redirectTo' => $redirectTo,
            ]);
        }
        return redirect($this->redirectTo);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request) {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            try {
                return $this->sendLockoutResponse($request);
            } catch (ValidationException $e) {
                $seconds = $this->limiter()->availableIn(
                    $this->throttleKey($request)
                );
                if (request()->ajax()) {
                    return response()->json([
                        'error' => EErrorCode::ERROR,
                        'availableIn' => $seconds,
                    ]);
                }
                return redirect()->route('back.login')
                    ->with(['availableIn' => $seconds])
                    ->withInput()
                    ->withErrors(['email' => true]);
            }
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }
}
