<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { home } from '@/routes';

const open = defineModel<boolean>('open', { required: true });
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="max-w-sm gap-0">
            <DialogHeader>
                <DialogTitle class="text-left"
                    >Report form coming soon</DialogTitle
                >
                <DialogDescription class="pt-1 text-left leading-relaxed">
                    Reporting isn't wired up yet. Once the reports module ships,
                    this button opens the form to submit a lost or found item —
                    with photos, location, and contact details.
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="mt-2 gap-2 sm:justify-start">
                <Button variant="outline" as-child>
                    <Link :href="home()">Browse reports meanwhile</Link>
                </Button>
                <DialogClose as-child>
                    <Button variant="ghost">Close</Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
