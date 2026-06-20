<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { usePermissions } from '@/composables/usePermissions';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps<{
    message: {
        id: number;
        name: string;
        email: string;
        subject: string;
        body: string;
        ip: string | null;
        received_at: string | null;
        read_at: string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Contact messages', href: '/admin/contact-messages' },
    { title: 'Detail', href: '#' },
];

const { can } = usePermissions();

const destroy = (id: number) => {
    if (! confirm('¿Eliminar este mensaje?')) return;
    router.delete(`/admin/contact-messages/${id}`);
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
    <Head :title="`Mensaje de ${message.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <Link href="/admin/contact-messages" class="btn btn-sm btn-outline-secondary">
                    ← Volver a la lista
                </Link>
                <button
                    v-if="can('delete-contact-messages')"
                    type="button"
                    class="btn btn-sm btn-outline-danger"
                    @click="destroy(message.id)"
                >
                    Borrar mensaje
                </button>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h1 class="h4 mb-1">{{ message.name }}</h1>
                            <a :href="`mailto:${message.email}`" class="text-decoration-none">{{ message.email }}</a>
                        </div>
                        <div class="text-end small text-body-secondary">
                            <div><strong>Asunto:</strong> {{ subjectLabel(message.subject) }}</div>
                            <div>Recibido: {{ message.received_at }}</div>
                            <div v-if="message.read_at">Leído: {{ message.read_at }}</div>
                            <div v-if="message.ip">IP: {{ message.ip }}</div>
                        </div>
                    </div>
                    <hr />
                    <p class="mb-0" style="white-space: pre-wrap;">{{ message.body }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
