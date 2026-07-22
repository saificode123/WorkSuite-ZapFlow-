<div id="commandPaletteOverlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.4);z-index:9999;" onclick="closeCommandPalette(event)"></div>
<div id="commandPalette" style="display:none;position:fixed;top:80px;left:50%;transform:translateX(-50%);width:600px;max-width:90vw;z-index:10000;background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;">
    <div style="padding:16px 20px;border-bottom:1px solid #e5e7eb;">
        <div style="display:flex;align-items:center;">
            <i class="fa fa-search text-muted mr-2"></i>
            <input type="text" id="cp-search" autofocus placeholder="Search bookings, passengers, vouchers, customers..." 
                   style="flex:1;border:none;outline:none;font-size:15px;background:transparent;">
            <kbd style="background:#f3f4f6;padding:2px 8px;border-radius:4px;font-size:11px;color:#6b7280;border:1px solid #d1d5db;">ESC</kbd>
        </div>
    </div>
    <div id="cp-results" style="max-height:400px;overflow-y:auto;padding:8px 0;">
        <div style="padding:20px;text-align:center;color:#9ca3af;">
            <i class="fa fa-search fa-2x d-block mb-2"></i>
            <small>@lang('app.typeToSearch')</small>
        </div>
    </div>
    <div style="padding:8px 16px;border-top:1px solid #e5e7eb;display:flex;gap:16px;font-size:11px;color:#9ca3af;">
        <span><kbd style="background:#f3f4f6;padding:0 6px;border-radius:3px;border:1px solid #d1d5db;">↑↓</kbd> @lang('app.navigate')</span>
        <span><kbd style="background:#f3f4f6;padding:0 6px;border-radius:3px;border:1px solid #d1d5db;">⏎</kbd> @lang('app.open')</span>
    </div>
</div>

@push('scripts')
<script>
let cpResults = [];
let cpIndex = -1;
let cpDebounce = null;

$(document).on('keydown', function(e) {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        toggleCommandPalette();
    }
    if (e.key === 'Escape') {
        closeCommandPalette();
    }
});

function toggleCommandPalette() {
    const isOpen = $('#commandPalette').is(':visible');
    if (isOpen) {
        closeCommandPalette();
    } else {
        openCommandPalette();
    }
}

function openCommandPalette() {
    $('#commandPaletteOverlay, #commandPalette').fadeIn(150);
    setTimeout(() => $('#cp-search').focus(), 200);
    cpResults = [];
    cpIndex = -1;
    $('#cp-search').val('').trigger('input');
}

function closeCommandPalette(e) {
    if (e && $(e.target).attr('id') !== 'commandPaletteOverlay') return;
    $('#commandPaletteOverlay, #commandPalette').fadeOut(100);
}

$('#cp-search').on('input', function() {
    clearTimeout(cpDebounce);
    const q = $(this).val();
    if (q.length < 2) {
        $('#cp-results').html(`<div style="padding:20px;text-align:center;color:#9ca3af;"><i class="fa fa-search fa-2x d-block mb-2"></i><small>@lang('app.typeToSearch')</small></div>`);
        return;
    }
    cpDebounce = setTimeout(() => search(q), 250);
});

function search(q) {
    $.easyAjax({
        url: "{{ route('command-palette.search') }}",
        type: 'GET',
        data: { q },
        success: function(response) {
            const items = response.data?.results || [];
            cpResults = items;
            cpIndex = -1;
            renderResults(items);
        }
    });
}

function renderResults(items) {
    if (!items.length) {
        $('#cp-results').html(`<div style="padding:20px;text-align:center;color:#9ca3af;"><i class="fa fa-times-circle fa-2x d-block mb-2"></i><small>@lang('app.noResults')</small></div>`);
        return;
    }
    let html = '';
    items.forEach((item, i) => {
        const typeLabels = { booking: 'Booking', passenger: 'Passenger', voucher: 'Voucher', customer: 'Customer' };
        html += `<a href="${item.url}" class="cp-item" data-index="${i}" style="display:flex;align-items:center;padding:10px 20px;text-decoration:none;color:#333;border-left:3px solid transparent;transition:all .1s;"
            onmouseenter="cpIndex=${i};highlightItem()" onclick="navigateTo('${item.url}')">
            <span style="width:32px;height:32px;border-radius:8px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;margin-right:12px;flex-shrink:0;">
                <i class="fa ${item.icon} text-muted"></i>
            </span>
            <div style="flex:1;min-width:0;">
                <div style="font-size:14px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escapeHtml(item.label)}</div>
                <div style="font-size:11px;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <span style="display:inline-block;padding:0 6px;border-radius:3px;background:#e5e7eb;font-size:10px;margin-right:6px;">${typeLabels[item.type] || item.type}</span>
                    ${escapeHtml(item.sub)}
                </div>
            </div>
            <i class="fa fa-chevron-right text-muted" style="font-size:12px;margin-left:8px;"></i>
        </a>`;
    });
    $('#cp-results').html(html);
    highlightItem();
}

function highlightItem() {
    $('.cp-item').css({ background: '', borderLeftColor: 'transparent' });
    const el = $(`.cp-item[data-index="${cpIndex}"]`);
    if (el.length) {
        el.css({ background: '#f3f4f6', borderLeftColor: '#6366f1' });
        el[0].scrollIntoView({ block: 'nearest' });
    }
}

$('#cp-search').on('keydown', function(e) {
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        cpIndex = Math.min(cpIndex + 1, cpResults.length - 1);
        highlightItem();
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        cpIndex = Math.max(cpIndex - 1, 0);
        highlightItem();
    } else if (e.key === 'Enter') {
        e.preventDefault();
        const item = cpResults[cpIndex];
        if (item) navigateTo(item.url);
    }
});

function navigateTo(url) {
    if (url) window.location.href = url;
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
}
</script>
@endpush
