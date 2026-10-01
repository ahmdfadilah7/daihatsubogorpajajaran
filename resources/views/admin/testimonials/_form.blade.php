@php
    $inputClass = 'w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500';
    $errClass = 'border-red-400 focus:border-red-500 focus:ring-red-500';
    $ratingValue = (int) old('rating', $testimonial->rating ?: 5);
@endphp

<div class="space-y-8">
    {{-- Identitas --}}
    <x-admin.form-section title="Identitas" subtitle="Nama pelanggan dan mobil yang dibeli" icon="fa-user">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Nama <span class="text-brand-600">*</span></label>
            <input type="text" name="name" value="{{ old('name', $testimonial->name) }}" class="{{ $inputClass }} @error('name') {{ $errClass }} @enderror">
            @error('name')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Kota <span class="text-brand-600">*</span></label>
            <input type="text" name="city" value="{{ old('city', $testimonial->city) }}" class="{{ $inputClass }} @error('city') {{ $errClass }} @enderror">
            @error('city')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Mobil <span class="text-brand-600">*</span></label>
            <input type="text" name="car" value="{{ old('car', $testimonial->car) }}" class="{{ $inputClass }} @error('car') {{ $errClass }} @enderror">
            @error('car')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            {{-- Alpine star picker: writes the 1..5 value to a hidden `rating` input. --}}
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Rating <span class="text-brand-600">*</span></label>
            <div x-data="{ rating: {{ $ratingValue }}, hover: 0 }" class="flex items-center gap-3">
                <input type="hidden" name="rating" :value="rating">
                <div class="flex items-center gap-1">
                    <template x-for="star in [1,2,3,4,5]" :key="star">
                        <button type="button"
                                @click="rating = star"
                                @mouseenter="hover = star"
                                @mouseleave="hover = 0"
                                :aria-label="`Beri ${star} bintang`"
                                class="text-2xl transition focus:outline-none"
                                :class="(hover || rating) >= star ? 'text-amber-400' : 'text-slate-300'">
                            <i class="fa-solid fa-star"></i>
                        </button>
                    </template>
                </div>
                <span class="text-sm font-medium text-slate-500" x-text="rating + ' / 5'"></span>
            </div>
            @error('rating')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>

    {{-- Konten & Tampilan --}}
    <x-admin.form-section title="Konten & Tampilan" subtitle="Kutipan testimoni, warna, dan foto" icon="fa-comment-dots">
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Teks Testimoni <span class="text-brand-600">*</span></label>
            <textarea name="text" rows="4" class="{{ $inputClass }} @error('text') {{ $errClass }} @enderror">{{ old('text', $testimonial->text) }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Kutipan pengalaman pelanggan yang akan ditampilkan di situs.</p>
            @error('text')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        @include('admin.partials.color-input', ['name' => 'color', 'label' => 'Warna', 'value' => $testimonial->color])
        <div></div>
        <div class="md:col-span-2">
            @include('admin.partials.image-input', ['name' => 'img', 'label' => 'Foto', 'value' => $testimonial->img])
        </div>
    </x-admin.form-section>
</div>

@include('admin.partials.form-actions', ['cancel' => route('admin.testimonials.index')])
