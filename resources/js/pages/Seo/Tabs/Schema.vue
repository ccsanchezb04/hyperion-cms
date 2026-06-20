<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import type { SeoSettings } from '../Index.vue';

const props = defineProps<{ settings: SeoSettings }>();
const org = props.settings.organization;

const form = useForm({
    tab: 'schema',
    values: {
        'site.organization.name':                org['site.organization.name'] ?? '',
        'site.organization.legal_code':          org['site.organization.legal_code'] ?? '',
        'site.organization.phone':               org['site.organization.phone'] ?? '',
        'site.organization.email':               org['site.organization.email'] ?? '',
        'site.organization.opening_hours':       org['site.organization.opening_hours'] ?? '',
        'site.organization.address.street':      org['site.organization.address.street'] ?? '',
        'site.organization.address.locality':    org['site.organization.address.locality'] ?? '',
        'site.organization.address.region':      org['site.organization.address.region'] ?? '',
        'site.organization.address.country':     org['site.organization.address.country'] ?? '',
        'site.organization.address.postal_code': org['site.organization.address.postal_code'] ?? '',
        'site.organization.social.facebook':     org['site.organization.social.facebook'] ?? '',
        'site.organization.social.instagram':    org['site.organization.social.instagram'] ?? '',
        'site.organization.social.linkedin':     org['site.organization.social.linkedin'] ?? '',
        'site.organization.social.tiktok':       org['site.organization.social.tiktok'] ?? '',
    },
});

const save = () => form.put('/admin/seo', { preserveScroll: true });
</script>

<template>
    <form @submit.prevent="save">
        <h2 class="h5 fw-bold mb-3">Schema.org / Organization</h2>
        <p class="text-body-secondary small mb-4">
            Datos que se emiten como JSON-LD InsuranceAgency en cada página.
            Visibles en buscadores y previews sociales.
        </p>

        <h3 class="h6 fw-semibold mt-2 mb-3">Datos legales</h3>
        <div class="row g-3">
            <div class="col-md-8 mb-3">
                <label class="form-label">Razón social</label>
                <input v-model="form.values['site.organization.name']" type="text" class="form-control" placeholder="JUANFER SEGUROS LTDA." />
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">NIT / código legal</label>
                <input v-model="form.values['site.organization.legal_code']" type="text" class="form-control" placeholder="902025311-6" />
            </div>
        </div>

        <h3 class="h6 fw-semibold mt-3 mb-3">Contacto</h3>
        <div class="row g-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Teléfono</label>
                <input v-model="form.values['site.organization.phone']" type="text" class="form-control" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Email público</label>
                <input v-model="form.values['site.organization.email']" type="email" class="form-control" />
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Horario de atención</label>
            <input v-model="form.values['site.organization.opening_hours']" type="text" class="form-control" placeholder="Mo-Fr 08:00-17:00" />
            <div class="form-text">Formato schema.org openingHours.</div>
        </div>

        <h3 class="h6 fw-semibold mt-3 mb-3">Dirección</h3>
        <div class="mb-3">
            <label class="form-label">Calle / dirección</label>
            <input v-model="form.values['site.organization.address.street']" type="text" class="form-control" />
        </div>
        <div class="row g-3">
            <div class="col-md-4 mb-3">
                <label class="form-label">Ciudad</label>
                <input v-model="form.values['site.organization.address.locality']" type="text" class="form-control" />
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Departamento</label>
                <input v-model="form.values['site.organization.address.region']" type="text" class="form-control" />
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">País (ISO)</label>
                <input v-model="form.values['site.organization.address.country']" type="text" class="form-control" maxlength="2" placeholder="CO" />
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">Código postal</label>
                <input v-model="form.values['site.organization.address.postal_code']" type="text" class="form-control" />
            </div>
        </div>

        <h3 class="h6 fw-semibold mt-3 mb-3">Redes sociales (sameAs)</h3>
        <div class="row g-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Facebook URL</label>
                <input v-model="form.values['site.organization.social.facebook']" type="url" class="form-control" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Instagram URL</label>
                <input v-model="form.values['site.organization.social.instagram']" type="url" class="form-control" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">LinkedIn URL</label>
                <input v-model="form.values['site.organization.social.linkedin']" type="url" class="form-control" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">TikTok URL</label>
                <input v-model="form.values['site.organization.social.tiktok']" type="url" class="form-control" />
            </div>
        </div>

        <div v-if="form.recentlySuccessful" class="alert alert-success py-2 mb-3">Settings guardados.</div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                {{ form.processing ? 'Guardando...' : 'Guardar' }}
            </button>
        </div>
    </form>
</template>
