<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout title="Reset password" description="Please enter your new password below">
        <Head title="Reset password" />

        <form @submit.prevent="submit" class="d-flex flex-column gap-3">
            <div>
                <label for="email" class="form-label">Email</label>
                <input id="email" v-model="form.email" type="email" name="email" class="form-control" autocomplete="email" readonly />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <label for="password" class="form-label">Password</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    name="password"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.password }"
                    autocomplete="new-password"
                    autofocus
                    placeholder="Password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div>
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    name="password_confirmation"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.password_confirmation }"
                    autocomplete="new-password"
                    placeholder="Confirm password"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2" :disabled="form.processing">
                <LoaderCircle v-if="form.processing" :size="16" class="spinner" />
                Reset password
            </button>
        </form>
    </AuthLayout>
</template>

<style scoped>
.spinner { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
