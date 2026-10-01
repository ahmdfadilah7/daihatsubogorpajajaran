// Branded delete-confirmation modal for the admin dashboard.
//
// Replaces the native `confirm()` on every delete form. A single Alpine store
// drives one modal that lives once in the admin layout
// (admin/partials/confirm-modal.blade.php). Any `<form data-confirm="...">`
// that is submitted is intercepted here, its submission paused, and the modal
// opened; only after the user presses "Hapus" does the form actually POST to
// its `admin.<entity>.destroy` route (with @csrf + @method('DELETE') intact).
//
// The interception uses ONE delegated `submit` listener on `document`. This is
// deliberate: DataTables moves rows in/out of the DOM across search / sort /
// pagination, so per-row listeners (or Alpine @click bindings) could be lost
// when a row is detached. A delegated document-level listener keeps working for
// a row on page 2 or after a search filter.

const DEFAULT_MESSAGE = 'Tindakan ini tidak dapat dibatalkan.';

function openDialog(message, onConfirm) {
    const store = window.Alpine?.store('confirmDialog');
    if (!store) {
        // Alpine not ready (should not happen in practice) — fall back to the
        // native confirm so deletion is never silently blocked.
        if (window.confirm(message || DEFAULT_MESSAGE)) {
            onConfirm();
        }
        return;
    }
    store.open(message, onConfirm);
}

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.matches('form[data-confirm]')) {
        return;
    }

    // Already confirmed by the modal — let this (programmatic) submit through.
    if (form.dataset.confirmed === 'true') {
        return;
    }

    event.preventDefault();

    const message = form.getAttribute('data-confirm') || DEFAULT_MESSAGE;

    openDialog(message, () => {
        form.dataset.confirmed = 'true';
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    });
});

// Register the Alpine store that the modal partial binds to.
document.addEventListener('alpine:init', () => {
    window.Alpine.store('confirmDialog', {
        show: false,
        message: DEFAULT_MESSAGE,
        onConfirm: null,

        open(message, onConfirm) {
            this.message = message || DEFAULT_MESSAGE;
            this.onConfirm = typeof onConfirm === 'function' ? onConfirm : null;
            this.show = true;
        },

        close() {
            this.show = false;
            this.onConfirm = null;
        },

        confirm() {
            const callback = this.onConfirm;
            this.show = false;
            this.onConfirm = null;
            if (callback) {
                callback();
            }
        },
    });
});
