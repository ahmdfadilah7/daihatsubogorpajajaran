@extends('layouts.admin')

@section('title', 'Testimoni')
@section('heading', 'Testimoni')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.testimonials.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Testimoni
        </a>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
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
                        <td class="px-4 py-3 font-medium">{{ $t->name }}</td>
                        <td class="px-4 py-3">{{ $t->city }}</td>
                        <td class="px-4 py-3">{{ $t->car }}</td>
                        <td class="px-4 py-3">{{ str_repeat('★', $t->rating) }}{{ str_repeat('☆', 5 - $t->rating) }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.testimonials.edit', $t) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.testimonials.destroy', $t) }}" method="POST" class="inline" onsubmit="return confirm('Hapus testimoni ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline ml-2">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada data testimoni.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
