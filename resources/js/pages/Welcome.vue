<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import LandingCategories from '@/components/landing/LandingCategories.vue';
import LandingCta from '@/components/landing/LandingCta.vue';
import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHero from '@/components/landing/LandingHero.vue';
import LandingHowItWorks from '@/components/landing/LandingHowItWorks.vue';
import LandingNavbar from '@/components/landing/LandingNavbar.vue';
import LandingRecentReports from '@/components/landing/LandingRecentReports.vue';
import ReportDetailsDialog from '@/components/landing/ReportDetailsDialog.vue';
import {
    LANDING_REPORTS,
    filterReports,
    hasActiveFilters,
    type CategoryId,
    type LandingReport,
    type ReportTypeFilter,
} from '@/components/landing/fixtures';

const user = computed(() => usePage().props.auth.user ?? null);

const searchQuery = ref('');
const activeType = ref<ReportTypeFilter>('all');
const activeCategory = ref<CategoryId | 'all'>('all');
const selectedReport = ref<LandingReport | null>(null);

const filteredReports = computed(() =>
    filterReports(LANDING_REPORTS, {
        query: searchQuery.value,
        type: activeType.value,
        category: activeCategory.value,
    }),
);

const filtersActive = computed(() =>
    hasActiveFilters({
        query: searchQuery.value,
        type: activeType.value,
        category: activeCategory.value,
    }),
);

function scrollToSection(id: 'recent-reports' | 'how-it-works'): void {
    document.getElementById(id)?.scrollIntoView({
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches
            ? 'auto'
            : 'smooth',
        block: 'start',
    });
}

function handleSearch(query: string): void {
    searchQuery.value = query;
    scrollToSection('recent-reports');
}

function handleSelectCategory(category: CategoryId): void {
    activeCategory.value = activeCategory.value === category ? 'all' : category;
    scrollToSection('recent-reports');
}

function showAllReports(): void {
    searchQuery.value = '';
    activeType.value = 'all';
    activeCategory.value = 'all';
}
</script>

<template>
    <Head title="NampakGak? — Campus Lost & Found" />

    <div class="min-h-screen bg-background text-foreground">
        <a
            href="#recent-reports"
            class="sr-only focus:not-sr-only focus:absolute focus:top-3 focus:left-3 focus:z-50 focus:rounded-md focus:bg-background focus:px-3 focus:py-2 focus:text-sm focus:shadow-xs focus:outline-none"
        >
            Skip to reports
        </a>

        <LandingNavbar :user="user" @jump="scrollToSection" />

        <main>
            <LandingHero :user="user" @search="handleSearch" />

            <LandingCategories
                :active-category="activeCategory"
                @select="handleSelectCategory"
            />

            <LandingRecentReports
                :reports="filteredReports"
                :total-count="LANDING_REPORTS.length"
                :active-type="activeType"
                :has-filters="filtersActive"
                @set-type="activeType = $event"
                @show-all="showAllReports"
                @open="selectedReport = $event"
            />

            <LandingHowItWorks />

            <LandingCta :user="user" />
        </main>

        <LandingFooter :user="user" @jump="scrollToSection" />

        <ReportDetailsDialog
            :report="selectedReport"
            :user="user"
            @close="selectedReport = null"
        />
    </div>
</template>
