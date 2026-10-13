<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Hand, Search, SearchX } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { create as foundCreate } from '@/routes/found-items';
import { index as itemsIndex } from '@/routes/items';
import { create as lostCreate } from '@/routes/lost-items';
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
            <Button variant="outline" as-child>
                <Link :href="lostCreate()">
                    <SearchX aria-hidden="true" />
                    Report a Lost Item
                </Link>
            </Button>
            <Button as-child>
                <Link :href="foundCreate()">
                    <Hand aria-hidden="true" />
                    Report a Found Item
                </Link>
            </Button>
            <Button variant="ghost" as-child class="sm:ml-auto">
                <Link :href="itemsIndex()">
                    <Search aria-hidden="true" />
                    Browse Reports
                </Link>
            </Button>
        </div>
    </section>
</template>
