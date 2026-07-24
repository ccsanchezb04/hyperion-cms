<script setup lang="ts">
import FacebookCard from '@/components/SeoPreviews/FacebookCard.vue';
import TwitterCard from '@/components/SeoPreviews/TwitterCard.vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { SeoSettings } from '../Index.vue';

const props = defineProps<{ settings: SeoSettings; activeLang?: string }>();

const form = useForm({
    tab: 'og',
    lang: props.activeLang ?? 'es',
    values: {
        'site.seo.og_image':       props.settings.seo['site.seo.og_image'] ?? '',
        'site.seo.og_description': props.settings.seo['site.seo.og_description'] ?? '',
        'site.seo.twitter_handle': props.settings.seo['site.seo.twitter_handle'] ?? '',
    },
});

const save = () => form.put('/admin/seo', { preserveScroll: true });

const uploadInput = ref<HTMLInputElement | null>(null);
const uploading = ref(false);
const uploadError = ref('');

const previewTitle = computed(() => {
    const tmpl = props.settings.seo['site.seo.title_template'] ?? '{page} | JuanFer Seguros';
    return tmpl.replace('{page}', 'Inicio');
});
const previewDesc = computed(() => form.values['site.seo.og_description'] || props.settings.seo['site.seo.description'] || '');
const previewUrl = computed(() => props.settings.seo['site.seo.canonical_host'] ?? 'https://juanferseguros.com');
const previewImage = computed(() => form.values['site.seo.og_image']);
const handle = computed(() => form.values['site.seo.twitter_handle']);

const uploadOgImage = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;
    uploading.value = true;
    uploadError.value = '';
    const fd = new FormData();
    fd.append('image', file);
    router.post('/admin/seo/og-image', fd, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => { uploading.value = false; },
        onError: (errors) => {
            uploading.value = false;
            uploadError.value = errors.image ?? 'Error al subir';
        },
    });
};
</script>

<template>
    <form @submit.prevent="save">
        <h2 class="h5 fw-bold mb-3">Open Graph &amp; Social</h2>

        <div class="mb-4">
            <label class="form-label">OG image default (1200×630)</label>
            <div class="d-flex align-items-start gap-3">
                <div class="border rounded p-1" style="width: 200px;">
                    <img
                        v-if="form.values['site.seo.og_image']"
                        :src="form.values['site.seo.og_image']"
                        alt="OG preview"
                        class="img-fluid"
                        style="aspect-ratio: 1200/630; object-fit: cover; width: 100%;"
                    />
                    <div v-else class="text-body-secondary small text-center py-4">Sin imagen</div>
                </div>
                <div class="flex-grow-1">
                    <input
                        ref="uploadInput"
                        type="file"
                        class="form-control"
                        accept="image/jpeg,image/png,image/webp"
                        :disabled="uploading"
                        @change="uploadOgImage"
                    />
                    <div class="form-text">JPG/PNG/WEBP, máx 2MB. Se guarda en /storage/site/seo/og-default.</div>
                    <div v-if="uploadError" class="text-danger small mt-1">{{ uploadError }}</div>
                    <input
                        v-model="form.values['site.seo.og_image']"
                        type="text"
                        class="form-control mt-2"
                        placeholder="/storage/site/seo/og-default.jpg"
                    />
                    <div class="form-text">O escribe la ruta manualmente (relativa al dominio).</div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">OG description (slogan corto)</label>
            <input v-model="form.values['site.seo.og_description']" type="text" class="form-control" maxlength="120" />
            <div class="form-text">Texto que aparece debajo de la imagen en previews de WhatsApp/Facebook/etc.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Twitter handle</label>
            <input v-model="form.values['site.seo.twitter_handle']" type="text" class="form-control" placeholder="@juanferseguros" />
        </div>

        <div class="row g-4 mt-2">
            <div class="col-md-6">
                <div class="text-body-secondary small mb-2">Preview Facebook / WhatsApp:</div>
                <FacebookCard :title="previewTitle" :description="previewDesc" :image="previewImage" :url="previewUrl" />
            </div>
            <div class="col-md-6">
                <div class="text-body-secondary small mb-2">Preview Twitter / X:</div>
                <TwitterCard :title="previewTitle" :description="previewDesc" :image="previewImage" :url="previewUrl" :handle="handle" />
            </div>
        </div>

        <div v-if="form.recentlySuccessful" class="alert alert-success py-2 mb-3 mt-3">Settings guardados.</div>

        <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                {{ form.processing ? 'Guardando...' : 'Guardar' }}
            </button>
        </div>
    </form>
</template>
