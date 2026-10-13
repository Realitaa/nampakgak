<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Loader2 } from '@lucide/vue';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { ItemType } from '@/types';

const props = defineProps<{
    open: boolean;
    reportId: number | null;
    reportTitle: string;
    /**
     * Delete endpoint for the report. Passed in so the same dialog serves both
     * the lost and found listing pages without importing either route set.
     */
    destroyUrl: string;
    /** Labels the copy for the report kind being removed. */
    type: ItemType;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const processing = ref(false);
const error = ref<string | null>(null);

const typeLabel = { lost: 'kehilangan', found: 'penemuan' } as const;

watch(
    () => props.open,
    (open) => {
        if (open) {
            error.value = null;
        }
    },
);

function close(): void {
    if (processing.value) {
        return;
    }

    emit('update:open', false);
}

function confirm(): void {
    if (processing.value || props.reportId === null) {
        return;
    }

    processing.value = true;
    error.value = null;

    router.delete(props.destroyUrl, {
        preserveScroll: true,
        onSuccess: () => emit('update:open', false),
        onError: () => {
            error.value = `Laporan ${typeLabel[props.type]} gagal dihapus. Silakan coba lagi.`;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>Hapus laporan ini?</DialogTitle>
                <DialogDescription>
                    Laporan
                    <span class="font-medium text-foreground">
                        “{{ reportTitle }}”
                    </span>
                    akan dihapus permanen beserta fotonya. Tindakan ini tidak
                    dapat dibatalkan.
                </DialogDescription>
            </DialogHeader>

            <InputError :message="error ?? undefined" />

            <DialogFooter>
                <Button
                    type="button"
                    variant="outline"
                    :disabled="processing"
                    @click="close"
                >
                    Batal
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    :disabled="processing"
                    @click="confirm"
                >
                    <Loader2
                        v-if="processing"
                        class="animate-spin"
                        aria-hidden="true"
                    />
                    {{ processing ? 'Menghapus…' : 'Hapus' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
