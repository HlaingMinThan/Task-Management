<script setup lang="ts">
import { onMounted, ref } from 'vue';

const model = defineModel<string>({ required: true });

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="mt-1 block w-full rounded-lg bg-slate-900 border-slate-700 text-white focus:border-purple-500 focus:ring-purple-500 shadow-sm transition px-4 py-2.5"
        v-model="model"
        ref="input"
    />
</template>
