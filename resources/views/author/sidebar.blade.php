<div class="deznav sidebar">
    <div class="deznav-scroll">
        <ul class="metismenu" id="menu">


            <li>
                <a href="{{ route('author.dashboard') }}" class="" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="ri-dashboard-line" width="22" height="22"></i>

                    </div>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            {{-- <li>
                <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="ri-user-3-line" width="22" height="22"></i>
                    </div>
                    <span class="nav-text">Users</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.staff.index') }}">Staff</a></li>
                    <li><a href="{{ route('admin.author.index') }}">Authors</a></li>

                </ul>
            </li> --}}

            <li>
                <a href="{{ route('author.application.index') }}" class="" aria-expanded="false">
                    <div class="menu-icon">
                        <i class="ri-draft-line" width="22" height="22"></i>
                    </div>
                    <span class="nav-text">Applications</span>
                </a>
            </li>

            {{-- <li class="menu-title">WEB CONFIGURATIONS</li>
            <li>
                <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">
                    <div class="menu-icon">
                        <div class="menu-icon">
                            <i class="ri-global-line" width="22" height="22"></i>
                        </div>
                    </div>
                    <span class="nav-text">Web </span>
                </a>
                <ul aria-expanded="false">
                    <li>
                        <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">Blogs</a>
                        <ul aria-expanded="false">
                            <li><a href="#">Blog Categories</a></li>
                            <li><a href="#">Blog List</a></li>
                            <li><a href="#">Create Blog</a></li>
                        </ul>
                    </li>
                    <li>
                        <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">Queries</a>
                        <ul aria-expanded="false">
                            <li><a href="#">Web Enquiries</a></li>
                            <li><a href="#">Service Enquiries</a></li>
                            <li><a href="#">Career Enquiries </a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="contacts.html">Forum & Discussion</a>
                    </li>

                    <li><a href="app-calender.html">SEO</a></li>
                    <li>
                        <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">Other</a>
                        <ul aria-expanded="false">
                            <li><a href="ecom-product-grid.html">Banners</a></li>
                            <li><a href="ecom-product-list.html">Frequently Asked Questions(FAQ)</a></li>
                            <li><a href="ecom-product-detail.html">Reviews and Ratings</a></li>
                            <li><a href="{{ route('admin.quicklink.index') }}">Important Links</a></li>
                            <li><a href="ecom-checkout.html">News and Updates</a></li>
                            <li><a href="ecom-invoice.html">Annoucements</a></li>

                        </ul>
                    </li>
                </ul>
            </li> --}}


        </ul>
        <div class="help-desk">
            <a href="{{ route('admin.logout') }}" class="btn btn-primary w-100">Logout</a>
        </div>
    </div>
</div>
