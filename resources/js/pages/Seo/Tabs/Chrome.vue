<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import type { SeoSettings } from '../Index.vue';

const props = defineProps<{ settings: SeoSettings; activeLang: string }>();

const get = (key: string) => props.settings.site[key] ?? '';

const form = useForm({
    tab: 'chrome',
    lang: props.activeLang,
    values: {
        'site.heading':                get('site.heading'),
        'site.tagline':                get('site.tagline'),
        'site.about.heading':          get('site.about.heading'),
        'site.about.intro':            get('site.about.intro'),
        'site.about.vision_title':     get('site.about.vision_title'),
        'site.about.vision_lead':      get('site.about.vision_lead'),
        'site.about.vision_body':      get('site.about.vision_body'),
        'site.about.mission_title':    get('site.about.mission_title'),
        'site.about.mission_body':     get('site.about.mission_body'),
        'site.solutions.heading':      get('site.solutions.heading'),
        'site.solutions.cta_all':      get('site.solutions.cta_all'),
        'site.testimonials.heading':   get('site.testimonials.heading'),
        'site.contact.heading':        get('site.contact.heading'),
        'site.footer.copy':            get('site.footer.copy'),
        'site.footer.address':         get('site.footer.address'),
    },
});

const save = () => form.put('/admin/seo', { preserveScroll: true });
</script>

<template>
    <form @submit.prevent="save">
        <h2 class="h5 fw-bold mb-3">Chrome del sitio (i18n)</h2>
        <p class="text-body-secondary small mb-4">
            Textos visibles del sitio público (headings, footer, About, etc.).
            Cambia el idioma arriba para editar traducciones; vacío = cae al
            Español por defecto.
        </p>

        <h3 class="h6 fw-semibold mt-2 mb-3">Identidad</h3>
        <div class="mb-3">
            <label class="form-label">Heading principal</label>
            <input v-model="form.values['site.heading']" type="text" class="form-control" placeholder="JuanFer Seguros" />
        </div>
        <div class="mb-3">
            <label class="form-label">Tagline</label>
            <input v-model="form.values['site.tagline']" type="text" class="form-control" />
        </div>

        <h3 class="h6 fw-semibold mt-3 mb-3">Soluciones</h3>
        <div class="row g-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Heading sección Soluciones</label>
                <input v-model="form.values['site.solutions.heading']" type="text" class="form-control" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">CTA "Ver todas"</label>
                <input v-model="form.values['site.solutions.cta_all']" type="text" class="form-control" />
            </div>
        </div>

        <h3 class="h6 fw-semibold mt-3 mb-3">About / Nosotros</h3>
        <div class="mb-3">
            <label class="form-label">Heading</label>
            <input v-model="form.values['site.about.heading']" type="text" class="form-control" />
        </div>
        <div class="mb-3">
            <label class="form-label">Intro</label>
            <textarea v-model="form.values['site.about.intro']" rows="3" class="form-control"></textarea>
        </div>
        <div class="row g-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Visión - título</label>
                <input v-model="form.values['site.about.vision_title']" type="text" class="form-control" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Visión - lead</label>
                <input v-model="form.values['site.about.vision_lead']" type="text" class="form-control" />
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Visión - cuerpo</label>
            <textarea v-model="form.values['site.about.vision_body']" rows="3" class="form-control"></textarea>
        </div>
        <div class="row g-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Misión - título</label>
                <input v-model="form.values['site.about.mission_title']" type="text" class="form-control" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Testimonios - heading</label>
                <input v-model="form.values['site.testimonials.heading']" type="text" class="form-control" />
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Misión - cuerpo</label>
            <textarea v-model="form.values['site.about.mission_body']" rows="3" class="form-control"></textarea>
        </div>

        <h3 class="h6 fw-semibold mt-3 mb-3">Contacto + Footer</h3>
        <div class="mb-3">
            <label class="form-label">Heading sección Contacto</label>
            <input v-model="form.values['site.contact.heading']" type="text" class="form-control" />
        </div>
        <div class="mb-3">
            <label class="form-label">Copyright / Footer copy</label>
            <input v-model="form.values['site.footer.copy']" type="text" class="form-control" />
        </div>
        <div class="mb-3">
            <label class="form-label">Dirección visible en footer</label>
            <input v-model="form.values['site.footer.address']" type="text" class="form-control" />
        </div>

        <div v-if="form.recentlySuccessful" class="alert alert-success py-2 mb-3">Settings guardados.</div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                {{ form.processing ? 'Guardando...' : 'Guardar' }}
            </button>
        </div>
    </form>
</template>
