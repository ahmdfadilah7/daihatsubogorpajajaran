/* =========================================================================
   CALCULATOR — Kalkulator cicilan interaktif (real-time).
   Harga diambil dari mobil yang dipilih (#calcCar). Parameter rumus (bunga,
   DP, tenor) dibaca dari window.App.CREDIT yang di-bootstrap server.
   Rumus bunga flat sederhana untuk estimasi angsuran.
   ========================================================================= */
window.App = window.App || {};

(function (App) {
  const { $, formatRupiah } = App;

  const calcCar   = $('#calcCar');
  const calcDp    = $('#calcDp');
  const calcTenor = $('#calcTenor');
  if (!calcCar || !calcDp || !calcTenor) return;

  // Params from server, with fallbacks reproducing the old hardcoded behavior.
  const C = App.CREDIT || {};
  const RATE = typeof C.rate === 'number' ? C.rate : 0.04;

  function priceOf() {
    const opt = calcCar.options[calcCar.selectedIndex];
    if (opt && opt.dataset && opt.dataset.price) return +opt.dataset.price || 0;
    // Fallback: look up by id in window.App.CARS.
    const id = +calcCar.value;
    const car = (App.CARS || []).find((c) => +c.id === id);
    return car ? +car.price || 0 : 0;
  }

  function updateCalc() {
    const price = priceOf();
    const dpPct = +calcDp.value;
    const years = +calcTenor.value;

    const dpAmount = price * dpPct / 100;
    const loan = price - dpAmount;
    const totalInterest = loan * RATE * years;
    const monthly = years > 0 ? (loan + totalInterest) / (years * 12) : 0;

    $('#calcPriceLabel').textContent = formatRupiah(price);
    $('#calcDpLabel').textContent = dpPct + '%';
    $('#calcTenorLabel').textContent = years + ' Tahun';
    $('#calcResult').textContent = formatRupiah(Math.round(monthly));
    $('#calcDpAmount').textContent = formatRupiah(dpAmount);
    $('#calcLoan').textContent = formatRupiah(loan);
  }

  calcCar.addEventListener('change', updateCalc);
  [calcDp, calcTenor].forEach((el) => el.addEventListener('input', updateCalc));
  updateCalc();
})(window.App);
