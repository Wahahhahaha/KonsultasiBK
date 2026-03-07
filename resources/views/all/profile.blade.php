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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title">Profile</h4>
                                        <form class="mt-4" action="/myprofile/update" method="post" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group mb-3">
                                                <label>ID Number</label>
                                                <input type="text" class="form-control" value="<?=$data->username ?>" disabled>
                                            </div>

                                            @if($data->levelid == 3)
                                            <div class="form-group mb-3">
                                                <label>Class</label>
                                                <input type="text" class="form-control" value="<?=$data->gradename ?> <?=$data->classname?> <?=$data->majorname?>" disabled>
                                            </div>
                                            @endif

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
                                                <input type="text" class="form-control" value="<?=$data->phonenumber ?>" disabled>
                                            </div>
                                            <div class="form-group mb-3">
                                                <button type="submit" class="btn btn-dark w-100">Save Profile</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title">Change Password</h4>
                                        <form class="mt-4" action="/myprofile/changepw" method="post">
                                            @csrf
                                            <div class="form-group mb-3">
                                                <label>Current Password</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control password-input" name="cp">
                                                    <button class="btn btn-outline-secondary toggle-password" type="button">Show</button>
                                                </div>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label>New Password</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control password-input" name="np">
                                                    <button class="btn btn-outline-secondary toggle-password" type="button">Show</button>
                                                </div>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label>Confirm New Password</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control password-input" name="rp">
                                                    <button class="btn btn-outline-secondary toggle-password" type="button">Show</button>
                                                </div>
                                            </div>
                                            <div class="form-group mb-3">
                                                <button type="submit" class="btn btn-primary w-100">Change Password</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="card mt-3">
                                    <div class="card-body">
                                        <h4 class="card-title">Change Phone (WhatsApp OTP)</h4>
                                        <div class="form-group mb-3">
                                            <label>New Phone (WhatsApp)</label>
                                            <input type="text" class="form-control" id="new_phone" placeholder="08xxxx">
                                        </div>
                                        <div class="form-group mb-3">
                                            <button class="btn btn-dark w-100" type="button" onclick="requestPhoneOtp()">Send OTP via WhatsApp</button>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>Enter OTP</label>
                                            <input type="text" class="form-control" id="phone_otp" placeholder="6-digit OTP">
                                        </div>
                                        <div class="form-group mb-3">
                                            <button class="btn btn-primary w-100" type="button" onclick="confirmPhoneOtp()">Confirm & Update Phone</button>
                                        </div>
                                        <div id="phone-otp-status" class="small text-muted"></div>
                                    </div>
                                </div>
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

<script>
document.querySelectorAll('.toggle-password').forEach(function(btn){
    btn.addEventListener('click', function(){
        const input = this.parentElement.querySelector('.password-input');
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            this.textContent = 'Hide';
        } else {
            input.type = 'password';
            this.textContent = 'Show';
        }
    });
});

function requestPhoneOtp() {
    const phone = document.getElementById('new_phone').value.trim();
    const statusEl = document.getElementById('phone-otp-status');
    if (!phone) { statusEl.textContent = 'Please enter new phone number.'; return; }
    fetch('/myprofile/request-phone-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ new_phone: phone })
    }).then(res => res.json()).then(data => {
        statusEl.textContent = data.message || (data.success ? 'OTP sent' : 'Failed to send OTP');
    });
}

function confirmPhoneOtp() {
    const otp = document.getElementById('phone_otp').value.trim();
    const statusEl = document.getElementById('phone-otp-status');
    if (!otp) { statusEl.textContent = 'Please enter OTP.'; return; }
    fetch('/myprofile/confirm-phone-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ otp })
    }).then(res => res.json()).then(data => {
        statusEl.textContent = data.message || (data.success ? 'Phone updated' : 'Failed to update phone');
        if (data.success) {
            location.reload();
        }
    });
}
</script>
