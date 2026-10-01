@php
    // Props: $cancel (route URL for the Batal/Kembali link), $label (submit label, default 'Simpan')
    $submitLabel = $label ?? 'Simpan';
@endphp
<div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-end">
    <a href="{{ $cancel }}"
       class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
        <i class="fa-solid fa-xmark"></i>
        Batal
    </a>
    <button type="submit"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
        <i class="fa-solid fa-floppy-disk"></i>
        {{ $submitLabel }}
    </button>
</div>
