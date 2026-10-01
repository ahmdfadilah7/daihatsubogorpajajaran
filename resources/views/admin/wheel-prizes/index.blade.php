@extends('layouts.admin')

@section('title', 'Hadiah Roda')
@section('heading', 'Hadiah Roda')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.wheel-prizes.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Hadiah
        </a>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Label</th>
                    <th class="px-4 py-3">Teks Singkat</th>
                    <th class="px-4 py-3">Warna</th>
                    <th class="px-4 py-3">Bobot</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($prizes as $prize)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $prize->label }}</td>
                        <td class="px-4 py-3 whitespace-pre-line">{{ $prize->short }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block w-4 h-4 rounded-full align-middle" style="background: {{ $prize->color }}"></span>
                            <span class="align-middle">{{ $prize->color }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $prize->weight }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.wheel-prizes.edit', $prize) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.wheel-prizes.destroy', $prize) }}" method="POST" class="inline" onsubmit="return confirm('Hapus hadiah ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline ml-2">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada data hadiah.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
