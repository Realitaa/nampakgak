<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingNavbar from '@/components/landing/LandingNavbar.vue';
import type { User } from '@/types';

const { variant = 'plain' } = defineProps<{
    /**
     * `landing` renders the marketing page's in-page navigation (jump links to
     * the reports / how-it-works sections). `plain` keeps only the shared
     * chrome, which is what the plain public pages such as Discover need.
     */
    variant?: 'plain' | 'landing';
}>();

const user = computed<User | null>(() => usePage().props.auth.user ?? null);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <LandingNavbar :user="user" :variant="variant" />

        <div class="flex flex-1 flex-col">
            <slot />
        </div>

        <LandingFooter :user="user" :variant="variant" />
    </div>
</template>
