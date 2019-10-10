<?php

use Illuminate\Database\Seeder;
use Knovators\Authentication\Models\Role;
use Knovators\Authentication\Repository\PermissionRepository;
use Knovators\Authentication\Repository\RoleRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class CreatePermissionSeeder
 */
class CreatePermissionSeeder extends Seeder
{

    protected $roleRepository;

    protected $permissionRepository;


    /**
     * PurchaseController constructor
     * @param RoleRepository       $roleRepository
     * @param PermissionRepository $permissionRepository
     */
    public function __construct(
        RoleRepository $roleRepository,
        PermissionRepository $permissionRepository
    ) {
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws RepositoryException
     */
    public function run() {
        $roles = $this->roleRepository->all()->keyBy('code');
        $permissions = $this->permissions();
        foreach ($roles as $role) {

            if (isset($permissions[$role['code']])) {
                $permissionIds = $this->permissionRepository->makeModel()
                                                            ->whereIn('route_name',
                                                                $permissions[$role['code']])
                                                            ->pluck('id')
                                                            ->toArray();
                /** @var Role $role */
                $role->permissions()->sync($permissionIds);

            }


        }

    }


    /**
     * @return mixed
     */
    private function permissions() {

        $roles['DESIGNER'] = [
            "designs.index",
            "designs.create",
            "designs.store",
            "designs.show",
            "designs.edit",
            "designs.update",
            "designs.destroy",
            "designs.partially-update",
            "designs.partially-approve",
            "designs.active-designs",
            "designs.export",
            "threads.colors-list",
            "recipes.active.index",
            "recipes.store",

            "users.change-password",
            "users.change-profile",

            "dashboard.most-used-designs",
            "dashboard.design-analysis",
        ];

        $roles['MANAGER'] = [
            "media.index",
            "masters.index",
            "masters.create",
            "masters.store",
            "masters.show",
            "masters.edit",
            "masters.update",
            "masters.destroy",
            "masters.partially.update",
            "masters.childs.index",
            "customers.index",
            "customers.create",
            "customers.store",
            "customers.show",
            "customers.edit",
            "customers.update",
            "customers.destroy",
            "customers.partially-update",
            "customers.agents.index",
            "customers.ledgers.index",
            "customers.ledgers.export",
            "designs.index",
            "designs.create",
            "designs.store",
            "designs.show",
            "designs.edit",
            "designs.update",
            "designs.destroy",
            "designs.partially-update",
            "designs.partially-approve",
            "designs.export",
            "machines.index",
            "machines.create",
            "machines.store",
            "machines.show",
            "machines.edit",
            "machines.update",
            "machines.destroy",
            "machines.partially-update",
            "purchases.index",
            "purchases.create",
            "purchases.store",
            "purchases.show",
            "purchases.edit",
            "purchases.update",
            "purchases.destroy",
            "purchases.change-status",
            "purchases.deliveries.index",
            "purchases.threads.index",
            "purchases.deliveries.create",
            "purchases.export",
            "recipes.index",
            "recipes.store",
            "recipes.store",
            "recipes.show",
            "recipes.edit",
            "recipes.update",
            "recipes.destroy",
            "recipes.partially-update",
            "recipes.active.index",
            "sales.index",
            "sales.create",
            "sales.store",
            "sales.show",
            "sales.edit",
            "sales.update",
            "sales.destroy",
            "sales.change-status",
            "sales.recipes.index",
            "sales.thread.analysis",
            "sales.manufacturing.companies",
            "sales.statuses",
            "sales.export.summary",
            "sales.export",
            "deliveries.create",
            "deliveries.update",
            "deliveries.destroy",
            "deliveries.index",
            "deliveries.change-status",
            "deliveries.export.manufacturing",
            "deliveries.export.accounting",
            "stocks.index",
            "stocks.count",
            "stocks.report",
            "threads.index",
            "threads.create",
            "threads.store",
            "threads.show",
            "threads.edit",
            "threads.update",
            "threads.destroy",
            "threads.partially-update",
            "threads.colors-partially-update",
            "threads.colors-list",
            "wastages.index",
            "wastages.create",
            "wastages.store",
            "wastages.show",
            "wastages.edit",
            "wastages.update",
            "wastages.destroy",
            "wastages.change-status",
            "wastages.export",
            "wastages.export.summary",
            "yarns.index",
            "yarns.create",
            "yarns.store",
            "yarns.show",
            "yarns.edit",
            "yarns.update",
            "yarns.destroy",
            "yarns.change-status",
            "yarns.statuses",
            "yarns.payment-approve",
            "yarns.export",
            "yarns.export.summary",
            "users.change-password",
            "users.change-profile",
            "dashboard.order-analysis",
            "dashboard.most-used-designs",
            "dashboard.design-analysis",
        ];

        $roles['PROGRAMMER'] = [
            // design
            "designs.index",
            "designs.create",
            "designs.store",
            "designs.show",
            "designs.edit",
            "designs.update",
            "designs.destroy",
            "designs.partially-update",
            "designs.partially-approve",
            "designs.export",
            "threads.colors-list",
            "recipes.active.index",
            "recipes.store",
            // machine
            "machines.index",
            "machines.create",
            "machines.store",
            "machines.show",
            "machines.edit",
            "machines.update",
            "machines.destroy",
            "machines.partially-update",

            // SO fabric
            "sales.index",
            "sales.create",
            "sales.store",
            "sales.show",
            "sales.edit",
            "sales.update",
            "sales.destroy",
            "sales.change-status",
            "sales.recipes.index",
            "sales.thread.analysis",
            "sales.manufacturing.companies",
            "sales.statuses",
            "sales.export.summary",
            "sales.export",
            "customers.index",
            "designs.active-designs",
            "designs.show",
            // SO fabric deliveries
            "deliveries.create",
            "deliveries.update",
            "deliveries.destroy",
            "deliveries.index",
            "deliveries.change-status",
            "deliveries.export.manufacturing",
            "deliveries.export.accounting",
            // SO Yarn
            "yarns.index",
            "yarns.create",
            "yarns.store",
            "yarns.show",
            "yarns.edit",
            "yarns.update",
            "yarns.destroy",
            "yarns.change-status",
            "yarns.statuses",
            "yarns.payment-approve",
            "yarns.export",
            "yarns.export.summary",
            // PO
            "purchases.index",
            "purchases.create",
            "purchases.store",
            "purchases.show",
            "purchases.edit",
            "purchases.update",
            "purchases.destroy",
            "purchases.change-status",
            "purchases.deliveries.index",
            "purchases.threads.index",
            "purchases.deliveries.create",
            "purchases.export",
            "masters.index",
            // Wastage
            "wastages.index",
            "wastages.create",
            "wastages.store",
            "wastages.show",
            "wastages.edit",
            "wastages.update",
            "wastages.destroy",
            "wastages.change-status",
            "wastages.export",
            "wastages.export.summary",

            "stocks.index",
            "stocks.count",
            "stocks.report",
            "threads.show",

            "users.change-password",
            "users.change-profile",
            "dashboard.order-analysis",
            "dashboard.most-used-designs",
            "dashboard.design-analysis",
        ];

        $roles['ACCOUNTANT'] = [
            // PO
            "purchases.index",
            "purchases.create",
            "purchases.store",
            "purchases.show",
            "purchases.edit",
            "purchases.update",
            "purchases.destroy",
            "purchases.change-status",
            "purchases.deliveries.index",
            "purchases.threads.index",
            "purchases.deliveries.create",
            "purchases.export",
            "masters.index",
            "customers.index",
            "threads.colors-list",
            // Stock
            "stocks.index",
            "stocks.count",
            "stocks.report",
            "threads.show",

            "dashboard.order-analysis",
            "sales.index",
            "users.change-password",
            "users.change-profile",
        ];

        return $roles;
    }


}
