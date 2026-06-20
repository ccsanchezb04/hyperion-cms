<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { FileQuestion } from 'lucide-vue-next';

defineProps<{
    status?: number;
    message?: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Not found', href: '#' },
];
</script>

<template>
    <Head title="Página no encontrada" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-5">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <FileQuestion :size="48" class="text-body-secondary mb-3" />
                    <p class="text-body-secondary small fw-semibold mb-1" style="letter-spacing: 0.1em;">
                        ERROR {{ status ?? 404 }}
                    </p>
                    <h1 class="h4 fw-bold mb-2">Página no encontrada</h1>
                    <p class="text-body-secondary mb-4">
                        {{ message ?? 'La dirección que ingresaste no existe dentro del panel.' }}
                    </p>
                    <Link href="/admin/dashboard" class="btn btn-primary">
                        Volver al dashboard
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
