<template>
    <section class="role-form-page">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <h1 class="h4 mb-0">Editar Perfil</h1>
            <router-link
                class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 align-self-start align-self-lg-auto"
                to="/roles">
                <i class="bi bi-arrow-left"></i>
                <span>Voltar</span>
            </router-link>
        </div>
        <form class="row justify-content-center" @submit.prevent="update">
            <Loading :loading="isLoading" />
            <div class="col-12 col-lg-10 col-xl-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="roleName" class="form-label">Nome</label>
                                <input id="roleName" v-model.trim="role.name" class="form-control" type="text"
                                    required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="roleDescription" class="form-label">Descrição</label>
                                <input id="roleDescription" v-model.trim="role.description" class="form-control"
                                    type="text">
                            </div>

                            <div class="col-12 col-md-8">
                                <label for="roleParent" class="form-label">Perfil Pai</label>
                                <select id="roleParent" v-model="role.parent_id" name="roleParent"
                                    class="form-select">
                                    <option value="">Nenhum (perfil principal)</option>
                                    <option v-for="parentRole in availableParentRoles" :key="parentRole.id"
                                        :value="parentRole.id">
                                        {{ parentRole.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input id="roleIsActive" v-model="role.is_active" class="form-check-input"
                                        type="checkbox" role="switch">
                                    <label class="form-check-label" for="roleIsActive">Perfil ativo</label>
                                </div>
                            </div>

                            <div class="col-12">
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
                                    :show-no-options="false"
                                    :disabled="isLoading"
                                >
                                    <template #noResult>
                                        Nenhuma permissão encontrada.
                                    </template>
                                </Multiselect>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                            <router-link class="btn btn-outline-secondary btn-sm" to="/roles">
                                Cancelar
                            </router-link>
                            <button class="btn btn-primary btn-sm text-white" type="submit" :disabled="isLoading">
                                <i class="bi bi-check-lg me-1"></i>
                                Salvar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
</template>

<script>
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';

export default {
    name: 'EditRole',
    components: {
        Multiselect,
    },
    props: {
        id: {
            type: [String, Number],
            required: true
        }
    },
    data() {
        return {
            isLoading: false,
            role: {
                name: '',
                description: '',
                parent_id: '',
                is_active: true,
                permissions: []
            },
            roles: [],
            permissions: []
        };
    },
    computed: {
        availableParentRoles() {
            return this.roles.filter(role => Number(role.id) !== Number(this.id));
        }
    },
    mounted() {
        this.loadData();
    },
    methods: {
        async loadData() {
            this.isLoading = true;

            try {
                const [roleResponse, rolesResponse, permissionsResponse] = await Promise.all([
                    axios.get(`/api/roles/find/${this.id}`),
                    axios.get('/api/roles/dropdown-list'),
                    axios.get('/api/permissions/list')
                ]);

                const role = Array.isArray(roleResponse.data)
                    ? roleResponse.data[0]
                    : roleResponse.data;

                this.role = this.normalizeRole(role);
                this.roles = Array.isArray(rolesResponse.data) ? rolesResponse.data : [];
                this.permissions = Array.isArray(permissionsResponse.data) ? permissionsResponse.data : [];
            } catch (error) {
                alertDanger(error);
            } finally {
                this.isLoading = false;
            }
        },
        async update() {
            this.isLoading = true;

            try {
                await axios.put(`/api/roles/update/${this.id}`, this.createRequestPayload());
                alertSuccess('Perfil alterado com sucesso!');
                this.$router.push({ name: 'roles' });
            } catch (error) {
                alertDanger(error);
            } finally {
                this.isLoading = false;
            }
        },
        normalizeRole(role) {
            const normalizedRole = role || {};

            return {
                name: normalizedRole.name || '',
                description: normalizedRole.description || '',
                parent_id: normalizedRole.parent_id || '',
                is_active: Boolean(normalizedRole.is_active),
                permissions: Array.isArray(normalizedRole.permissions)
                    ? normalizedRole.permissions
                    : []
            };
        },
        createRequestPayload() {
            return {
                ...this.role,
                parent_id: this.role.parent_id || null,
                permissions: this.role.permissions.map(permission => permission.id)
            };
        }
    }
};
</script>

<style scoped>
.role-form-page .card {
    border-radius: 0.5rem;
}

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
