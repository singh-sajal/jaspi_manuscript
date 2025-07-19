<div class="deznav sidebar">
    <div class="deznav-scroll">
        <ul class="metismenu" id="menu">


            <li>
                <a href="{{ route('admin.dashboard') }}" class="" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="ri-dashboard-line" width="22" height="22"></i>

                    </div>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            @if (canAccess('staff') || canAccess('author'))

                @if (canAccess('staff'))
                    <li>
                        <a href="{{ route('admin.staff.index') }}">
                            <div class="menu-icon">
                                <i class="ri-user-2-fill" width="22" height="22"></i>
                            </div>
                            <span class="nav-text">Staff</span>
                        </a>
                    </li>

                    {{-- <li>
                        <a href="{{ route('admin.staff.index') }}" class="" aria-expanded="false">
                            <div class="menu-icon">
                                <i class="ri-article-line" width="22" height="22"></i>
                            </div>
                            <span class="nav-text">Applications</span>
                        </a>
                    </li> --}}
                @endif

                @if (canAccess('author'))
                    <li>
                        <a href="{{ route('admin.author.index') }}">
                            <div class="menu-icon">
                                <i class="ri-user-3-line" width="22" height="22"></i>
                            </div>
                            <span class="nav-text">Authors</span>
                        </a>
                    </li>
                @endif

            @endif
            {{-- @if (canAccess('application')) --}}
            <li>
                <a href="{{ route('admin.application.index') }}" class="" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="ri-article-line" width="22" height="22"></i>
                    </div>
                    <span class="nav-text">Manuscript</span>
                </a>
            </li>
             <li>
                <a href="{{ route('admin.application.index', ['type'=>'published']) }}" class="" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="ri-article-line" width="22" height="22"></i>
                    </div>
                    <span class="nav-text">Published Manuscript</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.query.index') }}" class="" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="ri-article-line" width="22" height="22"></i>
                    </div>
                    <span class="nav-text">Web Queries</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.mail.index') }}" class="" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="ri-article-line" width="22" height="22"></i>
                    </div>
                    <span class="nav-text">Send Mail</span>
                </a>
            </li>
            {{-- @endif --}}
            {{-- @if (isSuperAdmin())
                <li class="menu-title">Role and Permissions</li>
                <li>
                    <a href="{{ route('admin.roles.index') }}" class="" aria-expanded="false">
                        <div class="menu-icon">
                            <i class="ri-shield-user-line" width="22" height="22"></i>

                        </div>
                        <span class="nav-text">Roles</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.permissions.manage', ['dev' => 'developer']) }}" class=""
                        aria-expanded="false">
                        <div class="menu-icon">
                            <i class="ri-shield-keyhole-line" width="22" height="22"></i>
                        </div>
                        <span class="nav-text">Permissions</span>
                    </a>
                </li>
            @endif --}}




        </ul>
        <div class="help-desk">
            <a href="{{ route('admin.logout') }}" class="btn btn-primary w-100">Logout</a>
        </div>
    </div>
</div>
