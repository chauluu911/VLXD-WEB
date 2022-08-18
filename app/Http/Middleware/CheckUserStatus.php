<?php

namespace App\Http\Middleware;

use App\Enums\EStatus;
use App\Constant\SessionKey;
use Closure;

class CheckUserStatus {
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next) {
        session()->put(SessionKey::ROUTE_INTENDED, url()->current());
        if (auth()->user()) {
            if (auth()->user()->status == EStatus::DELETED) {
                auth()->logout();
                return redirect()->route('home');
            } else if (!in_array(auth()->user()->status, [EStatus::ACTIVE, EStatus::DELETED])) {
                return redirect()->route('home');
            }
        } else {
            return redirect()->route('login');
        }
        return $next($request);
    }
}
