@php
    $inputClass = 'w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500';
    $errClass = 'border-red-400 focus:border-red-500 focus:ring-red-500';
    $val = fn ($key) => old($key, $settings[$key] ?? '');

    // Tab definitions for the settings form. Each key matches an x-show panel
    // below; the label + icon render the tab button. Keeping every panel in the
    // SAME <form> means Simpan still submits all fields regardless of the
    // active tab (x-show only hides via CSS, it does not detach the inputs).
    $tabs = [
        ['id' => 'identitas', 'label' => 'Identitas', 'icon' => 'fa-id-badge'],
        ['id' => 'seo', 'label' => 'SEO / Meta', 'icon' => 'fa-magnifying-glass'],
        ['id' => 'social', 'label' => 'Social / OG', 'icon' => 'fa-share-nodes'],
        ['id' => 'kontak', 'label' => 'Kontak', 'icon' => 'fa-address-book'],
        ['id' => 'fitur', 'label' => 'Fitur', 'icon' => 'fa-toggle-on'],
        ['id' => 'promo', 'label' => 'Promo & Navbar', 'icon' => 'fa-bullhorn'],
        ['id' => 'hero', 'label' => 'Hero', 'icon' => 'fa-star'],
        ['id' => 'bagian', 'label' => 'Bagian', 'icon' => 'fa-heading'],
        ['id' => 'kalkulator', 'label' => 'Kalkulator', 'icon' => 'fa-calculator'],
        ['id' => 'simulasi', 'label' => 'Simulasi Kredit', 'icon' => 'fa-percent'],
        ['id' => 'roda', 'label' => 'Roda', 'icon' => 'fa-trophy'],
        ['id' => 'footer', 'label' => 'Footer', 'icon' => 'fa-shoe-prints'],
    ];
@endphp

{{-- Tabbed layout: a single form, panels toggled by Alpine so the page no
     longer scrolls through every section at once. The active tab persists in
     the URL hash so a refresh / validation error keeps the user in place. --}}
<div x-data="{
        tab: (window.location.hash || '#identitas').replace('#', ''),
        valid: @js(collect($tabs)->pluck('id')->all()),
        init() { if (!this.valid.includes(this.tab)) this.tab = 'identitas'; },
        go(id) { this.tab = id; history.replaceState(null, '', '#' + id); },
     }">
    {{-- Tab bar (horizontally scrollable on small screens, sticks under the topbar) --}}
    <div class="sticky top-[57px] z-10 -mx-4 mb-6 border-b border-slate-200 bg-slate-100/95 px-4 backdrop-blur sm:-mx-6 sm:px-6">
        <nav class="flex gap-1 overflow-x-auto py-2" aria-label="Bagian pengaturan">
            @foreach ($tabs as $t)
                <button type="button" @click="go('{{ $t['id'] }}')"
                        :class="tab === '{{ $t['id'] }}'
                            ? 'bg-brand-600 text-white shadow-sm'
                            : 'text-slate-600 hover:bg-white hover:text-brand-600'"
                        class="flex shrink-0 items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold transition">
                    <i class="fa-solid {{ $t['icon'] }}" aria-hidden="true"></i>
                    <span>{{ $t['label'] }}</span>
                </button>
            @endforeach
        </nav>
    </div>

    {{-- ============================ Identitas Situs ============================ --}}
    <div x-show="tab === 'identitas'" x-cloak>
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
    </div>

    {{-- ============================ SEO / Meta ============================ --}}
    <div x-show="tab === 'seo'" x-cloak>
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
    </div>

    {{-- ============================ Social / Open Graph ============================ --}}
    <div x-show="tab === 'social'" x-cloak>
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
    </div>

    {{-- ============================ Kontak & Lainnya ============================ --}}
    <div x-show="tab === 'kontak'" x-cloak>
    <x-admin.form-section title="Kontak &amp; Lainnya" subtitle="Informasi kontak dan tautan media sosial (opsional)." icon="fa-address-book">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">WhatsApp</label>
            <input type="text" name="contact_whatsapp" value="{{ $val('contact_whatsapp') }}" placeholder="+62 812 3456 7890" class="{{ $inputClass }} @error('contact_whatsapp') {{ $errClass }} @enderror">
            @error('contact_whatsapp')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Telepon</label>
            <input type="text" name="contact_phone" value="{{ $val('contact_phone') }}" placeholder="+62 21 5000 1234" class="{{ $inputClass }} @error('contact_phone') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Nomor telepon untuk tombol Telepon di footer. Kosongkan untuk memakai nomor WhatsApp.</p>
            @error('contact_phone')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
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
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">TikTok</label>
            <input type="text" name="social_tiktok" value="{{ $val('social_tiktok') }}" placeholder="https://tiktok.com/@..." class="{{ $inputClass }} @error('social_tiktok') {{ $errClass }} @enderror">
            @error('social_tiktok')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Google Maps</label>
            <input type="text" name="maps_url" value="{{ $val('maps_url') }}" placeholder="https://www.google.com/maps?q=..." class="{{ $inputClass }} @error('maps_url') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Tempel tautan Google Maps lokasi showroom. Dipakai untuk peta dan tombol &quot;Buka di Google Maps&quot; di footer.</p>
            @error('maps_url')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>
    </div>

    {{-- ============================ Fitur Situs ============================ --}}
    <div x-show="tab === 'fitur'" x-cloak>
    @php
        // Flags default to ENABLED when the key is missing or empty so the
        // site looks unchanged until the admin turns a feature off.
        $flagOn = function ($key) use ($settings) {
            $raw = old($key, $settings[$key] ?? '1');
            return in_array(strtolower(trim((string) $raw)), ['1', 'true', 'on', 'yes'], true);
        };
        $features = [
            ['key' => 'feature_quiz', 'label' => 'Kuis', 'help' => "Bagian 'Cari Mobil Idealmu' di halaman utama.", 'icon' => 'fa-wand-magic-sparkles'],
            ['key' => 'feature_corner', 'label' => 'Gambar Pojok', 'help' => 'Widget gambar melayang di pojok kanan.', 'icon' => 'fa-image'],
            ['key' => 'feature_wheel', 'label' => 'Hadiah Roda', 'help' => 'Tombol & modal roda keberuntungan.', 'icon' => 'fa-trophy'],
        ];
    @endphp
    <x-admin.form-section title="Fitur Situs" subtitle="Aktifkan atau nonaktifkan fitur interaktif di situs." icon="fa-toggle-on">
        @foreach ($features as $feature)
            <div class="md:col-span-2">
                <label for="{{ $feature['key'] }}" class="flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-4 cursor-pointer transition hover:border-brand-300 hover:bg-slate-50">
                    <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                        <i class="fa-solid {{ $feature['icon'] }}" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-slate-700">{{ $feature['label'] }}</span>
                        <span class="mt-0.5 block text-xs text-slate-400">{{ $feature['help'] }}</span>
                    </span>
                    {{-- Hidden '0' guarantees an unchecked box still posts a value. --}}
                    <input type="hidden" name="{{ $feature['key'] }}" value="0">
                    <span class="relative mt-1 inline-flex shrink-0">
                        <input type="checkbox" id="{{ $feature['key'] }}" name="{{ $feature['key'] }}" value="1" @checked($flagOn($feature['key'])) class="peer sr-only">
                        <span class="h-6 w-11 rounded-full bg-slate-300 transition-colors peer-checked:bg-brand-600 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-400 peer-focus-visible:ring-offset-2"></span>
                        <span class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    </span>
                </label>
                @error($feature['key'])<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
            </div>
        @endforeach
    </x-admin.form-section>
    </div>

    {{-- ============================ Teks Promo & Navbar ============================ --}}
    <div x-show="tab === 'promo'" x-cloak>
    <x-admin.form-section title="Teks Promo &amp; Navbar" subtitle="Banner promo atas dan tombol kontak navbar." icon="fa-bullhorn">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Label Promo (tebal)</label>
            <input type="text" name="promo_badge" value="{{ $val('promo_badge') }}" placeholder="Promo Spesial!" class="{{ $inputClass }} @error('promo_badge') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Kosongkan untuk memakai teks bawaan.</p>
            @error('promo_badge')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Teks Promo</label>
            <input type="text" name="promo_text" value="{{ $val('promo_text') }}" placeholder="DP mulai 15 Juta" class="{{ $inputClass }} @error('promo_text') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Teks utama banner promo.</p>
            @error('promo_text')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Teks Promo Tambahan</label>
            <input type="text" name="promo_text_extra" value="{{ $val('promo_text_extra') }}" placeholder="+ gratis servis 1 tahun." class="{{ $inputClass }} @error('promo_text_extra') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Hanya tampil di layar lebar.</p>
            @error('promo_text_extra')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tautan Promo</label>
            <input type="text" name="promo_cta" value="{{ $val('promo_cta') }}" placeholder="Lihat mobil →" class="{{ $inputClass }} @error('promo_cta') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Teks tautan ke daftar mobil. Gunakan karakter panah → bila mau.</p>
            @error('promo_cta')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tombol CTA Navbar</label>
            <input type="text" name="nav_cta" value="{{ $val('nav_cta') }}" placeholder="Hubungi Kami" class="{{ $inputClass }} @error('nav_cta') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Label tombol kontak di navbar (desktop &amp; mobile).</p>
            @error('nav_cta')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal Berakhir Promo</label>
            {{-- datetime-local expects 'YYYY-MM-DDTHH:MM'; strip any stored seconds so the input prefills cleanly. --}}
            <input type="datetime-local" name="promo_deadline" value="{{ substr($val('promo_deadline'), 0, 16) }}" class="{{ $inputClass }} @error('promo_deadline') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Hitung mundur promo di hero akan menuju tanggal &amp; jam ini. Kosongkan untuk memakai akhir bulan berjalan.</p>
            @error('promo_deadline')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>
    </div>

    {{-- ============================ Teks Hero ============================ --}}
    <div x-show="tab === 'hero'" x-cloak>
    <x-admin.form-section title="Teks Hero" subtitle="Judul, deskripsi, benefit, tombol, dan pesan WhatsApp hero." icon="fa-star">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Badge Hero</label>
            <input type="text" name="hero_badge" value="{{ $val('hero_badge') }}" placeholder="PROMO" class="{{ $inputClass }} @error('hero_badge') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Kosongkan untuk memakai teks bawaan.</p>
            @error('hero_badge')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Label Harga Hero</label>
            <input type="text" name="hero_price_label" value="{{ $val('hero_price_label') }}" placeholder="Mulai" class="{{ $inputClass }} @error('hero_price_label') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Teks di atas harga pada badge melayang.</p>
            @error('hero_price_label')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Label Hitung Mundur (desktop)</label>
            <input type="text" name="hero_countdown_label" value="{{ $val('hero_countdown_label') }}" placeholder="Promo berakhir dalam" class="{{ $inputClass }} @error('hero_countdown_label') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Teks sebelum timer (layar lebar).</p>
            @error('hero_countdown_label')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Label Hitung Mundur (mobile)</label>
            <input type="text" name="hero_countdown_label_short" value="{{ $val('hero_countdown_label_short') }}" placeholder="Berakhir" class="{{ $inputClass }} @error('hero_countdown_label_short') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Teks sebelum timer (layar kecil).</p>
            @error('hero_countdown_label_short')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Hero (bagian 1)</label>
            <input type="text" name="hero_title" value="{{ $val('hero_title') }}" placeholder="Mobil Keluarga" class="{{ $inputClass }} @error('hero_title') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Bagian judul sebelum kata berwarna.</p>
            @error('hero_title')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Hero (kata berwarna)</label>
            <input type="text" name="hero_title_hl" value="{{ $val('hero_title_hl') }}" placeholder="Ceria" class="{{ $inputClass }} @error('hero_title_hl') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Kata yang disorot warna-warni.</p>
            @error('hero_title_hl')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Hero (bagian 2)</label>
            <input type="text" name="hero_title_suffix" value="{{ $val('hero_title_suffix') }}" placeholder="untuk Semua!" class="{{ $inputClass }} @error('hero_title_suffix') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Bagian judul setelah kata berwarna (baris kedua).</p>
            @error('hero_title_suffix')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Deskripsi Hero</label>
            <textarea name="hero_desc" rows="3" placeholder="Paragraf di bawah judul hero" class="{{ $inputClass }} @error('hero_desc') {{ $errClass }} @enderror">{{ $val('hero_desc') }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Paragraf di bawah judul hero.</p>
            @error('hero_desc')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Keunggulan Hero 1</label>
            <input type="text" name="hero_benefit_1" value="{{ $val('hero_benefit_1') }}" placeholder="DP mulai 15 Juta" class="{{ $inputClass }} @error('hero_benefit_1') {{ $errClass }} @enderror">
            @error('hero_benefit_1')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Keunggulan Hero 2</label>
            <input type="text" name="hero_benefit_2" value="{{ $val('hero_benefit_2') }}" placeholder="Cicilan s/d 6 Tahun" class="{{ $inputClass }} @error('hero_benefit_2') {{ $errClass }} @enderror">
            @error('hero_benefit_2')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Keunggulan Hero 3</label>
            <input type="text" name="hero_benefit_3" value="{{ $val('hero_benefit_3') }}" placeholder="Garansi 3 Tahun" class="{{ $inputClass }} @error('hero_benefit_3') {{ $errClass }} @enderror">
            @error('hero_benefit_3')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tombol Utama Hero</label>
            <input type="text" name="hero_btn_primary" value="{{ $val('hero_btn_primary') }}" placeholder="Lihat Semua Mobil" class="{{ $inputClass }} @error('hero_btn_primary') {{ $errClass }} @enderror">
            @error('hero_btn_primary')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tombol WhatsApp Hero</label>
            <input type="text" name="hero_btn_whatsapp" value="{{ $val('hero_btn_whatsapp') }}" placeholder="Test Drive" class="{{ $inputClass }} @error('hero_btn_whatsapp') {{ $errClass }} @enderror">
            @error('hero_btn_whatsapp')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Pesan WhatsApp Hero</label>
            <textarea name="hero_wa_message" rows="2" placeholder="Halo, saya mau test drive mobil Daihatsu" class="{{ $inputClass }} @error('hero_wa_message') {{ $errClass }} @enderror">{{ $val('hero_wa_message') }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Pesan otomatis saat klik Test Drive.</p>
            @error('hero_wa_message')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>
    </div>

    {{-- ============================ Teks Bagian (Section) ============================ --}}
    <div x-show="tab === 'bagian'" x-cloak>
    <x-admin.form-section title="Teks Bagian (Section)" subtitle="Eyebrow, judul, subjudul, dan status kosong tiap bagian halaman." icon="fa-heading">
        <p class="md:col-span-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Mobil / Inventory</p>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Eyebrow Bagian Mobil</label>
            <input type="text" name="sec_inventory_eyebrow" value="{{ $val('sec_inventory_eyebrow') }}" placeholder="Pilihan Mobil" class="{{ $inputClass }} @error('sec_inventory_eyebrow') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Kosongkan untuk memakai teks bawaan.</p>
            @error('sec_inventory_eyebrow')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Bagian Mobil (bagian 1)</label>
            <input type="text" name="sec_inventory_title" value="{{ $val('sec_inventory_title') }}" placeholder="Koleksi" class="{{ $inputClass }} @error('sec_inventory_title') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Sebelum kata berwarna.</p>
            @error('sec_inventory_title')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Bagian Mobil (kata berwarna)</label>
            <input type="text" name="sec_inventory_title_hl" value="{{ $val('sec_inventory_title_hl') }}" placeholder="Daihatsu" class="{{ $inputClass }} @error('sec_inventory_title_hl') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Kata yang disorot.</p>
            @error('sec_inventory_title_hl')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Subjudul Mobil (sebelum angka)</label>
            <input type="text" name="sec_inventory_subtitle_pre" value="{{ $val('sec_inventory_subtitle_pre') }}" placeholder="Menampilkan" class="{{ $inputClass }} @error('sec_inventory_subtitle_pre') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Teks sebelum jumlah mobil (angka diisi otomatis).</p>
            @error('sec_inventory_subtitle_pre')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Subjudul Mobil (setelah angka)</label>
            <textarea name="sec_inventory_subtitle_post" rows="2" placeholder="mobil. Semua unit bergaransi resmi & siap antar ke rumahmu." class="{{ $inputClass }} @error('sec_inventory_subtitle_post') {{ $errClass }} @enderror">{{ $val('sec_inventory_subtitle_post') }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Teks setelah jumlah mobil.</p>
            @error('sec_inventory_subtitle_post')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Petunjuk Geser (mobile)</label>
            <input type="text" name="inventory_swipe_hint" value="{{ $val('inventory_swipe_hint') }}" placeholder="Geser untuk melihat mobil lainnya" class="{{ $inputClass }} @error('inventory_swipe_hint') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Teks petunjuk geser di layar kecil.</p>
            @error('inventory_swipe_hint')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Hasil Kosong</label>
            <input type="text" name="inventory_empty_title" value="{{ $val('inventory_empty_title') }}" placeholder="Mobil tidak ditemukan" class="{{ $inputClass }} @error('inventory_empty_title') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Judul saat tidak ada mobil cocok filter.</p>
            @error('inventory_empty_title')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tombol Reset Hasil Kosong</label>
            <input type="text" name="inventory_empty_btn" value="{{ $val('inventory_empty_btn') }}" placeholder="Tampilkan Semua" class="{{ $inputClass }} @error('inventory_empty_btn') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Tombol untuk menampilkan semua mobil lagi.</p>
            @error('inventory_empty_btn')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Deskripsi Hasil Kosong</label>
            <textarea name="inventory_empty_desc" rows="2" placeholder="Coba ubah kriteria pencarian kamu." class="{{ $inputClass }} @error('inventory_empty_desc') {{ $errClass }} @enderror">{{ $val('inventory_empty_desc') }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Teks di bawah judul hasil kosong.</p>
            @error('inventory_empty_desc')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>

        <p class="md:col-span-2 mt-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Filter</p>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Filter</label>
            <input type="text" name="filter_heading" value="{{ $val('filter_heading') }}" placeholder="Filter Mobil" class="{{ $inputClass }} @error('filter_heading') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Judul kartu filter mobil.</p>
            @error('filter_heading')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>

        <p class="md:col-span-2 mt-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Simulasi Kredit</p>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Eyebrow Simulasi Kredit</label>
            <input type="text" name="sec_credit_eyebrow" value="{{ $val('sec_credit_eyebrow') }}" placeholder="Simulasi Kredit" class="{{ $inputClass }} @error('sec_credit_eyebrow') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Label kecil di atas judul.</p>
            @error('sec_credit_eyebrow')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Simulasi (bagian 1)</label>
            <input type="text" name="sec_credit_title" value="{{ $val('sec_credit_title') }}" placeholder="Hitung Cicilan" class="{{ $inputClass }} @error('sec_credit_title') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Sebelum kata berwarna.</p>
            @error('sec_credit_title')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Simulasi (kata berwarna)</label>
            <input type="text" name="sec_credit_title_hl" value="{{ $val('sec_credit_title_hl') }}" placeholder="Impianmu" class="{{ $inputClass }} @error('sec_credit_title_hl') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Kata yang disorot.</p>
            @error('sec_credit_title_hl')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Subjudul Simulasi</label>
            <textarea name="sec_credit_subtitle" rows="2" placeholder="Paragraf di bawah judul simulasi" class="{{ $inputClass }} @error('sec_credit_subtitle') {{ $errClass }} @enderror">{{ $val('sec_credit_subtitle') }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Paragraf di bawah judul.</p>
            @error('sec_credit_subtitle')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>

        <p class="md:col-span-2 mt-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Kuis</p>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Eyebrow Kuis</label>
            <input type="text" name="sec_quiz_eyebrow" value="{{ $val('sec_quiz_eyebrow') }}" placeholder="Bingung Pilih?" class="{{ $inputClass }} @error('sec_quiz_eyebrow') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Label kecil di atas judul kuis.</p>
            @error('sec_quiz_eyebrow')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Kuis (bagian 1)</label>
            <input type="text" name="sec_quiz_title" value="{{ $val('sec_quiz_title') }}" placeholder="Cari Mobil" class="{{ $inputClass }} @error('sec_quiz_title') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Sebelum kata berwarna.</p>
            @error('sec_quiz_title')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Kuis (kata berwarna)</label>
            <input type="text" name="sec_quiz_title_hl" value="{{ $val('sec_quiz_title_hl') }}" placeholder="Idealmu" class="{{ $inputClass }} @error('sec_quiz_title_hl') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Kata yang disorot.</p>
            @error('sec_quiz_title_hl')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Subjudul Kuis</label>
            <textarea name="sec_quiz_subtitle" rows="2" placeholder="Paragraf di bawah judul kuis" class="{{ $inputClass }} @error('sec_quiz_subtitle') {{ $errClass }} @enderror">{{ $val('sec_quiz_subtitle') }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Paragraf di bawah judul kuis.</p>
            @error('sec_quiz_subtitle')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>

        <p class="md:col-span-2 mt-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Testimoni</p>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Eyebrow Testimoni</label>
            <input type="text" name="sec_testi_eyebrow" value="{{ $val('sec_testi_eyebrow') }}" placeholder="Kata Mereka" class="{{ $inputClass }} @error('sec_testi_eyebrow') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Label kecil di atas judul.</p>
            @error('sec_testi_eyebrow')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Testimoni (bagian 1)</label>
            <input type="text" name="sec_testi_title" value="{{ $val('sec_testi_title') }}" placeholder="Cerita" class="{{ $inputClass }} @error('sec_testi_title') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Sebelum kata berwarna.</p>
            @error('sec_testi_title')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Testimoni (kata berwarna)</label>
            <input type="text" name="sec_testi_title_hl" value="{{ $val('sec_testi_title_hl') }}" placeholder="Sahabat Daihatsu" class="{{ $inputClass }} @error('sec_testi_title_hl') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Kata/frasa yang disorot.</p>
            @error('sec_testi_title_hl')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Subjudul Testimoni</label>
            <textarea name="sec_testi_subtitle" rows="2" placeholder="Paragraf di bawah judul testimoni" class="{{ $inputClass }} @error('sec_testi_subtitle') {{ $errClass }} @enderror">{{ $val('sec_testi_subtitle') }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Paragraf di bawah judul.</p>
            @error('sec_testi_subtitle')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>
    </div>

    {{-- ============================ Teks Kalkulator Kredit ============================ --}}
    <div x-show="tab === 'kalkulator'" x-cloak>
    <x-admin.form-section title="Teks Kalkulator Kredit" subtitle="Label dan catatan pada kalkulator cicilan." icon="fa-calculator">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Label Harga Mobil</label>
            <input type="text" name="calc_label_price" value="{{ $val('calc_label_price') }}" placeholder="Harga Mobil" class="{{ $inputClass }} @error('calc_label_price') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Kosongkan untuk memakai teks bawaan.</p>
            @error('calc_label_price')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Label Uang Muka</label>
            <input type="text" name="calc_label_dp" value="{{ $val('calc_label_dp') }}" placeholder="Uang Muka (DP)" class="{{ $inputClass }} @error('calc_label_dp') {{ $errClass }} @enderror">
            @error('calc_label_dp')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Label Tenor</label>
            <input type="text" name="calc_label_tenor" value="{{ $val('calc_label_tenor') }}" placeholder="Tenor" class="{{ $inputClass }} @error('calc_label_tenor') {{ $errClass }} @enderror">
            @error('calc_label_tenor')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Label Hasil Angsuran</label>
            <input type="text" name="calc_label_result" value="{{ $val('calc_label_result') }}" placeholder="Perkiraan Angsuran / Bulan" class="{{ $inputClass }} @error('calc_label_result') {{ $errClass }} @enderror">
            @error('calc_label_result')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Label Total DP</label>
            <input type="text" name="calc_label_total_dp" value="{{ $val('calc_label_total_dp') }}" placeholder="Total DP" class="{{ $inputClass }} @error('calc_label_total_dp') {{ $errClass }} @enderror">
            @error('calc_label_total_dp')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Label Total Pinjaman</label>
            <input type="text" name="calc_label_total_loan" value="{{ $val('calc_label_total_loan') }}" placeholder="Total Pinjaman" class="{{ $inputClass }} @error('calc_label_total_loan') {{ $errClass }} @enderror">
            @error('calc_label_total_loan')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tombol Ajukan Kredit</label>
            <input type="text" name="calc_btn" value="{{ $val('calc_btn') }}" placeholder="Ajukan Kredit" class="{{ $inputClass }} @error('calc_btn') {{ $errClass }} @enderror">
            @error('calc_btn')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Catatan Kaki Kalkulator</label>
            <textarea name="calc_footnote" rows="2" placeholder="*Estimasi bunga flat 4%/tahun. Angka sebenarnya menyesuaikan leasing." class="{{ $inputClass }} @error('calc_footnote') {{ $errClass }} @enderror">{{ $val('calc_footnote') }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Catatan kecil di bawah tombol.</p>
            @error('calc_footnote')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>
    </div>

    {{-- ============================ Simulasi Kredit (parameter rumus) ============================ --}}
    <div x-show="tab === 'simulasi'" x-cloak>
    <x-admin.form-section title="Parameter Simulasi Kredit" subtitle="Atur rumus kalkulator cicilan: bunga, uang muka, dan tenor." icon="fa-percent">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Bunga Flat per Tahun (%)</label>
            <input type="number" step="0.1" min="0" max="100" name="credit_interest_rate" value="{{ $val('credit_interest_rate') }}" placeholder="4" class="{{ $inputClass }} @error('credit_interest_rate') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Bunga flat per tahun dalam persen. Contoh: 8 untuk 8%.</p>
            @error('credit_interest_rate')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">DP Default (%)</label>
            <input type="number" step="1" min="0" max="100" name="credit_default_dp" value="{{ $val('credit_default_dp') }}" placeholder="20" class="{{ $inputClass }} @error('credit_default_dp') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Posisi awal slider uang muka.</p>
            @error('credit_default_dp')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">DP Minimum (%)</label>
            <input type="number" step="1" min="0" max="100" name="credit_min_dp" value="{{ $val('credit_min_dp') }}" placeholder="10" class="{{ $inputClass }} @error('credit_min_dp') {{ $errClass }} @enderror">
            @error('credit_min_dp')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">DP Maksimum (%)</label>
            <input type="number" step="1" min="0" max="100" name="credit_max_dp" value="{{ $val('credit_max_dp') }}" placeholder="50" class="{{ $inputClass }} @error('credit_max_dp') {{ $errClass }} @enderror">
            @error('credit_max_dp')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Kelipatan DP (%)</label>
            <input type="number" step="1" min="1" max="100" name="credit_dp_step" value="{{ $val('credit_dp_step') }}" placeholder="5" class="{{ $inputClass }} @error('credit_dp_step') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Langkah geser slider uang muka.</p>
            @error('credit_dp_step')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tenor Default (tahun)</label>
            <input type="number" step="1" min="1" max="30" name="credit_default_tenor" value="{{ $val('credit_default_tenor') }}" placeholder="4" class="{{ $inputClass }} @error('credit_default_tenor') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Posisi awal slider tenor.</p>
            @error('credit_default_tenor')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tenor Minimum (tahun)</label>
            <input type="number" step="1" min="1" max="30" name="credit_min_tenor" value="{{ $val('credit_min_tenor') }}" placeholder="1" class="{{ $inputClass }} @error('credit_min_tenor') {{ $errClass }} @enderror">
            @error('credit_min_tenor')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tenor Maksimum (tahun)</label>
            <input type="number" step="1" min="1" max="30" name="credit_max_tenor" value="{{ $val('credit_max_tenor') }}" placeholder="6" class="{{ $inputClass }} @error('credit_max_tenor') {{ $errClass }} @enderror">
            @error('credit_max_tenor')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>
    </div>

    {{-- ============================ Teks Roda Keberuntungan ============================ --}}
    <div x-show="tab === 'roda'" x-cloak>
    <x-admin.form-section title="Teks Roda Keberuntungan" subtitle="Judul, subjudul, dan tombol modal roda hadiah." icon="fa-trophy">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Eyebrow Roda</label>
            <input type="text" name="wheel_eyebrow" value="{{ $val('wheel_eyebrow') }}" placeholder="Roda Keberuntungan" class="{{ $inputClass }} @error('wheel_eyebrow') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Kosongkan untuk memakai teks bawaan.</p>
            @error('wheel_eyebrow')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Roda</label>
            <input type="text" name="wheel_title" value="{{ $val('wheel_title') }}" placeholder="Putar & Menangkan Hadiah!" class="{{ $inputClass }} @error('wheel_title') {{ $errClass }} @enderror">
            @error('wheel_title')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Subjudul Roda</label>
            <textarea name="wheel_subtitle" rows="2" placeholder="Coba keberuntunganmu — setiap putaran pasti dapat hadiah spesial." class="{{ $inputClass }} @error('wheel_subtitle') {{ $errClass }} @enderror">{{ $val('wheel_subtitle') }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Paragraf di bawah judul modal.</p>
            @error('wheel_subtitle')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Label Tombol Roda</label>
            <input type="text" name="wheel_trigger_label" value="{{ $val('wheel_trigger_label') }}" placeholder="Menangkan Hadiah!" class="{{ $inputClass }} @error('wheel_trigger_label') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Teks tombol melayang pembuka roda.</p>
            @error('wheel_trigger_label')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Teks Hasil Roda</label>
            <input type="text" name="wheel_result_lead" value="{{ $val('wheel_result_lead') }}" placeholder="Selamat! Kamu mendapatkan" class="{{ $inputClass }} @error('wheel_result_lead') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Teks di atas nama hadiah.</p>
            @error('wheel_result_lead')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tombol Klaim Hadiah</label>
            <input type="text" name="wheel_claim_btn" value="{{ $val('wheel_claim_btn') }}" placeholder="Klaim Hadiah Sekarang" class="{{ $inputClass }} @error('wheel_claim_btn') {{ $errClass }} @enderror">
            @error('wheel_claim_btn')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Catatan Klaim Roda</label>
            <textarea name="wheel_claim_note" rows="2" placeholder="*Tunjukkan hadiah ini saat menghubungi kami. Berlaku selama periode promo." class="{{ $inputClass }} @error('wheel_claim_note') {{ $errClass }} @enderror">{{ $val('wheel_claim_note') }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Catatan kecil di bawah tombol klaim.</p>
            @error('wheel_claim_note')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>
    </div>

    {{-- ============================ Teks Footer ============================ --}}
    <div x-show="tab === 'footer'" x-cloak>
    <x-admin.form-section title="Teks Footer" subtitle="CTA, kontak, lokasi, hak cipta, dan kredit footer." icon="fa-shoe-prints">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul CTA Footer</label>
            <input type="text" name="footer_cta_heading" value="{{ $val('footer_cta_heading') }}" placeholder="Siap Bawa Pulang Daihatsu Impianmu?" class="{{ $inputClass }} @error('footer_cta_heading') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Kosongkan untuk memakai teks bawaan.</p>
            @error('footer_cta_heading')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Subjudul CTA Footer</label>
            <textarea name="footer_cta_subtitle" rows="2" placeholder="Hubungi kami sekarang, gratis konsultasi & jadwal test drive." class="{{ $inputClass }} @error('footer_cta_subtitle') {{ $errClass }} @enderror">{{ $val('footer_cta_subtitle') }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Teks di bawah judul CTA.</p>
            @error('footer_cta_subtitle')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tombol WhatsApp Footer</label>
            <input type="text" name="footer_cta_wa_label" value="{{ $val('footer_cta_wa_label') }}" placeholder="Chat WhatsApp" class="{{ $inputClass }} @error('footer_cta_wa_label') {{ $errClass }} @enderror">
            @error('footer_cta_wa_label')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tombol Telepon Footer</label>
            <input type="text" name="footer_cta_phone_label" value="{{ $val('footer_cta_phone_label') }}" placeholder="Telepon" class="{{ $inputClass }} @error('footer_cta_phone_label') {{ $errClass }} @enderror">
            @error('footer_cta_phone_label')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tentang (Footer)</label>
            <textarea name="footer_about" rows="2" placeholder="Dealer resmi Daihatsu yang menemani keluarga Indonesia sejak 2011. Sahabat di setiap perjalanan." class="{{ $inputClass }} @error('footer_about') {{ $errClass }} @enderror">{{ $val('footer_about') }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Paragraf deskripsi di kolom brand.</p>
            @error('footer_about')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Kolom Kontak</label>
            <input type="text" name="footer_contact_heading" value="{{ $val('footer_contact_heading') }}" placeholder="Hubungi Kami" class="{{ $inputClass }} @error('footer_contact_heading') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Alamat &amp; email yang ditampilkan di footer diambil dari bagian "Kontak &amp; Lainnya".</p>
            @error('footer_contact_heading')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Jam Operasional</label>
            <input type="text" name="footer_hours" value="{{ $val('footer_hours') }}" placeholder="Sen – Sab, 08.00 – 20.00 WIB" class="{{ $inputClass }} @error('footer_hours') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Baris jam buka di kolom kontak.</p>
            @error('footer_hours')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Lokasi</label>
            <input type="text" name="footer_map_heading" value="{{ $val('footer_map_heading') }}" placeholder="Lokasi Kami" class="{{ $inputClass }} @error('footer_map_heading') {{ $errClass }} @enderror">
            @error('footer_map_heading')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tautan Peta</label>
            <input type="text" name="footer_map_cta" value="{{ $val('footer_map_cta') }}" placeholder="Buka di Google Maps" class="{{ $inputClass }} @error('footer_map_cta') {{ $errClass }} @enderror">
            @error('footer_map_cta')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Teks Hak Cipta</label>
            <input type="text" name="footer_copyright" value="{{ $val('footer_copyright') }}" placeholder="All rights reserved." class="{{ $inputClass }} @error('footer_copyright') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Teks setelah "© [tahun] [nama situs].".</p>
            @error('footer_copyright')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Teks Kredit Footer</label>
            <textarea name="footer_credit" rows="2" placeholder="Dibuat dengan ❤ untuk keluarga Indonesia." class="{{ $inputClass }} @error('footer_credit') {{ $errClass }} @enderror">{{ $val('footer_credit') }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Baris kecil di kanan bawah footer.</p>
            @error('footer_credit')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>
    </div>
</div>

@include('admin.partials.form-actions', ['cancel' => route('admin.settings.edit'), 'label' => 'Simpan Pengaturan'])
