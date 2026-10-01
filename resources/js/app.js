import './bootstrap';

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import Chart from 'chart.js/auto';

// Expose Chart.js globally so Blade-pushed init scripts (e.g. the admin
// dashboard widgets) can call `new window.Chart(...)` after the bundle loads.
window.Chart = Chart;

window.Alpine = Alpine;

// Focus plugin powers the confirm-modal's focus trap + Esc handling (x-trap).
Alpine.plugin(focus);

// Delete-confirmation modal store + delegated submit listener. Registers an
// `alpine:init` handler, so import it BEFORE Alpine.start().
import './confirm-delete';

// Bulk-select component for the admin index pages. Registers an `alpine:init`
// handler, so import it BEFORE Alpine.start().
import './bulk-select';

// Advanced tables (DataTables.net) on the admin index pages. Self-defers to
// DOMContentLoaded, so import order relative to Alpine.start() does not matter.
import './admin-tables';

Alpine.start();
