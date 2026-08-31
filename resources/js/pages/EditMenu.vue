<template>
    <section class="menu-form-page">
        <Loading :loading="isLoading" />

        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h4 mb-0">Editar Menu</h1>
            </div>
            <router-link
                class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 align-self-start align-self-lg-auto"
                to="/menus">
                <i class="bi bi-arrow-left"></i>
                <span>Voltar</span>
            </router-link>
        </div>

        <form class="row g-4" @submit.prevent="update">
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h2 class="h6 mb-0">Dados do menu</h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="menuName" class="form-label">Nome</label>
                                <input id="menuName" v-model.trim="menu.title" class="form-control" type="text"
                                    placeholder="Ex.: Dashboard" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="menuIcon" class="form-label">Icone</label>
                                <input id="menuIcon" v-model.trim="menu.icon" class="form-control" type="text"
                                    placeholder="Ex.: bi bi-grid" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="menuRoute" class="form-label">Rota</label>
                                <input id="menuRoute" v-model.trim="menu.route" class="form-control" type="text"
                                    placeholder="Ex.: /menus" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="menuParent" class="form-label">Menu pai</label>
                                <select id="menuParent" v-model="menu.parent_id" class="form-select">
                                    <option value="">Nenhum</option>
                                    <option v-for="item in parentMenus" :key="item.id" :value="item.id">
                                        {{ item.title }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <h2 class="h6 mb-0">Filhos</h2>
                            <button class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2"
                                type="button" :disabled="isLoading" @click="adicionarFilho">
                                <i class="bi bi-plus"></i>
                                <span>Adicionar</span>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div v-if="!hasChildren" class="empty-state text-center text-muted py-4">
                            <i class="bi bi-diagram-3 fs-2 d-block mb-2"></i>
                            <p class="small mb-0">Nenhum filho adicionado</p>
                        </div>

                        <div v-else class="vstack gap-3">
                            <div v-for="(child, index) in menu.children" :key="child.id || index"
                                class="child-menu-item border rounded p-3">
                                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                    <h3 class="h6 mb-0">Filho {{ index + 1 }}</h3>
                                    <button class="btn-close" type="button" aria-label="Remover filho"
                                        @click="menu.children.splice(index, 1)"></button>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label :for="`childName-${index}`" class="form-label">Nome</label>
                                        <input :id="`childName-${index}`" v-model.trim="child.title"
                                            class="form-control" type="text" placeholder="Ex.: Novo submenu" required>
                                    </div>
                                    <div class="col-12">
                                        <label :for="`childIcon-${index}`" class="form-label">Icone</label>
                                        <input :id="`childIcon-${index}`" v-model.trim="child.icon"
                                            class="form-control" type="text" placeholder="Ex.: bi bi-circle" required>
                                    </div>
                                    <div class="col-12">
                                        <label :for="`childRoute-${index}`" class="form-label">Destino</label>
                                        <input :id="`childRoute-${index}`" v-model.trim="child.route"
                                            class="form-control" type="text" placeholder="Ex.: /exemplo"
                                            required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex flex-column flex-sm-row justify-content-end gap-2">
                        <router-link class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center gap-2"
                            to="/menus">
                            <i class="bi bi-x-lg"></i>
                            <span>Cancelar</span>
                        </router-link>
                        <button class="btn btn-primary text-white btn-sm d-inline-flex align-items-center justify-content-center gap-2"
                            type="submit" :disabled="isLoading">
                            <i class="bi bi-check-lg"></i>
                            <span>Salvar</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </section>
</template>

<script>
export default {
    name: 'EditMenu',
    props: {
        id: {
            type: [String, Number],
            required: true
        }
    },
    data() {
        return {
            isLoading: false,
            menu: {
                title: '',
                icon: '',
                route: '',
                parent_id: '',
                children: []
            },
            menus: []
        };
    },
    computed: {
        hasChildren() {
            return Array.isArray(this.menu.children) && this.menu.children.length > 0;
        },
        parentMenus() {
            return this.menus.filter(item => Number(item.id) !== Number(this.id));
        }
    },
    mounted() {
        this.loadData();
    },
    methods: {
        async loadData() {
            this.isLoading = true;

            try {
                const [menuResponse, menusResponse] = await Promise.all([
                    axios.get(`/api/menus/find/${this.id}`),
                    axios.get('/api/menus/list-to-create')
                ]);

                const menu = Array.isArray(menuResponse.data) ? menuResponse.data[0] : menuResponse.data;

                this.menu = this.normalizeMenu(menu);
                this.menus = Array.isArray(menusResponse.data) ? menusResponse.data : [];
            } catch (error) {
                alertDanger(error);
            } finally {
                this.isLoading = false;
            }
        },
        async update() {
            if (!this.menu.title || !this.menu.icon || !this.menu.route) {
                alertDanger('Por favor, preencha todos os campos obrigatorios.');
                return;
            }

            this.isLoading = true;

            try {
                const response = await axios.put(`/api/menus/update/${this.id}`, this.createRequestPayload());
                alertSuccess('Menu alterado com sucesso!');
            } catch (error) {
                alertDanger(error);
            } finally {
                this.isLoading = false;
            }
        },
        adicionarFilho() {
            this.menu.children.push({
                title: '',
                icon: '',
                route: ''
            });
        },
        normalizeMenu(menu) {
            const normalizedMenu = menu || {};

            return {
                ...normalizedMenu,
                title: normalizedMenu.title || '',
                icon: normalizedMenu.icon || '',
                route: normalizedMenu.route || normalizedMenu.route || '',
                parent_id: normalizedMenu.parent_id || '',
                children: this.normalizeChildren(normalizedMenu.children)
            };
        },
        normalizeChildren(children) {
            if (!Array.isArray(children)) {
                return [];
            }

            return children.map(child => ({
                ...child,
                title: child.title || '',
                icon: child.icon || '',
                route: child.route || child.route || '',
                children: this.normalizeChildren(child.children)
            }));
        },
        createRequestPayload() {
            return {
                ...this.menu,
                children: this.createChildrenRequestPayload(this.menu.children)
            };
        },
        createChildrenRequestPayload(children) {
            if (!Array.isArray(children)) {
                return [];
            }

            return children.map(child => ({
                ...child,
                route: child.route || child.route || '',
                children: this.createChildrenRequestPayload(child.children)
            }));
        }
    }
};
</script>

<style scoped>
.menu-form-page .card {
    border-radius: 0.5rem;
}

.child-menu-item {
    background-color: var(--bs-tertiary-bg);
}

.empty-state {
    border: 1px dashed var(--bs-border-color);
    border-radius: 0.5rem;
}
</style>
