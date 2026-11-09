(function () {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const dropdown = document.querySelector('[data-notification-dropdown]');
    const badge = document.querySelector('[data-notification-count]');
    const list = document.querySelector('[data-notification-list]');
    const markAllButtons = document.querySelectorAll('[data-notification-mark-all]');
    const isNotificationsPage = Boolean(document.querySelector('[data-notification-page]'));

    function setCount(count) {
        if (!badge) {
            return;
        }

        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('d-none');
        } else {
            badge.textContent = '';
            badge.classList.add('d-none');
        }

        markAllButtons.forEach(function (button) {
            button.classList.toggle('d-none', count === 0);
        });
    }

    async function refresh() {
        if (!dropdown) {
            return;
        }

        const url = dropdown.dataset.latestUrl;

        if (!url) {
            return;
        }

        try {
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (list && typeof data.html === 'string') {
                list.innerHTML = data.html;
            }

            setCount(Number(data.count) || 0);
        } catch (error) {
            // Polling is best-effort; the next tick will retry.
        }
    }

    async function request(url, method) {
        const response = await fetch(url, {
            method: method || 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        let data = {};

        try {
            data = await response.json();
        } catch (error) {
            // Keep the status code as the source of truth.
        }

        if (!response.ok) {
            throw new Error(data.msg || 'The request could not be completed.');
        }

        return data;
    }

    function showError(error) {
        if (window.toastr) {
            window.toastr.error(error.message || 'Something went wrong.');
        }
    }

    markAllButtons.forEach(function (button) {
        button.addEventListener('click', async function (event) {
            event.preventDefault();

            const url = button.dataset.url;

            if (!url) {
                return;
            }

            button.disabled = true;

            try {
                await request(url, 'POST');

                if (isNotificationsPage) {
                    window.location.reload();
                    return;
                }

                await refresh();
            } catch (error) {
                showError(error);
            } finally {
                button.disabled = false;
            }
        });
    });

    document.addEventListener('click', async function (event) {
        const markButton = event.target.closest('[data-notification-mark-read]');
        const deleteButton = event.target.closest('[data-notification-delete]');
        const clearButton = event.target.closest('[data-notification-clear-read]');

        if (markButton) {
            event.preventDefault();
            markButton.disabled = true;

            try {
                await request(markButton.dataset.url, 'POST');
                window.location.reload();
            } catch (error) {
                markButton.disabled = false;
                showError(error);
            }

            return;
        }

        if (deleteButton) {
            event.preventDefault();
            deleteButton.disabled = true;

            try {
                await request(deleteButton.dataset.url, 'DELETE');
                window.location.reload();
            } catch (error) {
                deleteButton.disabled = false;
                showError(error);
            }

            return;
        }

        if (clearButton) {
            event.preventDefault();
            clearButton.disabled = true;

            try {
                await request(clearButton.dataset.url, 'DELETE');
                window.location.reload();
            } catch (error) {
                clearButton.disabled = false;
                showError(error);
            }
        }
    });

    if (dropdown) {
        window.setInterval(function () {
            if (!document.hidden) {
                refresh();
            }
        }, 30000);

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                refresh();
            }
        });
    }
})();
