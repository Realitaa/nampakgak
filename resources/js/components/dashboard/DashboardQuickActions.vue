<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Hand, Search, SearchX } from '@lucide/vue';
import { ref } from 'vue';
import ReportComingSoonDialog from '@/components/dashboard/ReportComingSoonDialog.vue';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';

const reportDialogOpen = ref(false);
</script>

<template>
    <section
        class="rounded-xl border bg-card p-5 shadow-xs"
        aria-label="Quick actions"
    >
        <h2 class="text-sm font-medium">Quick actions</h2>
        <div
            class="mt-3 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center"
        >
            <Button variant="outline" @click="reportDialogOpen = true">
                <SearchX aria-hidden="true" />
                Report a Lost Item
            </Button>
            <Button @click="reportDialogOpen = true">
                <Hand aria-hidden="true" />
                Report a Found Item
            </Button>
            <Button variant="ghost" as-child class="sm:ml-auto">
                <Link :href="home()">
                    <Search aria-hidden="true" />
                    Browse Reports
                </Link>
            </Button>
        </div>

        <ReportComingSoonDialog v-model:open="reportDialogOpen" />
    </section>
</template>
