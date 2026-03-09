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
                        @if(session('level') == 1)
                        <div class="d-flex justify-content-end mb-3">
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTeacherModal">Add Teacher</button>
                        </div>
                        @endif
                        
                        <!-- Filter & Search -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <input type="text" id="searchTeacher" class="form-control" placeholder="Search by name...">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <select id="filterGrade" class="form-control">
                                            <option value="">All Classes</option>
                                            @foreach($grades as $g)
                                                <option value="{{ $g->gradeid }}">{{ $g->gradename }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="teacher-container">
                            @include('student.teacher_data')
                        </div>
                        <div id="pagination-links" class="d-flex justify-content-center mt-3">
                            {{ $data->links('pagination::bootstrap-4') }}
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

@if(session('level') == 1)
<div class="modal fade" id="addTeacherModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Teacher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/teacher/add" method="post">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Phonenumber</label>
                        <input type="text" class="form-control" name="phonenumber" required>
                    </div>
                    <div class="small text-muted">
                        Default schedule will be created: Mon–Fri 08:00–15:00, Sat–Sun 08:00–12:00.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    let searchTimer = null;
    const searchInput = document.getElementById('searchTeacher');
    const filterSelect = document.getElementById('filterGrade');
    const container = document.getElementById('teacher-container');
    const paginationContainer = document.getElementById('pagination-links');

    function loadTeachers(page = 1) {
        const search = searchInput.value;
        const gradeid = filterSelect.value;
        
        // Add opacity to indicate loading
        container.style.opacity = '0.5';
        
        fetch(`/teacherlist?page=${page}&search=${encodeURIComponent(search)}&gradeid=${gradeid}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            container.innerHTML = data.html;
            paginationContainer.innerHTML = data.pagination;
            container.style.opacity = '1';
            attachBookEvents(); // Re-attach listeners to new buttons
        })
        .catch(err => {
            console.error(err);
            container.style.opacity = '1';
        });
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadTeachers(1), 500);
    });

    filterSelect.addEventListener('change', function() {
        loadTeachers(1);
    });

    // Handle Pagination Clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const href = e.target.closest('.pagination a').getAttribute('href');
            if (href) {
                const url = new URL(href);
                const page = url.searchParams.get('page');
                loadTeachers(page);
            }
        }
    });

    // Re-attach booking button events function
    function attachBookEvents() {
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
    }

    // Initial attach
    attachBookEvents();

    // Original Booking Logic
    const bookingDate = document.getElementById('bookingDate');
    const modalTeacherId = document.getElementById('modalTeacherId');
    const bookingTime = document.getElementById('bookingTime');
    const timeHelp = document.getElementById('timeHelp');

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
                    // Skip past slots completely
                    if (slot.is_past) {
                        return;
                    }

                    const option = document.createElement('option');
                    option.value = slot.slotid;
                    option.textContent = `${slot.start.substring(0, 5)} - ${slot.end.substring(0, 5)}`;
                    
                    if (slot.is_booked) {
                        option.disabled = true;
                        option.textContent += ' (Already Booked)';
                    }
                    
                    bookingTime.appendChild(option);
                });
                
                // If all slots were past and filtered out
                if (bookingTime.options.length === 1) {
                    bookingTime.innerHTML = '<option value="">No available slots (Time Passed)</option>';
                }
                
                bookingTime.disabled = false;
                timeHelp.textContent = 'Each session lasts for 30 minutes.';
            })
            .catch(error => {
                console.error('Error fetching times:', error);
                bookingTime.innerHTML = '<option value="">Error loading times</option>';
            });
    });
});
</script>
