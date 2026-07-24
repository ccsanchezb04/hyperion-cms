<script setup lang="ts">
import { useSwal } from '@/composables/useSwal';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    parent_id: number | null;
    content_count: number;
    children: Category[];
}

defineProps<{
    categories: Category[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Categorías', href: '/admin/categories' },
];

const expanded = ref<Set<number>>(new Set());
const searchQuery = ref('');

const toggleExpand = (id: number) => {
    if (expanded.value.has(id)) expanded.value.delete(id);
    else expanded.value.add(id);
};

const isExpanded = (id: number) => expanded.value.has(id);

// ── SweetAlert2 ────────────────────────────────────────────────────────
const { confirm, success, error } = useSwal();

const deleteCategory = async (cat: Category) => {
    if (cat.children?.length) {
        await error(
            'No se puede eliminar',
            `"${cat.name}" tiene ${cat.children.length} subcategoría(s). Elimínalas antes de continuar.`,
        );
        return;
    }

    if (cat.content_count > 0) {
        await error(
            'No se puede eliminar',
            `"${cat.name}" tiene ${cat.content_count} contenido(s) asociado(s). Elimina o reasigna los contenidos primero.`,
        );
        return;
    }

    const result = await confirm({
        title: '¿Eliminar categoría?',
        html: `La categoría <strong>${cat.name}</strong> será eliminada permanentemente.`,
        icon: 'warning',
        confirmButtonText: 'Sí, eliminar',
    });

    if (result.isConfirmed) {
        router.delete(`/admin/categories/${cat.id}`, { preserveScroll: true });
    }
};

// ── Flash messages desde el servidor ───────────────────────────────────
const page = usePage<{ flash: { success?: string; error?: string } }>();

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) success('¡Listo!', flash.success);
        if (flash?.error) error('No se pudo completar', flash.error);
    },
    { deep: true },
);

// ── Filtro de árbol ─────────────────────────────────────────────────────
const filterTree = (list: Category[]): Category[] => {
    if (!searchQuery.value) return list;
    const q = searchQuery.value.toLowerCase();
    return list.reduce<Category[]>((acc, cat) => {
        const matches = cat.name.toLowerCase().includes(q) || cat.slug.toLowerCase().includes(q);
        const children = filterTree(cat.children ?? []);
        if (matches || children.length) acc.push({ ...cat, children });
        return acc;
    }, []);
};
</script>

<template>
    <Head title="Categorías" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h4 fw-bold mb-0">Categorías</h1>
                        <Link href="/admin/categories/create" class="btn btn-primary">Crear categoría</Link>
                    </div>

                    <div class="mb-3">
                        <input v-model="searchQuery" type="text" placeholder="Buscar categorías..." class="form-control" />
                    </div>

                    <div v-if="filterTree(categories).length > 0" class="d-flex flex-column gap-2">
                        <div v-for="category in filterTree(categories)" :key="category.id">
                            <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                <div class="d-flex align-items-center gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-link text-body p-0"
                                        :class="{ invisible: !category.children?.length }"
                                        @click="toggleExpand(category.id)"
                                    >
                                        <i class="bi bi-chevron-right" :class="{ 'rotate-90': isExpanded(category.id) }"></i>
                                    </button>
                                    <div>
                                        <p class="fw-medium mb-0">{{ category.name }}</p>
                                        <p class="small text-body-secondary mb-0">
                                            {{ category.slug }}
                                            <span v-if="category.content_count > 0" class="ms-2 badge bg-secondary fw-normal">
                                                {{ category.content_count }} contenido(s)
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 small">
                                    <Link :href="`/admin/categories/${category.id}/edit`" class="link-primary text-decoration-none">Editar</Link>
                                    <button
                                        type="button"
                                        class="btn btn-link btn-sm text-danger p-0"
                                        @click="deleteCategory(category)"
                                    >Eliminar</button>
                                </div>
                            </div>

                            <div v-if="category.children?.length && isExpanded(category.id)" class="ms-4 mt-2 d-flex flex-column gap-2">
                                <div
                                    v-for="child in category.children"
                                    :key="child.id"
                                    class="d-flex justify-content-between align-items-center p-3 border rounded bg-body-tertiary"
                                >
                                    <div>
                                        <p class="fw-medium mb-0">{{ child.name }}</p>
                                        <p class="small text-body-secondary mb-0">
                                            {{ child.slug }}
                                            <span v-if="child.content_count > 0" class="ms-2 badge bg-secondary fw-normal">
                                                {{ child.content_count }} contenido(s)
                                            </span>
                                        </p>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 small">
                                        <Link :href="`/admin/categories/${child.id}/edit`" class="link-primary text-decoration-none">Editar</Link>
                                        <button
                                            type="button"
                                            class="btn btn-link btn-sm text-danger p-0"
                                            @click="deleteCategory(child)"
                                        >Eliminar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-5">
                        <i class="bi bi-tags display-1 text-body-secondary"></i>
                        <p class="mt-2 text-body-secondary mb-0">No se encontraron categorías.</p>
                        <p class="small text-body-secondary">Crea tu primera categoría para comenzar.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.bi.rotate-90 { transform: rotate(90deg); transition: transform 0.15s ease; }
.bi { transition: transform 0.15s ease; }
</style>
