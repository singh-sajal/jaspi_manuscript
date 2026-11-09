{{-- SheetJS for client-side Excel parsing --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<div class="modal-dialog modal-xl">
    <div class="modal-content border-0 shadow">

        <div class="modal-header bg-light">
            <h5 class="modal-title fw-bold">
                <i class="bi bi-envelope-paper me-2 text-primary"></i>Invite Reviewer(s)
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body p-0">

            {{-- ── Tabs ──────────────────────────────────────────────────── --}}
            <ul class="nav nav-tabs px-4 pt-3" id="inviteModeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#singlePane"
                        type="button">
                        <i class="ri-user-add-line me-1"></i> Single Invite
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#bulkPane" type="button">
                        <i class="ri-file-excel-2-line me-1"></i> Bulk Import from Excel
                    </button>
                </li>
            </ul>

            <div class="tab-content p-4">

                {{-- ── Single Invite ────────────────────────────────────── --}}
                <div class="tab-pane fade show active" id="singlePane" role="tabpanel">
                    <p class="text-muted mb-4">
                        Enter the email address of the person you wish to invite as a peer reviewer.
                    </p>
                    <form action="{{ route('admin.inviteReviewer.store') }}" method="POST" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                                placeholder="e.g., doctor@university.edu" required>
                            <span class="text-danger" data-error="email"></span>
                        </div>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send me-1"></i> Send Invitation
                        </button>
                    </form>
                </div>

                {{-- ── Bulk Import ──────────────────────────────────────── --}}
                <div class="tab-pane fade" id="bulkPane" role="tabpanel">
                    <p class="text-muted mb-3">
                        Upload an Excel or CSV file with a column header named <code>email</code>.
                        Emails are shown below for review — edit or remove any before sending.
                    </p>

                    {{-- File input --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Choose File</label>
                        <input type="file" class="form-control" id="excelFileInput" accept=".xlsx,.xls,.csv">
                        <div class="form-text text-muted">
                            Accepted: .xlsx, .xls, .csv &mdash; max 5 MB
                        </div>
                    </div>

                    {{-- Preview table (hidden until file is selected) --}}
                    <div id="emailPreviewSection" class="d-none">

                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold mb-0">
                                Email Preview
                                <span class="badge bg-primary ms-1" id="emailPreviewCount">0</span>
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="addEmailRowBtn">
                                <i class="ri-add-line me-1"></i> Add Row
                            </button>
                        </div>

                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width:50px;">#</th>
                                        <th>Email Address</th>
                                        <th class="text-center" style="width:80px;">Remove</th>
                                    </tr>
                                </thead>
                                <tbody id="emailPreviewBody"></tbody>
                            </table>
                        </div>

                        <div id="bulkSendResult" class="mt-3 d-none"></div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-success" id="sendBulkBtn">
                                <i class="ri-mail-send-line me-1"></i> Send Invitations
                                <span class="badge bg-white text-success ms-1" id="sendBulkCount">0</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="clearPreviewBtn">
                                <i class="ri-close-line me-1"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {

        // ── Helpers ───────────────────────────────────────────────────────────────
        function updateCount() {
            const count = document.querySelectorAll('#emailPreviewBody tr').length;

            const previewEl = document.getElementById('emailPreviewCount');
            if (previewEl) previewEl.textContent = count;

            const sendEl = document.getElementById('sendBulkCount');
            if (sendEl) sendEl.textContent = count;
        }

        function showResult(message, type = 'success') {
            const el = document.getElementById('bulkSendResult');
            el.className = `alert alert-${type} mt-3`;
            el.textContent = message;
            el.classList.remove('d-none');
        }

        function buildRow(email, index) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td class="text-muted text-center">${index}</td>
            <td>
                <input type="email"
                       class="form-control form-control-sm preview-email-input"
                       value="${email}"
                       placeholder="email@example.com">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-preview-row">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </td>
        `;

            tr.querySelector('.remove-preview-row').addEventListener('click', function() {
                tr.remove();
                // Re-index remaining rows
                document.querySelectorAll('#emailPreviewBody tr').forEach((r, i) => {
                    r.querySelector('td:first-child').textContent = i + 1;
                });
                updateCount();
                if (document.querySelectorAll('#emailPreviewBody tr').length === 0) {
                    document.getElementById('emailPreviewSection').classList.add('d-none');
                }
            });

            return tr;
        }

        function renderEmails(emails) {
            const tbody = document.getElementById('emailPreviewBody');
            tbody.innerHTML = '';
            emails.forEach((email, i) => tbody.appendChild(buildRow(email, i + 1)));
            document.getElementById('emailPreviewSection').classList.remove('d-none');
            document.getElementById('bulkSendResult').classList.add('d-none');
            updateCount();
        }

        // ── Excel Parsing (client-side via SheetJS) ───────────────────────────────
        document.getElementById('excelFileInput')?.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            const reader = new FileReader();

            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const wb = XLSX.read(data, {
                        type: 'array'
                    });
                    const sheet = wb.Sheets[wb.SheetNames[0]];
                    const rows = XLSX.utils.sheet_to_json(sheet, {
                        header: 1
                    });

                    if (!rows || rows.length < 2) {
                        alert('No data found in the file.');
                        return;
                    }

                    // Find "email" column (case-insensitive)
                    const headers = rows[0].map(h => String(h).toLowerCase().trim());
                    const emailCol = headers.indexOf('email');

                    if (emailCol === -1) {
                        alert(
                            'Column header "email" not found in the first row. Please check your file.'
                        );
                        return;
                    }

                    const emails = [...new Set(
                        rows.slice(1)
                        .map(row => String(row[emailCol] ?? '').toLowerCase().trim())
                        .filter(e => e && e.includes('@'))
                    )];

                    if (emails.length === 0) {
                        alert('No valid email addresses found in the file.');
                        return;
                    }

                    renderEmails(emails);

                } catch (err) {
                    alert('Could not read the file. Make sure it is a valid Excel or CSV file.');
                    console.error(err);
                }
            };

            reader.readAsArrayBuffer(file);
        });

        // ── Add Row Manually ──────────────────────────────────────────────────────
        document.getElementById('addEmailRowBtn')?.addEventListener('click', function() {
            const tbody = document.getElementById('emailPreviewBody');
            const index = tbody.querySelectorAll('tr').length + 1;
            tbody.appendChild(buildRow('', index));
            document.getElementById('emailPreviewSection').classList.remove('d-none');
            updateCount();
            tbody.querySelector('tr:last-child input')?.focus();
        });

        // ── Clear Preview ─────────────────────────────────────────────────────────
        document.getElementById('clearPreviewBtn')?.addEventListener('click', function() {
            document.getElementById('emailPreviewBody').innerHTML = '';
            document.getElementById('emailPreviewSection').classList.add('d-none');
            document.getElementById('excelFileInput').value = '';
            document.getElementById('bulkSendResult').classList.add('d-none');
            updateCount();
        });

        // ── Send Bulk via AJAX ────────────────────────────────────────────────────
        document.getElementById('sendBulkBtn')?.addEventListener('click', function() {
            const inputs = document.querySelectorAll('#emailPreviewBody .preview-email-input');
            const emails = [...inputs]
                .map(i => i.value.trim().toLowerCase())
                .filter(e => e && e.includes('@') && e.includes('.'));

            if (emails.length === 0) {
                alert('No valid emails to send. Please add or fix the email addresses.');
                return;
            }

            if (!confirm(`Send invitations to ${emails.length} reviewer(s)?`)) return;

            const btn = this;
            const originalHtml = btn.innerHTML; // Store original HTML to safely restore later

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

            fetch('{{ route('admin.inviteReviewer.send-bulk') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        emails
                    }),
                })
                .then(async (res) => {
                    const isJson = res.headers.get('content-type')?.includes('application/json');
                    const data = isJson ? await res.json() : null;

                    if (!res.ok) {
                        const errorMsg = data && data.message ? data.message :
                            `Server Error: ${res.status} ${res.statusText}`;
                        throw new Error(errorMsg);
                    }
                    return data;
                })
                .then(json => {
                    if (json && json.success) {
                        showResult(json.message, 'success');
                        document.getElementById('emailPreviewBody').innerHTML = '';
                        document.getElementById('excelFileInput').value = '';

                        setTimeout(() => {
                            // 1. Try Laravel DataTables
                            if (typeof window.LaravelDataTables !== 'undefined') {
                                window.LaravelDataTables['dataTableBuilder'].ajax.reload(null,
                                    false);
                            }
                            // 2. Try standard jQuery DataTables (ONLY if the plugin is actually loaded)
                            else if (typeof $ !== 'undefined' && typeof $.fn.DataTable !==
                                'undefined' && $('.dt-datatable').length) {
                                $('.dt-datatable').DataTable().ajax.reload(null, false);
                            }
                            // 3. Bulletproof fallback: simply reload the page to refresh the table
                            else {
                                window.location.reload();
                            }
                        },
                        1500); // 1.5 second delay so the user has time to read the green "Success" message!
                    } else {
                        showResult(json?.message ?? 'Something went wrong.', 'danger');
                    }
                })
                .catch(error => {
                    console.error('AJAX Error:', error);
                    showResult(error.message ||
                        'Request failed due to a server or network error. Please try again.',
                        'danger');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml; // Restore the badge span
                    updateCount(); // Now it is safe to update
                });
        });
    })();
</script>
