<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import type { SeoSettings } from '../Index.vue';

const props = defineProps<{ settings: SeoSettings }>();
const ints = props.settings.integrations;

const form = useForm({
    tab: 'integrations',
    values: {
        'site.integrations.gsc_verification':   ints['site.integrations.gsc_verification'] ?? '',
        'site.integrations.gtm_id':             ints['site.integrations.gtm_id'] ?? '',
        'site.integrations.ga4_measurement_id': ints['site.integrations.ga4_measurement_id'] ?? '',
        'site.integrations.fb_pixel_id':        ints['site.integrations.fb_pixel_id'] ?? '',
    },
});

const save = () => form.put('/admin/seo', { preserveScroll: true });
</script>

<template>
    <form @submit.prevent="save">
        <h2 class="h5 fw-bold mb-3">Integraciones</h2>
        <p class="text-body-secondary small mb-4">
            IDs de servicios de terceros para tracking y verificación. Vacíos =
            no se inyecta el script correspondiente.
        </p>

        <div class="mb-3">
            <label class="form-label">Google Search Console verification</label>
            <input v-model="form.values['site.integrations.gsc_verification']" type="text" class="form-control" placeholder="contenido del meta google-site-verification" />
            <div class="form-text">Solo el valor del content, sin el meta tag completo.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Google Tag Manager ID</label>
            <input v-model="form.values['site.integrations.gtm_id']" type="text" class="form-control" placeholder="GTM-XXXXXX" />
        </div>

        <div class="mb-3">
            <label class="form-label">Google Analytics 4 measurement ID</label>
            <input v-model="form.values['site.integrations.ga4_measurement_id']" type="text" class="form-control" placeholder="G-XXXXXXXXXX" />
        </div>

        <div class="mb-3">
            <label class="form-label">Facebook Pixel ID</label>
            <input v-model="form.values['site.integrations.fb_pixel_id']" type="text" class="form-control" placeholder="000000000000000" />
        </div>

        <div v-if="form.recentlySuccessful" class="alert alert-success py-2 mb-3">Settings guardados.</div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                {{ form.processing ? 'Guardando...' : 'Guardar' }}
            </button>
        </div>
    </form>
</template>
