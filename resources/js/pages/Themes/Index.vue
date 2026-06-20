<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { CheckCircle2, Paintbrush } from 'lucide-vue-next';
import { computed } from 'vue';

interface ThemeRow {
    slug: string;
    name: string;
    version: string | null;
    description: string | null;
    author: string | null;
    sections: string[];
    is_active: boolean;
}

defineProps<{
    themes: ThemeRow[];
    activeSlug: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Themes', href: '/admin/themes' },
];

const page = usePage<{ flash?: { status?: string } }>();
const status = computed(() => page.props.flash?.status ?? null);

const activate = (slug: string) => {
    if (! confirm(`¿Activar el tema "${slug}"? El sitio público cambiará al recargar.`)) return;
    router.post(
        '/admin/themes/activate',
        { slug },
        { preserveScroll: true, preserveState: false },
    );
};
</script>

<template>
    <Head title="Themes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h4 fw-bold mb-1">Themes</h1>
                    <p class="text-body-secondary small mb-0">
                        El tema activo controla la apariencia del sitio público en <code>/</code>.
                    </p>
                </div>
            </div>

            <div v-if="status" class="alert alert-success" role="alert">{{ status }}</div>

            <div class="row g-3">
                <div v-for="theme in themes" :key="theme.slug" class="col-md-6 col-lg-4">
                    <div
                        class="card h-100 shadow-sm"
                        :class="theme.is_active ? 'border-primary border-2' : ''"
                    >
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <Paintbrush :size="20" class="text-body-secondary" />
                                    <h5 class="card-title mb-0">{{ theme.name }}</h5>
                                </div>
                                <span
                                    v-if="theme.is_active"
                                    class="badge bg-primary d-inline-flex align-items-center gap-1"
                                >
                                    <CheckCircle2 :size="14" /> Activo
                                </span>
                            </div>
                            <p class="small text-body-secondary mb-2">
                                <code>{{ theme.slug }}</code>
                                <span v-if="theme.version"> · v{{ theme.version }}</span>
                                <span v-if="theme.author"> · {{ theme.author }}</span>
                            </p>
                            <p v-if="theme.description" class="card-text small mb-3">
                                {{ theme.description }}
                            </p>
                            <div v-if="theme.sections.length" class="d-flex flex-wrap gap-1 mb-3">
                                <span v-for="s in theme.sections" :key="s" class="badge bg-light text-dark">
                                    {{ s }}
                                </span>
                            </div>
                            <button
                                type="button"
                                class="btn btn-sm mt-auto"
                                :class="theme.is_active ? 'btn-outline-secondary' : 'btn-primary'"
                                :disabled="theme.is_active"
                                @click="activate(theme.slug)"
                            >
                                {{ theme.is_active ? 'Tema activo' : 'Activar' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-body-secondary small mt-4 mb-0">
                Para crear un tema nuevo: añade <code>resources/themes/{slug}/theme.json</code> y reconstruye
                los assets. Detalles en <code>resources/themes/README.md</code>.
            </p>
        </div>
    </AppLayout>
</template>
