<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Class Data</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="/home" class="text-muted">Home</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Class Data</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Class List</h4>
                        <div class="mb-3 d-flex gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">
                            Add Class
                        </button>
                        <a href="/classdata/export" class="btn btn-success">Export</a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importClassModal">Import</button>
                        </div>
                        <div class="table-responsive">
                            <table id="class_table" class="table table-striped table-bordered no-wrap">
                                <thead>
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Grade</th>
                                        <th>Major</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importClassModal" tabindex="-1" role="dialog" aria-labelledby="importClassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importClassModalLabel">Import Classes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/classdata/import" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File (CSV, .xls, atau .xlsx)</label>
                        <input type="file" name="file" class="form-control" accept=".csv,.xls,.xlsx" required>
                        <div class="small text-muted mt-2">
                            Kolom: classid(optional), classname, gradename(optional), majorname(optional).
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
 </div>

<div class="modal fade" id="addClassModal" tabindex="-1" role="dialog" aria-labelledby="addClassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addClassModalLabel">Add New Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addClassForm">
                @csrf
                <div class="modal-body">
                    <div id="formAlert" class="alert alert-danger d-none"></div>
                    <div class="form-group mb-3">
                        <label for="classname">Class Name</label>
                        <input type="text" class="form-control" id="classname" name="classname" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="gradeid">Grade</label>
                        <select class="form-control" id="gradeid" name="gradeid" required>
                            <option value="">Select Grade</option>
                            @foreach($grades as $g)
                                <option value="{{ $g->gradeid }}">{{ $g->gradename }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="majorid">Major</label>
                        <select class="form-control" id="majorid" name="majorid" required>
                            <option value="">Select Major</option>
                            @foreach($majors as $m)
                                <option value="{{ $m->majorid }}">{{ $m->majorname }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveClassBtn">Save Class</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Class Modal -->
<div class="modal fade" id="editClassModal" tabindex="-1" role="dialog" aria-labelledby="editClassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editClassModalLabel">Edit Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editClassForm">
                @csrf
                <input type="hidden" id="edit_classid" name="classid">
                <div class="modal-body">
                    <div id="editFormAlert" class="alert alert-danger d-none"></div>
                    <div class="form-group mb-3">
                        <label for="edit_classname">Class Name</label>
                        <input type="text" class="form-control" id="edit_classname" name="classname" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_gradeid">Grade</label>
                        <select class="form-control" id="edit_gradeid" name="gradeid" required>
                            <option value="">Select Grade</option>
                            @foreach($grades as $g)
                                <option value="{{ $g->gradeid }}">{{ $g->gradename }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_majorid">Major</label>
                        <select class="form-control" id="edit_majorid" name="majorid" required>
                            <option value="">Select Major</option>
                            @foreach($majors as $m)
                                <option value="{{ $m->majorid }}">{{ $m->majorname }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger me-auto" id="deleteClassBtn">Delete Class</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="updateClassBtn">Update Class</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable with AJAX
    const table = $('#class_table').DataTable({
        ajax: {
            url: '/classdata',
            dataSrc: 'data'
        },
        columns: [
            { data: 'classname' },
            { data: 'gradename' },
            { data: 'majorname', defaultContent: '-' },
            { 
                data: null, 
                render: function(data, type, row) {
                    return `<button class="btn btn-info btn-sm btn-detail" 
                        data-id="${row.classid}" 
                        data-classname="${row.classname}" 
                        data-gradeid="${row.gradeid}" 
                        data-majorid="${row.majorid}">Detail</button>`;
                }
            }
        ]
    });

    // Add Class Submit
    document.getElementById('addClassForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const btn = document.getElementById('saveClassBtn');
        const alertBox = document.getElementById('formAlert');
        const formData = new FormData(form);
        
        btn.disabled = true;
        btn.innerHTML = 'Saving...';
        alertBox.classList.add('d-none');
        
        fetch('/classdata/add', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalEl = document.getElementById('addClassModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                form.reset();
                table.ajax.reload(null, false);
                alert('Class added successfully!');
            } else {
                alertBox.textContent = data.message || 'Error adding class';
                alertBox.classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alertBox.textContent = 'An error occurred. Please try again.';
            alertBox.classList.remove('d-none');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Save Class';
        });
    });

    // Open Edit Modal
    $('#class_table tbody').on('click', '.btn-detail', function() {
        const id = $(this).data('id');
        const classname = $(this).data('classname');
        const gradeid = $(this).data('gradeid');
        const majorid = $(this).data('majorid'); // Note: Make sure backend sends majorid in JSON

        document.getElementById('edit_classid').value = id;
        document.getElementById('edit_classname').value = classname;
        
        // Select grade
        const gradeSelect = document.getElementById('edit_gradeid');
        for(let i=0; i<gradeSelect.options.length; i++) {
            if(gradeSelect.options[i].text == $(this).closest('tr').find('td:eq(1)').text()) {
               // Logic ini agak risky kalau nama grade sama, better pake ID dari data attribute row
            }
        }
        // Use data attribute is safer
        // We need backend to send gradeid and majorid in JSON response!
        // Assuming backend sends: classid, classname, gradename, majorname, gradeid, majorid
        
        // Let's rely on data attributes set in render function
        // Need to update controller to select gradeid and majorid too
        
        $('#edit_gradeid').val(gradeid);
        $('#edit_majorid').val(majorid);

        const modal = new bootstrap.Modal(document.getElementById('editClassModal'));
        modal.show();
    });

    // Update Class Submit
    document.getElementById('editClassForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const btn = document.getElementById('updateClassBtn');
        const alertBox = document.getElementById('editFormAlert');
        const formData = new FormData(form);
        
        btn.disabled = true;
        btn.innerHTML = 'Updating...';
        alertBox.classList.add('d-none');
        
        fetch('/classdata/update', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalEl = document.getElementById('editClassModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                table.ajax.reload(null, false);
                alert('Class updated successfully!');
            } else {
                alertBox.textContent = data.message || 'Error updating class';
                alertBox.classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alertBox.textContent = 'An error occurred. Please try again.';
            alertBox.classList.remove('d-none');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Update Class';
        });
    });

    // Delete Class
    document.getElementById('deleteClassBtn').addEventListener('click', function() {
        if(!confirm('Are you sure you want to delete this class?')) return;

        const classid = document.getElementById('edit_classid').value;
        const btn = this;
        const formData = new FormData();
        formData.append('classid', classid);

        btn.disabled = true;
        btn.innerHTML = 'Deleting...';

        fetch('/classdata/delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalEl = document.getElementById('editClassModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                table.ajax.reload(null, false);
                alert('Class deleted successfully!');
            } else {
                alert(data.message || 'Error deleting class');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Delete Class';
        });
    });
});
</script>
