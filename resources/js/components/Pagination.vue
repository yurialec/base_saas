<template>
    <div class="pagination-wrapper d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <span class="small text-muted">{{ summary }}</span>

        <nav v-if="totalPages > 1" aria-label="Paginacao">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item" :class="{ disabled: isFirstPage }">
                    <button class="page-link d-inline-flex align-items-center justify-content-center" type="button"
                        :disabled="isFirstPage" aria-label="Pagina anterior" @click="goToPage(currentPage - 1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                </li>

                <li v-for="item in visiblePages" :key="item.key" class="page-item"
                    :class="{ active: item.number === currentPage, disabled: item.type === 'ellipsis' }">
                    <button v-if="item.type === 'page'" class="page-link" type="button"
                        @click="goToPage(item.number)">
                        {{ item.number }}
                    </button>
                    <span v-else class="page-link">...</span>
                </li>

                <li class="page-item" :class="{ disabled: isLastPage }">
                    <button class="page-link d-inline-flex align-items-center justify-content-center" type="button"
                        :disabled="isLastPage" aria-label="Proxima pagina" @click="goToPage(currentPage + 1)">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    menus: {
        type: [Array, Object],
        default: () => []
    },
    response: {
        type: [Array, Object],
        default: null
    },
    rows: {
        type: [Number, String],
        default: null
    },
    perPage: {
        type: [Number, String],
        default: 10
    }
});

const emit = defineEmits(['page-change']);

const currentPage = ref(1);

const source = computed(() => {
    return props.response || props.menus;
});

const items = computed(() => {
    return normalizeItems(source.value);
});

const pageSize = computed(() => {
    const value = Number(props.rows || props.perPage || 10);

    return Number.isFinite(value) && value > 0 ? value : 10;
});

const totalItems = computed(() => items.value.length);

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(totalItems.value / pageSize.value));
});

const isFirstPage = computed(() => currentPage.value === 1);
const isLastPage = computed(() => currentPage.value === totalPages.value);

const firstItem = computed(() => {
    return totalItems.value === 0 ? 0 : ((currentPage.value - 1) * pageSize.value) + 1;
});

const lastItem = computed(() => {
    return Math.min(currentPage.value * pageSize.value, totalItems.value);
});

const paginatedItems = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;

    return items.value.slice(start, start + pageSize.value);
});

const summary = computed(() => {
    if (totalItems.value === 0) {
        return 'Nenhum registro para exibir';
    }

    const suffix = totalItems.value === 1 ? 'registro' : 'registros';

    return `Exibindo ${firstItem.value} a ${lastItem.value} de ${totalItems.value} ${suffix}`;
});

const visiblePages = computed(() => {
    if (totalPages.value <= 7) {
        return Array.from({ length: totalPages.value }, (_, index) => createPage(index + 1));
    }

    const pages = [createPage(1)];
    const start = Math.max(2, currentPage.value - 1);
    const end = Math.min(totalPages.value - 1, currentPage.value + 1);

    if (start > 2) {
        pages.push(createEllipsis('start'));
    }

    for (let page = start; page <= end; page += 1) {
        pages.push(createPage(page));
    }

    if (end < totalPages.value - 1) {
        pages.push(createEllipsis('end'));
    }

    pages.push(createPage(totalPages.value));

    return pages;
});

watch([items, pageSize], () => {
    currentPage.value = 1;
});

watch(totalPages, total => {
    if (currentPage.value > total) {
        currentPage.value = total;
    }
});

watch(paginatedItems, value => {
    emit('page-change', value);
}, { immediate: true });

function goToPage(page) {
    if (page < 1 || page > totalPages.value || page === currentPage.value) {
        return;
    }

    currentPage.value = page;
}

function normalizeItems(value) {
    if (Array.isArray(value)) {
        return value;
    }

    if (!value || typeof value !== 'object') {
        return [];
    }

    if (Array.isArray(value.data)) {
        return value.data;
    }

    if (value.data && Array.isArray(value.data.data)) {
        return value.data.data;
    }

    if (Array.isArray(value.menus)) {
        return value.menus;
    }

    if (value.data && Array.isArray(value.data.menus)) {
        return value.data.menus;
    }

    return [];
}

function createPage(number) {
    return {
        key: `page-${number}`,
        type: 'page',
        number
    };
}

function createEllipsis(position) {
    return {
        key: `ellipsis-${position}`,
        type: 'ellipsis',
        number: null
    };
}
</script>

<style scoped>
.pagination-wrapper {
    min-height: 2rem;
}

.page-link {
    min-width: 2rem;
}

.page-item.active .page-link {
    pointer-events: none;
}
</style>
