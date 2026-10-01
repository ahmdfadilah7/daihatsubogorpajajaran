@extends('layouts.admin')

@section('title', 'Testimoni')
@section('heading', 'Testimoni')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.testimonials.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            <i class="fa-solid fa-plus"></i> Tambah Testimoni
        </a>
    </div>

    <div x-data="bulkSelect()">
    @include('admin.partials.bulk-toolbar', ['route' => 'admin.testimonials.bulk-destroy'])

    <div class="bg-white rounded-lg border border-slate-200 overflow-x-auto">
        <table id="testimonials-table" @if ($testimonials->count()) data-dt data-dt-nosort="0,5" @endif class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" @change="toggleAllOnPage($event)" :checked="allOnPageChecked"
                               class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                               aria-label="Pilih semua di halaman ini">
                    </th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Kota</th>
                    <th class="px-4 py-3">Mobil</th>
                    <th class="px-4 py-3">Rating</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($testimonials as $t)
                    <tr>
                        <td class="px-4 py-3">
                            <input type="checkbox" class="row-check rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                   value="{{ $t->id }}" @change="toggle('{{ $t->id }}')" :checked="isChecked('{{ $t->id }}')"
                                   aria-label="Pilih {{ $t->name }}">
                        </td>
                        <td class="px-4 py-3 font-medium">{{ $t->name }}</td>
                        <td class="px-4 py-3">{{ $t->city }}</td>
                        <td class="px-4 py-3">{{ $t->car }}</td>
                        <td class="px-4 py-3">{{ str_repeat('★', $t->rating) }}{{ str_repeat('☆', 5 - $t->rating) }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.testimonials.edit', $t) }}" class="font-medium text-brand-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.testimonials.destroy', $t) }}" method="POST" class="inline" data-confirm="Testimoni ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.">
                                @csrf @method('DELETE')
                                <button class="ml-2 font-medium text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">Belum ada data testimoni.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
@endsection
