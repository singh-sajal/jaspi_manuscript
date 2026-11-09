<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById("applicationStatusChart").getContext("2d");
        const labels = @json($charts['applicationStatusChart']['labels']);
        const data = @json($charts['applicationStatusChart']['data']);

        const backgroundColor = labels.map(label => getStatusColor(label));

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Applications',
                    data: data,
                    backgroundColor: backgroundColor
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

    // Curated palette for readability, used first; anything beyond it
    // falls back to a deterministically generated HSL color so colors
    // stay distinct no matter how many statuses exist.
    const STATUS_PALETTE = [
        '#4e73df', '#1cc88a', '#f6c23e', '#e74a3b', '#36b9cc',
        '#858796', '#fd7e14', '#6f42c1', '#20c997', '#d63384',
        '#0dcaf0', '#198754', '#dc3545', '#6610f2', '#ffc107',
        '#0d6efd', '#adb5bd', '#157347', '#b02a37', '#5a3d99'
    ];

    function hashStringToHue(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        return Math.abs(hash) % 360;
    }

    function getStatusColor(label) {
        const index = STATUS_PALETTE.findIndex((_, i) => i === STATUS_PALETTE.length);
        // Use a stable index based on label hash so the same status
        // always gets the same color, even if order changes between loads.
        const hash = Math.abs(
            label.split('').reduce((acc, c) => acc + c.charCodeAt(0), 0)
        );

        if (hash < STATUS_PALETTE.length * 17) {
            return STATUS_PALETTE[hash % STATUS_PALETTE.length];
        }

        // Beyond the curated palette, generate a distinct HSL color
        const hue = hashStringToHue(label);
        return `hsl(${hue}, 65%, 55%)`;
    }
</script>
