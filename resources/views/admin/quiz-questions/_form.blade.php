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
@endphp

<div class="space-y-5">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-medium mb-1">Pertanyaan</label>
            <input type="text" name="question" value="{{ old('question', $question->question) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            @error('question')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Ikon (Font Awesome)</label>
            <input type="text" name="icon" value="{{ old('icon', $question->icon) }}" placeholder="fa-bullseye" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            @error('icon')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-2">
            <h3 class="font-semibold">Pilihan Jawaban</h3>
            <button type="button" id="add-option" class="text-sm bg-slate-200 hover:bg-slate-300 px-3 py-1.5 rounded">
                <i class="fa-solid fa-plus"></i> Tambah Pilihan
            </button>
        </div>
        @error('options')<p class="text-red-600 text-xs mb-2">{{ $message }}</p>@enderror
        <div id="options-wrap" class="space-y-4"></div>
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-5 py-2 rounded">Simpan</button>
    <a href="{{ route('admin.quiz-questions.index') }}" class="bg-slate-200 hover:bg-slate-300 text-sm px-5 py-2 rounded">Batal</a>
</div>

@push('scripts')
<script>
(function () {
    const carModels = @json($carModels);
    const initial = @json($optionsData);
    const wrap = document.getElementById('options-wrap');
    let optIndex = 0;

    function modelSelect(name, selected) {
        let html = '<select name="' + name + '" class="rounded border border-slate-300 px-2 py-1.5 text-sm">';
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
            '<input type="number" name="options[' + optI + '][scores][' + scoreCounter[optI] + '][points]" value="' + (points !== undefined && points !== null ? points : '') + '" placeholder="poin" min="0" max="100" class="w-24 rounded border border-slate-300 px-2 py-1.5 text-sm">' +
            '<button type="button" class="text-red-600 text-sm remove-score"><i class="fa-solid fa-xmark"></i></button>';
        scoreCounter[optI]++;
        row.querySelector('.remove-score').addEventListener('click', function () { row.remove(); });
        return row;
    }

    const scoreCounter = {};

    function optionBlock(opt) {
        const i = optIndex++;
        scoreCounter[i] = 0;
        const block = document.createElement('div');
        block.className = 'border border-slate-200 rounded p-4 bg-slate-50';
        block.innerHTML =
            '<div class="flex items-center justify-between mb-3">' +
                '<span class="text-sm font-medium text-slate-600">Pilihan</span>' +
                '<button type="button" class="text-red-600 text-sm remove-option"><i class="fa-solid fa-trash"></i> Hapus Pilihan</button>' +
            '</div>' +
            '<div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">' +
                '<div><label class="block text-xs mb-1">Teks</label>' +
                '<input type="text" name="options[' + i + '][text]" value="' + (opt.text ? opt.text.replace(/"/g, "&quot;") : '') + '" class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm"></div>' +
                '<div><label class="block text-xs mb-1">Ikon</label>' +
                '<input type="text" name="options[' + i + '][icon]" value="' + (opt.icon ? opt.icon.replace(/"/g, "&quot;") : '') + '" placeholder="fa-city" class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm"></div>' +
            '</div>' +
            '<div class="flex items-center justify-between mb-2">' +
                '<span class="text-xs text-slate-500">Skor per model</span>' +
                '<button type="button" class="text-xs bg-slate-200 hover:bg-slate-300 px-2 py-1 rounded add-score">+ Skor</button>' +
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
