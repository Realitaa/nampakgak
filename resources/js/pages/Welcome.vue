<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import LandingCategories from '@/components/landing/LandingCategories.vue';
import LandingCta from '@/components/landing/LandingCta.vue';
import LandingHero from '@/components/landing/LandingHero.vue';
import LandingHowItWorks from '@/components/landing/LandingHowItWorks.vue';
import LandingRecentReports from '@/components/landing/LandingRecentReports.vue';
import ReportDetailsDialog from '@/components/landing/ReportDetailsDialog.vue';
import type {
    LandingReport,
    ReportTypeFilter,
} from '@/components/landing/fixtures';

const props = defineProps<{
    reports: LandingReport[];
    categories: { id: number; name: string }[];
    statusOptions: Record<string, string>;
    typeOptions: Record<string, string>;
}>();

const user = computed(() => usePage().props.auth.user ?? null);

const searchQuery = ref('');
const activeType = ref<ReportTypeFilter>('all');
const activeCategory = ref<number | 'all'>('all');
const selectedReport = ref<LandingReport | null>(null);

/**
 * The landing page ships a small "recent" slice from the server and filters it
 * in the browser: search, type, and category never trigger a request. Discover
 * (`items/Index`) remains the paginated, server-filtered listing.
 */
const filteredReports = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();

    return props.reports.filter((report) => {
        if (activeType.value !== 'all' && report.type !== activeType.value) {
            return false;
        }

        if (
            activeCategory.value !== 'all' &&
            report.category.id !== activeCategory.value
        ) {
            return false;
        }

        if (!query) {
            return true;
        }

        return [
            report.title,
            report.description,
            report.category.name,
            report.location.name,
        ].some((field) => field.toLowerCase().includes(query));
    });
});

const filtersActive = computed(
    () =>
        searchQuery.value.trim() !== '' ||
        activeType.value !== 'all' ||
        activeCategory.value !== 'all',
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

function handleSelectCategory(category: number): void {
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

    <div>
        <a
            href="#recent-reports"
            class="sr-only focus:not-sr-only focus:absolute focus:top-3 focus:left-3 focus:z-50 focus:rounded-md focus:bg-background focus:px-3 focus:py-2 focus:text-sm focus:shadow-xs focus:outline-none"
        >
            Skip to reports
        </a>

        <main>
            <LandingHero :user="user" @search="handleSearch" />

            <LandingCategories
                :categories="categories"
                :active-category="activeCategory"
                @select="handleSelectCategory"
            />

            <LandingRecentReports
                :reports="filteredReports"
                :total-count="reports.length"
                :active-type="activeType"
                :has-filters="filtersActive"
                :type-options="typeOptions"
                :status-options="statusOptions"
                @set-type="activeType = $event"
                @show-all="showAllReports"
                @open="selectedReport = $event"
            />

            <LandingHowItWorks />

            <LandingCta :user="user" />
        </main>

        <ReportDetailsDialog
            :report="selectedReport"
            :user="user"
            :type-options="typeOptions"
            :status-options="statusOptions"
            @close="selectedReport = null"
        />
    </div>
</template>
