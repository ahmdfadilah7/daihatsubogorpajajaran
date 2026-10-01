// Expose jQuery on the global scope BEFORE DataTables is imported.
//
// DataTables 3.x auto-registers its jQuery plugin (`$.fn.dataTable`) when it
// sees `window.jQuery` at module-evaluation time (see the tail of
// datatables.net/js/dataTables.mjs). ES module imports are hoisted and run in
// source order, so this assignment must live in its OWN module that is
// imported ahead of any `datatables.net` import — a `window.jQuery = $`
// statement placed after an `import 'datatables.net'` in the same file would
// run too late. admin-tables.js imports this module first for that reason.
import $ from 'jquery';

window.$ = window.jQuery = $;

export default $;
