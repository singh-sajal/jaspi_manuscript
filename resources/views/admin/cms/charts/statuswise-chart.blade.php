<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById("applicationStatusChart").getContext("2d");
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($charts['applicationStatusChart']['labels']),
                datasets: [{
                    label: 'Applications',
                    data: @json($charts['applicationStatusChart']['data']),
                    backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b']
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>
