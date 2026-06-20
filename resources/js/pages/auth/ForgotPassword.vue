<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout title="Forgot password" description="Enter your email to receive a password reset link">
        <Head title="Forgot password" />

        <div v-if="status" class="alert alert-success small text-center py-2 mb-3">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="d-flex flex-column gap-3">
            <div>
                <label for="email" class="form-label">Email address</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    name="email"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.email }"
                    autocomplete="off"
                    autofocus
                    placeholder="email@example.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2" :disabled="form.processing">
                <LoaderCircle v-if="form.processing" :size="16" class="spinner" />
                Email password reset link
            </button>

            <div class="text-center text-body-secondary small">
                <span>Or, return to </span>
                <TextLink :href="route('login')">log in</TextLink>
            </div>
        </form>
    </AuthLayout>
</template>

<style scoped>
.spinner { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
