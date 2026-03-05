        <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar" data-navbarbg="skin6">
            <nav class="navbar top-navbar navbar-expand-lg">
                <div class="navbar-header" data-logobg="skin6">
                    <!-- This is for the sidebar toggle which is visible on mobile only -->
                    <a class="nav-toggler waves-effect waves-light d-block d-lg-none" href="javascript:void(0)"><i
                        class="ti-menu ti-close"></i></a>
                        <!-- ============================================================== -->
                        <!-- Logo -->
                        <!-- ============================================================== -->
                        <div class="navbar-brand">
                            <!-- Logo icon -->
                            <a href="/home">
                                <img src="<?= asset('storage/' . $system->systemlogo) ?>" style="width: 30px;" class="img-fluid"> <?= $system->systemname ?>
                            </a>
                        </div>
                        <a class="topbartoggler d-block d-lg-none waves-effect waves-light" href="javascript:void(0)"
                        data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i
                        class="ti-more"></i></a>
                    </div>
                    <div class="navbar-collapse collapse" id="navbarSupportedContent">
                        <!-- ============================================================== -->
                        <!-- toggle and nav items -->
                        <!-- ============================================================== -->
                        <ul class="navbar-nav float-left me-auto ms-3 ps-1">
                        </ul>
                        <!-- ============================================================== -->
                        <!-- Right side toggle and nav items -->
                        <!-- ============================================================== -->
                        <ul class="navbar-nav float-end">
                            <!-- ============================================================== -->
                            <!-- User profile and search -->
                            <!-- ============================================================== -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle pl-md-3 position-relative" href="javascript:void(0)"
                                id="bell" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <span><i data-feather="bell" class="svg-icon"></i></span>
                                <span class="badge text-bg-primary notify-no rounded-circle">5</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-left mailbox animated bounceInDown">
                                <ul class="list-style-none">
                                    <li>
                                        <div class="message-center notifications position-relative">
                                            <!-- Message -->
                                            <a href="javascript:void(0)"
                                            class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                            <div class="btn btn-danger rounded-circle btn-circle"><i
                                                data-feather="airplay" class="text-white"></i></div>
                                                <div class="w-75 d-inline-block v-middle ps-2">
                                                    <h6 class="message-title mb-0 mt-1">Luanch Admin</h6>
                                                    <span class="font-12 text-nowrap d-block text-muted">Just see
                                                        the my new
                                                    admin!</span>
                                                    <span class="font-12 text-nowrap d-block text-muted">9:30 AM</span>
                                                </div>
                                            </a>
                                            <!-- Message -->
                                            <a href="javascript:void(0)"
                                            class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                            <span class="btn btn-success text-white rounded-circle btn-circle"><i
                                                data-feather="calendar" class="text-white"></i></span>
                                                <div class="w-75 d-inline-block v-middle ps-2">
                                                    <h6 class="message-title mb-0 mt-1">Event today</h6>
                                                    <span
                                                    class="font-12 text-nowrap d-block text-muted text-truncate">Just
                                                a reminder that you have event</span>
                                                <span class="font-12 text-nowrap d-block text-muted">9:10 AM</span>
                                            </div>
                                        </a>
                                        <!-- Message -->
                                        <a href="javascript:void(0)"
                                        class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                        <span class="btn btn-info rounded-circle btn-circle"><i
                                            data-feather="settings" class="text-white"></i></span>
                                            <div class="w-75 d-inline-block v-middle ps-2">
                                                <h6 class="message-title mb-0 mt-1">Settings</h6>
                                                <span
                                                class="font-12 text-nowrap d-block text-muted text-truncate">You
                                                can customize this template
                                            as you want</span>
                                            <span class="font-12 text-nowrap d-block text-muted">9:08 AM</span>
                                        </div>
                                    </a>
                                    <!-- Message -->
                                    <a href="javascript:void(0)"
                                    class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                    <span class="btn btn-primary rounded-circle btn-circle"><i
                                        data-feather="box" class="text-white"></i></span>
                                        <div class="w-75 d-inline-block v-middle ps-2">
                                            <h6 class="message-title mb-0 mt-1">Pavan kumar</h6> <span
                                            class="font-12 text-nowrap d-block text-muted">Just
                                        see the my admin!</span>
                                        <span class="font-12 text-nowrap d-block text-muted">9:02 AM</span>
                                    </div>
                                </a>
                            </div>
                        </li>
                        <li>
                            <a class="nav-link pt-3 text-center text-dark" href="javascript:void(0);">
                                <strong>Check all notifications</strong>
                                <i class="fa fa-angle-right"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-bs-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <span class="ms-2 d-none d-lg-inline-block"><span>Hello,</span> <span
                class="text-dark">{{ session('name') }}</span> <i data-feather="chevron-down"
                class="svg-icon"></i></span>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-right user-dd animated flipInY">
                <a class="dropdown-item" href="/myprofile"><i data-feather="user"
                    class="svg-icon me-2 ms-1"></i>
                My Profile</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="javascript:void(0)"><i data-feather="settings"
                    class="svg-icon me-2 ms-1"></i>
                Account Setting</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="/logout"><i data-feather="power"
                    class="svg-icon me-2 ms-1"></i>
                Logout</a>
                <div class="dropdown-divider"></div>
                <div class="pl-4 p-3"><a href="javascript:void(0)" class="btn btn-sm btn-info">View
                Profile</a></div>
            </div>
        </li>
        <!-- ============================================================== -->
        <!-- User profile and search -->
        <!-- ============================================================== -->
    </ul>
</div>
</nav>
</header>
<!-- ============================================================== -->
<!-- End Topbar header -->
<!-- ============================================================== -->
<!-- ============================================================== -->
<!-- Left Sidebar - style you can find in sidebar.scss  -->
<!-- ============================================================== -->
<aside class="left-sidebar" data-sidebarbg="skin6">
    <!-- Sidebar scroll-->
    <div class="scroll-sidebar" data-sidebarbg="skin6">
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav">
            <ul id="sidebarnav">
                <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/home"
                    aria-expanded="false"><i data-feather="home" class="feather-icon"></i><span
                    class="hide-menu">Dashboard</span></a></li>
                    <li class="list-divider"></li>
                    <li class="nav-small-cap"><span class="hide-menu">Applications</span></li>

                    <li class="sidebar-item"> <a class="sidebar-link" href="/teacherlist"
                        aria-expanded="false"><i data-feather="list" class="feather-icon"></i><span
                        class="hide-menu">Teacher List
                    </span></a>
                </li>
                <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/chat"
                    aria-expanded="false"><i data-feather="message-square" class="feather-icon"></i><span
                    class="hide-menu">Chat</span></a></li>
                    <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="app-calendar.html"
                        aria-expanded="false"><i data-feather="calendar" class="feather-icon"></i><span
                        class="hide-menu">Calendar</span></a></li>

                        <li class="sidebar-item">
                            <a class="sidebar-link sidebar-link" href="/userdata" aria-expanded="false">
                                <i data-feather="calendar" class="feather-icon"></i>
                                <span class="hide-menu">User data</span>
                            </a>
                        </li>

                        <li class="list-divider"></li>

                        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/database"
                            aria-expanded="false"><i data-feather="layers" class="feather-icon"></i><span
                            class="hide-menu">Activity Logs
                        </span></a>
                    </li>

                    <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/database"
                        aria-expanded="false"><i data-feather="layers" class="feather-icon"></i><span
                        class="hide-menu">Trash Can
                    </span></a>
                </li>

                <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/database"
                    aria-expanded="false"><i data-feather="layers" class="feather-icon"></i><span
                    class="hide-menu">Database
                </span></a>
            </li>

            <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/setting"
                aria-expanded="false"><i data-feather="settings" class="feather-icon"></i><span
                class="hide-menu">Permission
            </span></a>
        </li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/setting"
            aria-expanded="false"><i data-feather="settings" class="feather-icon"></i><span
            class="hide-menu">Setting
        </span></a>
    </li>
    <li class="list-divider"></li>
    <li class="nav-small-cap"><span class="hide-menu">Authentication</span></li>

    <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/login"
        aria-expanded="false"><i data-feather="lock" class="feather-icon"></i><span
        class="hide-menu">Login
    </span></a>
</li>
</ul>
</nav>
<!-- End Sidebar navigation -->
</div>
<!-- End Sidebar scroll-->
</aside>