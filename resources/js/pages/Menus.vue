<template>
    <section class="menus-page">
        <Loading :loading="isLoading" />
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h4 mb-0">Menus</h1>
            </div>
            <router-link
                class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 align-self-start align-self-lg-auto text-white"
                to="/menus/create">
                <i class="bi bi-plus"></i><span>Cadastrar</span>
            </router-link>
        </div>
        <div class="row g-4">
            <div class="col-12 col-xl-2">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pb-0">
                        <h2 class="h6 mb-0">Filtros</h2>
                    </div>
                    <div class="card-body">
                        <form class="vstack gap-3" @submit.prevent="search">
                            <div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input id="menu-title-filter" v-model.trim="filter.title" class="form-control"
                                        type="text" placeholder="Titulo, rota ou icone">
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button
                                    class="btn btn-primary btn-sm d-inline-flex align-items-center justify-content-center gap-2 text-white"
                                    type="submit" :disabled="isLoading">
                                    <i class="bi bi-search"></i>
                                    <span>Pesquisar</span>
                                </button>
                                <button
                                    class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center gap-2"
                                    type="button" @click="clearFilters">
                                    <i class="bi bi-x-lg"></i>
                                    <span>Limpar filtros</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-10">
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 menus-table">
                            <colgroup>
                                <col class="menus-table-id">
                                <col class="menus-table-menu">
                                <col class="menus-table-icon">
                                <col class="menus-table-destination">
                                <col class="menus-table-status">
                                <col class="menus-table-order">
                                <col class="menus-table-actions">
                            </colgroup>
                            <thead class="table-light">
                                <tr>
                                    <th class="text-nowrap" scope="col">#</th>
                                    <th scope="col">Menu</th>
                                    <th scope="col">Icone</th>
                                    <th scope="col">Destino</th>
                                    <th class="text-center" scope="col">Status</th>
                                    <th class="text-end" scope="col">Ordem</th>
                                    <th class="text-center" scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="filteredMenus.length === 0">
                                    <td class="text-center text-muted py-5" colspan="7">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        Nenhum menu encontrado
                                    </td>
                                </tr>
                                <tr v-for="menu in paginatedMenus" :key="menu.id">
                                    <th class="text-muted fw-normal text-nowrap" scope="row">
                                        {{ menu.id }}
                                    </th>
                                    <td class="menu-title-cell">
                                        <div class="menu-title-content d-flex align-items-center gap-2"
                                            :style="{ paddingLeft: `${menu.depth * 1.25}rem` }">
                                            <button v-if="menu.hasChildren"
                                                class="btn btn-link btn-sm p-0 menu-toggle-button" type="button"
                                                :aria-label="menu.isCollapsed ? 'Expandir filhos' : 'Recolher filhos'"
                                                :title="menu.isCollapsed ? 'Expandir filhos' : 'Recolher filhos'"
                                                @click="toggleMenuChildren(menu.id)">
                                                <i
                                                    :class="menu.isCollapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-down'"></i>
                                            </button>
                                            <span v-else class="menu-toggle-placeholder"></span>
                                            <i v-if="menu.depth" class="bi bi-arrow-return-right text-muted"></i>
                                            <span class="fw-semibold menu-title-text">{{ menu.title }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="menu-icon-content d-inline-flex align-items-center gap-2">
                                            <span
                                                class="menu-icon-preview d-inline-flex align-items-center justify-content-center rounded bg-body-secondary text-secondary">
                                                <i v-if="menu.icon" :class="menu.icon"></i>
                                                <i v-else class="bi bi-dash-lg"></i>
                                            </span>
                                            <code
                                                class="menu-icon-value small text-body-secondary">{{ displayValue(menu.icon) }}</code>
                                        </div>
                                    </td>
                                    <td>
                                        <code class="menu-route small text-body-secondary">
                                            {{ displayValue(menu.route || menu.route) }}
                                        </code>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill" :class="statusClass(menu)">
                                            {{ statusLabel(menu) }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-semibold">
                                        <div class="d-flex justify-content-end align-items-center gap-2">
                                            <span>{{ menu.order }}</span>

                                            <button class="btn btn-link btn-sm p-0"
                                                :class="{ invisible: menu.order === 1 }"
                                                :disabled="isLoading || menu.order === 1"
                                                @click="changeMenuOrder(menu.id)">
                                                <i class="bi bi-arrow-up"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                Ações
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <router-link class="dropdown-item"
                                                        :to="{ name: 'menus.edit', params: { id: menu.id } }">
                                                        Editar
                                                    </router-link>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" @click="deleteMenu(menu.id)">
                                                        Excluir
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="card-footer bg-white d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <Pagination class="flex-grow-1" :menus="filteredMenus" :rows="10"
                            @page-change="setPaginatedMenus" />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'Menus',
    data() {
        return {
            isLoading: false,
            filter: {
                title: '',
            },
            menus: [],
            paginatedMenus: [],
            collapsedMenuIds: []
        };
    },
    computed: {
        flattenedMenus() {
            return this.flattenMenuTree(this.menus);
        },
        filteredMenus() {
            const term = this.normalizeText(this.filter.title);

            if (!term) {
                return this.flattenedMenus;
            }

            return this.flattenedMenus.filter(menu => {
                return [
                    menu.id,
                    menu.title,
                    menu.icon,
                    menu.route,
                    menu.parentTitle
                ].some(value => this.normalizeText(value).includes(term));
            });
        }
    },
    mounted() {
        this.search();
    },
    methods: {
        async search() {
            this.isLoading = true;

            try {
                const response = await axios.get('/api/menus/list');
                this.menus = Array.isArray(response.data)
                    ? response.data
                    : response.data.menus || [];
            } catch (error) {
                this.menus = [];
                console.error('Error fetching data:', error);
            } finally {
                this.isLoading = false;
            }
        },
        clearFilters() {
            this.filter.title = '';
        },
        setPaginatedMenus(menus) {
            this.paginatedMenus = menus;
        },
        flattenMenuTree(items, depth = 0, parentTitle = '') {
            if (!Array.isArray(items)) {
                return [];
            }

            return items.reduce((menus, item) => {
                const children = Array.isArray(item.children) ? item.children : [];
                const hasChildren = children.length > 0;
                const isCollapsed = this.isMenuCollapsed(item.id);

                menus.push({
                    ...item,
                    depth,
                    parentTitle,
                    hasChildren,
                    isCollapsed
                });

                if (hasChildren && !isCollapsed) {
                    menus.push(...this.flattenMenuTree(children, depth + 1, item.title));
                }

                return menus;
            }, []);
        },
        toggleMenuChildren(menuId) {
            if (!menuId) {
                return;
            }

            if (this.isMenuCollapsed(menuId)) {
                this.collapsedMenuIds = this.collapsedMenuIds.filter(id => id !== menuId);
                return;
            }

            this.collapsedMenuIds.push(menuId);
        },
        isMenuCollapsed(menuId) {
            return this.collapsedMenuIds.includes(menuId);
        },
        normalizeText(value) {
            return String(value === null || value === undefined ? '' : value).toLowerCase().trim();
        },
        displayValue(value) {
            return value || '-';
        },
        displayNumber(value) {
            return value === null || value === undefined ? '-' : value;
        },
        statusClass(menu) {
            return menu.is_active ? 'text-bg-success' : 'text-bg-secondary';
        },
        statusLabel(menu) {
            return menu.is_active ? 'Ativo' : 'Inativo';
        },
        deleteMenu(menuId) {
            if (!menuId) {
                return;
            }
            
            this.confirm('Essa ação não poderá ser desfeita.', 'Deseja excluir este item?').then(() => {
                    this.isLoading = true;
                    axios.delete(`/api/menus/${menuId}`)
                        .then(response => {
                            alertSuccess('Registro Excluido com sucesso!');
                            this.search();
                        })
                        .catch(error => {
                            alertDanger(error);
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
            });
        },
        changeMenuOrder(menuId) {
            if (!menuId) {
                return;
            }

            this.isLoading = true;
            axios.post(`/api/menus/change-menu-order/${menuId}`)
                .then(response => {
                    this.search();
                })
                .catch(error => {
                    alertDanger(error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        }
    }
};
</script>

<style scoped>
.menus-page .card {
    border-radius: 0.5rem;
}

.menus-table {
    table-layout: fixed;
    min-width: 64rem;
}

.menus-table-id {
    width: 4.5rem;
}

.menus-table-menu {
    width: 30%;
}

.menus-table-icon {
    width: 17%;
}

.menus-table-destination {
    width: 23%;
}

.menus-table-status {
    width: 7rem;
}

.menus-table-order {
    width: 7rem;
}

.menus-table-actions {
    width: 7rem;
}

.menu-title-cell {
    min-width: 12rem;
}

.menu-title-content,
.menu-icon-content {
    max-width: 100%;
    min-width: 0;
}

.menu-toggle-button,
.menu-toggle-placeholder {
    width: 1.25rem;
    height: 1.25rem;
    flex: 0 0 1.25rem;
}

.menu-icon-preview {
    width: 2rem;
    height: 2rem;
    flex: 0 0 2rem;
}

.menu-title-text,
.menu-icon-value,
.menu-route {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.menu-title-text,
.menu-icon-value {
    min-width: 0;
}

.menu-route {
    display: block;
}

.table> :not(caption)>*>* {
    padding: 0.875rem 1rem;
}
</style>
