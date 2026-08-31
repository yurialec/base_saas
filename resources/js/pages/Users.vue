<template>
    <section class="page">

        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h4 mb-0">Usuários</h1>
            </div>
            <router-link
                class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 align-self-start align-self-lg-auto text-white"
                :to="{ name: 'users.create' }">
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
                        <div class="mb-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input id="menu-title-filter" v-model="filter.name" class="form-control" type="text"
                                    placeholder="Nome">
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button
                                class="btn btn-primary btn-sm d-inline-flex align-items-center justify-content-center gap-2 text-white"
                                @click="searchFilter">
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
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-10">
                <div class="card border-0 shadow-sm">
                    <Loading :loading="isLoading" />
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-nowrap" scope="col">#</th>
                                    <th scope="col">Nome</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Perfil</th>
                                    <th class="text-center" scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="paginatedUsers.length === 0 && !isLoading">
                                    <td class="text-center text-muted py-5" colspan="4">Nenhum resultado encontrado</td>
                                </tr>
                                <tr v-for="user in paginatedUsers" :key="user.id">
                                    <td v-text="user.id"></td>
                                    <td v-text="user.name"></td>
                                    <td v-text="user.email"></td>
                                    <td v-text="user.role.name"></td>
                                    <td class="text-end">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            Ações
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <router-link class="dropdown-item"
                                                    :to="{ name: 'users.edit', params: { id: user.id } }">
                                                    Editar
                                                </router-link>
                                            </li>
                                            <li>
                                                <button class="dropdown-item" @click="deleteUser(user.id)">
                                                    Excluir
                                                </button>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white">
                        <Pagination :response="users" :rows="10" @page-change="setPaginatedUsers" />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'Users',
    data() {
        return {
            isLoading: false,
            filter: {
                name: '',
            },
            users: [],
            paginatedUsers: []
        };
    },
    mounted() {
        this.search();
    },
    methods: {
        search() {
            this.isLoading = true;

            axios.get('/api/users/list')
                .then(response => {
                    this.users = response.data;
                })
                .catch(error => {
                    alertDanger(error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        deleteUser(id) {
            this.confirm('', 'Deseja excluir este registro?').then(() => {
                axios.delete(`/api/users/delete/${id}`)
                    .then(response => {
                        alertSuccess('Registro excluÃ­do com sucesso!');
                        this.search();
                    })
                    .catch(error => {
                        alertDanger(error);
                    });
            });
        },
        setPaginatedUsers(users) {
            this.paginatedUsers = users;
        },
        searchFilter() {
            this.paginatedUsers = this.users.filter(user => {
                return user.name.toLowerCase().includes(this.filter.name.toLowerCase());
            });
        },
        clearFilters() {
            this.filter.name = '';
            this.paginatedUsers = this.users;
        },
    }
};
</script>
