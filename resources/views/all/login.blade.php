<div class="wrapper">

      <div class="auth-wrapper d-flex no-block justify-content-center align-items-center position-relative"
            style="background:url(../assets/images/big/auth-bg.jpg) no-repeat center center;">
            <div class="auth-box row">
                <div class="col-lg-7 col-md-5 modal-bg-img" style="background-image: url(../assets/images/big/img7.png  );">
                </div>
                <div class="col-lg-5 col-md-7 bg-white">
                    <div class="p-3">
                        <div class="text-center">
                            <img src="<?= asset('storage/' . $system->systemlogo) ?>" style="width: 80px;" class="img-fluid"></h2>
                        <h1 class="mb-6 text-left">
                        </div>
                        <h2 class="mt-3 text-center">Sign In</h2>
                        <p class="text-center">Enter your email address and password to access the website.</p>
                        <form action="/login/process" method="post">
                                    @csrf
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label text-dark" for="name">ID Number</label>
                                        <input class="form-control" id="name" type="text"
                                            placeholder="Enter your ID number" name="username">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label text-dark" for="pwd">Password</label>
                                        <input class="form-control" id="pwd" type="password"
                                            placeholder="Enter your password" name="password">
                                    </div>
                                </div>

                                @if($showCaptcha ?? false)
                                 <div class="col-lg-12">
                                     <div class="form-group mb-3">
                                         <!-- reCAPTCHA Container -->
                                         <div id="recaptcha-wrapper" style="min-height: 78px;">
                                             <div id="recaptcha-container"></div>
                                         </div>
                                         
                                         <!-- Math CAPTCHA Container (Fallback) -->
                                         <div id="math-captcha-container" style="display: none;">
                                             <label class="form-label text-dark">Security Check (Offline Mode)</label>
                                             <div class="d-flex align-items-center">
                                                 <span class="me-2 fw-bold" style="font-size: 1.2rem;">{{ $mathProblem }}</span>
                                                 <input type="number" class="form-control" name="math_answer" placeholder="Answer" style="width: 100px;">
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                 
                                 <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer onerror="fallbackCaptcha()"></script>
                                 <script>
                                     // Function to show Math Captcha as fallback
                                     function fallbackCaptcha() {
                                         console.log('Switching to Math Captcha (Offline/Error)');
                                         document.getElementById('recaptcha-wrapper').style.display = 'none';
                                         document.getElementById('math-captcha-container').style.display = 'block';
                                         
                                         // If we fallback, remove the g-recaptcha-response input if it exists to avoid validation confusion
                                         // (though controller logic handles priority)
                                     }

                                     // Callback when Google reCAPTCHA script loads successfully
                                     function onloadCallback() {
                                         console.log('reCAPTCHA script loaded. Checking connectivity...');
                                         
                                         if (navigator.onLine) {
                                             try {
                                                 console.log('Online detected. Rendering reCAPTCHA...');
                                                 grecaptcha.render('recaptcha-container', {
                                                     'sitekey' : '{{ env('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI') }}',
                                                     'theme': 'light',
                                                     'error-callback': function() {
                                                         console.error('reCAPTCHA error-callback fired.');
                                                         fallbackCaptcha();
                                                     }
                                                 });
                                             } catch(e) {
                                                 console.error('reCAPTCHA render exception:', e);
                                                 fallbackCaptcha();
                                             }
                                         } else {
                                             console.warn('Navigator reports offline.');
                                             fallbackCaptcha();
                                         }
                                     }

                                     // Immediate check for offline status
                                     if (!navigator.onLine) {
                                         fallbackCaptcha();
                                     }

                                     // Event listeners for connectivity changes
                                     window.addEventListener('offline', fallbackCaptcha);
                                     window.addEventListener('online', function() {
                                         console.log('Back online. Reloading page to try reCAPTCHA...');
                                         // Optional: Reload page to restore reCAPTCHA if user comes back online
                                         // location.reload(); 
                                     });
                                 </script>
                                 @endif

                                <div class="col-lg-12  justify-content-between" style="padding-top: 15px; padding-bottom: 15px;">
                                    <button type="submit" class="btn w-100 btn-dark">Sign In</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
      </div>
