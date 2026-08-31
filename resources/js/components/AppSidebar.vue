<template>
    <nav id="sidenavAccordion" class="sb-sidenav accordion sb-sidenav-light shadow">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <sidebar-menu-item v-for="menu in menus" :key="menu.id" :menu="menu" :open-menus="openMenus"
                    @toggle-menu="toggleMenu" />
            </div>
        </div>
        <div class="sb-sidenav-footer shadow">
            <button class="btn btn-link d-flex align-items-center gap-2 text-dark-50 text-decoration-none p-0"
                type="button" @click="$emit('logout')">
                <i class="bi bi-power fs-4"></i>
                Sair
            </button>
        </div>
    </nav>
</template>

<script>
import SidebarMenuItem from './SidebarMenuItem.vue';

export default {
    name: 'AppSidebar',
    components: {
        SidebarMenuItem
    },
    data() {
        return {
            menus: [],
            openMenus: []
        };
    },
    mounted() {
        this.loadSidebar();
    },
    methods: {
        async loadSidebar() {
            try {
                const response = await axios.get('/api/sidebar');
                this.menus = Array.isArray(response.data)
                    ? response.data
                    : response.data.menus || [];
            } catch (error) {
                console.error('Erro ao carregar sidebar', error);
                this.menus = [];
            }
        },
        toggleMenu(menuId) {
            if (this.openMenus.includes(menuId)) {
                this.openMenus = this.openMenus.filter(id => id !== menuId);
                return;
            }

            this.openMenus.push(menuId);
        }
    }
};
</script>
