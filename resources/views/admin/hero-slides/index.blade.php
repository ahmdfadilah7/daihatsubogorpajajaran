@extends('layouts.admin')

@section('title', 'Slide Hero')
@section('heading', 'Slide Hero')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.hero-slides.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            <i class="fa-solid fa-plus"></i> Tambah Slide
        </a>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 overflow-x-auto">
        <table id="hero-slides-table" @if ($heroSlides->count()) data-dt data-dt-nosort="0,4" @endif class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Pratinjau</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Tag</th>
                    <th class="px-4 py-3">Harga</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($heroSlides as $slide)
                    <tr>
                        <td class="px-4 py-3">
                            <img src="{{ \Illuminate\Support\Str::startsWith($slide->img, ['http://','https://']) ? $slide->img : asset($slide->img) }}" alt="{{ $slide->name }}" class="h-12 w-20 object-cover rounded">
                        </td>
                        <td class="px-4 py-3 font-medium">{{ $slide->name }}</td>
                        <td class="px-4 py-3">{{ $slide->tag }}</td>
                        <td class="px-4 py-3">{{ $slide->price }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.hero-slides.edit', $slide) }}" class="font-medium text-brand-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.hero-slides.destroy', $slide) }}" method="POST" class="inline" data-confirm="Slide hero ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.">
                                @csrf @method('DELETE')
                                <button class="ml-2 font-medium text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada data slide.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
