@extends('admin.app')
@section('title', 'Bulk Change Status')
@section('css')
    <style>
        /* Clean, modern styling for the dashboard */
        .bulk-update-container {
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            font-family: sans-serif;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .action-controls {
            display: flex;
            gap: 10px;
        }

        .action-controls input {
            padding: 10px 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 250px;
            font-size: 14px;
        }

        .btn-primary {
            background-color: #0056b3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.2s;
        }

        .btn-primary:hover {
            background-color: #004494;
        }

        .btn-primary:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .manuscript-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .manuscript-table th,
        .manuscript-table td {
            border-bottom: 1px solid #eee;
            padding: 12px;
            text-align: left;
        }

        .manuscript-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .manuscript-row:hover {
            background-color: #fbfbfb;
        }

        .manuscript-row.selected {
            background-color: #eef5ff;
        }

        .status-badge {
            background: #e2e8f0;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            color: #334155;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: 500;
        }

        .alert.hidden {
            display: none;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
@endsection
@section('content')
    <div class="bulk-update-container">
        <div class="header-actions">
            <h2>Bulk Update Manuscript Status</h2>

            <div class="action-controls">
                <input type="text" id="newStatusInput" placeholder="Enter new status (e.g., Under Review)" required>
                <button id="updateSelectedBtn" class="btn-primary">Update Selected</button>
            </div>
        </div>

        <div id="alertBanner" class="alert hidden"></div>

        <div class="table-responsive">
            <table class="manuscript-table">
                <thead>
                    <tr>
                        <th width="5%"><input type="checkbox" id="selectAllCheckbox"></th>
                        <th width="20%">Application ID</th>
                        <th width="50%">Title</th>
                        <th width="25%">Current Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($manuscripts as $manuscript)
                        <tr class="manuscript-row">
                            <td>
                                <input type="checkbox" class="row-checkbox" value="{{ $manuscript->id }}">
                            </td>
                            <td>{{ $manuscript->application_id ?? 'N/A' }}</td>
                            <td>{{ $manuscript->title }}</td>
                            <td>
                                <span class="status-badge">{{ $manuscript->status ?? 'Pending' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center;">No manuscripts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('javascripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');
            const updateBtn = document.getElementById('updateSelectedBtn');
            const statusInput = document.getElementById('newStatusInput');
            const alertBanner = document.getElementById('alertBanner');

            // 1. Handle "Select All" toggle
            selectAllCheckbox.addEventListener('change', function(e) {
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                    toggleRowHighlight(checkbox);
                });
            });

            // 2. Handle individual row highlighting
            rowCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    toggleRowHighlight(this);
                    // Uncheck "Select All" if not all are checked
                    if (!this.checked) selectAllCheckbox.checked = false;
                });
            });

            function toggleRowHighlight(checkbox) {
                const row = checkbox.closest('tr');
                if (checkbox.checked) {
                    row.classList.add('selected');
                } else {
                    row.classList.remove('selected');
                }
            }

            // 3. Handle the Bulk Update Request
            updateBtn.addEventListener('click', async function() {
                const newStatus = statusInput.value.trim();

                // Gather selected IDs
                const selectedIds = Array.from(rowCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => parseInt(cb.value));

                // Validation
                if (selectedIds.length === 0) {
                    showAlert('error', 'Please select at least one manuscript to update.');
                    return;
                }
                if (!newStatus) {
                    showAlert('error', 'Please enter a name for the new status.');
                    return;
                }

                // Disable button to prevent double submission
                updateBtn.disabled = true;
                updateBtn.innerText = 'Updating...';

                try {
                    // Ensure this URL matches your actual route for the controller method
                    const response = await fetch("{{ url()->current() }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}' // Essential for Laravel
                        },
                        body: JSON.stringify({
                            manuscript_ids: selectedIds,
                            status: newStatus
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        showAlert('success', data.message);

                        // Optional: Update UI without reloading, or just reload the page
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showAlert('error', data.message ||
                            'Validation failed or server error occurred.');
                        updateBtn.disabled = false;
                        updateBtn.innerText = 'Update Selected';
                    }

                } catch (error) {
                    showAlert('error', 'A network error occurred. Please try again.');
                    updateBtn.disabled = false;
                    updateBtn.innerText = 'Update Selected';
                }
            });

            function showAlert(type, message) {
                alertBanner.className = `alert ${type}`;
                alertBanner.innerText = message;

                // Auto-hide after 5 seconds
                setTimeout(() => {
                    alertBanner.className = 'alert hidden';
                }, 5000);
            }
        });
    </script>
@endsection

