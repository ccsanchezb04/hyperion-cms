<script setup lang="ts">
import { computed } from 'vue';

interface PermissionItem {
    slug: string;
    name: string;
    description: string | null;
}

const props = defineProps<{
    /** Permisos agrupados por dominio: { content: [...], user: [...], ... } */
    groups: Record<string, PermissionItem[]>;
    /** v-model: lista de slugs seleccionados */
    modelValue: string[];
    disabled?: boolean;
}>();

const emit = defineEmits<{ 'update:modelValue': [string[]] }>();

const selected = computed<Set<string>>(() => new Set(props.modelValue));

const groupAllSelected = (key: string): boolean => {
    const items = props.groups[key] ?? [];
    return items.length > 0 && items.every((p) => selected.value.has(p.slug));
};

const groupSomeSelected = (key: string): boolean => {
    const items = props.groups[key] ?? [];
    return items.some((p) => selected.value.has(p.slug)) && !groupAllSelected(key);
};

const toggleGroup = (key: string, value: boolean) => {
    if (props.disabled) return;
    const items = props.groups[key] ?? [];
    const next = new Set(props.modelValue);
    items.forEach((p) => {
        if (value) next.add(p.slug);
        else next.delete(p.slug);
    });
    emit('update:modelValue', Array.from(next));
};

const togglePermission = (slug: string) => {
    if (props.disabled) return;
    const next = new Set(props.modelValue);
    if (next.has(slug)) next.delete(slug);
    else next.add(slug);
    emit('update:modelValue', Array.from(next));
};

const domainTitle = (key: string): string => {
    return key.charAt(0).toUpperCase() + key.slice(1).replace('-', ' ');
};
</script>

<template>
    <div class="d-flex flex-column gap-3">
        <div v-for="(items, key) in groups" :key="key" class="border rounded p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h3 class="h6 mb-0">{{ domainTitle(key) }}</h3>
                <div class="form-check form-switch">
                    <input
                        :id="`group-${key}`"
                        type="checkbox"
                        class="form-check-input"
                        :checked="groupAllSelected(key)"
                        :indeterminate.prop="groupSomeSelected(key)"
                        :disabled="disabled"
                        @change="toggleGroup(key, ($event.target as HTMLInputElement).checked)"
                    />
                    <label :for="`group-${key}`" class="form-check-label small text-body-secondary">Todos</label>
                </div>
            </div>

            <div class="row g-1">
                <div v-for="p in items" :key="p.slug" class="col-md-6">
                    <div class="form-check">
                        <input
                            :id="`perm-${p.slug}`"
                            type="checkbox"
                            class="form-check-input"
                            :checked="selected.has(p.slug)"
                            :disabled="disabled"
                            @change="togglePermission(p.slug)"
                        />
                        <label :for="`perm-${p.slug}`" class="form-check-label">
                            {{ p.name }}
                            <code class="small text-body-secondary ms-1">{{ p.slug }}</code>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
