<script setup lang="ts">
import { IdCard, KeyRound, Laptop, Package, Shirt, Wallet } from '@lucide/vue';
import type { Component } from 'vue';
import {
    LANDING_CATEGORIES,
    type CategoryId,
} from '@/components/landing/fixtures';

defineProps<{
    activeCategory: CategoryId | 'all';
}>();

const emit = defineEmits<{
    (e: 'select', category: CategoryId): void;
}>();

const categoryIcons: Record<CategoryId, Component> = {
    documents: IdCard,
    electronics: Laptop,
    'wallets-bags': Wallet,
    keys: KeyRound,
    clothing: Shirt,
    other: Package,
};

function select(category: CategoryId): void {
    emit('select', category);
}
</script>

<template>
    <section
        id="categories"
        class="mx-auto w-full max-w-6xl scroll-mt-24 px-4 pb-14 sm:px-6 sm:pb-20"
    >
        <div class="max-w-xl">
            <h2 class="text-2xl font-semibold tracking-tight">
                Browse by category
            </h2>
            <p class="mt-1.5 text-sm text-muted-foreground">
                Jump straight to the item types reported most on campus.
            </p>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <button
                v-for="category in LANDING_CATEGORIES"
                :key="category.id"
                type="button"
                class="flex flex-col items-start gap-3 rounded-xl border bg-card p-4 text-left shadow-xs transition-colors outline-none hover:border-ring/60 hover:bg-accent/40 focus-visible:ring-[3px] focus-visible:ring-ring/50"
                :class="
                    activeCategory === category.id
                        ? 'border-ring/60 bg-accent/40'
                        : ''
                "
                :aria-pressed="activeCategory === category.id"
                @click="select(category.id)"
            >
                <span
                    class="flex size-9 items-center justify-center rounded-lg bg-muted text-foreground"
                >
                    <component
                        :is="categoryIcons[category.id]"
                        class="size-4.5"
                        aria-hidden="true"
                    />
                </span>
                <span class="text-sm leading-snug font-medium">
                    {{ category.label }}
                </span>
            </button>
        </div>
    </section>
</template>
