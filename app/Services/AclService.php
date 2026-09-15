<?php

namespace App\Services;

class AclService
{
    private const DEVELOPER_TENANT = 'desenvolvedor';
    private const ADMIN_ROLE = 'Administrativo';
    private const MENUS_PERMISSION = 'menus';

    public function can(string $permission): bool
    {
        /*
         * CRUD de menus é exclusivo do tenant desenvolvedor.
         */
        if ($permission === self::MENUS_PERMISSION) {
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
        return request()->route('tenant') === self::DEVELOPER_TENANT;
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
        $tenant = request()->route('tenant') ?? '';
        $role = session('user.role.name', '');

        $permissions = implode('|', $this->permissionSlugs());

        return hash('sha256',"{$tenant}|{$role}|{$permissions}");
    }
}
