@extends($layout)

@section('title', 'Mail')
@section('css')

@endsection
@section('breadcrumb', 'Mail')
@section('page-title', 'Send email')
@section('breadcrumb-button')
@endsection
@section('content')


    <form action="{{ route('admin.sendMail') }}" method="post" novalidate enctype="multipart/form-data"
        class="card p-4 shadow-sm">
        @csrf
        @if (isSuperAdmin())
            <div class="mb-3">
                <label class="form-label">Select Role</label>
                <select id="role" class="form-select" required>
                    <option value="">-- Select Role --</option>
                    <option value="author">Author</option>
                    <option value="Editor">Editor</option>
                    <option value="Reviewer">Reviewer</option>
                    <option value="Publisher">Publisher</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Select Person</label>
                <select id="person" class="form-select" required>
                    <option value="">-- Select Person --</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" id="email" class="form-control" name="email" readonly>
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





@endsection

@section('javascripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById("role");
            const personSelect = document.getElementById("person");
            const emailInput = document.getElementById("email");
            const messageForm = document.getElementById("messageForm");
            const alertBox = document.getElementById("form-alert");

            roleSelect.addEventListener("change", function() {
                const selectedRole = this.value;
                personSelect.innerHTML = '<option value="">-- Select Person --</option>';
                emailInput.value = "";

                if (!selectedRole) return;

                fetch(`{{ route('admin.getUsersByRole') }}?role=${encodeURIComponent(selectedRole)}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(user => {
                            const option = document.createElement("option");
                            option.value = user.email;
                            option.textContent = user.name;
                            personSelect.appendChild(option);
                        });
                    });
            });

            personSelect.addEventListener("change", function() {
                emailInput.value = this.value;
            });

            // messageForm.addEventListener("submit", function(e) {
            //     e.preventDefault();

            //     const payload = {
            //         email: emailInput.value,
            //         subject: document.getElementById("subject").value,
            //         message: document.getElementById("message").value,
            //         _token: document.querySelector('input[name="_token"]').value
            //     };

            //     fetch(`{{ route('admin.sendMail') }}`, {
            //             method: "POST",
            //             headers: {
            //                 "Content-Type": "application/json",
            //                 "Accept": "application/json",
            //                 "X-CSRF-TOKEN": payload._token
            //             },
            //             body: JSON.stringify(payload)
            //         })
            //         .then(res => res.json())
            //         .then(res => {
            //             alertBox.classList.remove("d-none", "alert-danger");
            //             alertBox.classList.add("alert", "alert-success");
            //             alertBox.innerText = "Mail sent successfully!";

            //             setTimeout(() => {
            //                 alertBox.classList.add("d-none");
            //             }, 3000);

            //             messageForm.reset();
            //             emailInput.value = "";
            //             personSelect.innerHTML = '<option value="">-- Select Person --</option>';
            //         })
            //         .catch(error => {
            //             alertBox.classList.remove("d-none", "alert-success");
            //             alertBox.classList.add("alert", "alert-danger");
            //             alertBox.innerText = "Failed to send mail.";

            //             setTimeout(() => {
            //                 alertBox.classList.add("d-none");
            //             }, 3000);

            //             console.error(error);
            //         });
            // });
        });
    </script>

@endsection
