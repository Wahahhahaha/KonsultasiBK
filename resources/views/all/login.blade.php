<div class="wrapper">
<<<<<<< HEAD
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
                                <div class="col-lg-12 text-center">
                                    <button type="submit" class="btn w-100 btn-dark">Sign In</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
=======
      <section class="login-content">
         <div class="row m-0 align-items-center bg-white vh-100">            
            <div class="col-md-6">
               <div class="row justify-content-center">
                  <div class="col-md-10">
                     <h2 class="mb-2 mt-3 text-left"><img src="<?= asset('storage/' . $system->systemlogo) ?>" style="width: 80px;" class="img-fluid"></h2>
                        <h1 class="mb-6 text-left"><?= $system->systemname ?></h2>

                     <div class="card card-transparent shadow-none d-flex justify-content-center mb-0 auth-card">
                        <div class="card-body z-3 px-md-0 px-lg-4">
                           <a href="/home" class="navbar-brand d-flex align-items-center mb-3">

                              <div class="col-lg-7 col-md-5 modal-bg-img" style="background-image: url(../assets/images/big/3.jpg);">
                </div>
                           </a>
                           <h2 class="mb-2 text-center">Sign In</h2>
                           <p class="text-center">Login to stay connected.</p>
                              <div class="row">
                                 <div class="col-lg-12">
                                  <form action="/login/process" method="post">
                                    @csrf
                                    <div class="form-group">
                                       <label for="username" class="form-label">ID Number</label>
                                       <input type="text" class="form-control" name="username" id="username" aria-describedby="username" placeholder=" ">
                                    </div>
                                 </div>
                                 <div class="col-lg-12">
                                    <div class="form-group">
                                       <label for="password" class="form-label">Password</label>
                                       <input type="password" class="form-control" name="password" id="password" aria-describedby="password" placeholder=" ">
                                    </div>
                                 </div>
                                 <div class="col-lg-12  justify-content-between" style="padding-top: 15px; padding-bottom: 15px;">
                                    <a href="recoverpw.html" style="text-align: center;">Forgot Password?</a>
                                 </div>
                              </div>
                              <div class="d-flex justify-content-center">
                                 <button type="submit" class="btn w-100 btn-dark">Sign In</button>
                              </div>
                           </form>
                           <p class="mt-3 text-center">
                              @if(session('error'))
                              <div class="alert alert-danger">
                               {{ session('error') }}
                            </div>
                            @endif

                            @if ($errors->any())
                            <div class="alert alert-danger">
                               @foreach ($errors->all() as $error)
                               <div>{{ $error }}</div>
                               @endforeach
                            </div>
                            @endif
                            <!-- Don’t have an account? <a href="/register" class="text-underline">Click here to sign up.</a> -->
                         </p>
                      </div>
                   </div>
                </div>
             </div>
          </div>
          <div class="col-md-6 d-md-block d-none bg-primary p-0 mt-n1 vh-100 overflow-hidden" style="background:url(../assets/images/big/auth-bg.jpg) no-repeat center center;">
          </div>
       </div>
      </section>
>>>>>>> b7901593b3017170e4c24a8a370bd99885522be3
      </div>
