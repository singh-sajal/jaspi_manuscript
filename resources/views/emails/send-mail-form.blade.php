@extends($layout)

@section('title', 'Mail')
@section('css')
    <style>
        #recipientList {
            max-height: 320px;
            overflow-y: auto;
        }

        .recipient-group-header {
            background: var(--rgba-primary-1, rgba(196, 90, 17, 0.08));
            color: var(--primary);
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 0.35rem;
            margin: 0.5rem 0 0.25rem;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .recipient-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.75rem;
            border-radius: 0.3rem;
            cursor: pointer;
        }

        .recipient-row:hover {
            background: rgba(0, 0, 0, 0.04);
        }

        .recipient-row .r-name {
            font-weight: 600;
        }

        .recipient-row .r-meta {
            color: var(--text-muted, #6c757d);
            font-size: 0.8rem;
        }
    </style>
@endsection
@section('breadcrumb', 'Mail')
@section('page-title', $isAdmin ? 'Send email' : 'Contact admin')
@section('breadcrumb-button')
@endsection
@section('content')


    <form action="{{ route('admin.sendMail') }}" method="post" novalidate enctype="multipart/form-data"
        class="card p-4 shadow-sm">
        @csrf

        @if (auth()->guard('admin')->check() && !$isAdmin)
            <div class="alert alert-info d-flex align-items-center gap-2">
                <i class="ri-information-line"></i>
                <span>
                    Your message is sent to the Editor in Chief
                    {{ $chief?->name ? '(' . $chief->name : '' }}{{ $chief?->email ? ' — ' . $chief->email : '' }}{{ $chief?->name ? ')' : '' }}.
                </span>
            </div>
        @endif

        @if ($isAdmin)
            <div id="recipientsContainer"></div>

            <div class="mb-3">
                <label class="form-label">Select Role</label>
                <select id="role" class="form-select" required>
                    <option value="">-- Select Role --</option>
                    <option value="author">Author</option>
                    <option value="Editor">Editor</option>
                    <option value="Reviewer">Reviewer</option>
                    <option value="Publisher">Publisher</option>
                    <option value="technicalEditor">Technical Editor</option>
                    <option value="assistantEditor">Assistant Editor</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Search Recipients</label>
                <input type="text" id="recipientSearch" class="form-control" placeholder="Search by name or email...">
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Select Recipients</label>
                    <div class="d-flex align-items-center gap-3">
                        <span class="form-text">Selected: <strong id="selectedCount">0</strong></span>
                        <label class="form-label mb-0 d-flex align-items-center gap-1">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                            Select all
                        </label>
                    </div>
                </div>
                <div id="recipientList" class="border rounded p-2">
                    <p class="text-muted mb-0 small px-2 py-3">
                        Choose a role above to load recipients.
                    </p>
                </div>
            </div>
        @endif

        <div class="mb-3">
            <label class="form-label">Subject</label>
            <input type="text" id="subject" class="form-control" name="subject" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea id="message" rows="5" class="form-control" name="message" required></textarea>
        </div>

        <div id="form-alert" class="alert d-none"></div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Send Mail</button>
        </div>
    </form>

    @if ($isAdmin)
        <div class="card mt-4 shadow-sm">
            <div class="card-header fw-semibold">
                <i class="ri-mail-send-line me-1"></i> Mail Log
            </div>
            <div class="card-body table-responsive">
                @if ($emailLogs->count())
                    <table class="table table-bordered table-striped table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Sent At</th>
                                <th>From</th>
                                <th>To</th>
                                <th>To Email</th>
                                <th>Subject</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($emailLogs as $log)
                                <tr>
                                    <td>{{ $log->sent_at?->format('d-M-Y, h:i A') ?? '---' }}</td>
                                    <td>
                                        @if ($log->sender)
                                            {{ $log->sender->name ?? '---' }}
                                            <span
                                                class="badge {{ $log->sender instanceof \App\Models\Admin ? 'bg-primary' : 'bg-info' }}">
                                                {{ $log->sender instanceof \App\Models\Admin ? 'Admin' : 'Author' }}
                                            </span>
                                        @else
                                            <span class="text-muted">---</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($log->recipient)
                                            {{ $log->recipient->name ?? '---' }}
                                            <span
                                                class="badge {{ $log->recipient instanceof \App\Models\Admin ? 'bg-primary' : 'bg-info' }}">
                                                {{ $log->recipient instanceof \App\Models\Admin ? 'Admin' : 'Author' }}
                                            </span>
                                        @else
                                            <span class="text-muted">---</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->to_email }}</td>
                                    <td>{{ Str::limit($log->subject, 40, '...') }}</td>
                                    <td>
                                        @if ($log->status === 'sent')
                                            <span class="badge bg-success">Sent</span>
                                        @else
                                            <span class="badge bg-danger" title="{{ $log->error }}">Failed</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted mb-0">No emails sent yet.</p>
                @endif
            </div>
        </div>
    @endif


@endsection

@section('javascripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById("role");
            const searchInput = document.getElementById("recipientSearch");
            const recipientList = document.getElementById("recipientList");
            const selectAll = document.getElementById("selectAll");
            const selectedCount = document.getElementById("selectedCount");
            const recipientsContainer = document.getElementById("recipientsContainer");

            let loadedUsers = [];

            function visibleCheckboxes() {
                const result = [];
                recipientList.querySelectorAll('.recipient-group').forEach(function(group) {
                    if (group.style.display === 'none') return;
                    group.querySelectorAll('.recipient-row').forEach(function(row) {
                        if (row.style.display === 'none') return;
                        const cb = row.querySelector('input[type="checkbox"]');
                        if (cb) result.push(cb);
                    });
                });
                return result;
            }

            function syncHiddenRecipients() {
                recipientsContainer.innerHTML = '';
                const checked = recipientList.querySelectorAll('.recipient-row input[type="checkbox"]:checked');
                checked.forEach(function(cb, index) {
                    const h1 = document.createElement('input');
                    h1.type = 'hidden';
                    h1.name = `recipients[${index}][recipient_type]`;
                    h1.value = cb.dataset.type || '';
                    recipientsContainer.appendChild(h1);

                    const h2 = document.createElement('input');
                    h2.type = 'hidden';
                    h2.name = `recipients[${index}][recipient_id]`;
                    h2.value = cb.dataset.id || '';
                    recipientsContainer.appendChild(h2);
                });
                selectedCount.textContent = checked.length;

                // Keep "Select all" in sync with the visible selection.
                const visible = visibleCheckboxes();
                selectAll.checked = visible.length > 0 && visible.every(function(cb) { return cb.checked; });
            }

            function groupLetter(name) {
                const first = (name || '').trim().charAt(0);
                return /^[a-z]/i.test(first) ? first.toUpperCase() : '#';
            }

            function renderRecipients(users) {
                recipientList.innerHTML = '';
                loadedUsers = users;

                if (!users.length) {
                    const p = document.createElement('p');
                    p.className = 'text-muted mb-0 small px-2 py-3';
                    p.textContent = 'No recipients found for this role.';
                    recipientList.appendChild(p);
                    return;
                }

                const query = (searchInput.value || '').trim().toLowerCase();
                const groups = {};

                users.forEach(function(user) {
                    if (query) {
                        const hay = `${user.name} ${user.email}`.toLowerCase();
                        if (!hay.includes(query)) return;
                    }
                    const letter = groupLetter(user.name);
                    (groups[letter] = groups[letter] || []).push(user);
                });

                const letters = Object.keys(groups).sort(function(a, b) {
                    if (a === '#') return 1;
                    if (b === '#') return -1;
                    return a < b ? -1 : (a > b ? 1 : 0);
                });

                letters.forEach(function(letter) {
                    const group = document.createElement('div');
                    group.className = 'recipient-group';

                    const header = document.createElement('div');
                    header.className = 'recipient-group-header';
                    header.textContent = letter;
                    group.appendChild(header);

                    groups[letter].forEach(function(user) {
                        const row = document.createElement('label');
                        row.className = 'recipient-row mb-0';
                        row.dataset.name = user.name;
                        row.dataset.email = user.email;

                        const cb = document.createElement('input');
                        cb.type = 'checkbox';
                        cb.className = 'form-check-input recipient-check';
                        cb.dataset.id = user.id;
                        cb.dataset.type = user.recipient_type || '';
                        cb.addEventListener('change', syncHiddenRecipients);
                        row.appendChild(cb);

                        const text = document.createElement('span');
                        text.className = 'd-flex flex-column';

                        const name = document.createElement('span');
                        name.className = 'r-name';
                        name.textContent = user.name;
                        text.appendChild(name);

                        const meta = document.createElement('span');
                        meta.className = 'r-meta';
                        meta.textContent = `${user.email} — ${user.sent_count} mail(s)`;
                        text.appendChild(meta);

                        row.appendChild(text);
                        group.appendChild(row);
                    });

                    recipientList.appendChild(group);
                });

                syncHiddenRecipients();
            }

            roleSelect.addEventListener("change", function() {
                const selectedRole = this.value;
                searchInput.value = "";
                selectAll.checked = false;
                recipientList.innerHTML = '<p class="text-muted mb-0 small px-2 py-3">Loading recipients...</p>';
                syncHiddenRecipients();

                if (!selectedRole) {
                    recipientList.innerHTML =
                        '<p class="text-muted mb-0 small px-2 py-3">Choose a role above to load recipients.</p>';
                    return;
                }

                fetch(`{{ route('admin.getUsersByRole') }}?role=${encodeURIComponent(selectedRole)}`)
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        renderRecipients(data || []);
                    })
                    .catch(function() {
                        recipientList.innerHTML =
                            '<p class="text-danger mb-0 small px-2 py-3">Failed to load recipients.</p>';
                    });
            });

            searchInput.addEventListener("input", function() {
                if (loadedUsers.length) {
                    renderRecipients(loadedUsers);
                }
            });

            selectAll.addEventListener("change", function() {
                visibleCheckboxes().forEach(function(cb) {
                    cb.checked = selectAll.checked;
                });
                syncHiddenRecipients();
            });
        });
    </script>

@endsection