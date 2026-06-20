<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase title="Log in to your account" description="Enter your email and password below to log in">
        <Head title="Log in" />

        <div v-if="status" class="alert alert-success small text-center mb-3 py-2">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="d-flex flex-column gap-3">
            <div>
                <label for="email" class="form-label">Email address</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.email }"
                    required
                    autofocus
                    tabindex="1"
                    autocomplete="email"
                    placeholder="email@example.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <div class="d-flex align-items-center justify-content-between">
                    <label for="password" class="form-label mb-0">Password</label>
                    <TextLink v-if="canResetPassword" :href="route('password.request')" :tabindex="5">Forgot password?</TextLink>
                </div>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="form-control mt-1"
                    :class="{ 'is-invalid': form.errors.password }"
                    required
                    tabindex="2"
                    autocomplete="current-password"
                    placeholder="Password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="form-check">
                <input id="remember" v-model="form.remember" type="checkbox" class="form-check-input" tabindex="3" />
                <label for="remember" class="form-check-label">Remember me</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2" tabindex="4" :disabled="form.processing">
                <LoaderCircle v-if="form.processing" :size="16" class="spinner" />
                Log in
            </button>

            <div class="text-center text-body-secondary small">
                Don't have an account?
                <TextLink :href="route('register')" :tabindex="5">Sign up</TextLink>
            </div>
        </form>
    </AuthBase>
</template>

<style scoped>
.spinner {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
