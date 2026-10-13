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
import { destroy } from '@/routes/lost-items';

const props = defineProps<{
    open: boolean;
    reportId: number | null;
    reportTitle: string;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const processing = ref(false);
const error = ref<string | null>(null);

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

    router.delete(destroy.url(props.reportId), {
        preserveScroll: true,
        onSuccess: () => emit('update:open', false),
        onError: () => {
            error.value = 'Laporan gagal dihapus. Silakan coba lagi.';
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
