<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { BModal } from 'bootstrap-vue-next';
import { ref } from 'vue';

const passwordInput = ref<HTMLInputElement | null>(null);
const showModal = ref(false);

const form = useForm({
    password: '',
});

const deleteUser = (e: Event) => {
    e.preventDefault();

    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    form.clearErrors();
    form.reset();
    showModal.value = false;
};
</script>

<template>
    <div class="d-flex flex-column gap-3">
        <HeadingSmall title="Delete account" description="Delete your account and all of its resources" />

        <div class="alert alert-danger d-flex flex-column gap-3">
            <div>
                <p class="fw-medium mb-1">Warning</p>
                <p class="small mb-0">Please proceed with caution, this cannot be undone.</p>
            </div>
            <div>
                <button type="button" class="btn btn-danger" @click="showModal = true">Delete account</button>
            </div>
        </div>

        <BModal v-model="showModal" title="Are you sure you want to delete your account?" hide-footer @hidden="closeModal">
            <form class="d-flex flex-column gap-3" @submit="deleteUser">
                <p class="small text-body-secondary mb-0">
                    Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your password to confirm
                    you would like to permanently delete your account.
                </p>

                <div>
                    <label for="password" class="visually-hidden">Password</label>
                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        name="password"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.password }"
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                    <button type="submit" class="btn btn-danger" :disabled="form.processing">Delete account</button>
                </div>
            </form>
        </BModal>
    </div>
</template>
