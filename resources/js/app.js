import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

// Expose Chart.js globally so Blade-pushed init scripts (e.g. the admin
// dashboard widgets) can call `new window.Chart(...)` after the bundle loads.
window.Chart = Chart;

window.Alpine = Alpine;

Alpine.start();
