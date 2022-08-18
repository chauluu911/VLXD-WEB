<?php

namespace App\Providers;

use App\Enums\EUserType;
use App\Enums\EStatus;
use App\Repositories\AclObjectRepository;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class AuthServiceProvider extends ServiceProvider {
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot() {
        $this->registerPolicies();
        $aclObjectRepository = $this->app->make('App\Repositories\AclObjectRepository');
        $modules = $aclObjectRepository->getByOptions([
            'status' => EStatus::ACTIVE,
            'get' => true,
        ]);

        foreach ($modules as $module) {
            $value = $module->code;
            $key = Str::slug($value);
            Gate::define("use-$key", function ($user) use ($value) {
                if ($user->type == EUserType::ADMIN) {
                    return true;
                }

                foreach ($user->aclUserRolePermissions as $permission) {
                    $aclObject = $permission->permissionGroup;
                    if ($aclObject->code === $value) {
                        return true;
                    }
                }

                return false;
            });
        }
    }
}
