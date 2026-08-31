<template>
    <div class="sidebar-menu-item">
        <router-link v-if="isLink"
            class="nav-link sidebar-menu-link"
            :class="activeClasses"
            :style="itemStyle"
            :to="menu.route">
            <div v-if="menu.icon" class="sb-nav-link-icon text-primary">
                <i :class="`${menu.icon} fs-4`"></i>
            </div>
            <span :class="labelClasses">{{ menu.title }}</span>
        </router-link>
        <button v-else class="nav-link btn btn-link sidebar-menu-link text-start text-decoration-none"
            :class="buttonClasses" :style="itemStyle" type="button" @click="$emit('toggle-menu', menu.id)">
            <div v-if="menu.icon" class="sb-nav-link-icon">
                <i :class="`${menu.icon} fs-4 text-primary`"></i>
            </div>
            <span :class="labelClasses">{{ menu.title }}</span>
            <div class="sb-sidenav-collapse-arrow">
                <i class="bi bi-chevron-down"></i>
            </div>
        </button>
        <transition :css="false" @before-enter="collapseBeforeEnter" @enter="collapseEnter" @leave="collapseLeave">
            <div v-show="hasChildren && isOpen" class="sb-sidenav-menu-nested nav">
                <SidebarMenuItem v-for="child in menu.children" :key="child.id" :menu="child" :open-menus="openMenus"
                    :depth="depth + 1" @toggle-menu="$emit('toggle-menu', $event)" />
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    name: 'SidebarMenuItem',
    props: {
        menu: {
            type: Object,
            required: true
        },
        openMenus: {
            type: Array,
            required: true
        },
        depth: {
            type: Number,
            default: 0
        }
    },
    computed: {
        hasChildren() {
            return this.menu.children && this.menu.children.length > 0;
        },
        isOpen() {
            return this.openMenus.includes(this.menu.id);
        },
        isLink() {
            return  !this.hasChildren;
        },
        isActive() {
            return this.isCurrentRoute(this.menu) || this.hasActiveChild(this.menu);
        },
        activeClasses() {
            return {
                active: this.isActive,
                'bg-primary bg-opacity-10': this.isActive
            };
        },
        buttonClasses() {
            return {
                ...this.activeClasses,
                collapsed: !this.isOpen
            };
        },
        labelClasses() {
            return this.isActive
                ? 'fw-semibold text-primary text-opacity-10'
                : 'fw-semibold text-dark';
        },
        itemStyle() {
            return {
                paddingLeft: `${1 + (this.depth * 1.25)}rem`
            };
        }
    },

    methods: {
        isCurrentRoute(menu) {
            if (!menu.route || menu.route === '#') {
                return false;
            }

            const menuPath = this.resolveMenuPath(menu.route);
            const currentPath = this.normalizePath(this.$route.path);

            return currentPath === menuPath || currentPath.startsWith(`${menuPath}/`);
        },
        resolveMenuPath(route) {
            const resolvedRoute = this.$router.resolve(route);
            const redirect = resolvedRoute.matched.find(record => record.redirect)?.redirect;

            if (redirect) {
                const redirectTarget = typeof redirect === 'function'
                    ? redirect(resolvedRoute)
                    : redirect;

                return this.normalizePath(this.$router.resolve(redirectTarget).path);
            }

            return this.normalizePath(resolvedRoute.path);
        },
        normalizePath(path) {
            if (!path || path === '/') {
                return '/';
            }

            return path.replace(/\/+$/, '');
        },
        hasActiveChild(menu) {
            if (!menu.children || menu.children.length === 0) {
                return false;
            }

            return menu.children.some(child => {
                return this.isCurrentRoute(child) || this.hasActiveChild(child);
            });
        },
        collapseBeforeEnter(el) {
            el.style.height = '0px';
            el.style.opacity = '0';
            el.style.overflow = 'hidden';
            el.style.visibility = 'hidden';
        },
        collapseEnter(el, done) {
            this.animateCollapse(el, done, true);
        },
        collapseLeave(el, done) {
            this.animateCollapse(el, done, false);
        },
        animateCollapse(el, done, isOpening) {
            const duration = 220;

            const finish = () => {
                el.style.height = '';
                el.style.opacity = '';
                el.style.overflow = '';
                el.style.transition = '';
                el.style.visibility = '';
                done();
            };

            el.style.overflow = 'hidden';
            el.style.transition = `height ${duration}ms ease, opacity ${duration}ms ease`;

            if (isOpening) {
                el.style.height = '0px';
                el.style.opacity = '0';
                el.style.visibility = 'hidden';

                window.requestAnimationFrame(() => {
                    el.style.visibility = 'visible';
                    el.style.height = `${el.scrollHeight}px`;
                    el.style.opacity = '1';
                });
            } else {
                el.style.height = `${el.scrollHeight}px`;
                el.style.opacity = '1';
                el.style.visibility = 'visible';

                window.requestAnimationFrame(() => {
                    el.style.height = '0px';
                    el.style.opacity = '0';
                });
            }

            window.setTimeout(finish, duration);
        }
    }
};
</script>
