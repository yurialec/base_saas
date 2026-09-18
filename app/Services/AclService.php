<?php

namespace App\Services;

class AclService
{
    private const DEVELOPER_TENANT = 'desenvolvedor';
    private const ADMIN_ROLE = 'Administrativo';
    private const DEVELOPER_PERMISSIONS = ['menus', 'permissions'];

    public function can(string $permission): bool
    {
        /*
         * CRUD de menus e permissões é exclusivo do tenant desenvolvedor.
         */
        if (in_array($permission, self::DEVELOPER_PERMISSIONS, true)) {
            return $this->isDeveloperTenant();
        }

        /*
         * Administrativo acessa todos os demais módulos.
         */
        if ($this->isAdministrator()) {
            return true;
        }

        /*
         * Outros perfis dependem das permissões vinculadas.
         */
        return $this->hasPermission($permission);
    }

    public function isDeveloperTenant(): bool
    {
        return $this->tenantSlug() === self::DEVELOPER_TENANT;
    }

    public function isAdministrator(): bool
    {
        return session('user.role.name') === self::ADMIN_ROLE;
    }

    public function hasPermission(string $permission): bool
    {
        return in_array(
            $permission,
            $this->permissionSlugs(),
            true
        );
    }

    public function permissionSlugs(): array
    {
        return collect(session('user.role.permissions', []))
            ->map(function ($permission) {
                if (is_array($permission)) {
                    return $permission['slug'] ?? null;
                }

                return $permission->slug ?? null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    public function accessSignature(): string
    {
        $tenant = $this->tenantSlug();
        $role = session('user.role.name', '');

        $permissions = implode('|', $this->permissionSlugs());
        $developerPermissions = implode('|', self::DEVELOPER_PERMISSIONS);

        return hash('sha256', "{$tenant}|{$role}|{$permissions}|{$developerPermissions}");
    }

    private function tenantSlug(): string
    {
        $route = request()->route();
        return $route ? (string) $route->originalParameter('tenant', '') : '';
    }
}
