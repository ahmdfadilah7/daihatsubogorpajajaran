// Bulk-select behaviour for the admin index pages.
//
// Registers an Alpine component `bulkSelect()` that wraps each list table and
// drives the "Hapus Terpilih" (bulk delete) toolbar. It keeps a reactive array
// of selected string ids so a selection made on page 1 survives navigating to
// page 2 — DataTables detaches off-page rows from the DOM, so a DOM-only
// select-all cannot persist the selection.
//
// The "Hapus Terpilih" button opens the SAME confirm modal used by single-row
// deletes (`$store.confirmDialog`), then submits a hidden DELETE form whose
// `ids[]` inputs are rendered from the reactive array.

document.addEventListener('alpine:init', () => {
    window.Alpine.data('bulkSelect', () => ({
        // Selected row ids as strings (ids come from the DOM as strings).
        ids: [],

        get count() {
            return this.ids.length;
        },

        isChecked(id) {
            return this.ids.includes(String(id));
        },

        toggle(id) {
            const key = String(id);
            const i = this.ids.indexOf(key);
            if (i === -1) {
                this.ids.push(key);
            } else {
                this.ids.splice(i, 1);
            }
        },

        // Checkboxes currently rendered inside this component's table. Only the
        // active DataTables page is in the DOM, so this is page-scoped by nature.
        pageCheckboxes() {
            return Array.from(this.$root.querySelectorAll('input.row-check'));
        },

        // True only when every checkbox on the current page is selected (and at
        // least one exists), used to reflect the header select-all state.
        get allOnPageChecked() {
            const boxes = this.pageCheckboxes();
            return boxes.length > 0 && boxes.every((box) => this.isChecked(box.value));
        },

        // Select / deselect every checkbox on the current page.
        toggleAllOnPage(event) {
            const check = event.target.checked;
            this.pageCheckboxes().forEach((box) => {
                const key = String(box.value);
                const i = this.ids.indexOf(key);
                if (check && i === -1) {
                    this.ids.push(key);
                } else if (!check && i !== -1) {
                    this.ids.splice(i, 1);
                }
            });
        },

        // Open the shared confirm modal; on confirm, submit the bulk form.
        openConfirm() {
            if (this.count === 0) {
                return;
            }

            const message = this.count
                + ' data terpilih akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.';

            const store = window.Alpine?.store('confirmDialog');
            if (!store) {
                // Alpine store unavailable — fall back to native confirm so the
                // action is never silently blocked.
                if (window.confirm(message)) {
                    this.submit();
                }
                return;
            }

            store.open(message, () => this.submit());
        },

        // Render one hidden `ids[]` input per selected id into the form's
        // container, then submit the DELETE form.
        submit() {
            const form = this.$root.querySelector('form[data-bulk-form]');
            if (!form) {
                return;
            }

            const container = form.querySelector('[data-bulk-ids]');
            if (!container) {
                return;
            }

            container.innerHTML = '';
            this.ids.forEach((id) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                container.appendChild(input);
            });

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        },
    }));
});
