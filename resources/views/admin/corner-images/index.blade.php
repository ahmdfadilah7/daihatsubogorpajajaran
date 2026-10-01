@extends('layouts.admin')

@section('title', 'Gambar Pojok')
@section('heading', 'Gambar Pojok')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.corner-images.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Gambar
        </a>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
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
                            <img src="{{ \Illuminate\Support\Str::startsWith($img->src, ['http://','https://']) ? $img->src : asset($img->src) }}" alt="{{ $img->alt }}" class="h-12 w-12 object-cover rounded">
                        </td>
                        <td class="px-4 py-3">{{ $img->alt }}</td>
                        <td class="px-4 py-3 break-all text-xs text-slate-500">{{ $img->src }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.corner-images.edit', $img) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.corner-images.destroy', $img) }}" method="POST" class="inline" onsubmit="return confirm('Hapus gambar ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline ml-2">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">Belum ada data gambar pojok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
