/**
 * ZapFlow real-time notifications listener.
 *
 * Subscribes to private per-company channels for payments, visa, and vouchers.
 * Shows a toast (SweetAlert2 / Toast) on each broadcast and refreshes the
 * relevant DataTable if one is on the page.
 *
 * Loaded from resources/views/layouts/app.blade.php after Echo is ready.
 */
(function () {
    'use strict';

    if (!window.Echo) {
        // Echo only initializes if MIX_PUSHER_APP_KEY is set. Without Pusher creds
        // we degrade gracefully — toasts just won't appear.
        return;
    }

    const companyId = window.ZapFlowCompanyId || null;
    if (!companyId) return;

    const userId = window.ZapFlowUserId || null;
    const toast = (icon, title, body) => {
        if (!window.Swal || !window.Swal.mixin) return;
        const Toast = window.Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4500,
            timerProgressBar: true,
        });
        Toast.fire({ icon, title, text: body });
    };

    const money = (amount, currency) => {
        try {
            return new Intl.NumberFormat(undefined, {
                style: 'currency',
                currency: currency || 'USD',
            }).format(amount);
        } catch (e) {
            return `${currency || ''} ${amount}`;
        }
    };

    const refreshDataTable = (selector) => {
        const table = window.LaravelDataTables && selector ? window.LaravelDataTables[selector] : null;
        if (table && typeof table.draw === 'function') {
            table.draw(false);
        }
    };

    // ── Travel Payments ────────────────────────────────────────────────────
    window.Echo.private(`travel-payments.${companyId}`)
        .listen('.payment.received', (e) => {
            toast('success',
                '💰 Payment received',
                `${money(e.amount, e.currency)} from ${e.received_from || 'unknown'}`
            );
            refreshDataTable('travel-payments-table');
        })
        .listen('.payment.made', (e) => {
            toast('warning',
                '💸 Payment sent',
                `${money(e.amount, e.currency)} to ${e.paid_to || 'supplier'}`
            );
            refreshDataTable('travel-payments-table');
        });

    // ── Visa Pipeline ──────────────────────────────────────────────────────
    window.Echo.private(`visa-pipeline.${companyId}`)
        .listen('.visa.status-changed', (e) => {
            toast('info',
                `Visa: ${e.from_status || '—'} → ${e.to_status}`,
                `${e.passenger_name} (${e.booking_group || 'no group'})`
            );
            // The pipeline page uses a custom kanban; just redraw the DataTable fallback.
            refreshDataTable('visa-pipeline-table');
        });

    // ── Vouchers ───────────────────────────────────────────────────────────
    window.Echo.private(`vouchers.${companyId}`)
        .listen('.voucher.redeemed', (e) => {
            toast('success',
                `✅ Voucher redeemed: ${e.voucher_number}`,
                `${e.hotel || ''} ${e.passenger ? '· ' + e.passenger : ''}`
            );
            refreshDataTable('vouchers-table');
        });
})();
