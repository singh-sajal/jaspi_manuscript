<div class="header">
    <div class="header-content">
        <nav class="navbar navbar-expand">
            <div class="navbar-collapse justify-content-between collapse">
                <div class="header-left">
                    <ul class="d-flex" id="authorMenu">


                        <li>
                            <a href="{{ route('author.dashboard') }}"
                                class="d-flex align-items-center {{ request()->routeIs('author.dashboard') ? 'active' : '' }}"
                                aria-expanded="false">
                                <div class="menu-icon">
                                    <i class="ri-dashboard-line" width="22" height="22"></i>

                                </div>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </li>



                        <li>
                            <a href="{{ route('author.application.index') }}"
                                class="d-flex align-items-center {{ request()->routeIs('author.application.index') ? 'active' : '' }}"
                                aria-expanded="false">
                                <div class="menu-icon">
                                    <i class="ri-calendar-2-line" width="22" height="22"></i>
                                </div>
                                <span class="nav-text">Manu Script</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('author.help-and-support') }}"
                                class="d-flex align-items-center {{ request()->routeIs('author.help-and-support') ? 'active' : '' }}"
                                aria-expanded="false">
                                <div class="menu-icon">
                                    <i class="ri-calendar-2-line" width="22" height="22"></i>
                                </div>
                                <span class="nav-text">Help & Support</span>
                            </a>
                        </li>



                    </ul>
                </div>
                <ul class="navbar-nav header-right">

                    {{-- <li class="nav-item dropdown notification_dropdown">
                        <a class="nav-link text-white" href="javascript:void(0);" role="button"
                            data-bs-toggle="dropdown">
                            <i class="ri-notification-3-line"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div id="DZ_W_Notification1" class="widget-media dz-scroll p-3" style="height: 380px">
                                <ul class="timeline">
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media me-2">
                                                <img alt="image" width="50" src="images/avatar/1.jpg" />
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Dr sultads Send you Photo</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media media-info me-2">KG</div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Resport created successfully</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media media-success me-2">
                                                <i class="fa fa-home"></i>
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Reminder : Treatment Time!</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media me-2">
                                                <img alt="image" width="50" src="images/avatar/1.jpg" />
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Dr sultads Send you Photo</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media media-danger me-2">KG</div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Resport created successfully</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media media-primary me-2">
                                                <i class="fa fa-home"></i>
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Reminder : Treatment Time!</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media me-2">
                                                <img alt="image" width="50" src="images/avatar/1.jpg" />
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Dr sultads Send you Photo</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media media-info me-2">KG</div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Resport created successfully</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media media-success me-2">
                                                <i class="fa fa-home"></i>
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Reminder : Treatment Time!</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media me-2">
                                                <img alt="image" width="50" src="images/avatar/1.jpg" />
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Dr sultads Send you Photo</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media media-danger me-2">KG</div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Resport created successfully</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media media-primary me-2">
                                                <i class="fa fa-home"></i>
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Reminder : Treatment Time!</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <a class="all-notification" href="javascript:void(0);">See all notifications <i
                                    class="ti-arrow-end"></i></a>
                        </div>
                    </li> --}}

                    {{-- <li class="nav-item dropdown notification_dropdown">
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
                    </li> --}}
                    <li class="nav-item ps-3">
                        <div class="dropdown header-profile2">
                            <a class="nav-link" href="javascript:void(0);" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <div class="header-info2 d-flex align-items-center">
                                    <div class="header-media">
                                        <img src="{{ asset(authUser('web')->avatar ?? 'admin/assets/images/avatar/1.png') }}"
                                            alt="" />
                                    </div>
                                    <div class="header-info">
                                        <h6>{{ ucfirst(authUser('web')->name ?? 'Admin') }}</h6>
                                        <p>{{ authUser('web')->email ?? 'info@ca.com' }}</p>
                                    </div>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" style="">
                                <div class="card mb-0 border-0">
                                    <div class="card-header py-2">
                                        <div class="products">
                                            <img src="{{ asset(authUser('web')->avatar ?? 'admin/assets/images/avatar/1.png') }}"
                                                class="avatar avatar-md" alt="" />
                                            <div>
                                                <h6>{{ authUser('web')->name ?? 'Author' }}</h6>
                                                <span
                                                    class="fw-semibold text-primary">{{ authUser('web')->username ?? 'Author' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body px-0 py-2">
                                        <a href="{{ route('author.profile', authUser('web')->uuid) }}"
                                            class="dropdown-item ai-icon">
                                            <i class="ri-user-line fs-16" height="20" width="20"></i>

                                            <span class="ms-2">Profile </span>
                                        </a>

                                        {{-- <a href="#" class="dropdown-item ai-icon">
                                            <i class="ri-notification-3-line fs-16" height="20"
                                                width="20"></i>
                                            <span class="ms-2">Notification </span>
                                        </a> --}}
                                        <a href="#" data-url="{{ route('author.password.update') }}"
                                            class="dropdown-item ai-icon actionHandler">
                                            <i class="ri-lock-line fs-16" height="20" width="20"></i>
                                            <span class="ms-2">Change Password </span>
                                        </a>
                                    </div>
                                    <div class="card-footer px-0 py-2">
                                        <a href="{{ route('author.logout') }}"
                                            class="dropdown-item ai-icon bg-danger text-white">
                                            <i class="ri-logout-box-line"></i>
                                            <span class="ms-2 text-white">Logout </span>
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
