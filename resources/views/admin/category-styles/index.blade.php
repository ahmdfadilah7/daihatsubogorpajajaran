@extends('layouts.admin')

@section('title', 'Gaya Kategori')
@section('heading', 'Gaya Kategori')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.category-styles.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Kategori
        </a>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Label</th>
                    <th class="px-4 py-3">Warna Latar</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($categoryStyles as $style)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $style->category }}</td>
                        <td class="px-4 py-3">{{ $style->label }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block w-4 h-4 rounded-full align-middle" style="background: {{ $style->bg }}"></span>
                            <span class="align-middle">{{ $style->bg }}</span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.category-styles.edit', $style) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.category-styles.destroy', $style) }}" method="POST" class="inline" onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline ml-2">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">Belum ada data kategori.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
