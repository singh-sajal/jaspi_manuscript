<div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content shadow-lg rounded-4">
        <div class="modal-header text-white">
            <h5 class="modal-title">Response to Assistant Editor</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('admin.application.chiefToAssistanResponse', [$uuid]) }}" method="post" novalidate
            enctype="multipart/form-data">
            @csrf

            <div class="modal-body">
                <div class="card-body">

                    <div class="mb-4">
                        <label class="form-label fw-bold d-block">Decision</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="decision" id="decisionAccept"
                                value="accept" required>
                            <label class="form-check-label text-success fw-bold" for="decisionAccept">
                                Accept
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="decision" id="decisionReject"
                                value="reject" required>
                            <label class="form-check-label text-danger fw-bold" for="decisionReject">
                                Reject
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Feedback</label>
                        <textarea class="form-control" name="description" id="description" rows="8" placeholder="Enter feedback here...">{{ $app_info->description ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-end">
                <button type="button" class="btn btn-primary px-4 py-2 rounded-pill"
                    onclick="confirmation(this)">Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
    window.confirmation = (button) => {



        const form = button.closest('form');

        const url = form.action;



        // Sync editor content before submit

        const editor = document.getElementById('editor');

        const descriptionInput = document.getElementById('description');

        if (editor && descriptionInput) {

            descriptionInput.value = editor.innerHTML.trim();

        }



        const formData = new FormData(form);



        swal({

            title: "Are you sure?",

            text: "This action cannot be reversed!",

            icon: "warning",

            buttons: true,

            dangerMode: true,

        }).then((willProceed) => {



            if (!willProceed) return;



            const originalHTML = button.innerHTML;

            button.disabled = true;

            button.innerHTML = settings.spinner;



            fetch(url, {

                    method: "POST",

                    headers: {

                        Accept: "application/json",

                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value

                    },

                    body: formData

                })

                .then(response => response.json())

                .then(data => {

                    if (data.status == 200 || data.status === '200') {

                        toastr.success(data.msg);



                        if (data.redirect) {

                            window.location.href = data.redirect;

                        }

                    } else {

                        toastr.error(data.msg || 'Action failed');

                    }

                })

                .catch(error => {

                    console.error(error);

                    toastr.error('Something went wrong');

                })

                .finally(() => {

                    button.disabled = false;

                    button.innerHTML = originalHTML;

                });



        });

    }
</script>
