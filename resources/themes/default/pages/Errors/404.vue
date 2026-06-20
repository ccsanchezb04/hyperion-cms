<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import SiteLayout from '../../layouts/SiteLayout.vue';

defineProps<{
    status?: number;
    message?: string;
}>();
</script>

<template>
    <Head title="Página no encontrada" />
    <SiteLayout>
        <section class="df-section" style="text-align: center;">
            <div class="df-container" style="max-width: 560px;">
                <p style="color: var(--df-muted); font-weight: 600; letter-spacing: 0.1em;">
                    ERROR {{ status ?? 404 }}
                </p>
                <h2>No encontramos esta página</h2>
                <p style="color: var(--df-muted); margin-bottom: 2rem;">
                    {{ message ?? 'La dirección que ingresaste no existe.' }}
                </p>
                <Link href="/" class="df-btn">Volver al inicio</Link>
            </div>
        </section>
    </SiteLayout>
</template>
