<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Settings',
        href: '/admin/settings',
    },
];

interface SettingGroup {
    [key: string]: { [key: string]: string };
}

const settings = ref<SettingGroup>({});
const loading = ref(true);
const activeTab = ref('general');
const saving = ref(false);
const errors = ref<Record<string, string>>({});

const tabs = [
    { id: 'general', label: 'General', icon: '⚙️' },
    { id: 'seo', label: 'SEO', icon: '🔍' },
    { id: 'media', label: 'Media', icon: '📷' },
    { id: 'mail', label: 'Mail', icon: '📧' },
];

const fetchSettings = async () => {
    loading.value = true;
    try {
        const response = await fetch('/api/v1/settings', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
        });
        const data = await response.json();
        settings.value = data.data;
    } catch (error) {
        console.error('Error fetching settings:', error);
    } finally {
        loading.value = false;
    }
};

const saveSettings = async () => {
    saving.value = true;
    errors.value = {};

    try {
        const response = await fetch('/api/v1/settings', {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                settings: settings.value[activeTab.value],
                group: activeTab.value,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            if (data.errors) {
                errors.value = data.errors;
            } else {
                errors.value = { general: data.message || 'An error occurred' };
            }
            return;
        }

        // Update local settings
        settings.value[activeTab.value] = data.data;
        alert('Settings saved successfully!');
    } catch (error) {
        console.error('Error saving settings:', error);
        errors.value = { general: 'An error occurred while saving settings' };
    } finally {
        saving.value = false;
    }
};

const resetGroup = async () => {
    if (confirm('Are you sure you want to reset all settings in this group to default values?')) {
        try {
            const response = await fetch(`/api/v1/settings/group/${activeTab.value}/reset`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Accept': 'application/json',
                },
            });

            if (response.ok) {
                await fetchSettings();
                alert('Settings reset to defaults successfully!');
            }
        } catch (error) {
            console.error('Error resetting settings:', error);
            alert('Error resetting settings');
        }
    }
};

onMounted(() => {
    fetchSettings();
});
</script>

<template>
    <Head title="Settings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h1 class="text-2xl font-bold text-gray-900 mb-6">Settings</h1>

                        <!-- Tabs -->
                        <div class="border-b border-gray-200 mb-6">
                            <nav class="-mb-px flex space-x-8">
                                <button
                                    v-for="tab in tabs"
                                    :key="tab.id"
                                    @click="activeTab = tab.id"
                                    :class="[
                                        activeTab === tab.id
                                            ? 'border-indigo-500 text-indigo-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                    ]"
                                >
                                    {{ tab.icon }} {{ tab.label }}
                                </button>
                            </nav>
                        </div>

                        <!-- Loading State -->
                        <div v-if="loading" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        </div>

                        <!-- Settings Content -->
                        <div v-else>
                            <!-- Error Messages -->
                            <div v-if="errors.general" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                                {{ errors.general }}
                            </div>

                            <!-- General Settings -->
                            <div v-if="activeTab === 'general'" class="space-y-4">
                                <div v-for="(value, key) in settings.general" :key="key">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ formatSettingKey(key) }}
                                    </label>
                                    <input
                                        v-if="typeof value === 'string' && !isBoolean(value)"
                                        v-model="settings.general[key]"
                                        type="text"
                                        class="w-full border border-gray-300 rounded-md px-4 py-2"
                                    />
                                    <select
                                        v-else-if="isBoolean(value)"
                                        v-model="settings.general[key]"
                                        class="w-full border border-gray-300 rounded-md px-4 py-2"
                                    >
                                        <option value="1">Enabled</option>
                                        <option value="0">Disabled</option>
                                    </select>
                                </div>
                            </div>

                            <!-- SEO Settings -->
                            <div v-if="activeTab === 'seo'" class="space-y-4">
                                <div v-for="(value, key) in settings.seo" :key="key">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ formatSettingKey(key) }}
                                    </label>
                                    <input
                                        v-if="typeof value === 'string' && !isBoolean(value)"
                                        v-model="settings.seo[key]"
                                        type="text"
                                        class="w-full border border-gray-300 rounded-md px-4 py-2"
                                    />
                                    <select
                                        v-else-if="isBoolean(value)"
                                        v-model="settings.seo[key]"
                                        class="w-full border border-gray-300 rounded-md px-4 py-2"
                                    >
                                        <option value="1">Enabled</option>
                                        <option value="0">Disabled</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Media Settings -->
                            <div v-if="activeTab === 'media'" class="space-y-4">
                                <div v-for="(value, key) in settings.media" :key="key">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ formatSettingKey(key) }}
                                    </label>
                                    <input
                                        v-if="typeof value === 'string' && !isBoolean(value)"
                                        v-model="settings.media[key]"
                                        type="text"
                                        class="w-full border border-gray-300 rounded-md px-4 py-2"
                                    />
                                    <select
                                        v-else-if="isBoolean(value)"
                                        v-model="settings.media[key]"
                                        class="w-full border border-gray-300 rounded-md px-4 py-2"
                                    >
                                        <option value="1">Enabled</option>
                                        <option value="0">Disabled</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Mail Settings -->
                            <div v-if="activeTab === 'mail'" class="space-y-4">
                                <div v-for="(value, key) in settings.mail" :key="key">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ formatSettingKey(key) }}
                                    </label>
                                    <input
                                        v-if="typeof value === 'string' && !isBoolean(value)"
                                        v-model="settings.mail[key]"
                                        type="text"
                                        class="w-full border border-gray-300 rounded-md px-4 py-2"
                                    />
                                    <select
                                        v-else-if="isBoolean(value)"
                                        v-model="settings.mail[key]"
                                        class="w-full border border-gray-300 rounded-md px-4 py-2"
                                    >
                                        <option value="1">Enabled</option>
                                        <option value="0">Disabled</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
                                <button
                                    @click="resetGroup"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                                >
                                    Reset to Defaults
                                </button>
                                <button
                                    @click="saveSettings"
                                    :disabled="saving"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                                >
                                    {{ saving ? 'Saving...' : 'Save Settings' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
const formatSettingKey = (key: string) => {
    return key
        .replace(/_/g, ' ')
        .replace(/\b\w/g, l => l.toUpperCase());
};

const isBoolean = (value: any) => {
    return value === '0' || value === '1' || value === true || value === false;
};
</script>