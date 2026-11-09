<script>
    let weeklyChartInstance;
    let currentWeekOffset = 0;

    function renderWeeklyChart(labels, data, rangeLabel) {
        const ctx = document.getElementById("weeklyApplicationChart").getContext("2d");
        if (weeklyChartInstance) {
            weeklyChartInstance.destroy();
        }
        weeklyChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Manuscripts Submitted',
                    data: data,
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.2)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 }
                    }
                },
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        const rangeEl = document.getElementById('weeklyChartRange');
        if (rangeEl && rangeLabel) {
            rangeEl.textContent = rangeLabel;
        }
    }

    function loadWeeklyChart(offset) {
        fetch(`{{ route('admin.dashboard.weekly-chart') }}?offset=${offset}`)
            .then(res => res.json())
            .then(json => {
                currentWeekOffset = json.weekOffset;
                renderWeeklyChart(json.labels, json.data, json.rangeLabel);
            })
            .catch(err => console.error('Weekly chart fetch failed:', err));
    }

    document.addEventListener("DOMContentLoaded", function() {
        renderWeeklyChart(
            @json($charts['lastSevenDaysApplicationChart']['labels']),
            @json($charts['lastSevenDaysApplicationChart']['data']),
            @json($charts['lastSevenDaysApplicationChart']['rangeLabel'] ?? '')
        );

        document.getElementById('weeklyChartPrev')?.addEventListener('click', function() {
            loadWeeklyChart(currentWeekOffset - 1);
        });
        document.getElementById('weeklyChartNext')?.addEventListener('click', function() {
            loadWeeklyChart(currentWeekOffset + 1);
        });
    });
</script>
