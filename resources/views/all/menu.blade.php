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
                                <?php
                                    $notifCount = 0;
                                    try {
                                        $uid = session('userid');
                                        if ($uid) {
                                            $notifCount = DB::table('notifications')->where('userid',$uid)->where('is_read',0)->count();
                                        }
                                    } catch (\Exception $e) {}
                                ?>
                                <span class="badge text-bg-primary notify-no rounded-circle">{{ $notifCount }}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-right mailbox animated bounceInDown">
                                <ul class="list-style-none">
                                    <li>
                                        <div class="message-center notifications position-relative">
                                            <?php
                                                $rows = [];
                                                try {
                                                    $uid = session('userid');
                                                    if ($uid) {
                                                        $rows = DB::table('notifications')->where('userid',$uid)->orderBy('created_at','desc')->limit(10)->get();
                                                    }
                                                } catch (\Exception $e) {}
                                            ?>
                                            @forelse($rows as $n)
                                                <a href="javascript:void(0)" class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                                    <div class="btn btn-success rounded-circle btn-circle"><i data-feather="bell" class="text-white"></i></div>
                                                    <div class="w-75 d-inline-block v-middle ps-2">
                                                        <h6 class="message-title mb-0 mt-1">{{ $n->title }}</h6>
                                                        <span class="font-12 text-nowrap d-block text-muted text-truncate">{{ $n->body }}</span>
                                                        <span class="font-12 text-nowrap d-block text-muted">{{ date('d M Y H:i', strtotime($n->created_at)) }}</span>
                                                    </div>
                                                </a>
                                            @empty
                                                <div class="px-3 py-2 text-muted">No notifications</div>
                                            @endforelse
                                        </div>
                        </li>
                        <li>
                            <a class="nav-link pt-3 text-center text-dark" href="/notifications">
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
                @if(session('level') == 2)
                <a class="dropdown-item" href="/followups"><i data-feather="clipboard"
                    class="svg-icon me-2 ms-1"></i>
                Follow-ups</a>
                @endif
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="/logout"><i data-feather="power"
                    class="svg-icon me-2 ms-1"></i>
                Logout</a>
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
            <?php
                $isLogin = session('is_login') === true;
                $level = session('level');
                $roleName = strtolower(str_replace(' ', '_', session('role') ?? ''));
                if (!$isLogin) {
                    $subject = 'guest';
                } else if ($level == 1) {
                    $subject = in_array($roleName, ['superadmin','admin']) ? $roleName : 'admin';
                } else if ($level == 2) {
                    $subject = in_array($roleName, ['counselling_teacher','homeroom_teacher']) ? $roleName : 'counselling_teacher';
                } else if ($level == 3) {
                    $subject = 'student';
                } else {
                    $subject = 'guest';
                }
                $allowed = [];
                try {
                    $allowed = collect(DB::table('menu_permissions')->where('subject',$subject)->where('allowed',1)->pluck('menu_key'))->all();
                } catch (\Exception $e) {
                    $allowed = [];
                }
                $can = function($key) use ($isLogin, $allowed) {
                    if (!$isLogin) {
                        return in_array($key, ['dashboard','login']);
                    }
                    if (in_array($key, ['dashboard'])) return true;
                    return empty($allowed) ? true : in_array($key, $allowed);
                };
            ?>
            <ul id="sidebarnav">
                <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/home"
                    aria-expanded="false"><i data-feather="home" class="feather-icon"></i><span
                    class="hide-menu">Dashboard</span></a></li>

                    <?php if(!$can('login')) { ?>
                    <li class="list-divider"></li>
                <?php } ?>
                    
                    <?php if($can('teacherlist')) { ?>
                    <li class="sidebar-item"> <a class="sidebar-link" href="/teacherlist"
                        aria-expanded="false"><i data-feather="list" class="feather-icon"></i><span
                        class="hide-menu">Teacher List
                    </span></a>
                </li>
                <?php } ?>
                @if(session('level') == 2)
                <?php if($can('followups')) { ?>
                <li class="sidebar-item"> <a class="sidebar-link" href="/followups"
                    aria-expanded="false"><i data-feather="clipboard" class="feather-icon"></i><span
                    class="hide-menu">Follow-ups
                </span></a>
                </li>
                <?php } ?>
                @endif
                <?php if($can('chat')) { ?>
                <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/chat"
                    aria-expanded="false"><i data-feather="message-square" class="feather-icon"></i><span
                    class="hide-menu">Chat</span></a></li>
                <?php } ?>

         <?php if($can('notifications')) { ?>
                <li class="sidebar-item"> <a class="sidebar-link" href="/notifications"
                    aria-expanded="false"><i data-feather="bell" class="feather-icon"></i><span
                    class="hide-menu">Notifications
                </span></a>
            </li>
            <?php } ?>

                <?php if($can('classdata') || $can('gradedata') || $can('majordata')) { ?>
                <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)"
                    aria-expanded="false"><i data-feather="grid" class="feather-icon"></i><span
                    class="hide-menu">Manage Data</span></a>
                    <ul aria-expanded="false" class="collapse  first-level base-level-line">
                        <?php if($can('classdata')) { ?><li class="sidebar-item"><a href="/classdata" class="sidebar-link"><span
                            class="hide-menu"> Class Data
                        </span></a>
                        </li><?php } ?>
                        <?php if($can('gradedata')) { ?><li class="sidebar-item"><a href="/gradedata" class="sidebar-link"><span
                            class="hide-menu"> Grade Data
                        </span></a>
                        </li><?php } ?>
                        <?php if($can('majordata')) { ?><li class="sidebar-item"><a href="/majordata" class="sidebar-link"><span
                            class="hide-menu"> Major Data
                        </span></a>
                        </li><?php } ?>
                    </ul>
                </li>
                <?php } ?>

                <?php if($can('userdata')) { ?>
                <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/userdata"
                    aria-expanded="false"><i data-feather="users" class="feather-icon"></i><span
                    class="hide-menu">User data</span></a></li>
                <?php } ?>

            <?php if(!$can('login')) { ?>
                <li class="list-divider"></li>
            <?php } ?>

                        <?php if($can('activity_logs')) { ?><li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/activity-logs"
                            aria-expanded="false"><i data-feather="bar-chart-2" class="feather-icon"></i><span
                            class="hide-menu">Activity Logs
                        </span></a>
                    </li><?php } ?>

                    <?php if($can('database')) { ?><li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/trash"
                        aria-expanded="false"><i data-feather="trash-2" class="feather-icon"></i><span
                        class="hide-menu">Trash Can
                    </span></a>
                </li><?php } ?>

                <?php if($can('database')) { ?><li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/database"
                    aria-expanded="false"><i data-feather="layers" class="feather-icon"></i><span
                    class="hide-menu">Database
                </span></a>
            </li><?php } ?>

            <?php if($can('permission')) { ?>
            <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/permission"
                aria-expanded="false"><i data-feather="shield" class="feather-icon"></i><span
                class="hide-menu">Permission
            </span></a>
        </li>
        <?php } ?>

        <?php if($can('setting')) { ?>
        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/setting"
            aria-expanded="false"><i data-feather="settings" class="feather-icon"></i><span
            class="hide-menu">Setting
        </span></a>
    </li>
    <?php } ?>
    <li class="list-divider"></li>

    <?php if($can('login')) { ?>
    <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="/login"
        aria-expanded="false"><i data-feather="lock" class="feather-icon"></i><span
        class="hide-menu">Login
    </span></a>
</li>
<?php } ?>
</ul>
</nav>
<!-- End Sidebar navigation -->
</div>
<!-- End Sidebar scroll-->
</aside>
