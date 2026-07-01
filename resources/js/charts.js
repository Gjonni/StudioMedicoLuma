import {
    Chart,
    BarController,
    BarElement,
    DoughnutController,
    ArcElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
} from 'chart.js';

Chart.register(
    BarController,
    BarElement,
    DoughnutController,
    ArcElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
);

function renderCharts() {
    document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        if (canvas.dataset.chartRendered) {
            return;
        }
        canvas.dataset.chartRendered = 'true';

        const config = JSON.parse(canvas.dataset.chart);
        new Chart(canvas.getContext('2d'), config);
    });
}

document.addEventListener('DOMContentLoaded', renderCharts);
