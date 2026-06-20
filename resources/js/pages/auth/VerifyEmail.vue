<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};
</script>

<template>
    <AuthLayout title="Verify email" description="Please verify your email address by clicking on the link we just emailed to you.">
        <Head title="Email verification" />

        <div v-if="status === 'verification-link-sent'" class="alert alert-success small text-center py-2 mb-3">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form @submit.prevent="submit" class="d-flex flex-column gap-3 text-center">
            <button type="submit" class="btn btn-secondary d-inline-flex align-items-center justify-content-center gap-2" :disabled="form.processing">
                <LoaderCircle v-if="form.processing" :size="16" class="spinner" />
                Resend verification email
            </button>

            <TextLink :href="route('logout')" method="post" as="button">Log out</TextLink>
        </form>
    </AuthLayout>
</template>

<style scoped>
.spinner { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
