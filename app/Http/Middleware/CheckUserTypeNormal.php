<?php

namespace App\Http\Middleware;

use App\Enums\EUserType;
use App\Enums\EStatus;
use Closure;

class CheckUserTypeNormal {
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next) {
        if (auth()->user()) {
            if (!auth()->user()->type == EUserType::NORMAL_USER || auth()->user()->status == EStatus::DELETED) {
                session()->flush();
                auth()->logout();
                return redirect()->route('home');
            }
        }
        return $next($request);
    }
}
