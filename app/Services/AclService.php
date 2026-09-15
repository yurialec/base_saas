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
         * O CRUD de menus é exclusivo do tenant desenvolvedor.
         */
        if ($permission === self::MENUS_PERMISSION) {
            return $this->isDeveloperTenant();
        }

        /*
         * Administrador possui acesso total aos demais módulos.
         */
        if ($this->isAdministrator()) {
            return true;
        }

        /*
         * Demais usuários dependem das permissões do perfil.
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
        return collect(session('user.role.permissions', []))
            ->contains(function ($item) use ($permission) {

                if (is_array($item)) {
                    return ($item['slug'] ?? null) === $permission;
                }

                return ($item->slug ?? null) === $permission;
            });
    }
}
