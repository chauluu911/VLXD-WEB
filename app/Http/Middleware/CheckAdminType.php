<?php

namespace App\Http\Middleware;

use App\Enums\EUserType;
use Closure;

class CheckAdminType {
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next) {
        if (auth()->user()) {
            if (auth()->user()->type != EUserType::ADMIN) {
                session()->flush();
                auth()->logout();
                return redirect()->route('home');
            }
        }
        return $next($request);
    }
}
