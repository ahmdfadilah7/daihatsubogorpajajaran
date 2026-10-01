/* =========================================================================
   CALCULATOR — Kalkulator cicilan interaktif (real-time).
   Rumus bunga flat sederhana untuk estimasi angsuran.
   ========================================================================= */
window.App = window.App || {};

(function (App) {
  const { $, formatRupiah } = App;

  const calcPrice = $('#calcPrice');
  const calcDp    = $('#calcDp');
  const calcTenor = $('#calcTenor');
  if (!calcPrice) return;

  const RATE = 0.04; // bunga flat per tahun (estimasi)

  function updateCalc() {
    const price = +calcPrice.value;
    const dpPct = +calcDp.value;
    const years = +calcTenor.value;

    const dpAmount = price * dpPct / 100;
    const loan = price - dpAmount;
    const totalInterest = loan * RATE * years;
    const monthly = (loan + totalInterest) / (years * 12);

    $('#calcPriceLabel').textContent = formatRupiah(price);
    $('#calcDpLabel').textContent = dpPct + '%';
    $('#calcTenorLabel').textContent = years + ' Tahun';
    $('#calcResult').textContent = formatRupiah(Math.round(monthly));
    $('#calcDpAmount').textContent = formatRupiah(dpAmount);
    $('#calcLoan').textContent = formatRupiah(loan);
  }

  [calcPrice, calcDp, calcTenor].forEach((el) => el.addEventListener('input', updateCalc));
  updateCalc();
})(window.App);
