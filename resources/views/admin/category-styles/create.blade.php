@extends('layouts.admin')

@section('title', 'Tambah Kategori')
@section('heading', 'Tambah Gaya Kategori')

@section('content')
    <div class="bg-white rounded-lg border border-slate-200 p-6 max-w-xl">
        <form action="{{ route('admin.category-styles.store') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium mb-1">Kategori</label>
                    <input type="text" name="category" value="{{ old('category', $categoryStyle->category) }}"
                           placeholder="LCGC / MPV / SUV / Niaga"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
                    @error('category')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Label</label>
                    <input type="text" name="label" value="{{ old('label', $categoryStyle->label) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
                    @error('label')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                @include('admin.partials.color-input', ['name' => 'bg', 'label' => 'Warna Latar', 'value' => $categoryStyle->bg])
            </div>
            <div class="mt-6 flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-5 py-2 rounded">Simpan</button>
                <a href="{{ route('admin.category-styles.index') }}" class="bg-slate-200 hover:bg-slate-300 text-sm px-5 py-2 rounded">Batal</a>
            </div>
        </form>
    </div>
@endsection
