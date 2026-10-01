@extends('layouts.admin')

@section('title', 'Slide Hero')
@section('heading', 'Slide Hero')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.hero-slides.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Slide
        </a>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
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
                            <a href="{{ route('admin.hero-slides.edit', $slide) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.hero-slides.destroy', $slide) }}" method="POST" class="inline" onsubmit="return confirm('Hapus slide ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline ml-2">Hapus</button>
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
