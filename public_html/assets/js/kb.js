// ── KB: Dashboard — daily reads chart (stacked by source) ────────────────────

(function () {
    var canvas = document.getElementById('chart-kb-daily-views');
    if (!canvas) return;

    var raw  = document.getElementById('kb-chart-data');
    var data = raw ? JSON.parse(raw.textContent) : { labels: [], web: [], remote: [], ai: [] };

    // Total per day across all sources (for trend line + bar labels)
    var totals = data.labels.map(function (_, i) {
        return (data.web[i] || 0) + (data.remote[i] || 0) + (data.ai[i] || 0);
    });

    // Linear regression over daily totals
    function trendLine(values) {
        var n = values.length;
        if (n < 2) return values.slice();
        var sumX = 0, sumY = 0, sumXY = 0, sumXX = 0;
        for (var i = 0; i < n; i++) {
            sumX  += i;
            sumY  += values[i];
            sumXY += i * values[i];
            sumXX += i * i;
        }
        var denom     = (n * sumXX - sumX * sumX) || 1;
        var slope     = (n * sumXY - sumX * sumY) / denom;
        var intercept = (sumY - slope * sumX) / n;
        return values.map(function (_, i) {
            return Math.max(0, Math.round((slope * i + intercept) * 10) / 10);
        });
    }

    // Custom plugin: draw daily total above the stacked bar
    var barLabelsPlugin = {
        id: 'barLabels',
        afterDatasetsDraw: function (chart) {
            var ctx = chart.ctx;
            ctx.save();
            ctx.font         = 'bold 11px "Open Sans", sans-serif';
            ctx.fillStyle    = '#34495e';
            ctx.textAlign    = 'center';
            ctx.textBaseline = 'bottom';

            // Use the top (last) stacked bar meta for y position
            var lastBarMeta = chart.getDatasetMeta(2); // AI is last stacked dataset
            lastBarMeta.data.forEach(function (bar, i) {
                var total = totals[i];
                if (!total) return;
                // bar.y is the top of the last segment; find the actual stack top
                var stackTop = chart.scales.y.getPixelForValue(totals[i]);
                ctx.fillText(total, bar.x, stackTop - 3);
            });
            ctx.restore();
        }
    };

    /* global Chart */
    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Web',
                    data: data.web,
                    backgroundColor: 'rgba(52, 120, 190, 0.8)',
                    borderColor:     'rgba(52, 120, 190, 1)',
                    borderWidth: 1,
                    borderRadius: 0,
                    stack: 'reads',
                    order: 2,
                },
                {
                    type: 'bar',
                    label: 'Remote Application',
                    data: data.remote,
                    backgroundColor: 'rgba(39, 174, 96, 0.8)',
                    borderColor:     'rgba(39, 174, 96, 1)',
                    borderWidth: 1,
                    borderRadius: 0,
                    stack: 'reads',
                    order: 2,
                },
                {
                    type: 'bar',
                    label: 'AI',
                    data: data.ai,
                    backgroundColor: 'rgba(142, 68, 173, 0.8)',
                    borderColor:     'rgba(142, 68, 173, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                    stack: 'reads',
                    order: 2,
                },
                {
                    type: 'line',
                    label: 'Trend',
                    data: trendLine(totals),
                    borderColor:  'rgba(231, 76, 60, 0.85)',
                    borderWidth: 2,
                    borderDash: [5, 4],
                    pointRadius: 0,
                    fill: false,
                    tension: 0,
                    order: 1,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true }
            },
            scales: {
                x: { stacked: true },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: { stepSize: 1, precision: 0 }
                }
            }
        },
        plugins: [barLabelsPlugin]
    });
}());
