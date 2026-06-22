<script setup lang="ts">
import GoogleSnippet from '@/components/SeoPreviews/GoogleSnippet.vue';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { SeoSettings } from '../Index.vue';

const props = defineProps<{ settings: SeoSettings; activeLang?: string }>();

const form = useForm({
    tab: 'general',
    lang: props.activeLang ?? 'es',
    values: {
        'site.seo.title_template': props.settings.seo['site.seo.title_template'] ?? '',
        'site.seo.description':    props.settings.seo['site.seo.description'] ?? '',
        'site.seo.keywords':       props.settings.seo['site.seo.keywords'] ?? '',
        'site.seo.canonical_host': props.settings.seo['site.seo.canonical_host'] ?? '',
        'site.seo.locale':         props.settings.seo['site.seo.locale'] ?? 'es_CO',
        'site.seo.robots':         props.settings.seo['site.seo.robots'] ?? 'noindex,nofollow',
        'site.seo.schema_type':    props.settings.seo['site.seo.schema_type'] ?? 'InsuranceAgency',
    },
});

const save = () => form.put('/admin/seo', { preserveScroll: true });

const previewTitle = computed(() =>
    (form.values['site.seo.title_template'] || '{page} | JuanFer Seguros').replace('{page}', 'Inicio'),
);
const previewUrl = computed(() => form.values['site.seo.canonical_host'] || 'https://juanferseguros.com');
const previewDesc = computed(() => form.values['site.seo.description'] || '');
const siteName = computed(() => props.settings.site['site.heading'] ?? 'JuanFer Seguros');
</script>

<template>
    <form @submit.prevent="save">
        <h2 class="h5 fw-bold mb-3">General</h2>

        <div class="mb-3">
            <label class="form-label">Title template</label>
            <input v-model="form.values['site.seo.title_template']" type="text" class="form-control" placeholder="{page} | JuanFer Seguros" />
            <div class="form-text">
                Usa <code>{page}</code> como placeholder del título de la página.
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Meta description global</label>
            <textarea v-model="form.values['site.seo.description']" class="form-control" rows="2" maxlength="320"></textarea>
            <div class="form-text">Máximo 160 caracteres recomendado por Google.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Keywords (separadas por coma)</label>
            <textarea v-model="form.values['site.seo.keywords']" class="form-control" rows="2"></textarea>
        </div>

        <div class="row g-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Dominio canónico</label>
                <input v-model="form.values['site.seo.canonical_host']" type="url" class="form-control" placeholder="https://juanferseguros.com" />
                <div class="form-text">Base para canonical URL, og:url y sitemap.</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Locale</label>
                <input v-model="form.values['site.seo.locale']" type="text" class="form-control" placeholder="es_CO" />
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Política de robots</label>
                <select v-model="form.values['site.seo.robots']" class="form-select">
                    <option value="noindex,nofollow">noindex, nofollow (oculto a buscadores)</option>
                    <option value="index,follow">index, follow (público)</option>
                    <option value="index,nofollow">index, nofollow</option>
                    <option value="noindex,follow">noindex, follow</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Schema.org type</label>
                <select v-model="form.values['site.seo.schema_type']" class="form-select">
                    <option value="InsuranceAgency">InsuranceAgency (recomendado)</option>
                    <option value="FinancialService">FinancialService</option>
                    <option value="LocalBusiness">LocalBusiness</option>
                </select>
            </div>
        </div>

        <div class="mt-4">
            <div class="text-body-secondary small mb-1">Preview en Google (página Inicio):</div>
            <GoogleSnippet :title="previewTitle" :description="previewDesc" :url="previewUrl" :site-name="siteName" />
        </div>

        <div v-if="form.recentlySuccessful" class="alert alert-success py-2 mb-3 mt-3">Settings guardados.</div>

        <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                {{ form.processing ? 'Guardando...' : 'Guardar' }}
            </button>
        </div>
    </form>
</template>
