<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById("monthlyApplicationChart").getContext("2d");
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($charts['monthlyApplicationChart']['labels']),
                datasets: [{
                    label: 'Applications per Month',
                    data: @json($charts['monthlyApplicationChart']['data']),
                    backgroundColor: '#ff9f00'
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
