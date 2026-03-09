<div class="wrapper">
    <div class="auth-wrapper d-flex no-block justify-content-center align-items-center position-relative" style="background:url(../assets/images/big/auth-bg.jpg) no-repeat center center;">
        <div class="auth-box row" style="max-width: 900px; width: 90%;">
            <div class="col-lg-7 col-md-5 modal-bg-img" style="background-image: url(../assets/images/big/img7.png);">
            </div>
            <div class="col-lg-5 col-md-7 bg-white">
                <div class="p-3">
                    <div class="text-center mb-2">
                        <img src="<?= asset('storage/' . $system->systemlogo) ?>" style="width: 80px;" class="img-fluid">
                    </div>
                    <h2 class="mt-2 text-center">Forgot Password</h2>
                    <p class="text-center">Choose a method to reset your password.</p>
                    @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <div class="d-flex justify-content-center gap-3 mb-3">
                        <button id="tab-email" class="btn btn-outline-primary btn-sm">Via Email</button>
                        <button id="tab-phone" class="btn btn-outline-secondary btn-sm">Via Phone Number</button>
                    </div>
                    <div id="form-email">
                        <form action="/forgot-password/email" method="post">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="form-label text-dark">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter registered email">
                            </div>
                            <button type="submit" class="btn w-100 btn-dark">Send Reset Link</button>
                        </form>
                    </div>
                    <div id="form-phone" style="display:none;">
                        <form action="/forgot-password/phone" method="post">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="form-label text-dark">Phone Number</label>
                                <input type="text" name="phone" class="form-control" placeholder="Example: 62812xxxxxxx">
                            </div>
                            <button type="submit" class="btn w-100 btn-dark">Send OTP Code</button>
                        </form>
                    </div>
                    <div class="text-center mt-3">
                        <a href="/login" class="small">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const tabEmail = document.getElementById('tab-email');
        const tabPhone = document.getElementById('tab-phone');
        const formEmail = document.getElementById('form-email');
        const formPhone = document.getElementById('form-phone');
        tabEmail.addEventListener('click', function() {
            formEmail.style.display = 'block';
            formPhone.style.display = 'none';
            tabEmail.classList.remove('btn-outline-primary'); tabEmail.classList.add('btn-primary');
            tabPhone.classList.remove('btn-primary'); tabPhone.classList.add('btn-outline-secondary');
        });
        tabPhone.addEventListener('click', function() {
            formEmail.style.display = 'none';
            formPhone.style.display = 'block';
            tabPhone.classList.remove('btn-outline-secondary'); tabPhone.classList.add('btn-primary');
            tabEmail.classList.remove('btn-primary'); tabEmail.classList.add('btn-outline-primary');
        });
    </script>
</div>
