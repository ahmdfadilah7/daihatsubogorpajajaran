{{-- Global delete-confirmation modal.
     Included once in the admin layout. Driven by the Alpine `confirmDialog`
     store (see resources/js/confirm-delete.js). A single delegated submit
     listener on `document` opens this modal for any `<form data-confirm="...">`
     and gates the actual deletion behind the "Hapus" button. --}}
<div
    x-data
    x-cloak
    x-show="$store.confirmDialog.show"
    x-transition.opacity
    @keydown.escape.window="$store.confirmDialog.close()"
    class="fixed inset-0 z-[60] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="confirm-dialog-title"
    aria-describedby="confirm-dialog-message"
>
    {{-- Backdrop (click to cancel) --}}
    <div
        class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
        @click="$store.confirmDialog.close()"
    ></div>

    {{-- Card --}}
    <div
        x-show="$store.confirmDialog.show"
        x-transition
        x-trap.noscroll="$store.confirmDialog.show"
        class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5"
    >
        <div class="flex items-start gap-4 p-6">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                <i class="fa-solid fa-trash-can text-lg"></i>
            </span>
            <div class="min-w-0 flex-1">
                <h2 id="confirm-dialog-title" class="text-lg font-bold text-slate-900">Hapus data?</h2>
                <p id="confirm-dialog-message" class="mt-1 text-sm text-slate-600" x-text="$store.confirmDialog.message"></p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
            <button
                type="button"
                @click="$store.confirmDialog.close()"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
            >
                Batal
            </button>
            <button
                type="button"
                x-ref="confirmButton"
                x-init="$watch('$store.confirmDialog.show', value => { if (value) $nextTick(() => $refs.confirmButton.focus()); })"
                @click="$store.confirmDialog.confirm()"
                class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
            >
                <i class="fa-solid fa-trash-can"></i>
                Hapus
            </button>
        </div>
    </div>
</div>
