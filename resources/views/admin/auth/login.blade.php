@extends('includes.authlayout')

@section('content')
    <div class="login-form">
        <div class="text-start">
            <h3 class="title">Login</h3>
            <p>Login in to your admin account to start using Admin dashboard</p>
        </div>
        <form action="{{ route('admin.login') }}" method="post" novalidate>
            @csrf
            <div class="mb-4">
                <label class="text-dark mb-1">Email</label>
                <input type="text" class="form-control form-control" required name="username" value=""
                    placeholder="Username or Email">
                <span class="text-danger" data-error="username"></span>
            </div>
            <label class="form-label required text-dark mb-1" for="password">Password</label>
            <div class="position-relative  mb-4">

                <input type="password" id="dz-password" name="password" required class="form-control passbox" value=""
                    placeholder="Enter your password" name="password">
                <button class="btn btn-link position-absolute text-decoration-none text-muted password-addon end-0 top-0"
                    name="password" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>

            </div>
            <span class="text-danger" data-error="password"></span>
            <div class="form-row d-flex justify-content-between mb-2 mt-4">
                <div class="mb-4">
                    <div class="form-check custom-checkbox mb-3">
                        <input type="checkbox" class="form-check-input" id="customCheckBox1">
                        <label class="form-check-label" for="customCheckBox1">Remember me</label>
                    </div>
                </div>
                <div class="mb-4">
                    <a href="#" class="btn-link text-primary">Forgot
                        Password?</a>
                </div>
            </div>
            <div class="mb-4 text-center">
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </div>

        </form>
    </div>
@endsection
