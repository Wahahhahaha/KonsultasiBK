        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">My Profile</h4>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="/home" class="text-muted">Home</a></li>
                                    <li class="breadcrumb-item text-muted active" aria-current="page">Profile</li>
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
                                <form class="mt-4" action="/myprofile/update" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label>ID Number</label>
                                        <input type="text" class="form-control" name="name" value="<?=$data->username ?>">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Name</label>
                                        <input type="text" class="form-control" value="<?=$data->name ?>" name="name">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Email</label>
                                        <input type="email" class="form-control" value="<?=$data->email ?>" name="email">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Phonenumber</label>
                                        <input type="text" class="form-control" value="<?=$data->phonenumber ?>" name="phone">
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