<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message" class="text-red-400 text-sm mt-1">
        {{ message }}
    </div>
</template>
