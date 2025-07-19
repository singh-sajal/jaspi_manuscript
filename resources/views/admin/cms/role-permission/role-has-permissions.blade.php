@extends('admin.app')

@section('title', 'Assign Permissions')
@section('breadcrumb', 'Assign Permissions')
@section('page-title', 'Assign Permissions')
@section('breadcrumb-button')
    Role: {{ $role->name ?? '' }}
@endsection
@section('css')
    <style>
        .kpi-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.7rem;
            margin: 0;
        }
    </style>
@endsection
@section('content')
    <div class="card">
        <form action="{{ route('admin.roles.permissions.assign', $role->id) }}" method="post" novalidate>
            <div class="card-body">
                @csrf
                @include('admin.cms.role-permission.role-has-permission-form')

            </div>
            <div class="card-footer d-flex justify-content-end">

                <button type="submit" class="btn btn-primary">Assign</button>
            </div>
        </form>
    </div>
@endsection
@section('javascripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const masterCheckboxes = document.querySelectorAll('.master-checkbox');
            masterCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('click', function() {
                    const category = this.dataset.category;
                    const permissionCheckboxes = document.querySelectorAll(
                        `.form-check[data-category="${category}"] .permission-checkbox`);
                    const isChecked = this.checked;
                    permissionCheckboxes.forEach(permissionCheckbox => {
                        permissionCheckbox.checked = isChecked;
                    });
                });
            });
        });
    </script>
@endsection
