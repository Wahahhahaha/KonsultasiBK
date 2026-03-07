<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Major Data</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="/home" class="text-muted">Home</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Major Data</li>
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
                        <h4 class="card-title">Major List</h4>
                        <div class="mb-3 d-flex gap-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMajorModal">
                                Add Major
                            </button>
                            <a href="/majordata/export" class="btn btn-success">Export</a>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importMajorModal">
                                Import
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table id="major_table" class="table table-striped table-bordered no-wrap">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Major Name</th>
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

<div class="modal fade" id="importMajorModal" tabindex="-1" role="dialog" aria-labelledby="importMajorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importMajorModalLabel">Import Majors</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/majordata/import" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File (CSV, .xls, atau .xlsx)</label>
                        <input type="file" name="file" class="form-control" accept=".csv,.xls,.xlsx" required>
                        <div class="small text-muted mt-2">
                            Kolom: majorid(optional), majorname.
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

<div class="modal fade" id="addMajorModal" tabindex="-1" role="dialog" aria-labelledby="addMajorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMajorModalLabel">Add New Major</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addMajorForm">
                <div class="modal-body">
                    <div id="formAlert" class="alert alert-danger d-none"></div>
                    <div class="form-group mb-3">
                        <label for="majorname">Major Name</label>
                        <input type="text" class="form-control" id="majorname" name="majorname" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveMajorBtn">Save Major</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Major Modal -->
<div class="modal fade" id="editMajorModal" tabindex="-1" role="dialog" aria-labelledby="editMajorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMajorModalLabel">Edit Major</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMajorForm">
                <input type="hidden" id="edit_majorid" name="majorid">
                <div class="modal-body">
                    <div id="editFormAlert" class="alert alert-danger d-none"></div>
                    <div class="form-group mb-3">
                        <label for="edit_majorname">Major Name</label>
                        <input type="text" class="form-control" id="edit_majorname" name="majorname" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger me-auto" id="deleteMajorBtn">Delete Major</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="updateMajorBtn">Update Major</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable with AJAX
    const table = $('#major_table').DataTable({
        ajax: {
            url: '/majordata',
            dataSrc: 'data'
        },
        columns: [
            { data: 'majorid' },
            { data: 'majorname' },
            { 
                data: null, 
                render: function(data, type, row) {
                    return `<button class="btn btn-info btn-sm btn-detail" 
                        data-id="${row.majorid}" 
                        data-majorname="${row.majorname}">Detail</button>`;
                }
            }
        ]
    });

    // Add Major Submit
    document.getElementById('addMajorForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const btn = document.getElementById('saveMajorBtn');
        const alertBox = document.getElementById('formAlert');
        const formData = new FormData(form);
        
        btn.disabled = true;
        btn.innerHTML = 'Saving...';
        alertBox.classList.add('d-none');
        
        fetch('/majordata/add', {
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
                const modalEl = document.getElementById('addMajorModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                form.reset();
                table.ajax.reload(null, false);
                alert('Major added successfully!');
            } else {
                alertBox.textContent = data.message || 'Error adding major';
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
            btn.innerHTML = 'Save Major';
        });
    });

    // Open Edit Modal
    $('#major_table tbody').on('click', '.btn-detail', function() {
        const id = $(this).data('id');
        const majorname = $(this).data('majorname');

        document.getElementById('edit_majorid').value = id;
        document.getElementById('edit_majorname').value = majorname;

        const modal = new bootstrap.Modal(document.getElementById('editMajorModal'));
        modal.show();
    });

    // Update Major Submit
    document.getElementById('editMajorForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const btn = document.getElementById('updateMajorBtn');
        const alertBox = document.getElementById('editFormAlert');
        const formData = new FormData(form);
        
        btn.disabled = true;
        btn.innerHTML = 'Updating...';
        alertBox.classList.add('d-none');
        
        fetch('/majordata/update', {
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
                const modalEl = document.getElementById('editMajorModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                table.ajax.reload(null, false);
                alert('Major updated successfully!');
            } else {
                alertBox.textContent = data.message || 'Error updating major';
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
            btn.innerHTML = 'Update Major';
        });
    });

    // Delete Major
    document.getElementById('deleteMajorBtn').addEventListener('click', function() {
        if(!confirm('Are you sure you want to delete this major?')) return;

        const majorid = document.getElementById('edit_majorid').value;
        const btn = this;
        const formData = new FormData();
        formData.append('majorid', majorid);

        btn.disabled = true;
        btn.innerHTML = 'Deleting...';

        fetch('/majordata/delete', {
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
                const modalEl = document.getElementById('editMajorModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                table.ajax.reload(null, false);
                alert('Major deleted successfully!');
            } else {
                alert(data.message || 'Error deleting major');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Delete Major';
        });
    });
});
</script>
