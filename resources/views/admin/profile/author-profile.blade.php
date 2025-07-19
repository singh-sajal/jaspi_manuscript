@extends('admin.app')
@section('title', 'Profile')
@section('css')
    <style>
        .avatar.avatar-xxl {
            width: 5rem;
            height: 5rem;
            line-height: 5rem;
            font-size: 1.5rem;
        }
    </style>
@endsection
@section('content')

    {{-- <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4 class="fw-bold">Profile</h4>
                <h6>Manage your profile</h6>
            </div>
        </div>

    </div> --}}
    @php
        $adminData = $admin->data ?? [];
    @endphp
    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Profile Details</h3>
        </div>
        <div class="row">
            <div class="col-md-4 col-12">
                <div class="card-body">

                    <div class="d-flex align-items-center card-body border-bottom mb-3">
                        <div class="avatar avatar-xxl border-primary rounded border p-1">
                            <img src="{{ asset($admin->avatar ?? 'admin/assets/images/avatar/1.png') }}" class="img-fluid"
                                alt="Img">
                        </div>
                        <div class="ms-3">
                            <h6 class="fw-bold mb-0">{{ $admin->name ?? '' }}</h6>
                            <p class="fw-medium mb-1">{{ $admin->username ?? '' }}</p>
                            <button class="btn actionHandler btn-dark btn-sm" data-handler="updateAvatar"
                                data-url="{{ route('admin.avatar.update') }}">Change Avatar</button>
                        </div>
                    </div>


                    <div class="card-body border-bottom mb-3">

                        <h6 class="fw-bold mb-2">Contact</h6>
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-mail"></i>
                            <span class="fw-bold mx-2">Email</span>
                            <p class="mb-0 me-1">{{ $admin->email ?? '' }}</p>
                            <i
                                class="{{ $admin->is_email_verified ? 'ri-verified-badge-fill  text-success ' : 'ri-close-circle-fill  text-danger ' }}"></i>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-phone"></i>
                            <span class="fw-bold mx-2">Phone</span>
                            <p class="mb-0">{{ $admin->phone ?? '' }}</p>
                            <i
                                class="{{ $admin->is_phone_verified ? 'ri-verified-badge-fill  text-success ' : 'ri-close-circle-fill  text-danger ' }}"></i>
                        </div>

                    </div>


                    <div class="card-body border-bottom mb-3">

                        <h6 class="fw-bold mb-2">Basic Information</h6>
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-user"></i>
                            <span class="fw-bold mx-2">Gender</span>
                            <p class="mb-0">{{ $admin->gender ?? '---' }}</p>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-calendar"></i>
                            <span class="fw-bold mx-2">Date of Birth</span>
                            <p class="mb-0">{{ $admin->dob ?? '---' }}</p>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-calendar"></i>
                            <span class="fw-bold mx-2">Joined On</span>
                            <p class="mb-0">{{ $admin->created_at->format('F jS, Y') }}</p>
                        </div>

                    </div>


                    <div class="card-body border-bottom mb-3">

                        <h6 class="fw-bold mb-2">Address Information</h6>
                        <div class="d-flex align-items-center mb-2">
                            <i class="ri ri-flag-fill"></i>
                            <span class="fw-bold mx-2">Country</span>
                            <p class="mb-0">{{ $admin->country ?? 'India' }}</p>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="ri ri-map-pin-2-fill"></i>
                            <span class="fw-bold mx-2">State</span>
                            <p class="mb-0">{{ $admin->state ?? '---' }}</p>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="ri ri-building-fill"></i>
                            <span class="fw-bold mx-2">City</span>
                            <p class="mb-0">{{ $admin->city ?? '---' }}</p>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="ri ri-map-pin-2-fill"></i>
                            <span class="fw-bold mx-2">Pincode</span>
                            <p class="mb-0">{{ $admin->pincode ?? '---' }}</p>
                        </div>
                        <address class="fw-medium text-muted bg-light border-primary rounded border p-2">
                            {{ $admin->address ?? '--- ' }}
                        </address>


                    </div>

                </div>


            </div>
            <div class="col-md-8 col-12">
                <div class="border">
                    <form action="{{ route('admin.profile.update', ['uuid' => $admin->uuid]) }}" novalidate method="post">
                        <div class="card-body">
                            @include('admin.profile.form')
                        </div>
                        {{-- <div class="card-footer d-flex justify-content-between align-items-center">
                            <p class="flex-grow-1 mb-0">Want to change your password ? <a href="#"
                                    class="actionHandler" data-url="{{ route('admin.authorChangePassword', $admin->uuid) }}"
                                    class="link-primary">Click Here</a></p>
                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                        </div> --}}
                    </form>

                </div>
            </div>
        </div>

    </div>

@endsection
@section('javascripts')
    <script src="{{ asset('engines/profile-module.js') }}"></script>
@endsection
