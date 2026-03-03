        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Counselling Teacher</h4>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="/home" class="text-muted">Home</a></li>
                                    <li class="breadcrumb-item text-muted active" aria-current="page">Counselling Teacher   </li>
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
                <!-- Row -->
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                        <?php foreach($data as $key) { ?>
                            <div class="col-lg-3 col-md-6">
                                <!-- Card -->
                                <div class="card">
                                    <img class="card-img-top img-fluid" src="../assets/images/big/img1.jpg"
                                        alt="Card image cap">
                                    <div class="card-body">
                                        <h4 class="card-title"><?= $key->name ?></h4>
                                        <div class="card-text mb-2">
                                            <span class="badge bg-light-primary text-primary">
                                                Class of <?= $key->gradename ?>
                                            </span>
                                        </div>
                                        <div class="card-text mb-3">
                                            <small class="text-muted font-weight-bold d-block mb-1">Schedule:</small>
                                            <div class="schedule-list">
                                                <?php if (count($key->schedules) > 0) { 
                                                    $weekdaySchedules = [];
                                                    $weekendSchedules = [];
                                                    
                                                    foreach ($key->schedules as $s) {
                                                        $day = strtolower($s->day_of_week);
                                                        $timeStr = substr($s->start_time, 0, 5) . ' - ' . substr($s->end_time, 0, 5);
                                                        
                                                        if (in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])) {
                                                            $weekdaySchedules[$timeStr][] = $day;
                                                        } else {
                                                            $weekendSchedules[$timeStr][] = $day;
                                                        }
                                                    }

                                                    // Tampilkan Weekdays
                                                    foreach ($weekdaySchedules as $time => $days) {
                                                        $dayLabel = (count($days) >= 5) ? 'Monday - Friday' : implode(', ', array_map('ucfirst', $days));
                                                        echo '<div class="d-flex justify-content-between small">
                                                                <span>'.$dayLabel.'</span>
                                                                <span class="text-dark font-weight-medium">'.$time.'</span>
                                                              </div>';
                                                    }

                                                    // Tampilkan Weekends
                                                    foreach ($weekendSchedules as $time => $days) {
                                                        $dayLabel = (count($days) >= 2) ? 'Saturday - Sunday' : implode(', ', array_map('ucfirst', $days));
                                                        echo '<div class="d-flex justify-content-between small">
                                                                <span>'.$dayLabel.'</span>
                                                                <span class="text-dark font-weight-medium">'.$time.'</span>
                                                              </div>';
                                                    }
                                                } else { ?>
                                                    <span class="text-danger small">No schedule available</span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary btn-book w-100" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#bookConsult"
                                            data-teacherid="<?= $key->teacherid ?>"
                                            data-teachername="<?= $key->name ?>">
                                            Book Consult
                                        </button>
                                    </div>
                                </div>
                            </div>
                  <?php } ?>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End PAge Content -->
                <!-- ============================================================== -->
            </div>
            <footer class="footer text-center">
                All Rights Reserved by Freedash. Designed and Developed by <a
                    href="https://wrappixel.com">WrapPixel</a>.
            </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
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
                    <input type="date" name="date" id="bookingDate" class="form-control" required min="<?= date('Y-m-d') ?>">
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
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
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
            
            // Reset modal state
            bookingDate.value = '';
            bookingTime.innerHTML = '<option value="">Select date first</option>';
            bookingTime.disabled = true;
            timeHelp.textContent = '';
        });
    });

    bookingDate.addEventListener('change', function() {
        const date = this.value;
        const teacherId = modalTeacherId.value;

        if (!date || !teacherId) return;

        bookingTime.innerHTML = '<option value="">Loading times...</option>';
        bookingTime.disabled = true;

        fetch(`/get-available-times?teacherid=${teacherId}&date=${date}`)
            .then(response => response.json())
            .then(data => {
                bookingTime.innerHTML = '';
                
                if (!data.available) {
                    bookingTime.innerHTML = `<option value="">${data.message}</option>`;
                    timeHelp.textContent = 'Teacher is not available on this day.';
                    return;
                }

                if (data.slots.length === 0) {
                    bookingTime.innerHTML = '<option value="">No slots available</option>';
                    timeHelp.textContent = 'All slots for this day are fully booked or outside working hours.';
                    return;
                }

                bookingTime.innerHTML = '<option value="">Choose a time slot</option>';
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
                timeHelp.textContent = 'Each session lasts for 1 hour.';
            })
            .catch(error => {
                console.error('Error fetching times:', error);
                bookingTime.innerHTML = '<option value="">Error loading times</option>';
            });
    });
});
</script>