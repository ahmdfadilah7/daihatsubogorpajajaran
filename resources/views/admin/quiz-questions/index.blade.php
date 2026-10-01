@extends('layouts.admin')

@section('title', 'Kuis')
@section('heading', 'Pertanyaan Kuis')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.quiz-questions.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Pertanyaan
        </a>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Pertanyaan</th>
                    <th class="px-4 py-3">Ikon</th>
                    <th class="px-4 py-3">Jumlah Pilihan</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($questions as $q)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $q->question }}</td>
                        <td class="px-4 py-3"><i class="fa-solid {{ $q->icon }}"></i> <span class="text-xs text-slate-500">{{ $q->icon }}</span></td>
                        <td class="px-4 py-3">{{ $q->options_count }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.quiz-questions.edit', $q) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.quiz-questions.destroy', $q) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pertanyaan ini beserta pilihannya?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline ml-2">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">Belum ada data pertanyaan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
