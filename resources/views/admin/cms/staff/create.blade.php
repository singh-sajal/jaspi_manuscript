<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Add New Staff Member</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
            </button>
        </div>
        <form action="{{ route('admin.staff.store') }}" method="post" novalidate enctype="multipart/form-data">
            @csrf

            @include('admin.cms.staff.form')




            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>

    </div>
</div>
{{-- jQuery --}}
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
     <script>
         $(document).ready(function() {
             function toggleReviewerType() {
                 if ($('#roleSelect').val()?.toLowerCase() === "reviewer") {
                     $('#reviewerTypeWrapper').show();
                     $('#reviewerType').prop('required', true);
                 } else {
                     $('#reviewerTypeWrapper').hide();
                     $('#reviewerType').prop('required', false).val('');
                 }
             }

             $('#roleSelect').on('change', toggleReviewerType);

             // Run on page load (edit mode)
             toggleReviewerType();
         });
     </script> --}}


