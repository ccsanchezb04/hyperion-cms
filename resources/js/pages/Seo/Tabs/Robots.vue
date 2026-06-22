<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { SeoMeta, SeoSettings } from '../Index.vue';

const props = defineProps<{ settings: SeoSettings; meta: SeoMeta; activeLang?: string }>();

const form = useForm({
    tab: 'robots',
    lang: props.activeLang ?? 'es',
    values: {
        'site.seo.robots': props.settings.seo['site.seo.robots'] ?? 'noindex,nofollow',
    },
});

const save = () => form.put('/admin/seo', { preserveScroll: true });

const currentPolicy = computed(() => form.values['site.seo.robots']);
const isBlocking = computed(() => currentPolicy.value.includes('noindex'));
</script>

<template>
    <div>
        <h2 class="h5 fw-bold mb-3">Robots.txt</h2>
        <p class="text-body-secondary small mb-4">
            El <code>robots.txt</code> se genera dinámicamente a partir de la
            política global y se sirve en <code>/robots.txt</code>.
        </p>

        <form @submit.prevent="save" class="mb-4">
            <label class="form-label">Política de robots</label>
            <select v-model="form.values['site.seo.robots']" class="form-select mb-2">
                <option value="noindex,nofollow">noindex, nofollow (oculto a buscadores)</option>
                <option value="index,follow">index, follow (público)</option>
                <option value="index,nofollow">index, nofollow</option>
                <option value="noindex,follow">noindex, follow</option>
            </select>

            <div v-if="isBlocking" class="alert alert-warning py-2 mb-2 small">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Mientras esté activo <strong>noindex</strong>, Google no indexará ninguna página
                del sitio. Cambia a <code>index,follow</code> cuando el cliente apruebe el lanzamiento.
            </div>

            <div v-if="form.recentlySuccessful" class="alert alert-success py-2 mb-2">Política guardada.</div>

            <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
                {{ form.processing ? 'Guardando...' : 'Guardar política' }}
            </button>
        </form>

        <div>
            <h3 class="h6 fw-semibold mb-2">Preview de /robots.txt</h3>
            <pre class="border rounded p-3 bg-body-tertiary small"><code>{{ meta.robots_txt }}</code></pre>
            <a href="/robots.txt" target="_blank" class="small text-decoration-none">
                Abrir endpoint <i class="bi bi-box-arrow-up-right"></i>
            </a>
        </div>
    </div>
</template>
