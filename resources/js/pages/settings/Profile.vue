<script setup lang="ts">
import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { TransitionRoot } from '@headlessui/vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Profile settings', href: '/admin/settings/profile' }];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(route('profile.update'), { preserveScroll: true });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="d-flex flex-column gap-4">
                <HeadingSmall title="Profile information" description="Update your name and email address" />

                <form @submit.prevent="submit" class="d-flex flex-column gap-3">
                    <div>
                        <label for="name" class="form-label">Name</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.name }"
                            required
                            autocomplete="name"
                            placeholder="Full name"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div>
                        <label for="email" class="form-label">Email address</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.email }"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at" class="alert alert-warning py-2">
                        <p class="small mb-2">
                            Your email address is unverified.
                            <Link :href="route('verification.send')" method="post" as="button" class="btn btn-link p-0 text-decoration-underline">
                                Click here to re-send the verification email.
                            </Link>
                        </p>
                        <div v-if="status === 'verification-link-sent'" class="small text-success fw-medium mb-0">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">Save</button>

                        <TransitionRoot
                            :show="form.recentlySuccessful"
                            enter="transition-opacity"
                            enter-from="opacity-0"
                            leave="transition-opacity"
                            leave-to="opacity-0"
                        >
                            <p class="small text-body-secondary mb-0">Saved.</p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>

<style scoped>
.transition-opacity {
    transition: opacity 0.2s ease-in-out;
}
</style>
