@extends('layouts.admin')

@section('title', 'Gambar Pojok')
@section('heading', 'Gambar Pojok')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.corner-images.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            <i class="fa-solid fa-plus"></i> Tambah Gambar
        </a>
    </div>

    <div x-data="bulkSelect()">
    @include('admin.partials.bulk-toolbar', ['route' => 'admin.corner-images.bulk-destroy'])

    <div class="bg-white rounded-lg border border-slate-200 overflow-x-auto">
        <table id="corner-images-table" @if ($cornerImages->count()) data-dt data-dt-nosort="0,1,4" @endif class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" @change="toggleAllOnPage($event)" :checked="allOnPageChecked"
                               class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                               aria-label="Pilih semua di halaman ini">
                    </th>
                    <th class="px-4 py-3">Pratinjau</th>
                    <th class="px-4 py-3">Teks Alternatif</th>
                    <th class="px-4 py-3">Sumber</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($cornerImages as $img)
                    <tr>
                        <td class="px-4 py-3">
                            <input type="checkbox" class="row-check rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                   value="{{ $img->id }}" @change="toggle('{{ $img->id }}')" :checked="isChecked('{{ $img->id }}')"
                                   aria-label="Pilih {{ $img->alt }}">
                        </td>
                        <td class="px-4 py-3">
                            <img src="{{ \Illuminate\Support\Str::startsWith($img->src, ['http://','https://']) ? $img->src : asset($img->src) }}" alt="{{ $img->alt }}" class="h-12 w-12 object-cover rounded">
                        </td>
                        <td class="px-4 py-3">{{ $img->alt }}</td>
                        <td class="px-4 py-3 break-all text-xs text-slate-500">{{ $img->src }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.corner-images.edit', $img) }}" class="font-medium text-brand-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.corner-images.destroy', $img) }}" method="POST" class="inline" data-confirm="Gambar pojok ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.">
                                @csrf @method('DELETE')
                                <button class="ml-2 font-medium text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada data gambar pojok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
@endsection
