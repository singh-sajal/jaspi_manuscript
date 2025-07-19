<div class="header">
    <div class="header-content">
        <nav class="navbar navbar-expand">
            <div class="navbar-collapse justify-content-between collapse">
                <div class="header-left">

                </div>
                <ul class="navbar-nav header-right">

                    <li class="nav-item dropdown notification_dropdown">
                        <a class="nav-link bell dz-fullscreen text-white" href="javascript:void(0);">
                            <svg id="icon-full" viewBox="0 0 24 24" width="20" height="20"
                                stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"
                                stroke-linejoin="round" class="css-i6dzq1">
                                <path
                                    d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"
                                    style="stroke-dasharray: 37, 57; stroke-dashoffset: 0"></path>
                            </svg>
                            <svg id="icon-minimize" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="A098AE" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-minimize">
                                <path
                                    d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"
                                    style="stroke-dasharray: 37, 57; stroke-dashoffset: 0"></path>
                            </svg>
                        </a>
                    </li>
                    <li class="nav-item ps-3">
                        <div class="dropdown header-profile2">
                            <a class="nav-link" href="javascript:void(0);" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <div class="header-info2 d-flex align-items-center">
                                    <div class="header-media">
                                        <img src="{{ asset(authUser('admin')->avatar ?? 'admin/assets/images/avatar/1.png') }}"
                                            alt="" />
                                    </div>
                                    <div class="header-info">
                                        <h6>{{ ucfirst(authUser('admin')->name ?? 'Admin') }}</h6>
                                        <p>{{ authUser('admin')->email ?? 'info@manuscript.com' }}</p>
                                    </div>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" style="">
                                <div class="card mb-0 border-0">
                                    <div class="card-header py-2">
                                        <div class="products">
                                            <img src="{{ asset(authUser('admin')->avatar ?? 'admin/assets/images/avatar/1.png') }}"
                                                class="avatar avatar-md" alt="" />
                                            <div>
                                                <h6>{{ ucfirst(authUser('admin')->name ?? 'Admin') }}</h6>
                                                <span
                                                    class="fw-semibold text-primary">{{ authUser('admin')->username ?? 'Admin' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body px-0 py-2">
                                        <a href="{{ route('admin.profile',authUser('admin')->uuid) }}" class="dropdown-item ai-icon">
                                            <i class="ri-user-line fs-16" height="20" width="20"></i>

                                            <span class="ms-2">Profile </span>
                                        </a>

                                        {{-- <a href="email-inbox.html" class="dropdown-item ai-icon">
                                            <i class="ri-notification-3-line fs-16" height="20"
                                                width="20"></i>
                                            <span class="ms-2">Notification </span>
                                        </a> --}}
                                        <a href="#" data-url="{{ route("admin.password.update") }}" class="dropdown-item ai-icon actionHandler">
                                            <i class="ri-lock-line fs-16" height="20"
                                                width="20"></i>
                                            <span class="ms-2">Change Password </span>
                                        </a>
                                    </div>
                                    <div class="card-footer px-0 py-2">

                                        <a href="{{ route('admin.logout') }}" class="dropdown-item ai-icon bg-danger text-white">
                                            <i class="ri-logout-box-line"></i>
                                            <span class="text-white ms-2">Logout </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>
