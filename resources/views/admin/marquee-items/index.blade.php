@extends('layouts.admin')

@section('title', 'Teks Berjalan')
@section('heading', 'Teks Berjalan')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.marquee-items.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            <i class="fa-solid fa-plus"></i> Tambah Teks
        </a>
    </div>

    <div x-data="bulkSelect()">
    @include('admin.partials.bulk-toolbar', ['route' => 'admin.marquee-items.bulk-destroy'])

    <div class="bg-white rounded-lg border border-slate-200 overflow-x-auto">
        <table id="marquee-items-table" @if ($items->count()) data-dt data-dt-nosort="0,2,3,4" @endif class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" @change="toggleAllOnPage($event)" :checked="allOnPageChecked"
                               class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                               aria-label="Pilih semua di halaman ini">
                    </th>
                    <th class="px-4 py-3">Teks</th>
                    <th class="px-4 py-3">Ikon</th>
                    <th class="px-4 py-3">Warna</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($items as $item)
                    <tr>
                        <td class="px-4 py-3">
                            <input type="checkbox" class="row-check rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                   value="{{ $item->id }}" @change="toggle('{{ $item->id }}')" :checked="isChecked('{{ $item->id }}')"
                                   aria-label="Pilih {{ $item->text }}">
                        </td>
                        <td class="px-4 py-3 font-medium">
                            <span class="inline-block w-4 h-4 rounded-full align-middle" style="background: {{ $item->color }}"></span>
                            <span class="align-middle">{{ $item->text }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <i class="fa-solid {{ $item->icon }}" style="color: {{ $item->color }}"></i>
                            <span class="ml-1 align-middle text-slate-500">{{ $item->icon }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block w-4 h-4 rounded-full align-middle" style="background: {{ $item->color }}"></span>
                            <span class="align-middle">{{ $item->color }}</span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.marquee-items.edit', $item) }}" class="font-medium text-brand-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.marquee-items.destroy', $item) }}" method="POST" class="inline" data-confirm="Teks berjalan ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.">
                                @csrf @method('DELETE')
                                <button class="ml-2 font-medium text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada teks berjalan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
@endsection
