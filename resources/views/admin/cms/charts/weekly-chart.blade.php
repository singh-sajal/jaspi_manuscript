<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById("weeklyApplicationChart").getContext("2d");
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($charts['lastSevenDaysApplicationChart']['labels']),
                datasets: [{
                    label: 'Last 7 Days Applications',
                    data: @json($charts['lastSevenDaysApplicationChart']['data']),
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
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>
