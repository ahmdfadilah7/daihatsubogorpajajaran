@extends('layouts.admin')

@section('title', 'Gaya Kategori')
@section('heading', 'Gaya Kategori')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.category-styles.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            <i class="fa-solid fa-plus"></i> Tambah Kategori
        </a>
    </div>

    <div x-data="bulkSelect()">
    @include('admin.partials.bulk-toolbar', ['route' => 'admin.category-styles.bulk-destroy'])

    <div class="bg-white rounded-lg border border-slate-200 overflow-x-auto">
        <table id="category-styles-table" @if ($categoryStyles->count()) data-dt data-dt-nosort="0,4" @endif class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" @change="toggleAllOnPage($event)" :checked="allOnPageChecked"
                               class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                               aria-label="Pilih semua di halaman ini">
                    </th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Label</th>
                    <th class="px-4 py-3">Warna Latar</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($categoryStyles as $style)
                    <tr>
                        <td class="px-4 py-3">
                            <input type="checkbox" class="row-check rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                   value="{{ $style->id }}" @change="toggle('{{ $style->id }}')" :checked="isChecked('{{ $style->id }}')"
                                   aria-label="Pilih {{ $style->category }}">
                        </td>
                        <td class="px-4 py-3 font-medium">{{ $style->category }}</td>
                        <td class="px-4 py-3">{{ $style->label }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block w-4 h-4 rounded-full align-middle" style="background: {{ $style->bg }}"></span>
                            <span class="align-middle">{{ $style->bg }}</span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.category-styles.edit', $style) }}" class="font-medium text-brand-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.category-styles.destroy', $style) }}" method="POST" class="inline" data-confirm="Gaya kategori ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.">
                                @csrf @method('DELETE')
                                <button class="ml-2 font-medium text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada data kategori.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
@endsection
