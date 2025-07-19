@extends('admin.app')

@section('title', 'Permissions')
@section('breadcrumb', 'Permissions')
@section('page-title', 'Permissions')
@section('content')
    <div class="card">
        <div class="card-header d-block">

            <form action="{{ route('admin.permissions.manage', ['dev' => 'developer']) }}"
                class="d-flex align-items-center justify-content-between" method="post" enctype="multipart/form-data">
                <input class="form-control flex-grow-1" name="name" type="text" value=""
                    placeholder="Add New Permission">
                <button class="btn btn-primary ms-1" type="submit">
                    Submit</button>

            </form>

        </div>
        <div class="card-body">
            <div class="card-body" id="Saarni">

                <table class="table-bordered w-100 dt-datatable table">
                    <thead>
                        <tr>
                            <th data-sortable="true" data-column="name">Permission</th>
                            <th>Updated At</th>
                            <th>Options</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>



@endsection
@section('javascripts')
@section('javascripts')
    @php

        $options = [
            'selector' => 'Saarni',
            'url' => route('admin.permissions.manage', ['dev' => 'developer']),
            'moduleName' => 'Role List',
        ];
    @endphp
    @include('includes.saarnijs', ['options' => $options])
@endsection
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.querySelector(`input[name="name"]`);
        console.log(input);
        input.focus();
    });
</script>
@endsection
