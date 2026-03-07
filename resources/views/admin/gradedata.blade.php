<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Grade Data</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="/home" class="text-muted">Home</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Grade Data</li>
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
                        <h4 class="card-title">Grade List</h4>
                        <div class="mb-3 d-flex gap-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGradeModal">
                                Add Grade
                            </button>
                            <a href="/gradedata/export" class="btn btn-success">Export</a>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importGradeModal">
                                Import
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table id="grade_table" class="table table-striped table-bordered no-wrap">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Grade Name</th>
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

<div class="modal fade" id="importGradeModal" tabindex="-1" role="dialog" aria-labelledby="importGradeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importGradeModalLabel">Import Grades</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/gradedata/import" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File (CSV, .xls, atau .xlsx)</label>
                        <input type="file" name="file" class="form-control" accept=".csv,.xls,.xlsx" required>
                        <div class="small text-muted mt-2">
                            Kolom: gradeid(optional), gradename.
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

<div class="modal fade" id="addGradeModal" tabindex="-1" role="dialog" aria-labelledby="addGradeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGradeModalLabel">Add New Grade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addGradeForm">
                <div class="modal-body">
                    <div id="formAlert" class="alert alert-danger d-none"></div>
                    <div class="form-group mb-3">
                        <label for="gradename">Grade Name</label>
                        <input type="text" class="form-control" id="gradename" name="gradename" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveGradeBtn">Save Grade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Grade Modal -->
<div class="modal fade" id="editGradeModal" tabindex="-1" role="dialog" aria-labelledby="editGradeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editGradeModalLabel">Edit Grade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editGradeForm">
                <input type="hidden" id="edit_gradeid" name="gradeid">
                <div class="modal-body">
                    <div id="editFormAlert" class="alert alert-danger d-none"></div>
                    <div class="form-group mb-3">
                        <label for="edit_gradename">Grade Name</label>
                        <input type="text" class="form-control" id="edit_gradename" name="gradename" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger me-auto" id="deleteGradeBtn">Delete Grade</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="updateGradeBtn">Update Grade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable with AJAX
    const table = $('#grade_table').DataTable({
        ajax: {
            url: '/gradedata',
            dataSrc: 'data'
        },
        columns: [
            { data: 'gradeid' },
            { data: 'gradename' },
            { 
                data: null, 
                render: function(data, type, row) {
                    return `<button class="btn btn-info btn-sm btn-detail" 
                        data-id="${row.gradeid}" 
                        data-gradename="${row.gradename}">Detail</button>`;
                }
            }
        ]
    });

    // Add Grade Submit
    document.getElementById('addGradeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const btn = document.getElementById('saveGradeBtn');
        const alertBox = document.getElementById('formAlert');
        const formData = new FormData(form);
        
        btn.disabled = true;
        btn.innerHTML = 'Saving...';
        alertBox.classList.add('d-none');
        
        fetch('/gradedata/add', {
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
                const modalEl = document.getElementById('addGradeModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                form.reset();
                table.ajax.reload(null, false);
                alert('Grade added successfully!');
            } else {
                alertBox.textContent = data.message || 'Error adding grade';
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
            btn.innerHTML = 'Save Grade';
        });
    });

    // Open Edit Modal
    $('#grade_table tbody').on('click', '.btn-detail', function() {
        const id = $(this).data('id');
        const gradename = $(this).data('gradename');

        document.getElementById('edit_gradeid').value = id;
        document.getElementById('edit_gradename').value = gradename;

        const modal = new bootstrap.Modal(document.getElementById('editGradeModal'));
        modal.show();
    });

    // Update Grade Submit
    document.getElementById('editGradeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const btn = document.getElementById('updateGradeBtn');
        const alertBox = document.getElementById('editFormAlert');
        const formData = new FormData(form);
        
        btn.disabled = true;
        btn.innerHTML = 'Updating...';
        alertBox.classList.add('d-none');
        
        fetch('/gradedata/update', {
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
                const modalEl = document.getElementById('editGradeModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                table.ajax.reload(null, false);
                alert('Grade updated successfully!');
            } else {
                alertBox.textContent = data.message || 'Error updating grade';
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
            btn.innerHTML = 'Update Grade';
        });
    });

    // Delete Grade
    document.getElementById('deleteGradeBtn').addEventListener('click', function() {
        if(!confirm('Are you sure you want to delete this grade?')) return;

        const gradeid = document.getElementById('edit_gradeid').value;
        const btn = this;
        const formData = new FormData();
        formData.append('gradeid', gradeid);

        btn.disabled = true;
        btn.innerHTML = 'Deleting...';

        fetch('/gradedata/delete', {
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
                const modalEl = document.getElementById('editGradeModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                table.ajax.reload(null, false);
                alert('Grade deleted successfully!');
            } else {
                alert(data.message || 'Error deleting grade');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Delete Grade';
        });
    });
});
</script>
