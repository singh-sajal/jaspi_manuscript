<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Entities</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container-fluid py-4">

        <div class="offcanvas offcanvas-end" tabindex="-1" style="z-index: 10000; width:80%" id="FileOffcanvas"
            data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="FileOffcanvasLabel">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title fw-bold" id="FileOffcanvasLabel">File Viewer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body" id="FileOffcanvasBody">
                </div>
        </div>

        <div class="modal fade" id="AjaxModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            {{-- <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">All Entities</h5>
            </div> --}}

            <div class="card-body" id="Saarni">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover w-100 dt-datatable">
                        <thead class="table-light">
                            <tr>
                                <th>Sr. No.</th>
                                <th data-sortable="true" data-column="title">Manuscript Id</th>
                                <th>First Page</th>
                                <th>ICJME Declaration</th>
                                <th>Cover Letter</th>
                                <th>Main Manuscript</th>
                                <th data-sortable="true" data-column="title">Article Type</th>

                                {{-- @if (authUser('admin')->is_admin == 1) --}}
                                    <th>Author Name</th>
                                {{-- @endif --}}

                                <th>Published with DOI</th>

                                {{-- @if(authUser('admin')->is_admin == 1 || isEditor())
                                    <th>Assigned to</th>
                                @endif --}}

                                {{-- <th data-sortable="true" data-column="status">Status</th>
                                <th data-sortable="true" data-column="created_at">Created At</th>
                                <th>Options</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    {{-- Assuming $options is defined in your controller or earlier in the blade file --}}
    @include('includes.saarnijs', ['options' => $options ?? []])
    <script src="{{ asset('engines/fileviewer.js') }}"></script>

</body>
</html>
