<script>
    let monthlyChartInstance;

    function renderMonthlyChart(labels, data) {
        const ctx = document.getElementById("monthlyApplicationChart").getContext("2d");
        if (monthlyChartInstance) {
            monthlyChartInstance.destroy();
        }
        monthlyChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Manuscripts per Month',
                    data: data,
                    backgroundColor: '#ff9f00'
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
    }

    document.addEventListener("DOMContentLoaded", function() {
        renderMonthlyChart(
            @json($charts['monthlyApplicationChart']['labels']),
            @json($charts['monthlyApplicationChart']['data'])
        );

        const yearSelect = document.getElementById('monthlyChartYear');
        console.log('monthlyChartYear element:', yearSelect); // remove after confirming it works

        yearSelect?.addEventListener('change', function() {
            fetch(`{{ route('admin.dashboard.monthly-chart') }}?year=${this.value}`)
                .then(res => res.json())
                .then(json => renderMonthlyChart(json.labels, json.data))
                .catch(err => console.error('Monthly chart fetch failed:', err));
        });
    });
</script>
