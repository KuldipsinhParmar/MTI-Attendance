// ── Sidebar toggle ──────────────────────────────────────────
document.getElementById('sidebarToggle')?.addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('collapsed');
});

// ── DataTables (Bootstrap 5 skin) ───────────────────────────
$(document).ready(function () {
    $('.datatable').each(function () {
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

            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });
    });
});
