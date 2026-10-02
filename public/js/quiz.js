/* =========================================================================
   QUIZ — "Mobil Daihatsu apa yang cocok untukmu?"
   Memandu pengunjung menjawab beberapa pertanyaan, menjumlahkan skor per
   model, lalu merekomendasikan model dengan skor tertinggi.
   Data pertanyaan: App.QUIZ. Data mobil: App.CARS.
   ========================================================================= */
window.App = window.App || {};

(function (App) {
  const { $, esc, formatRupiah } = App;
  const QUIZ = App.QUIZ || [];
  const CARS = App.CARS || [];

  const stage = $('#quizStage');
  const bar   = $('#quizBar');
  const stepLabel = $('#quizStepLabel');
  const percentEl = $('#quizPercent');
  const progressWrap = $('#quizProgressWrap');
  if (!stage || QUIZ.length === 0) return;

  const WA_NUMBER = (window.App && window.App.WA) ? window.App.WA : '6281234567890';
  let step = 0;
  const scores = {}; // { model: totalPoin }

  /* ---------- Progress ---------- */
  function updateProgress() {
    const pct = Math.round((step / QUIZ.length) * 100);
    bar.style.width = pct + '%';
    percentEl.textContent = pct + '%';
    stepLabel.textContent = `Pertanyaan ${Math.min(step + 1, QUIZ.length)} dari ${QUIZ.length}`;
  }

  /* ---------- Render satu pertanyaan ---------- */
  function renderQuestion() {
    progressWrap.style.display = '';
    updateProgress();
    const item = QUIZ[step];

    stage.innerHTML = `
      <div class="quiz-fade">
        <div class="flex items-center gap-3 mb-6">
          <span class="w-11 h-11 rounded-2xl grid place-items-center text-white" style="background:linear-gradient(135deg,#0a5fd1,#2e86ff)">
            <i class="fa-solid ${esc(item.icon)}" aria-hidden="true"></i>
          </span>
          <h3 class="quiz-q text-lg sm:text-xl text-ink">${esc(item.q)}</h3>
        </div>
        <div class="grid gap-3">
          ${item.options.map((o, i) => `
            <button type="button" class="quiz-option" data-opt="${i}">
              <span class="qo-icon"><i class="fa-solid ${esc(o.icon)}" aria-hidden="true"></i></span>
              <span>${esc(o.t)}</span>
              <span class="qo-arrow"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </button>`).join('')}
        </div>
        ${step > 0 ? `<button type="button" id="quizBack" class="quiz-back mt-6 inline-flex items-center gap-2"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Kembali</button>` : ''}
      </div>`;

    // Pilih opsi
    stage.querySelectorAll('.quiz-option').forEach((btn) => {
      btn.addEventListener('click', () => {
        const opt = item.options[+btn.dataset.opt];
        // Simpan skor opsi ini (tandai step agar bisa dibatalkan saat "Kembali")
        opt._step = step;
        for (const model in opt.score) scores[model] = (scores[model] || 0) + opt.score[model];
        answers[step] = opt;
        step++;
        if (step >= QUIZ.length) showResult();
        else renderQuestion();
      });
    });

    const back = $('#quizBack');
    if (back) back.addEventListener('click', goBack);
  }

  const answers = []; // menyimpan opsi terpilih tiap step untuk dibatalkan

  function goBack() {
    if (step === 0) return;
    step--;
    // Batalkan skor dari jawaban step ini
    const prev = answers[step];
    if (prev) { for (const m in prev.score) scores[m] -= prev.score[m]; answers[step] = null; }
    renderQuestion();
  }

  /* ---------- Tentukan & tampilkan hasil ---------- */
  function bestModel() {
    let best = null, max = -1;
    for (const model in scores) {
      if (scores[model] > max) { max = scores[model]; best = model; }
    }
    return best;
  }

  function showResult() {
    progressWrap.style.display = 'none';
    const modelName = bestModel();
    // Ambil varian termurah dari model tsb sebagai rekomendasi konkret
    const matches = CARS.filter((c) => c.model === modelName).sort((a, b) => a.price - b.price);
    const car = matches[0];

    if (!car) { // fallback bila tak ketemu
      stage.innerHTML = `<p class="text-center text-ink-500">Maaf, terjadi kesalahan. <button id="quizRetry" class="text-brand font-semibold underline">Ulangi</button></p>`;
      $('#quizRetry').addEventListener('click', restart);
      return;
    }

    const waMsg = `Halo, dari kuis di website, Daihatsu ${car.model} direkomendasikan untuk saya. Saya mau tanya lebih lanjut.`;

    stage.innerHTML = `
      <div class="quiz-fade text-center">
        <span class="quiz-match-badge"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> Rekomendasi Terbaik</span>
        <h3 class="font-display font-extrabold text-2xl text-ink mt-4">Daihatsu ${esc(car.model)}</h3>
        <p class="text-ink-500 text-sm">${esc(car.type)}</p>

        <img src="${esc(car.img)}" alt="Daihatsu ${esc(car.model)}" class="quiz-result-img mt-5"
             onerror="this.onerror=null;this.src=FALLBACK_IMG;" />

        <p class="font-display font-black text-2xl text-brand mt-4">${formatRupiah(car.price)}</p>

        <div class="grid grid-cols-3 gap-2 mt-5 text-xs text-ink-500 font-medium">
          <div class="bg-cream rounded-xl py-3"><i class="fa-solid fa-gears text-sky2 block text-base mb-1" aria-hidden="true"></i>${esc(car.transmission)}</div>
          <div class="bg-cream rounded-xl py-3"><i class="fa-solid fa-gas-pump text-mango block text-base mb-1" aria-hidden="true"></i>${esc(car.fuel)}</div>
          <div class="bg-cream rounded-xl py-3"><i class="fa-solid fa-users text-brand block text-base mb-1" aria-hidden="true"></i>${car.seats} Kursi</div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 mt-7">
          <a href="https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(waMsg)}" target="_blank" rel="noopener"
             class="btn-fun text-white font-display font-bold py-3.5 rounded-full flex-1 inline-flex items-center justify-center gap-2">
            <i class="fa-brands fa-whatsapp text-lg" aria-hidden="true"></i> Tanya Sekarang
          </a>
          <button type="button" id="quizDetail" class="btn-soft text-ink font-display font-bold py-3.5 rounded-full flex-1 inline-flex items-center justify-center gap-2">
            <i class="fa-solid fa-circle-info text-brand" aria-hidden="true"></i> Lihat Detail
          </button>
        </div>

        <button type="button" id="quizRetry" class="quiz-back mt-6 inline-flex items-center gap-2">
          <i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Ulangi Kuis
        </button>
      </div>`;

    $('#quizRetry').addEventListener('click', restart);
    const detailBtn = $('#quizDetail');
    if (detailBtn && App.openCarDetail) detailBtn.addEventListener('click', () => App.openCarDetail(car.id));
  }

  function restart() {
    step = 0;
    for (const k in scores) delete scores[k];
    answers.length = 0;
    renderQuestion();
  }

  // Mulai
  renderQuestion();
})(window.App);
