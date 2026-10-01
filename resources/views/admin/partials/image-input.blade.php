@php
    // Props: $name (string field: img or src), $label, $value (current string)
    $current = old($name, $value ?? '');
    $currentUrl = !empty($value)
        ? (\Illuminate\Support\Str::startsWith($value, ['http://', 'https://']) ? $value : asset($value))
        : null;
@endphp
<div class="space-y-3" x-data="{ preview: null, fileName: '' }">
    <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>

    <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-slate-50/60 p-4 sm:flex-row sm:items-start">
        {{-- Live thumbnail: newly chosen file wins, else the current stored image --}}
        <div class="shrink-0">
            <div class="flex h-28 w-28 items-center justify-center overflow-hidden rounded-lg border border-dashed border-slate-300 bg-white">
                <template x-if="preview">
                    <img :src="preview" alt="Pratinjau gambar baru" class="h-full w-full object-cover">
                </template>
                <template x-if="!preview">
                    @if ($currentUrl)
                        <img src="{{ $currentUrl }}" alt="Gambar saat ini" class="h-full w-full object-cover">
                    @else
                        <span class="flex flex-col items-center gap-1 text-slate-300">
                            <i class="fa-regular fa-image text-2xl"></i>
                            <span class="text-[10px] uppercase tracking-wide">Tanpa gambar</span>
                        </span>
                    @endif
                </template>
            </div>
            @if ($currentUrl)
                <a href="{{ $currentUrl }}" target="_blank"
                   class="mt-1.5 block max-w-28 truncate text-center text-[11px] text-brand-600 hover:underline"
                   x-show="!preview">Lihat gambar</a>
            @endif
        </div>

        <div class="flex-1 space-y-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">URL atau path gambar</label>
                <input type="text" name="{{ $name }}" value="{{ $current }}"
                       placeholder="URL (https://...) atau path (img/.. , storage/..)"
                       class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 @error($name) border-red-400 @enderror">
                @error($name)
                    <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">atau unggah berkas</label>
                <input type="file" name="image" accept="image/*"
                       @change="const f = $event.target.files[0]; preview = f ? URL.createObjectURL(f) : null; fileName = f ? f.name : ''"
                       class="w-full cursor-pointer text-sm text-slate-600 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-600 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-700">
                <p class="mt-1 text-[11px] text-slate-400">
                    <span x-show="fileName" x-text="'Berkas dipilih: ' + fileName"></span>
                    <span x-show="!fileName">PNG, JPG, atau WEBP. Berkas baru akan menggantikan gambar saat ini.</span>
                </p>
                @error('image')
                    <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </p>
                @enderror
            </div>
        </div>
    </div>
</div>
