<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { BModal } from 'bootstrap-vue-next';
import { CheckCircle2, GripVertical } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import draggable from 'vuedraggable';

interface MenuItem {
    id: number;
    title: string;
    type: 'url' | 'content' | 'category';
    link: string | null;
    ref_id: number | null;
    parent_id: number | null;
    order: number;
}

interface Menu {
    id: number;
    name: string;
    slug: string;
    is_site_main: boolean;
    items: MenuItem[];
}

interface ContentOption { id: number; title: string; slug: string }
interface CategoryOption { id: number; name: string; slug: string }

const props = defineProps<{
    menus: Menu[];
    contents: ContentOption[];
    categories: CategoryOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Menus', href: '/admin/menus' },
];

const selectedMenu = ref<Menu | null>(
    props.menus.find((m) => m.is_site_main) ?? props.menus[0] ?? null,
);

// Tree shaping ---------------------------------------------------------------

interface TreeNode extends MenuItem {
    children: TreeNode[];
}

const tree = computed<TreeNode[]>(() => {
    if (!selectedMenu.value) return [];
    const items = selectedMenu.value.items.slice().sort((a, b) => a.order - b.order);
    const map = new Map<number, TreeNode>();
    items.forEach((it) => map.set(it.id, { ...it, children: [] }));

    const roots: TreeNode[] = [];
    map.forEach((node) => {
        if (node.parent_id && map.has(node.parent_id)) {
            map.get(node.parent_id)!.children.push(node);
        } else {
            roots.push(node);
        }
    });
    return roots;
});

const flatItemsForParentPicker = computed(() => {
    if (!selectedMenu.value) return [];
    return selectedMenu.value.items.slice().sort((a, b) => a.order - b.order);
});

// Resolve display label for type=content/category ---------------------------

const displayLink = (item: MenuItem): string => {
    if (item.type === 'url') return item.link ?? '';
    if (item.type === 'content' && item.ref_id) {
        const c = props.contents.find((x) => x.id === item.ref_id);
        return c ? `/${c.slug}` : '(content faltante)';
    }
    if (item.type === 'category' && item.ref_id) {
        const c = props.categories.find((x) => x.id === item.ref_id);
        return c ? `/category/${c.slug}` : '(categoría faltante)';
    }
    return '';
};

const displayTarget = (item: MenuItem): string => {
    if (item.type === 'content' && item.ref_id) {
        return props.contents.find((x) => x.id === item.ref_id)?.title ?? '';
    }
    if (item.type === 'category' && item.ref_id) {
        return props.categories.find((x) => x.id === item.ref_id)?.name ?? '';
    }
    return '';
};

// Modals --------------------------------------------------------------------

const showCreateMenu = ref(false);
const showItemModal = ref(false);
const editingItemId = ref<number | null>(null);

const menuForm = useForm({ name: '', slug: '' });

const itemForm = useForm<{
    title: string;
    type: 'url' | 'content' | 'category';
    link: string;
    ref_id: number | null;
    parent_id: number | null;
}>({
    title: '',
    type: 'url',
    link: '',
    ref_id: null,
    parent_id: null,
});

const openCreateItem = () => {
    editingItemId.value = null;
    itemForm.reset();
    itemForm.clearErrors();
    showItemModal.value = true;
};

const openEditItem = (item: MenuItem) => {
    editingItemId.value = item.id;
    itemForm.title = item.title;
    itemForm.type = item.type;
    itemForm.link = item.link ?? '';
    itemForm.ref_id = item.ref_id;
    itemForm.parent_id = item.parent_id;
    itemForm.clearErrors();
    showItemModal.value = true;
};

const submitItem = () => {
    if (!selectedMenu.value) return;

    if (editingItemId.value === null) {
        itemForm.post(`/admin/menus/${selectedMenu.value.id}/items`, {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                showItemModal.value = false;
                itemForm.reset();
            },
        });
    } else {
        itemForm.put(`/admin/menus/${selectedMenu.value.id}/items/${editingItemId.value}`, {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                showItemModal.value = false;
                editingItemId.value = null;
                itemForm.reset();
            },
        });
    }
};

const deleteItem = (id: number) => {
    if (!confirm('¿Eliminar este ítem? Sus sub-ítems se moverán al nivel del padre.')) return;
    router.delete(`/admin/menus/items/${id}`, { preserveScroll: true });
};

// Drag & drop ---------------------------------------------------------------

const onDragEnd = () => {
    if (!selectedMenu.value) return;

    const flat: { id: number; order: number; parent_id: number | null }[] = [];
    const walk = (nodes: TreeNode[], parentId: number | null) => {
        nodes.forEach((node, idx) => {
            flat.push({ id: node.id, order: idx + 1, parent_id: parentId });
            if (node.children.length) walk(node.children, node.id);
        });
    };
    walk(tree.value, null);

    router.post(
        `/admin/menus/${selectedMenu.value.id}/reorder`,
        { items: flat },
        { preserveScroll: true, preserveState: false },
    );
};

// Menu CRUD -----------------------------------------------------------------

const createMenu = () => {
    menuForm.post('/admin/menus', {
        preserveScroll: true,
        onSuccess: () => {
            showCreateMenu.value = false;
            menuForm.reset();
        },
    });
};

const deleteMenu = (menuId: number) => {
    if (!confirm('Borrar este menú y todos sus ítems?')) return;
    router.delete(`/admin/menus/${menuId}`, {
        preserveScroll: true,
        onSuccess: () => {
            if (selectedMenu.value?.id === menuId) selectedMenu.value = null;
        },
    });
};

// Helpers for the type picker UI -------------------------------------------

const onTypeChange = () => {
    // Limpiar campos del otro tipo para no enviar valores inconsistentes
    if (itemForm.type === 'url') {
        itemForm.ref_id = null;
    } else {
        itemForm.link = '';
    }
};
</script>

<template>
    <Head title="Menus" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h4 fw-bold mb-1">Menus</h1>
                            <p class="text-body-secondary small mb-0">
                                Arrastra para reordenar. El menú con badge
                                <span class="badge bg-success">Sitio público</span> alimenta la navegación de <code>/</code>.
                            </p>
                        </div>
                        <button type="button" class="btn btn-primary" @click="showCreateMenu = true">Crear menú</button>
                    </div>

                    <div v-if="menus.length === 0" class="text-center py-5 text-body-secondary">
                        <p class="mb-0">Sin menús todavía.</p>
                    </div>

                    <div v-else class="row g-4">
                        <!-- Sidebar: lista de menús -->
                        <div class="col-12 col-lg-4">
                            <h2 class="h6 fw-medium mb-3">Selecciona un menú</h2>
                            <div class="list-group">
                                <button
                                    v-for="menu in menus"
                                    :key="menu.id"
                                    type="button"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                    :class="{ active: selectedMenu?.id === menu.id }"
                                    @click="selectedMenu = menu"
                                >
                                    <span class="d-flex flex-column align-items-start">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="fw-medium">{{ menu.name }}</span>
                                            <span
                                                v-if="menu.is_site_main"
                                                class="badge bg-success d-inline-flex align-items-center gap-1"
                                                style="font-weight: normal;"
                                            >
                                                <CheckCircle2 :size="12" /> Sitio público
                                            </span>
                                        </span>
                                        <small :class="selectedMenu?.id === menu.id ? 'text-white-50' : 'text-body-secondary'">
                                            {{ menu.slug }} · {{ menu.items.length }} ítems
                                        </small>
                                    </span>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-link p-0"
                                        :class="selectedMenu?.id === menu.id ? 'text-white' : 'text-danger'"
                                        @click.stop="deleteMenu(menu.id)"
                                        aria-label="Borrar menú"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </button>
                            </div>
                        </div>

                        <!-- Items tree -->
                        <div class="col-12 col-lg-8">
                            <div v-if="selectedMenu">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h2 class="h6 fw-medium mb-0">Ítems de "{{ selectedMenu.name }}"</h2>
                                    <button type="button" class="btn btn-primary btn-sm" @click="openCreateItem">Añadir ítem</button>
                                </div>

                                <div v-if="tree.length === 0" class="text-center py-4 text-body-secondary border rounded">
                                    <p class="mb-2">No hay ítems en este menú.</p>
                                    <button type="button" class="btn btn-link" @click="openCreateItem">Añadir el primer ítem</button>
                                </div>

                                <draggable
                                    v-else
                                    v-model="tree"
                                    group="menu-items"
                                    item-key="id"
                                    handle=".jf-drag-handle"
                                    class="d-flex flex-column gap-2"
                                    @end="onDragEnd"
                                >
                                    <template #item="{ element: node }">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 p-3 border rounded bg-body-tertiary">
                                                <span class="jf-drag-handle text-body-secondary" style="cursor: grab;">
                                                    <GripVertical :size="16" />
                                                </span>
                                                <div class="flex-grow-1">
                                                    <p class="fw-medium mb-0">
                                                        {{ node.title }}
                                                        <span v-if="displayTarget(node)" class="text-body-secondary small fw-normal">
                                                            → {{ displayTarget(node) }}
                                                        </span>
                                                    </p>
                                                    <p class="small text-body-secondary mb-0">{{ displayLink(node) }}</p>
                                                </div>
                                                <span class="badge bg-secondary">{{ node.type }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="openEditItem(node)">Editar</button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" @click="deleteItem(node.id)">Borrar</button>
                                            </div>

                                            <!-- Children -->
                                            <draggable
                                                v-if="node.children.length"
                                                v-model="node.children"
                                                group="menu-items"
                                                item-key="id"
                                                handle=".jf-drag-handle"
                                                class="d-flex flex-column gap-2 ms-4 mt-2"
                                                @end="onDragEnd"
                                            >
                                                <template #item="{ element: child }">
                                                    <div class="d-flex align-items-center gap-2 p-2 border rounded">
                                                        <span class="jf-drag-handle text-body-secondary" style="cursor: grab;">
                                                            <GripVertical :size="14" />
                                                        </span>
                                                        <div class="flex-grow-1">
                                                            <p class="fw-medium mb-0 small">
                                                                {{ child.title }}
                                                                <span v-if="displayTarget(child)" class="text-body-secondary fw-normal">
                                                                    → {{ displayTarget(child) }}
                                                                </span>
                                                            </p>
                                                            <p class="small text-body-secondary mb-0">{{ displayLink(child) }}</p>
                                                        </div>
                                                        <span class="badge bg-light text-dark small">{{ child.type }}</span>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="openEditItem(child)">Editar</button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" @click="deleteItem(child.id)">Borrar</button>
                                                    </div>
                                                </template>
                                            </draggable>
                                        </div>
                                    </template>
                                </draggable>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: crear menú -->
        <BModal v-model="showCreateMenu" title="Crear menú" hide-footer>
            <form @submit.prevent="createMenu" class="d-flex flex-column gap-3">
                <div>
                    <label class="form-label">Nombre *</label>
                    <input v-model="menuForm.name" type="text" class="form-control" :class="{ 'is-invalid': menuForm.errors.name }" required />
                    <div v-if="menuForm.errors.name" class="invalid-feedback d-block">{{ menuForm.errors.name }}</div>
                </div>
                <div>
                    <label class="form-label">Slug *</label>
                    <input v-model="menuForm.slug" type="text" class="form-control" :class="{ 'is-invalid': menuForm.errors.slug }" required />
                    <div v-if="menuForm.errors.slug" class="invalid-feedback d-block">{{ menuForm.errors.slug }}</div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" @click="showCreateMenu = false">Cancelar</button>
                    <button type="submit" class="btn btn-primary" :disabled="menuForm.processing">Crear</button>
                </div>
            </form>
        </BModal>

        <!-- Modal: crear/editar ítem -->
        <BModal v-model="showItemModal" :title="editingItemId === null ? 'Añadir ítem' : 'Editar ítem'" hide-footer>
            <form @submit.prevent="submitItem" class="d-flex flex-column gap-3">
                <div>
                    <label class="form-label">Etiqueta *</label>
                    <input v-model="itemForm.title" type="text" class="form-control" :class="{ 'is-invalid': itemForm.errors.title }" required />
                    <div v-if="itemForm.errors.title" class="invalid-feedback d-block">{{ itemForm.errors.title }}</div>
                </div>

                <div>
                    <label class="form-label">Tipo</label>
                    <select v-model="itemForm.type" class="form-select" @change="onTypeChange">
                        <option value="url">URL libre</option>
                        <option value="content">Contenido</option>
                        <option value="category">Categoría</option>
                    </select>
                </div>

                <div v-if="itemForm.type === 'url'">
                    <label class="form-label">URL *</label>
                    <input
                        v-model="itemForm.link"
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': itemForm.errors.link }"
                        placeholder="/, https://..., #anchor"
                    />
                    <div v-if="itemForm.errors.link" class="invalid-feedback d-block">{{ itemForm.errors.link }}</div>
                </div>

                <div v-if="itemForm.type === 'content'">
                    <label class="form-label">Contenido *</label>
                    <select v-model="itemForm.ref_id" class="form-select" :class="{ 'is-invalid': itemForm.errors.ref_id }">
                        <option :value="null" disabled>Selecciona un contenido</option>
                        <option v-for="c in contents" :key="c.id" :value="c.id">{{ c.title }} ({{ c.slug }})</option>
                    </select>
                    <div v-if="itemForm.errors.ref_id" class="invalid-feedback d-block">{{ itemForm.errors.ref_id }}</div>
                </div>

                <div v-if="itemForm.type === 'category'">
                    <label class="form-label">Categoría *</label>
                    <select v-model="itemForm.ref_id" class="form-select" :class="{ 'is-invalid': itemForm.errors.ref_id }">
                        <option :value="null" disabled>Selecciona una categoría</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }} ({{ c.slug }})</option>
                    </select>
                    <div v-if="itemForm.errors.ref_id" class="invalid-feedback d-block">{{ itemForm.errors.ref_id }}</div>
                </div>

                <div>
                    <label class="form-label">Ítem padre (opcional)</label>
                    <select v-model="itemForm.parent_id" class="form-select" :class="{ 'is-invalid': itemForm.errors.parent_id }">
                        <option :value="null">— Sin padre (raíz) —</option>
                        <option
                            v-for="opt in flatItemsForParentPicker"
                            :key="opt.id"
                            :value="opt.id"
                            :disabled="opt.id === editingItemId"
                        >
                            {{ opt.title }}
                        </option>
                    </select>
                    <div v-if="itemForm.errors.parent_id" class="invalid-feedback d-block">{{ itemForm.errors.parent_id }}</div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" @click="showItemModal = false">Cancelar</button>
                    <button type="submit" class="btn btn-primary" :disabled="itemForm.processing">
                        {{ editingItemId === null ? 'Añadir' : 'Guardar cambios' }}
                    </button>
                </div>
            </form>
        </BModal>
    </AppLayout>
</template>
