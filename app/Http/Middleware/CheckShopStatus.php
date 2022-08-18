<?php

namespace App\Http\Middleware;

use App\Enums\EStatus;
use Closure;

class CheckShopStatus {
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next) {
        if (auth()->user()) {
            if (auth()->user()->getShop->status == EStatus::DELETED) {
                return redirect()->route('home');
            }
        } else {
            return redirect()->route('login');
        }
        return $next($request);
    }
}
