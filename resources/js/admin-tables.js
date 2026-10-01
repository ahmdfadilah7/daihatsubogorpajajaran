// Advanced table enhancement for the admin dashboard.
//
// Finds every `<table data-dt>` rendered by an admin index view and upgrades it
// with the full DataTables.net library: client-side search, sortable column
// headers, pagination with a page-size selector, a responsive layout, and a
// "Menampilkan X–Y dari N data" info line.
//
// Columns listed in the table's `data-dt-nosort` attribute (a comma-separated
// list of 0-based column indexes — always the final "Aksi" column, plus any
// image/preview column) are marked `orderable: false` via `columnDefs`.
//
// jQuery is required by DataTables and is set on `window` by the
// `./jquery-global` module, which is imported FIRST below so it runs before
// the `datatables.net-*` modules evaluate.
//
// NOTE on the delete-confirmation modal: unlike simple-datatables, DataTables
// keeps the existing <td> DOM you authored (it does not rebuild rows from
// parsed data), so the per-row delete forms survive search / sort / paging.
// The confirm-delete behaviour additionally relies on a single delegated
// `submit` listener on `document` (see confirm-delete.js), which keeps working
// for rows on any page regardless of re-rendering.

import './jquery-global';

// `datatables.net-dt` is the default styling build; `-responsive-dt` adds the
// Responsive extension plus its styling. Both re-export the same DataTable
// constructor, which we drive directly (`new DataTable(table, {...})`).
import DataTable from 'datatables.net-dt';
import 'datatables.net-responsive-dt';

// Bundle the vendor stylesheets through Vite. app.css adds brand/slate
// overrides on top of these.
import 'datatables.net-dt/css/dataTables.dataTables.css';
import 'datatables.net-responsive-dt/css/responsive.dataTables.css';

const LANGUAGE = {
    search: 'Cari:',
    searchPlaceholder: '',
    lengthMenu: 'Tampilkan _MENU_ data',
    info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
    infoEmpty: 'Tidak ada data',
    infoFiltered: '(disaring dari _MAX_ total data)',
    zeroRecords: 'Tidak ada data yang cocok',
    emptyTable: 'Belum ada data',
    paginate: {
        first: 'Pertama',
        last: 'Terakhir',
        next: 'Berikutnya',
        previous: 'Sebelumnya',
    },
};

function parseNoSort(table) {
    const raw = table.getAttribute('data-dt-nosort');
    if (!raw) {
        return [];
    }
    return raw
        .split(',')
        .map((part) => parseInt(part.trim(), 10))
        .filter((n) => Number.isInteger(n));
}

function initTable(table) {
    // Skip tables that were already enhanced (defensive against double init).
    if (DataTable.isDataTable(table)) {
        return;
    }

    const noSort = parseNoSort(table);

    const columnDefs = noSort.length
        ? [{ orderable: false, targets: noSort }]
        : [];

    // eslint-disable-next-line no-new
    new DataTable(table, {
        searching: true,
        ordering: true,
        paging: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        responsive: true,
        autoWidth: false,
        language: LANGUAGE,
        columnDefs,
    });
}

export function initAdminTables() {
    const tables = document.querySelectorAll('table[data-dt]');
    tables.forEach((table) => {
        // Guard each table independently so one bad table never blocks the rest.
        try {
            initTable(table);
        } catch (error) {
            // eslint-disable-next-line no-console
            console.error('Gagal menginisialisasi tabel admin:', error);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminTables);
} else {
    initAdminTables();
}
