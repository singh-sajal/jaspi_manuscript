@extends($layout)

@section('title', 'Mail')
@section('css')

@endsection
@section('breadcrumb', 'Mail')
@section('page-title', 'Send email to admin')
@section('breadcrumb-button')
@endsection
@section('content')


    <form action="{{ route('author.sendMail') }}" method="post" novalidate enctype="multipart/form-data"
        class="card p-4 shadow-sm">
        @csrf


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


@endsection
