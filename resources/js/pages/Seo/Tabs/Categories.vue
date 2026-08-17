<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import type { SeoSettings, SolutionCategory } from '../Index.vue';

const props = defineProps<{
    settings: SeoSettings;
    activeLang: string;
    categories: SolutionCategory[];
}>();

// Construye el objeto values con dos keys por categoría (description + og_image),
// inicializando desde las settings actuales del lang activo.
const buildInitialValues = (): Record<string, string> => {
    const out: Record<string, string> = {};
    for (const cat of props.categories) {
        out[`site.seo.category.${cat.slug}.description`] =
            props.settings.seo[`site.seo.category.${cat.slug}.description`] ?? '';
        out[`site.seo.category.${cat.slug}.og_image`] =
            props.settings.seo[`site.seo.category.${cat.slug}.og_image`] ?? '';
    }
    return out;
};

const form = useForm({
    tab: 'categories',
    lang: props.activeLang,
    values: buildInitialValues(),
});

const save = () => form.put('/admin/seo', { preserveScroll: true });

// Helpers para acceder a los campos en el template
const descKey = (slug: string) => `site.seo.category.${slug}.description`;
const ogKey   = (slug: string) => `site.seo.category.${slug}.og_image`;
</script>

<template>
    <form @submit.prevent="save">
        <h2 class="h5 fw-bold mb-3">Categorías OG (i18n)</h2>
        <p class="text-body-secondary small mb-4">
            Meta description y OG image por categoría. El idioma activo se
            controla con el selector arriba. Vacío = cae al valor por defecto
            (Español).
        </p>

        <div v-if="categories.length === 0" class="alert alert-warning small">
            No hay categorías hijas de <code>soluciones</code>. Crea categorías
            primero en /admin/categories.
        </div>

        <div v-for="cat in categories" :key="cat.slug" class="mb-4 pb-3 border-bottom">
            <h3 class="h6 fw-semibold mb-3">
                <i class="bi bi-tag-fill me-1"></i>
                {{ cat.name }}
                <code class="text-body-secondary fs-7">{{ cat.slug }}</code>
            </h3>

            <div class="mb-3">
                <label class="form-label">Meta description</label>
                <textarea
                    v-model="form.values[descKey(cat.slug)]"
                    class="form-control"
                    rows="2"
                    maxlength="320"
                    placeholder="Texto que aparece debajo del título en buscadores"
                ></textarea>
            </div>

            <div class="row g-3 align-items-start">
                <div class="col-md-9">
                    <label class="form-label">OG image (URL o path)</label>
                    <input
                        v-model="form.values[ogKey(cat.slug)]"
                        type="text"
                        class="form-control"
                        placeholder="/storage/site/seo/og-vida-en.jpg"
                    />
                    <div class="form-text">
                        URL absoluta o path relativo al dominio. Por idioma; si no se
                        sobreescribe, se usa la OG por defecto (Español).
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-1 bg-body-tertiary text-center">
                        <img
                            v-if="form.values[ogKey(cat.slug)]"
                            :src="form.values[ogKey(cat.slug)]"
                            :alt="`OG ${cat.name}`"
                            class="img-fluid"
                            style="aspect-ratio: 1200/630; object-fit: cover; width: 100%;"
                        />
                        <div v-else class="text-body-secondary small py-3">
                            Sin imagen
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="form.recentlySuccessful" class="alert alert-success py-2 mb-3">Settings guardados.</div>

        <div v-if="categories.length" class="d-flex justify-content-end gap-2 pt-3 border-top">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                {{ form.processing ? 'Guardando...' : 'Guardar' }}
            </button>
        </div>
    </form>
</template>
