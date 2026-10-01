@extends('layouts.admin')

@section('title', 'Pengguna')
@section('heading', 'Pengguna')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            <i class="fa-solid fa-plus"></i> Tambah Pengguna
        </a>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 overflow-x-auto">
        <table id="users-table" @if ($users->count()) data-dt data-dt-nosort="3" @endif class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Dibuat</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium">
                            {{ $user->name }}
                            @if ($user->id === auth()->id())
                                <span class="ml-1.5 rounded-full bg-brand-50 px-2 py-0.5 text-[11px] font-semibold text-brand-600">Anda</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->created_at?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-brand-600 hover:underline">Edit</a>
                            @if ($user->id === auth()->id())
                                <span class="ml-2 cursor-not-allowed font-medium text-slate-300" title="Anda tidak dapat menghapus akun Anda sendiri">Hapus</span>
                            @else
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" data-confirm="Pengguna ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.">
                                    @csrf @method('DELETE')
                                    <button class="ml-2 font-medium text-red-600 hover:underline">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">Belum ada data pengguna.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
