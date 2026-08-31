<template>
    <div>
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h4 mb-0">Cadastrar Novo Perfil</h1>
            </div>
            <router-link
                class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 align-self-start align-self-lg-auto"
                to="/roles">
                <i class="bi bi-arrow-left"></i>
                <span>Voltar</span>
            </router-link>
        </div>
        <form class="row justify-content-center" @submit.prevent="store">
            <div class="col-12 col-lg-10 col-xl-8">
                <Loading :loading="isLoading" />
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="roleName" class="form-label">Nome</label>
                                <input id="roleName" v-model="role.name" class="form-control" type="text" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="roleDescription" class="form-label">Descrição</label>
                                <input id="roleDescription" v-model="role.description" class="form-control" type="text"
                                    required>
                            </div>
                            <div class="col-6 col-md-6">
                                <label for="roleParent" class="form-label">Perfil Pai</label>
                                <select id="roleParent" v-model="role.parent_id" name="roleParent" class="form-select">
                                    <option value="">Nenhum (perfil principal)</option>
                                    <option v-for="r in roles" :key="r.id" :value="r.id">
                                        {{ r.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="rolePermissions" class="form-label">Permissões</label>
                                <Multiselect
                                    id="rolePermissions"
                                    v-model="role.permissions"
                                    :options="permissions"
                                    :multiple="true"
                                    :close-on-select="false"
                                    :clear-on-select="false"
                                    :preserve-search="true"
                                    :hide-selected="true"
                                    track-by="id"
                                    label="name"
                                    placeholder="Selecione uma ou mais permissões"
                                    select-label="Selecionar"
                                    selected-label="Selecionada"
                                    deselect-label="Remover"
                                    tag-placeholder="Adicionar"
                                    :show-no-options="false"
                                    :disabled="isLoading"
                                >
                                    <template #noResult>
                                        Nenhuma permissão encontrada.
                                    </template>
                                </Multiselect>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-sm btn-primary text-white" :disabled="isLoading">
                                <i class="bi bi-check-lg me-1"></i>
                                Cadastrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
<script>
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';

export default {
    name: 'CreateRole',
    components: {
        Multiselect,
    },
    data() {
        return {
            isLoading: false,
            role: {
                name: '',
                description: '',
                parent_id: '',
                permissions: [],
            },
            roles: [],
            permissions: [],
        };
    },
    mounted() {
        this.search();
        this.searchPermissions();
    },
    methods: {
        search() {
            axios.get(`/api/roles/dropdown-list`)
                .then(response => {
                    this.roles = response.data;
                })
                .catch(error => {
                    alertDanger(error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        searchPermissions() {
            axios.get(`/api/permissions/list`)
                .then(response => {
                    this.permissions = response.data;
                })
                .catch(error => {
                    alertDanger(error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        store() {
            const payload = {
                ...this.role,
                permissions: this.role.permissions.map(permission => permission.id),
            };

            axios.post(`/api/roles/store`, payload)
                .then(response => {
                    alertSuccess('Perfil cadastrado com sucesso!');
                })
                .catch(error => {
                    alertDanger(error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
    }
}
</script>

<style scoped>
.multiselect {
    min-height: 38px;
    color: var(--bs-body-color);
    font-size: 1rem;
}

.multiselect :deep(.multiselect__tags) {
    min-height: 38px;
    padding: 7px 40px 0 12px;
    border-color: var(--bs-border-color);
    border-radius: var(--bs-border-radius);
}

.multiselect :deep(.multiselect__placeholder),
.multiselect :deep(.multiselect__single) {
    margin: 0;
    padding: 0;
    line-height: 24px;
}

.multiselect :deep(.multiselect__input),
.multiselect :deep(.multiselect__single) {
    font-size: 1rem;
}

.multiselect :deep(.multiselect__tag) {
    margin: 0 5px 5px 0;
    padding: 5px 26px 5px 10px;
    border-radius: 4px;
    background: var(--bs-primary);
}

.multiselect :deep(.multiselect__option--highlight) {
    background: var(--bs-primary);
}

.multiselect :deep(.multiselect__option--highlight::after) {
    background: transparent;
}

.multiselect :deep(.multiselect__content-wrapper) {
    border-color: var(--bs-border-color);
}
</style>