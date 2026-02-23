// ── Sidebar toggle ──────────────────────────────────────────
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');

document.getElementById('sidebarToggle')?.addEventListener('click', function () {
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('show');
        overlay?.classList.toggle('show');
    } else {
        sidebar.classList.toggle('collapsed');
    }
});

overlay?.addEventListener('click', function () {
    sidebar.classList.remove('show');
    overlay.classList.remove('show');
});

// ── DataTables (Bootstrap 5 skin) ───────────────────────────
function initDataTables() {
    $('.datatable').each(function () {
        if ($.fn.DataTable.isDataTable(this)) {
            $(this).DataTable().destroy();
        }

        const tableId = $(this).attr('id');

        // Column-specific settings per table
        let columnDefs = [{ orderable: false, targets: -1 }];

        // Attendance table: also disable ordering on Break column (index 3 — contains HTML)
        if (tableId === 'attendance-table') {
            columnDefs = [
                { orderable: false, targets: [3, 6] }   // Break col + Status col
            ];
        }

        $(this).DataTable({
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],

            //  Toolbar:  [Search LEFT]  ·············  [Buttons RIGHT]
            //  Table:    [t]
            //  Footer:   [Show · Info]  ·············  [Pagination]
            dom:
                "<'dt-toolbar d-flex align-items-center justify-content-between gap-3 px-3 py-3'fB>" +
                "<'table-responsive'tr>" +
                "<'dt-footer d-flex align-items-center justify-content-between flex-wrap gap-2 px-3 py-2'<'d-flex align-items-center gap-3'li>p>",

            buttons: [
                {
                    extend:    'csvHtml5',
                    text:      '<i class="bi bi-filetype-csv me-1"></i>CSV',
                    titleAttr: 'Export CSV',
                    className: 'dt-export-btn'
                },
                {
                    extend:    'pdfHtml5',
                    text:      '<i class="bi bi-filetype-pdf me-1"></i>PDF',
                    titleAttr: 'Export PDF',
                    className: 'dt-export-btn dt-export-pdf',
                    orientation: 'landscape',
                    pageSize:    'A4',
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend:    'print',
                    text:      '<i class="bi bi-printer me-1"></i>Print',
                    titleAttr: 'Print',
                    className: 'dt-export-btn'
                }
            ],

            language: {
                search:            '',
                searchPlaceholder: 'Search…',
                lengthMenu:        'Show _MENU_',
                info:              'Showing <strong>_START_–_END_</strong> of <strong>_TOTAL_</strong>',
                infoEmpty:         'No records',
                emptyTable:        'No data available',
                paginate: {
                    next:     '<i class="bi bi-chevron-right"></i>',
                    previous: '<i class="bi bi-chevron-left"></i>'
                }
            },

            columnDefs: columnDefs
        });
    });
}

// Intercept GET forms (like filters) to use AJAX instead of URL-based page reloads
$(document).on('submit', 'form[method="GET"]:not(.no-ajax)', function(e) {
    if ($(this).find('input[type="date"], input[type="month"], select, input[type="text"]').length > 0) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action') || window.location.pathname;
        const formData = form.serialize();
        const fullUrl = url + '?' + formData;
        
        const btn = form.find('button[type="submit"]');
        const origContent = btn.html();
        if (btn.length > 0) {
            btn.html('<span class="spinner-border spinner-border-sm me-1" style="width:14px;height:14px;"></span> Filtering...');
            btn.prop('disabled', true);
        }

        $.ajax({
            url: fullUrl,
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                // Parse full HTML string into a DOM
                const parser = new DOMParser();
                const doc = parser.parseFromString(response, 'text/html');
                const newContent = doc.querySelector('main.content');

                if (newContent) {
                    // Update the page content smoothly
                    $('main.content').html(newContent.innerHTML);
                    // Reinitialize custom elements like Datatables
                    initDataTables();
                } else {
                    window.location.href = fullUrl; // Fallback
                }
            },
            error: function() {
                window.location.href = fullUrl; // Fallback
            },
            complete: function() {
                // If DOM wasn't replaced (like on error) restore the button
                if (document.body.contains(btn[0])) {
                    btn.html(origContent);
                    btn.prop('disabled', false);
                }
            }
        });
    }
});

$(document).ready(function () {
    initDataTables();
});

