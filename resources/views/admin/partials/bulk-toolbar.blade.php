{{--
    Bulk-delete toolbar for the admin index pages.

    Renders the selected-row counter, the "Hapus Terpilih" button (disabled when
    nothing is selected), and the hidden DELETE form whose `ids[]` inputs the
    `bulkSelect()` Alpine component injects on confirm. Place this INSIDE the
    `<div x-data="bulkSelect()">` wrapper, above the table.

    Params:
      - route : named bulk-destroy route, e.g. 'admin.cars.bulk-destroy'
--}}
<div class="mb-3 flex items-center gap-3" x-cloak x-show="count > 0" x-transition>
    <p class="text-sm text-slate-600">
        <span class="font-semibold text-slate-800" x-text="count"></span> data terpilih
    </p>
    <button type="button"
            @click="openConfirm()"
            :disabled="count === 0"
            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
        Hapus Terpilih
    </button>
</div>

<form method="POST" action="{{ route($route) }}" data-bulk-form class="hidden">
    @csrf
    @method('DELETE')
    <div data-bulk-ids></div>
</form>
