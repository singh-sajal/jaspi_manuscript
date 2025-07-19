<canvas id="approvedRejectedChart" height="250"></canvas>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById("approvedRejectedChart").getContext("2d");
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($charts['approvedAndRejectedChart']['labels']),
                datasets: [{
                    label: 'Approved vs Rejected',
                    data: @json($charts['approvedAndRejectedChart']['data']),
                    backgroundColor: ['#36b9cc', '#e74a3b']
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { beginAtZero: true }
                }
            }
        });
    });
</script>
