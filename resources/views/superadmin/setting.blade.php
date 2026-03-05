        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Website Setting</h4>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="/home" class="text-muted">Home</a></li>
                                    <li class="breadcrumb-item text-muted active" aria-current="page">Setting</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Default Input</h4>
                                <h6 class="card-subtitle">To use add <code>form-control</code> class to the input</h6>
                                <form class="mt-4" action="/setting/update" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="systemid" value="<?= $system->systemid ?>">
                                    <div class="form-group mb-3">
                                        <label>System Name</label>
                                        <input type="text" class="form-control" name="name" value="<?=$system->systemname ?>">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>System Logo</label>
                                        <input type="file" class="form-control" value="<?=$system->systemlogo ?>" name="logo">
                                    </div>
                                    <label > Current Logo </label>
                                    <div class="form-group mb-3">
                                        <img src="<?= asset('storage/' . $system->systemlogo) ?>" style="width: 100px;">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>System Address</label>
                                        <input type="text" class="form-control" value="<?=$system->systemaddress ?>" name="address">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>System Manager</label>
                                        <input type="text" class="form-control" value="<?=$system->systemmanager ?>" name="manager">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>System Contact</label>
                                        <input type="text" class="form-control" value="<?=$system->systemcontact ?>" name="contact">
                                    </div>
                                    <div class="form-group mb-3">
                                        <button type="submit" class="btn btn-dark w-100">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
            </div>
            <footer class="footer text-center">
                All Rights Reserved by Freedash. Designed and Developed by <a
                    href="https://wrappixel.com">WrapPixel</a>.
            </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>