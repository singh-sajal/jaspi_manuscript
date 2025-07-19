<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tryInit = () => {
            if (typeof SaarniJs === 'undefined') {
                return setTimeout(tryInit, 1000); // Retry until SaarniJs is defined
            }

            try {
                if (!window.saarni) {
                    window.saarni = new SaarniJs(
                        "{{ $options['selector'] ?? 'Saarni' }}",
                        "{{ $options['url'] }}",
                        "{{ $options['moduleName'] }}"
                    );
                }
            } catch (e) {
                console.error("Error initializing SaarniJs:", e);
            }
        };

        tryInit();
    });
</script>
