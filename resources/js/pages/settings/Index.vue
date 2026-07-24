<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type SettingValue = string | number | boolean;
type SettingGroup = Record<string, Record<string, SettingValue>>;

const props = defineProps<{
    settings: SettingGroup;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Configuración', href: '/admin/settings' },
];

const tabs = [
    { id: 'general', label: 'General', icon: 'gear-fill' },
    { id: 'seo', label: 'SEO', icon: 'search' },
    { id: 'media', label: 'Medios', icon: 'image' },
    { id: 'mail', label: 'Correo', icon: 'envelope-fill' },
];

const activeTab = ref('general');

// Local mutable copy of the settings prop so users can edit before saving.
const localSettings = ref<SettingGroup>(JSON.parse(JSON.stringify(props.settings ?? {})));

const currentValues = computed(() => localSettings.value[activeTab.value] ?? {});

const formatSettingKey = (key: string) =>
    key.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());

const isBoolean = (value: SettingValue) =>
    value === '0' || value === '1' || value === true || value === false;

const form = useForm<{ group: string; settings: Record<string, SettingValue> }>({
    group: 'general',
    settings: {},
});

const save = () => {
    form.group = activeTab.value;
    form.settings = currentValues.value;
    form.put('/admin/settings', { preserveScroll: true });
};
</script>

<template>
    <Head title="Configuración" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-4">Configuración</h1>

                    <ul class="nav nav-tabs mb-4">
                        <li v-for="tab in tabs" :key="tab.id" class="nav-item">
                            <button
                                type="button"
                                class="nav-link d-inline-flex align-items-center gap-2"
                                :class="{ active: activeTab === tab.id }"
                                @click="activeTab = tab.id"
                            >
                                <i :class="`bi bi-${tab.icon}`" aria-hidden="true"></i>
                                {{ tab.label }}
                            </button>
                        </li>
                    </ul>

                    <form @submit.prevent="save" class="d-flex flex-column gap-3">
                        <div v-if="Object.keys(currentValues).length === 0" class="text-body-secondary">
                            No hay configuraciones definidas para este grupo.
                        </div>

                        <div v-for="(value, key) in currentValues" :key="String(key)">
                            <label class="form-label">{{ formatSettingKey(String(key)) }}</label>
                            <select v-if="isBoolean(value)" v-model="localSettings[activeTab][key]" class="form-select">
                                <option :value="'1'">Habilitado</option>
                                <option :value="'0'">Deshabilitado</option>
                            </select>
                            <input v-else v-model="localSettings[activeTab][key]" type="text" class="form-control" />
                        </div>

                        <div v-if="form.recentlySuccessful" class="alert alert-success py-2 mb-0">Configuración guardada.</div>

                        <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                {{ form.processing ? 'Guardando...' : 'Guardar configuración' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
