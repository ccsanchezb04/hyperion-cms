<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useSite } from '../composables/useSite';
import SocialLinks from './SocialLinks.vue';

const { setting } = useSite();

const form = useForm({
    nombre: '',
    email: '',
    asunto: '',
    mensaje: '',
});

const page = usePage<{ flash?: { contact_status?: string } }>();
const sent = computed(() => page.props.flash?.contact_status === 'sent');

const submit = () => {
    form.post(route('site.contact.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <section id="contacto" class="bg-white py-5">
        <div class="container">
            <h2 class="text-center my-4 jf-heading">
                {{ setting('site.contact.heading', 'Estamos más cerca de ti') }}
            </h2>
            <div class="row">
                <div class="col-lg-6 d-flex flex-column justify-content-center gap-3 py-3">
                    <p class="text-muted mb-0 fw-semibold">
                        {{ setting('site.contact.social_text', 'Síguenos en nuestras redes sociales') }}
                    </p>
                    <SocialLinks variant="grid" />
                </div>
                <div class="col-lg-6">
                    <div v-if="sent" class="alert alert-success" role="alert">
                        Gracias por escribirnos. Te responderemos pronto.
                    </div>

                    <form @submit.prevent="submit">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input
                                v-model="form.nombre"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.nombre }"
                                id="nombre"
                                placeholder="Tu nombre"
                            />
                            <div v-if="form.errors.nombre" class="invalid-feedback">
                                {{ form.errors.nombre }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.email }"
                                id="email"
                                placeholder="Tu correo electrónico"
                            />
                            <div v-if="form.errors.email" class="invalid-feedback">
                                {{ form.errors.email }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="asunto" class="form-label">Asunto</label>
                            <select
                                v-model="form.asunto"
                                id="asunto"
                                class="form-select"
                                :class="{ 'is-invalid': form.errors.asunto }"
                            >
                                <option value="" disabled>Selecciona un asunto</option>
                                <option value="cotizacion">Cotización</option>
                                <option value="soporte">Soporte</option>
                                <option value="otros">Otros</option>
                            </select>
                            <div v-if="form.errors.asunto" class="invalid-feedback">
                                {{ form.errors.asunto }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="mensaje" class="form-label">Mensaje</label>
                            <textarea
                                v-model="form.mensaje"
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.mensaje }"
                                id="mensaje"
                                rows="4"
                                placeholder="Escribe tu mensaje aquí"
                            ></textarea>
                            <div v-if="form.errors.mensaje" class="invalid-feedback">
                                {{ form.errors.mensaje }}
                            </div>
                        </div>
                        <button type="submit" class="jf-btn" :disabled="form.processing">
                            {{ form.processing ? 'Enviando…' : 'Enviar mensaje' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>
