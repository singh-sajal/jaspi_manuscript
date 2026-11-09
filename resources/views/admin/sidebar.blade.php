<style>
    .deznav .metismenu>li>a {
        display: flex !important;
        align-items: flex-start !important;
        gap: 10px;
    }

    .deznav .metismenu>li>a .menu-icon {
        flex-shrink: 0;
        min-width: 22px;
        width: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 2px;
        margin-right: 0;
    }

    .deznav .metismenu>li>a .nav-text {
        line-height: 1.4;
        white-space: normal;
        word-break: break-word;
        flex: 1;
    }

    .deznav .metismenu ul a .menu-count {
        float: right;
        min-width: 1.3rem;
        font-size: 0.7rem;
        font-weight: 600;
        line-height: 1.3rem;
        text-align: center;
        border-radius: 3.75rem;
        padding: 0 0.4rem;
        color: var(--primary);
        background: var(--rgba-primary-1, rgba(196, 90, 17, 0.1));
    }
</style>
<div class="deznav sidebar">
    <div class="deznav-scroll">
        <ul class="metismenu" id="menu">

            <li>
                <a href="{{ route('admin.dashboard') }}">
                    <div class="menu-icon">
                        <i class="ri-dashboard-line"></i>
                    </div>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            @if (canAccess('staff') || canAccess('author'))

                @if (canAccess('staff'))
                    <li>
                        <a href="{{ route('admin.staff.index') }}">
                            <div class="menu-icon">
                                <i class="ri-team-line"></i>
                            </div>
                            <span class="nav-text">Staff</span>
                        </a>
                    </li>
                @endif

                @if (canAccess('author'))
                    <li>
                        <a href="{{ route('admin.author.index') }}">
                            <div class="menu-icon">
                                <i class="ri-user-star-line"></i>
                            </div>
                            <span class="nav-text">Authors</span>
                        </a>
                    </li>
                @endif

            @endif

            <li>
                <a class="has-arrow" href="javascript:void(0)" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="ri-file-paper-2-line"></i>
                    </div>
                    <span class="nav-text">Manuscript</span>
                </a>
                @php
                    $manuscriptCounts = manuscriptCounts();
                    $isManuscriptAdmin = canViewMyManuscripts();
                @endphp
                <ul aria-expanded="false">
                    {{-- Chief Approval: superadmin-only queue of manuscripts awaiting the chief's permission to proceed. --}}
                    {{-- @if (isSuperAdmin())
                        <li><a href="{{ route('admin.application.index', ['type' => 'chief']) }}">Chief Approval <span class="menu-count">{{ $manuscriptCounts['chief'] }}</span></a></li>
                    @endif --}}
                    {{-- My Manuscripts is a filter in the admin Active view; staff
                         keep it as a direct queue link. --}}
                    @if ($isManuscriptAdmin && authUser('admin')->is_admin == 0)
                        <li><a href="{{ route('admin.application.index', ['type' => 'mine']) }}">My Manuscripts <span class="menu-count">{{ $manuscriptCounts['mine'] ?? 0 }}</span></a></li>
                    @endif
                    <li><a href="{{ route('admin.application.index') }}">Active<span class="menu-count">{{ $manuscriptCounts['all'] }}</span></a></li>
                    @if (authUser('admin')->is_admin == 0)
                        {{-- Completed records the specific person's finished work; Closed is the terminal status group. --}}
                        <li><a href="{{ route('admin.application.index', ['type' => 'completed']) }}">Completed <span class="menu-count">{{ $manuscriptCounts['completed'] ?? 0 }}</span></a></li>
                    @endif
                    <li><a href="{{ route('admin.application.index', ['type' => 'closed']) }}">Closed <span class="menu-count">{{ $manuscriptCounts['closed'] ?? 0 }}</span></a></li>
                    {{-- For admins, Published/Rejected/Withdrawn/Duplicate are filters inside Closed. --}}
                </ul>
            </li>

            @if (canViewInactiveApplications() || canViewReviewDeadlines())
                <li>
                    <a class="has-arrow" href="javascript:void(0)" aria-expanded="false">
                        <div class="menu-icon">
                            <i class="ri-tools-line"></i>
                        </div>
                        <span class="nav-text">Inactive and Deadlines</span>
                    </a>
                    <ul aria-expanded="false">
                        {{-- Inactive: assigned to the author (revise/proofreading) with no action for 3+ weeks. --}}
                        @if (canViewInactiveApplications())
                            <li><a href="{{ route('admin.application.index', ['type' => 'inactive']) }}">Inactive <span class="menu-count">{{ $manuscriptCounts['inactive'] ?? 0 }}</span></a></li>
                        @endif
                        {{-- Review Deadlines: per-reviewer reminder tracking. Admins + handling editors. --}}
                        @if (canViewReviewDeadlines())
                            <li><a href="{{ route('admin.application.index', ['type' => 'review-deadline']) }}">Review Deadlines <span class="menu-count">{{ $manuscriptCounts['review_deadline'] ?? 0 }}</span></a></li>
                        @endif
                    </ul>
                </li>
            @endif

            <li>
                <a href="{{ route('admin.query.index') }}">
                    <div class="menu-icon">
                        <i class="ri-question-answer-line"></i>
                    </div>
                    <span class="nav-text">Web Queries</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.mail.index') }}">
                    <div class="menu-icon">
                        <i class="ri-mail-send-line"></i>
                    </div>
                    <span class="nav-text">{{ authUser('admin')->is_admin == 1 ? 'Send Mail' : 'Contact Admin' }}</span>
                </a>
            </li>

            @if (authUser('admin')->is_admin == 1)
                <li>
                    <a href="{{ route('admin.inviteReviewer.index') }}">
                        <div class="menu-icon">
                            <i class="ri-user-search-line"></i>
                        </div>
                        <span class="nav-text">Invite External Reviewer</span>
                    </a>
                </li>
            @endif

            @if (canAccess('integration'))
                <li>
                    <a href="{{ route('admin.integration.index') }}">
                        <div class="menu-icon">
                            <i class="ri-drag-drop-line"></i>
                        </div>
                        <span class="nav-text">Interests</span>
                    </a>
                </li>
            @endif

        </ul>
        <div class="help-desk">
            <a href="{{ route('admin.logout') }}" class="btn btn-primary w-100">Logout</a>
        </div>
    </div>
</div>
