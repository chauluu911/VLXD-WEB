<?php

namespace App\Http\Middleware;

use App\Enums\EUserType;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class CanUseModule {
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $modules) {
        $user = $request->user();
        /*$uri = $request->getRequestUri();
        foreach($user->aclUserRolePermissions as $permission) {
            $uriPaths = $permission->permissionGroup->uri_path;
            foreach ($uriPaths as $path) {
                if (strpos($uri, $path) === 0) {
                    return $next($request);
                }
            }
        }*/

        if ($user->type == EUserType::ADMIN) {
            return $next($request);
        }

        foreach ($user->aclUserRolePermissions as $permission) {
            $found = collect($modules)->first(function($module) use ($permission) {
                return $module === $permission->permissionGroup->code;
            });
            if ($found) {
                return $next($request);
            }
        }

        abort(Response::HTTP_UNAUTHORIZED);
    }
}
