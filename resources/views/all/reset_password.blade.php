<div class="wrapper">
    <div class="auth-wrapper d-flex no-block justify-content-center align-items-center position-relative" style="background:url(../assets/images/big/auth-bg.jpg) no-repeat center center;">
        <div class="auth-box row" style="max-width: 900px; width: 90%;">
            <div class="col-lg-7 col-md-5 modal-bg-img" style="background-image: url(../assets/images/big/img7.png);">
            </div>
            <div class="col-lg-5 col-md-7 bg-white">
                <div class="p-3">
                    <h2 class="mt-2 text-center">Reset Password</h2>
                    @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(($method ?? '') === 'email')
                    <form action="/reset-password" method="post">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="method" value="email">
                        <div class="form-group mb-3">
                            <label class="form-label text-dark">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $email }}" readonly>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label text-dark">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters">
                        </div>
                        <div class="form-group mb-4">
                            <label class="form-label text-dark">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password">
                        </div>
                        <button type="submit" class="btn w-100 btn-dark">Reset Password</button>
                    </form>
                    @elseif(($method ?? '') === 'phone')
                    <form action="/reset-password/phone" method="post">
                        @csrf
                        <input type="hidden" name="method" value="phone">
                        <div class="form-group mb-3">
                            <label class="form-label text-dark">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ $phone }}" readonly>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label text-dark">OTP Code</label>
                            <input type="text" name="otp" class="form-control" placeholder="Enter 6-digit OTP">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label text-dark">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters">
                        </div>
                        <div class="form-group mb-4">
                            <label class="form-label text-dark">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password">
                        </div>
                        <button type="submit" class="btn w-100 btn-dark">Reset Password</button>
                    </form>
                    @endif
                    <div class="text-center mt-3">
                        <a href="/login" class="small">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
