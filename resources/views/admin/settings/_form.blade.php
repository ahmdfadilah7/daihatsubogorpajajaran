@php
    $inputClass = 'w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500';
    $errClass = 'border-red-400 focus:border-red-500 focus:ring-red-500';
    $val = fn ($key) => old($key, $settings[$key] ?? '');
@endphp

<div class="space-y-10">
    {{-- ============================ Identitas Situs ============================ --}}
    <x-admin.form-section title="Identitas Situs" subtitle="Nama, tagline, logo, dan favicon situs." icon="fa-id-badge">
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Nama Website</label>
            <input type="text" name="site_name" value="{{ $val('site_name') }}" placeholder="Daihatsu Sahabat" class="{{ $inputClass }} @error('site_name') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Ditampilkan pada navbar dan footer situs.</p>
            @error('site_name')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tagline <span class="text-slate-400 font-normal">(opsional)</span></label>
            <input type="text" name="tagline" value="{{ $val('tagline') }}" placeholder="Sahabat di setiap perjalanan" class="{{ $inputClass }} @error('tagline') {{ $errClass }} @enderror">
            @error('tagline')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            @include('admin.partials.image-input', ['name' => 'logo', 'label' => 'Logo', 'value' => $settings['logo'] ?? '', 'fileName' => 'logo_file'])
        </div>
        <div class="md:col-span-2">
            @include('admin.partials.image-input', ['name' => 'favicon', 'label' => 'Favicon', 'value' => $settings['favicon'] ?? '', 'fileName' => 'favicon_file'])
        </div>
    </x-admin.form-section>

    {{-- ============================ SEO / Meta ============================ --}}
    <x-admin.form-section title="SEO / Meta" subtitle="Judul, deskripsi, kata kunci, dan penulis untuk mesin pencari." icon="fa-magnifying-glass">
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Meta (Title)</label>
            <input type="text" name="meta_title" value="{{ $val('meta_title') }}" placeholder="Daihatsu Sahabat | Dealer Resmi Daihatsu" class="{{ $inputClass }} @error('meta_title') {{ $errClass }} @enderror">
            @error('meta_title')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Deskripsi Meta</label>
            <textarea name="meta_description" rows="3" placeholder="Deskripsi singkat situs untuk hasil pencarian (maks. 300 karakter)" class="{{ $inputClass }} @error('meta_description') {{ $errClass }} @enderror">{{ $val('meta_description') }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Maksimal 300 karakter.</p>
            @error('meta_description')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Kata Kunci Meta</label>
            <input type="text" name="meta_keywords" value="{{ $val('meta_keywords') }}" placeholder="Daihatsu, Ayla, Sigra, ..." class="{{ $inputClass }} @error('meta_keywords') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Pisahkan dengan koma.</p>
            @error('meta_keywords')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Penulis Meta <span class="text-slate-400 font-normal">(opsional)</span></label>
            <input type="text" name="meta_author" value="{{ $val('meta_author') }}" placeholder="Daihatsu Sahabat" class="{{ $inputClass }} @error('meta_author') {{ $errClass }} @enderror">
            @error('meta_author')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>

    {{-- ============================ Social / Open Graph ============================ --}}
    <x-admin.form-section title="Social / Open Graph" subtitle="Pratinjau saat dibagikan di media sosial." icon="fa-share-nodes">
        <div class="md:col-span-2">
            @include('admin.partials.image-input', ['name' => 'og_image', 'label' => 'Gambar Open Graph (OG Image)', 'value' => $settings['og_image'] ?? '', 'fileName' => 'og_image_file'])
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul OG <span class="text-slate-400 font-normal">(opsional)</span></label>
            <input type="text" name="og_title" value="{{ $val('og_title') }}" placeholder="Kosongkan untuk memakai Judul Meta" class="{{ $inputClass }} @error('og_title') {{ $errClass }} @enderror">
            @error('og_title')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Deskripsi OG <span class="text-slate-400 font-normal">(opsional)</span></label>
            <textarea name="og_description" rows="2" placeholder="Kosongkan untuk memakai Deskripsi Meta" class="{{ $inputClass }} @error('og_description') {{ $errClass }} @enderror">{{ $val('og_description') }}</textarea>
            @error('og_description')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>

    {{-- ============================ Kontak & Lainnya ============================ --}}
    <x-admin.form-section title="Kontak &amp; Lainnya" subtitle="Informasi kontak dan tautan media sosial (opsional)." icon="fa-address-book">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">WhatsApp</label>
            <input type="text" name="contact_whatsapp" value="{{ $val('contact_whatsapp') }}" placeholder="+62 812 3456 7890" class="{{ $inputClass }} @error('contact_whatsapp') {{ $errClass }} @enderror">
            @error('contact_whatsapp')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
            <input type="text" name="contact_email" value="{{ $val('contact_email') }}" placeholder="halo@example.com" class="{{ $inputClass }} @error('contact_email') {{ $errClass }} @enderror">
            @error('contact_email')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Alamat</label>
            <input type="text" name="contact_address" value="{{ $val('contact_address') }}" placeholder="Jl. Raya Sahabat No. 88, Jakarta" class="{{ $inputClass }} @error('contact_address') {{ $errClass }} @enderror">
            @error('contact_address')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Facebook</label>
            <input type="text" name="social_facebook" value="{{ $val('social_facebook') }}" placeholder="https://facebook.com/..." class="{{ $inputClass }} @error('social_facebook') {{ $errClass }} @enderror">
            @error('social_facebook')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Instagram</label>
            <input type="text" name="social_instagram" value="{{ $val('social_instagram') }}" placeholder="https://instagram.com/..." class="{{ $inputClass }} @error('social_instagram') {{ $errClass }} @enderror">
            @error('social_instagram')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">YouTube</label>
            <input type="text" name="social_youtube" value="{{ $val('social_youtube') }}" placeholder="https://youtube.com/..." class="{{ $inputClass }} @error('social_youtube') {{ $errClass }} @enderror">
            @error('social_youtube')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>
</div>

@include('admin.partials.form-actions', ['cancel' => route('admin.settings.edit'), 'label' => 'Simpan Pengaturan'])
