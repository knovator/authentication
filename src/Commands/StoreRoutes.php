<?php

namespace Knovators\Authentication\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Knovators\Authentication\Constants\Role as RoleConstant;
use Knovators\Authentication\Repository\PermissionRepository;
use Knovators\Authentication\Repository\RoleRepository;
use Prettus\Validator\Exceptions\ValidatorException;


/**
 * Class StoreRoutes
 * @package Knovators\Authentication\Commands
 */
class StoreRoutes extends Command
{

    protected $permissionRepository;

    protected $roleRepository;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store:routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store routes in to permissions table';

    /**
     * Create a new command instance.
     * @param PermissionRepository $permissionRepository
     * @param RoleRepository       $roleRepository
     */
    public function __construct(
        PermissionRepository $permissionRepository,
        RoleRepository $roleRepository
    ) {
        $this->permissionRepository = $permissionRepository;
        $this->roleRepository = $roleRepository;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws ValidatorException
     */
    public function handle() {
        foreach ($this->getAllRoutes() as $route) {
            /** @var \Illuminate\Routing\Route $route */
            $data['route_name'] = $route->getName();

            if ($data['route_name'] && $this->strPositionArray($data['route_name'],
                    config('authentication.permission.except_modules')) === false) {
                /** @var \Illuminate\Routing\Route $route */
                $data['uri'] = $route->uri;
                if ($data['module'] = strstr($data['route_name'], '.', true)) {
                    $role = $this->roleRepository->getRole(RoleConstant::ADMIN);
                    $permission = $this->createPermission($data);
                    $storeRoles = 'storeRoles' . config('authentication.db');
                    $this->$storeRoles($permission, $role);
                }
            }
        }

        return 'routes stored successfully.';
    }

    /**
     * @return mixed
     */
    private function getAllRoutes() {
        return Route::getRoutes();
    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool|int
     */
    private function strPositionArray($haystack, $needle) {
        if (!is_array($needle)) {
            $needle = [$needle];
        }
        foreach ($needle as $what) {
            if (($pos = strpos($haystack, $what)) !== false) {
                return $pos;
            }
        }

        return false;
    }

    /**
     * @param $data
     * @return mixed
     * @throws ValidatorException
     */
    private function createPermission($data) {
        return $this->permissionRepository->updateOrCreate(['route_name' => $data['route_name']],
            $data);

    }

    /**
     * @param $permission
     * @param $role
     */
    protected function storeRolesmysql($permission, $role) {
        $permission->roles()->sync([$role->id]);
    }

    /**
     * @param $permission
     * @param $role
     */
    protected function storeRolesmongodb($permission, $role) {
        $role->permissions()->attach($permission);
    }
}
