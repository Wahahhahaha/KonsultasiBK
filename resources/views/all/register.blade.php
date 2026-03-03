      <div class="wrapper">
      <section class="login-content">
         <div class="row m-0 align-items-center bg-white h-100">            
               <div class="col-md-6 d-md-block d-none bg-primary p-0 mt-n1 vh-100 overflow-hidden" style="background:url(../assets/images/big/auth-bg.jpg) no-repeat center center;">
            </div>
            <div class="col-md-6">               
               <div class="row justify-content-center">
                  <div class="col-md-10">
                     <div class="card card-transparent auth-card shadow-none d-flex justify-content-center mb-0">
                        <div class="card-body">
                           <a href="/home" class="navbar-brand d-flex align-items-center mb-3">
                              
                              <h4 class="logo-title ms-3"><?= $system->systemname ?></h4>
                           </a>
                           <h2 class="mb-2 text-center">Sign Up</h2>
                           <p class="text-center">Create your Hope UI account.</p>
                           <form>
                              <div class="row">
                                 <div class="col-lg-6">
                                    <div class="form-group">
                                       <label for="last-name" class="form-label">Name</label>
                                       <input type="text" class="form-control" id="last-name" placeholder=" ">
                                    </div>
                                 </div>
                                 <div class="col-lg-6">
                                    <div class="form-group">
                                       <label for="email" class="form-label">Email</label>
                                       <input type="email" class="form-control" id="email" placeholder=" ">
                                    </div>
                                 </div>
                                 <div class="col-lg-6">
                                    <div class="form-group">
                                       <label for="phone" class="form-label">Phone No.</label>
                                       <input type="text" class="form-control" id="phone" placeholder=" ">
                                    </div>
                                 </div>
                                 <div class="col-lg-6">
                                    <div class="form-group">
                                       <label for="phone" class="form-label">Gender</label>
                                       <select class="form-select">
                                         <option selected disabled>Gender</option>
                                         <option>Male</option>
                                         <option>Female</option>
                                       </select>
                                    </div>
                                 </div>
                                 <div class="col-lg-6">
                                    <div class="form-group">
                                       <label for="password" class="form-label">Password</label>
                                       <input type="password" class="form-control" id="password" placeholder=" ">
                                    </div>
                                 </div>
                                 <div class="col-lg-6">
                                    <div class="form-group">
                                       <label for="confirm-password" class="form-label">Confirm Password</label>
                                       <input type="text" class="form-control" id="confirm-password" placeholder=" ">
                                    </div>
                                 </div>
                                 <div class="col-lg-12 d-flex justify-content-center">
                                    <div class="form-check mb-3">
                                       <input type="checkbox" class="form-check-input" id="customCheck1">
                                       <label class="form-check-label" for="customCheck1">I agree with the terms of use</label>
                                    </div>
                                 </div>
                              </div>
                              <div class="d-flex justify-content-center">
                                 <button type="submit" class="btn w-100 btn-dark">Sign Up</button>
                              </div>
                              <p class="mt-3 text-center">
                                 Already have an Account <a href="/login" class="text-underline">Sign In</a>
                              </p>
                           </form>
                        </div>
                     </div>    
                  </div>
               </div>           
            </div>   
         </div>
      </section>