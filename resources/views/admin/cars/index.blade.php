@extends('layouts.admin')

@section('title', 'Mobil')
@section('heading', 'Mobil')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.cars.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
            <i class="fa-solid fa-plus"></i> Tambah Mobil
        </a>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 overflow-x-auto">
        <table id="cars-table" @if ($cars->count()) data-dt data-dt-nosort="5,6" @endif class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Model</th>
                    <th class="px-4 py-3">Tipe</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Tahun</th>
                    <th class="px-4 py-3">Harga</th>
                    <th class="px-4 py-3">Aksen</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($cars as $car)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $car->model }}</td>
                        <td class="px-4 py-3">{{ $car->type }}</td>
                        <td class="px-4 py-3">{{ $car->category }}</td>
                        <td class="px-4 py-3">{{ $car->year }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($car->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block w-4 h-4 rounded-full align-middle" style="background: {{ $car->accent1 }}"></span>
                            <span class="inline-block w-4 h-4 rounded-full align-middle" style="background: {{ $car->accent2 }}"></span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.cars.edit', $car) }}" class="font-medium text-brand-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.cars.destroy', $car) }}" method="POST" class="inline" data-confirm="Mobil ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.">
                                @csrf @method('DELETE')
                                <button class="ml-2 font-medium text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">Belum ada data mobil.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
