@php
    // Build the editable options structure from old() input (on validation
    // failure) or the loaded model relations (on edit), else one empty option.
    if (old('options') !== null) {
        $optionsData = [];
        foreach (old('options') as $opt) {
            $scores = [];
            foreach ($opt['scores'] ?? [] as $sc) {
                $scores[] = ['car_model' => $sc['car_model'] ?? '', 'points' => $sc['points'] ?? ''];
            }
            $optionsData[] = [
                'text' => $opt['text'] ?? '',
                'icon' => $opt['icon'] ?? '',
                'scores' => $scores,
            ];
        }
    } elseif ($question->exists && $question->options->isNotEmpty()) {
        $optionsData = $question->options->map(fn ($o) => [
            'text' => $o->text,
            'icon' => $o->icon,
            'scores' => $o->scores->map(fn ($s) => [
                'car_model' => $s->car_model,
                'points' => $s->points,
            ])->values()->all(),
        ])->values()->all();
    } else {
        $optionsData = [['text' => '', 'icon' => '', 'scores' => []]];
    }

    $inputClass = 'w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500';
    $errClass = 'border-red-400 focus:border-red-500 focus:ring-red-500';
@endphp

<div class="space-y-8">
    {{-- Pertanyaan --}}
    <x-admin.form-section title="Pertanyaan" subtitle="Teks pertanyaan dan ikon pendukung" icon="fa-circle-question">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Pertanyaan <span class="text-brand-600">*</span></label>
            <input type="text" name="question" value="{{ old('question', $question->question) }}" class="{{ $inputClass }} @error('question') {{ $errClass }} @enderror">
            @error('question')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Ikon (Font Awesome) <span class="text-brand-600">*</span></label>
            <input type="text" name="icon" value="{{ old('icon', $question->icon) }}" placeholder="fa-bullseye" class="{{ $inputClass }} @error('icon') {{ $errClass }} @enderror">
            @error('icon')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>

    {{-- Pilihan Jawaban (repeater) --}}
    <section class="space-y-5">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                    <i class="fa-solid fa-list-check text-sm"></i>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Pilihan Jawaban</h3>
                    <p class="text-xs text-slate-500">Setiap pilihan dapat memberi skor pada beberapa model mobil.</p>
                </div>
            </div>
            <button type="button" id="add-option"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-brand-600">
                <i class="fa-solid fa-plus"></i> Tambah Pilihan
            </button>
        </div>
        @error('options')<p class="flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        <div id="options-wrap" class="space-y-4"></div>
    </section>
</div>

@include('admin.partials.form-actions', ['cancel' => route('admin.quiz-questions.index')])

@push('scripts')
<script>
(function () {
    const carModels = @json($carModels);
    const initial = @json($optionsData);
    const wrap = document.getElementById('options-wrap');
    let optIndex = 0;

    function modelSelect(name, selected) {
        let html = '<select name="' + name + '" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">';
        html += '<option value="">— pilih model —</option>';
        carModels.forEach(function (m) {
            html += '<option value="' + m + '"' + (m === selected ? ' selected' : '') + '>' + m + '</option>';
        });
        html += '</select>';
        return html;
    }

    function scoreRow(optI, model, points) {
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2 score-row';
        const base = 'options[' + optI + '][scores][]';
        row.innerHTML =
            modelSelect('options[' + optI + '][scores][' + scoreCounter[optI] + '][car_model]', model || '') +
            '<input type="number" name="options[' + optI + '][scores][' + scoreCounter[optI] + '][points]" value="' + (points !== undefined && points !== null ? points : '') + '" placeholder="poin" min="0" max="100" class="w-24 rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">' +
            '<button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600 remove-score" aria-label="Hapus skor"><i class="fa-solid fa-xmark"></i></button>';
        scoreCounter[optI]++;
        row.querySelector('.remove-score').addEventListener('click', function () { row.remove(); });
        return row;
    }

    const scoreCounter = {};

    function optionBlock(opt) {
        const i = optIndex++;
        scoreCounter[i] = 0;
        const block = document.createElement('div');
        block.className = 'rounded-xl border border-slate-200 bg-slate-50/60 p-4 shadow-sm';
        block.innerHTML =
            '<div class="flex items-center justify-between mb-3">' +
                '<span class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-700"><i class="fa-solid fa-circle-dot text-brand-500"></i> Pilihan</span>' +
                '<button type="button" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 remove-option"><i class="fa-solid fa-trash"></i> Hapus Pilihan</button>' +
            '</div>' +
            '<div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">' +
                '<div><label class="block text-xs font-medium text-slate-600 mb-1">Teks</label>' +
                '<input type="text" name="options[' + i + '][text]" value="' + (opt.text ? opt.text.replace(/"/g, "&quot;") : '') + '" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"></div>' +
                '<div><label class="block text-xs font-medium text-slate-600 mb-1">Ikon</label>' +
                '<input type="text" name="options[' + i + '][icon]" value="' + (opt.icon ? opt.icon.replace(/"/g, "&quot;") : '') + '" placeholder="fa-city" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"></div>' +
            '</div>' +
            '<div class="flex items-center justify-between mb-2">' +
                '<span class="text-xs font-medium text-slate-500">Skor per model</span>' +
                '<button type="button" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 add-score"><i class="fa-solid fa-plus"></i> Skor</button>' +
            '</div>' +
            '<div class="scores-wrap space-y-2"></div>';

        const scoresWrap = block.querySelector('.scores-wrap');
        (opt.scores || []).forEach(function (sc) {
            scoresWrap.appendChild(scoreRow(i, sc.car_model, sc.points));
        });
        block.querySelector('.add-score').addEventListener('click', function () {
            scoresWrap.appendChild(scoreRow(i, '', ''));
        });
        block.querySelector('.remove-option').addEventListener('click', function () { block.remove(); });
        return block;
    }

    initial.forEach(function (opt) { wrap.appendChild(optionBlock(opt)); });
    document.getElementById('add-option').addEventListener('click', function () {
        wrap.appendChild(optionBlock({ text: '', icon: '', scores: [] }));
    });
})();
</script>
@endpush
