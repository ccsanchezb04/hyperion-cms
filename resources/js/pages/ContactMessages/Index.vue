<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { usePermissions } from '@/composables/usePermissions';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

interface MessageRow {
    id: number;
    name: string;
    email: string;
    subject: string;
    is_read: boolean;
    received_at: string | null;
}

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{
    messages: Paginated<MessageRow>;
    filter: 'all' | 'unread' | 'read';
    unreadCount: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Contact messages', href: '/admin/contact-messages' },
];

const { can } = usePermissions();

const setFilter = (value: 'all' | 'unread' | 'read') => {
    router.get('/admin/contact-messages', { filter: value }, { preserveScroll: true, preserveState: true });
};

const destroy = (id: number) => {
    if (! confirm('¿Eliminar este mensaje? Esta acción no se puede deshacer fácilmente.')) return;
    router.delete(`/admin/contact-messages/${id}`, { preserveScroll: true });
};

const subjectLabel = (slug: string): string => {
    const map: Record<string, string> = {
        cotizacion: 'Cotización',
        soporte: 'Soporte',
        otros: 'Otros',
    };
    return map[slug] ?? slug;
};
</script>

<template>
    <Head title="Contact messages" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h4 fw-bold mb-1">Contact messages</h1>
                            <p class="text-body-secondary small mb-0">
                                {{ unreadCount }} sin leer
                            </p>
                        </div>
                        <div class="btn-group" role="group">
                            <button
                                type="button"
                                class="btn btn-sm"
                                :class="filter === 'all' ? 'btn-primary' : 'btn-outline-secondary'"
                                @click="setFilter('all')"
                            >
                                Todos
                            </button>
                            <button
                                type="button"
                                class="btn btn-sm"
                                :class="filter === 'unread' ? 'btn-primary' : 'btn-outline-secondary'"
                                @click="setFilter('unread')"
                            >
                                Sin leer
                            </button>
                            <button
                                type="button"
                                class="btn btn-sm"
                                :class="filter === 'read' ? 'btn-primary' : 'btn-outline-secondary'"
                                @click="setFilter('read')"
                            >
                                Leídos
                            </button>
                        </div>
                    </div>

                    <div v-if="messages.data.length === 0" class="text-center py-5 text-body-secondary">
                        <i class="bi bi-inbox display-3"></i>
                        <p class="mt-2 mb-0">No hay mensajes en esta vista.</p>
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Asunto</th>
                                    <th>Recibido</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="m in messages.data" :key="m.id" :class="{ 'fw-bold': !m.is_read }">
                                    <td>
                                        <span
                                            class="badge rounded-circle"
                                            :class="m.is_read ? 'bg-secondary' : 'bg-primary'"
                                            style="width: 0.6rem; height: 0.6rem; padding: 0;"
                                        ></span>
                                    </td>
                                    <td>{{ m.name }}</td>
                                    <td>
                                        <a :href="`mailto:${m.email}`" class="text-decoration-none">{{ m.email }}</a>
                                    </td>
                                    <td>{{ subjectLabel(m.subject) }}</td>
                                    <td class="text-body-secondary small">{{ m.received_at }}</td>
                                    <td class="text-end">
                                        <Link
                                            :href="`/admin/contact-messages/${m.id}`"
                                            class="btn btn-sm btn-outline-primary me-2"
                                        >
                                            Ver
                                        </Link>
                                        <button
                                            v-if="can('delete-contact-messages')"
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            @click="destroy(m.id)"
                                        >
                                            Borrar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <nav v-if="messages.last_page > 1" class="mt-3">
                        <ul class="pagination justify-content-center mb-0">
                            <li
                                v-for="(link, idx) in messages.links"
                                :key="idx"
                                class="page-item"
                                :class="{ active: link.active, disabled: !link.url }"
                            >
                                <Link v-if="link.url" :href="link.url" class="page-link" v-html="link.label" />
                                <span v-else class="page-link" v-html="link.label" />
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
