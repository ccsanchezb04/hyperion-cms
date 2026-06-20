<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Footer from '../../components/Footer.vue';
import Hero from '../../components/Hero.vue';
import Navbar from '../../components/Navbar.vue';
import SiteLayout from '../../layouts/SiteLayout.vue';

defineProps<{
    status?: number;
    message?: string;
}>();
</script>

<template>
    <Head title="Página no encontrada" />
    <SiteLayout>
        <Hero />
        <Navbar />

        <section class="py-5 text-center">
            <div class="container" style="max-width: 640px;">
                <p class="text-body-secondary fw-semibold mb-2" style="letter-spacing: 0.1em;">
                    ERROR {{ status ?? 404 }}
                </p>
                <h1 class="jf-heading display-5 fw-bold mb-3">
                    No encontramos esta página
                </h1>
                <p class="lead text-body-secondary mb-4">
                    {{ message ?? 'La dirección que ingresaste no existe o el contenido fue removido.' }}
                </p>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <Link href="/" class="jf-btn">Volver al inicio</Link>
                    <Link href="/soluciones" class="jf-btn jf-btn--secondary">Ver soluciones</Link>
                </div>
            </div>
        </section>

        <Footer />
    </SiteLayout>
</template>
