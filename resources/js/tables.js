import { Grid, html } from 'gridjs';
import 'gridjs/dist/theme/mermaid.min.css';

function renderTables() {
    document.querySelectorAll('.js-datatable').forEach((el) => {
        if (el.dataset.gridBooted) {
            return;
        }
        el.dataset.gridBooted = 'true';

        const columns = JSON.parse(el.dataset.columns);
        const rows = JSON.parse(el.dataset.rows);
        const paginate = el.dataset.paginate !== 'false';

        new Grid({
            columns: columns.map((column) => ({
                name: column.name,
                formatter: column.html ? (cell) => html(cell ?? '') : undefined,
            })),
            data: rows,
            search: rows.length > 0,
            sort: true,
            pagination: paginate && rows.length > 10 ? { limit: 10 } : false,
            language: {
                search: { placeholder: 'Cerca…' },
                pagination: {
                    previous: 'Precedente',
                    next: 'Successiva',
                    showing: 'Da',
                    to: 'a',
                    of: 'di',
                    results: 'risultati',
                },
                noRecordsFound: 'Nessun dato disponibile.',
            },
            className: {
                table: 'w-full text-sm text-left',
                th: '!bg-gray-50 !text-gray-700 !font-semibold',
                search: 'mb-4',
                pagination: 'mt-4 text-sm',
            },
        }).render(el);
    });
}

document.addEventListener('DOMContentLoaded', renderTables);
