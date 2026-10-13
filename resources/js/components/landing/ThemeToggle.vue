<script setup lang="ts">
import { Moon, Sun } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/composables/useAppearance';

const { resolvedAppearance, updateAppearance } = useAppearance();

const isDark = computed(() => resolvedAppearance.value === 'dark');

function toggleTheme(): void {
    updateAppearance(isDark.value ? 'light' : 'dark');
}
</script>

<template>
    <Button
        variant="ghost"
        size="icon"
        type="button"
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        @click="toggleTheme"
    >
        <Sun v-if="isDark" class="size-4.5" />
        <Moon v-else class="size-4.5" />
    </Button>
</template>
