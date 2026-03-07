        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <?php $displayName = session('is_login') ? session('name') : 'Guest'; ?>
                        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1"><span id="greet"></span> {{ $displayName }}!</h3>
                        <script>
                            (function(){
                                var h = new Date().getHours();
                                var g = 'Good Night';
                                if (h >= 5 && h < 12) g = 'Good Morning';
                                else if (h >= 12 && h < 17) g = 'Good Afternoon';
                                else if (h >= 17 && h < 21) g = 'Good Evening';
                                document.getElementById('greet').textContent = g;
                            })();
                        </script>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="index.html">Dashboard</a>
                                    </li>
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
                <!-- *************************************************************** -->
                <!-- Start First Cards -->
                <!-- *************************************************************** -->
                <div class="row">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-end">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h2 class="text-dark mb-1 font-weight-medium">{{ $countUsers ?? 0 }}</h2>
                                        <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Total Users</h6>
                                    </div>
                                    <div class="ms-auto mt-md-3 mt-lg-0">
                                        <span class="opacity-7 text-muted"><i data-feather="users"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-end ">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h2 class="text-dark mb-1 w-100 text-truncate font-weight-medium">{{ $countStudents ?? 0 }}</h2>
                                        <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Total Students</h6>
                                    </div>
                                    <div class="ms-auto mt-md-3 mt-lg-0">
                                        <span class="opacity-7 text-muted"><i data-feather="user"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-end ">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h2 class="text-dark mb-1 font-weight-medium">{{ $countTeachers ?? 0 }}</h2>
                                        <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Total Teachers</h6>
                                    </div>
                                    <div class="ms-auto mt-md-3 mt-lg-0">
                                        <span class="opacity-7 text-muted"><i data-feather="user-check"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card ">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h2 class="text-dark mb-1 font-weight-medium">{{ $countConsults ?? 0 }}</h2>
                                        <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Approved Consultations</h6>
                                    </div>
                                    <div class="ms-auto mt-md-3 mt-lg-0">
                                        <span class="opacity-7 text-muted"><i data-feather="message-square"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- *************************************************************** -->
                <!-- End First Cards -->
                <!-- *************************************************************** -->
                <!-- *************************************************************** -->
                <!-- Teacher List -->
                <!-- *************************************************************** -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <h4 class="card-title mb-0">Counselling Teachers</h4>
                                </div>
                                <div class="row">
                                    @foreach(($teachers ?? []) as $key)
                                    <div class="col-md-6 col-lg-3 mb-3">
                                        <div class="border p-3 rounded h-100 d-flex flex-column">
                                            <div class="mb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="btn btn-primary btn-circle me-2">
                                                        <i data-feather="user"></i>
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-medium">{{ $key->name }}</div>
                                                        <div class="small text-muted">{{ $key->gradename ? 'Counsellor - ' . $key->gradename : 'Counsellor' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <small class="text-muted font-weight-bold d-block mb-1">Schedule:</small>
                                                <div class="small">
                                                    @php $weekday = []; $weekend = []; @endphp
                                                    @foreach(($key->schedules ?? []) as $s)
                                                        @php
                                                            $day = strtolower($s->day_of_week);
                                                            $timeStr = substr($s->start_time,0,5) . ' - ' . substr($s->end_time,0,5);
                                                        @endphp
                                                        @if(in_array($day, ['monday','tuesday','wednesday','thursday','friday']))
                                                            @php $weekday[$timeStr][] = $day; @endphp
                                                        @else
                                                            @php $weekend[$timeStr][] = $day; @endphp
                                                        @endif
                                                    @endforeach
                                                    @foreach($weekday as $time => $days)
                                                        <div class="d-flex justify-content-between">
                                                            <span>Weekdays</span><span class="text-dark">{{ $time }}</span>
                                                        </div>
                                                    @endforeach
                                                    @foreach($weekend as $time => $days)
                                                        <div class="d-flex justify-content-between">
                                                            <span>Weekend</span><span class="text-dark">{{ $time }}</span>
                                                        </div>
                                                    @endforeach
                                                    @if((count($key->schedules ?? []) === 0))
                                                        <span class="text-danger">No schedule available</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if(session('userid'))
                                                <button class="btn btn-primary btn-book mt-3"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#bookConsult"
                                                    data-teacherid="{{ $key->teacherid }}"
                                                    data-teachername="{{ $key->name }}">
                                                    Book Consult
                                                </button>
                                            @else
                                                <span class="text-muted small mt-3">Login to book consultation</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                    @if(empty($teachers) || count($teachers) === 0)
                                        <div class="text-muted">No teachers available</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="bookConsult" tabindex="-1" role="dialog"aria-labelledby="scrollableModalTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-scrollable  modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="scrollableModalTitle">Book Consultation with <span id="modalTeacherName"></span></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"aria-label="Close"></button>
                            </div>
                            <form action="/book-consult" method="post">
                                @csrf
                                <input type="hidden" name="teacherid" id="modalTeacherId">
                                <div class="modal-body">
                                    <div class="form-group mb-3">
                                        <label>Problem</label>
                                        <textarea name="problem" class="form-control" rows="3" required></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Date</label>
                                        <input type="date" name="date" id="bookingDate" class="form-control" required min="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Available Time</label>
                                        <select name="slotid" id="bookingTime" class="form-control" required disabled>
                                            <option value="">Select date first</option>
                                        </select>
                                        <small id="timeHelp" class="form-text text-muted"></small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Submit Booking</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const bookButtons = document.querySelectorAll('.btn-book');
                    const modalTeacherName = document.getElementById('modalTeacherName');
                    const modalTeacherId = document.getElementById('modalTeacherId');
                    const bookingDate = document.getElementById('bookingDate');
                    const bookingTime = document.getElementById('bookingTime');
                    const timeHelp = document.getElementById('timeHelp');
                    bookButtons.forEach(button => {
                        button.addEventListener('click', function() {
                            const teacherId = this.getAttribute('data-teacherid');
                            const teacherName = this.getAttribute('data-teachername');
                            modalTeacherId.value = teacherId;
                            modalTeacherName.textContent = teacherName;
                            bookingDate.value = '';
                            bookingTime.innerHTML = '<option value=\"Select date first\">Select date first</option>';
                            bookingTime.disabled = true;
                            timeHelp.textContent = '';
                        });
                    });
                    bookingDate.addEventListener('change', function() {
                        const date = this.value;
                        const teacherId = modalTeacherId.value;
                        if (!date || !teacherId) return;
                        bookingTime.innerHTML = '<option value=\"Loading times...\">Loading times...</option>';
                        bookingTime.disabled = true;
                        fetch(`/get-available-times?teacherid=${teacherId}&date=${date}`)
                            .then(response => response.json())
                            .then(data => {
                                bookingTime.innerHTML = '';
                                if (!data.available) {
                                    bookingTime.innerHTML = `<option value=\"\">${data.message}</option>`;
                                    timeHelp.textContent = 'Teacher is not available on this day.';
                                    return;
                                }
                                if (data.slots.length === 0) {
                                    bookingTime.innerHTML = '<option value=\"\">No slots available</option>';
                                    timeHelp.textContent = 'All slots for this day are fully booked or outside working hours.';
                                    return;
                                }
                                bookingTime.innerHTML = '<option value=\"\">Choose a time slot</option>';
                                data.slots.forEach(slot => {
                                    const option = document.createElement('option');
                                    option.value = slot.slotid;
                                    option.textContent = `${slot.start.substring(0, 5)} - ${slot.end.substring(0, 5)}`;
                                    if (slot.is_booked) {
                                        option.disabled = true;
                                        option.textContent += ' (Already Booked)';
                                    } else if (slot.is_past) {
                                        option.disabled = true;
                                        option.textContent += ' (Time Passed)';
                                    }
                                    bookingTime.appendChild(option);
                                });
                                bookingTime.disabled = false;
                                timeHelp.textContent = 'Each session lasts for 30 minutes.';
                            })
                            .catch(() => {
                                bookingTime.innerHTML = '<option value=\"\">Error loading times</option>';
                            });
                    });
                });
                </script>


            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <footer class="footer text-center text-muted">
                All Rights Reserved by Freedash. Designed and Developed by <a
                    href="https://adminmart.com/">Adminmart</a>.
            </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
