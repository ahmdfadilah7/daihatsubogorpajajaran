@extends('layouts.admin')

@section('title', 'Kuis')
@section('heading', 'Pertanyaan Kuis')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.quiz-questions.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            <i class="fa-solid fa-plus"></i> Tambah Pertanyaan
        </a>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 overflow-x-auto">
        <table id="quiz-questions-table" @if ($questions->count()) data-dt data-dt-nosort="3" @endif class="w-full text-sm">
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
                            <a href="{{ route('admin.quiz-questions.edit', $q) }}" class="font-medium text-brand-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.quiz-questions.destroy', $q) }}" method="POST" class="inline" data-confirm="Pertanyaan ini beserta seluruh pilihannya akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.">
                                @csrf @method('DELETE')
                                <button class="ml-2 font-medium text-red-600 hover:underline">Hapus</button>
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
