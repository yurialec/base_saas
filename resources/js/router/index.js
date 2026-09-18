import { createRouter, createWebHistory } from 'vue-router';

import adminRoutes from './admin';
import menuRoutes from './menus';
import roleRoutes from './roles';
import permissionRoutes from './permissions';
import usersRoutes from './users';

const tenant = window.App.tenant;

const router = createRouter({
    history: createWebHistory(`/${tenant.slug}/`),

    routes: [
        ...adminRoutes,
        ...menuRoutes,
        ...roleRoutes,
        ...permissionRoutes,
        ...usersRoutes
    ]
});

const DEVELOPER_TENANT = 'desenvolvedor';
const ADMIN_ROLE = 'Administrativo';
const DEVELOPER_PERMISSIONS = ['menus', 'permissions'];

function canAccess(permission) {
    if (!permission) {
        return true;
    }

    const tenant = window.App.tenant || {};
    const user = window.App.user || {};
    const role = user.role || {};

    if (DEVELOPER_PERMISSIONS.includes(permission)) {
        return tenant.slug === DEVELOPER_TENANT;
    }

    if (role.name === ADMIN_ROLE) {
        return true;
    }

    return Array.isArray(window.App.permissions)
        && window.App.permissions.includes(permission);
}

router.beforeEach((to) => {
    const permission = to.meta.permission;

    if (canAccess(permission)) {
        return true;
    }

    return { name: 'dashboard' };
});

export default router;
