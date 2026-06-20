<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { SeoMeta, SeoSettings } from '../Index.vue';

defineProps<{ settings: SeoSettings; meta: SeoMeta }>();

const flushing = ref(false);
const flushSuccess = ref(false);

const flush = () => {
    flushing.value = true;
    flushSuccess.value = false;
    router.post('/admin/seo/sitemap/flush', {}, {
        preserveScroll: true,
        onSuccess: () => {
            flushing.value = false;
            flushSuccess.value = true;
            setTimeout(() => flushSuccess.value = false, 3000);
        },
        onError: () => { flushing.value = false; },
    });
};
</script>

<template>
    <div>
        <h2 class="h5 fw-bold mb-3">Sitemap</h2>
        <p class="text-body-secondary small mb-4">
            El sitemap se genera dinámicamente y se cachea por 1h. Cualquier
            cambio en contenido publicado lo invalida automáticamente.
        </p>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="border rounded p-3 h-100">
                    <div class="text-body-secondary small">URLs incluidas</div>
                    <div class="h3 fw-bold mb-0">{{ meta.sitemap_url_count }}</div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-body-secondary small">Endpoint público</div>
                        <a href="/sitemap.xml" target="_blank" class="text-decoration-none">
                            /sitemap.xml <i class="bi bi-box-arrow-up-right small"></i>
                        </a>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" :disabled="flushing" @click="flush">
                            <i class="bi bi-arrow-clockwise"></i>
                            {{ flushing ? 'Regenerando...' : 'Forzar regeneración' }}
                        </button>
                        <span v-if="flushSuccess" class="text-success small align-self-center">
                            <i class="bi bi-check-circle-fill"></i> Cache limpiado
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <details>
            <summary class="fw-semibold mb-2" style="cursor: pointer;">Ver XML</summary>
            <pre class="border rounded p-3 bg-body-tertiary small overflow-auto" style="max-height: 400px;"><code>{{ meta.sitemap_xml }}</code></pre>
        </details>
    </div>
</template>
