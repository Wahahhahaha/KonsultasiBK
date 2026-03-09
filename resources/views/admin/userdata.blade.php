        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Userdata</h4>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="/home" class="text-muted">Home</a></li>
                                    <li class="breadcrumb-item text-muted active" aria-current="page">Userdata</li>
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
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Validation Error!</strong><br>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-rounded btn-info" data-bs-toggle="modal"data-bs-target="#addUser">Add User</button>
                    <a href="/userdata/export" class="btn btn-rounded btn-success">Export</a>
                    <button type="button" class="btn btn-rounded btn-primary" data-bs-toggle="modal" data-bs-target="#importUser">Import</button>
                </div>
                <br><br>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="multi_col_order"
                                        class="table border table-striped table-bordered text-nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phonenumber</th>
                                                <th>Level</th>
                                                <th>Role</th>
                                                <th>Action</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <?php foreach ($data as $key) { ?>
                                                <td><?= $key->username ?></td>
                                                <td><?= $key->name ?> 
                                                <?php if($key->levelid == 3) { ?>
                                                <?=$key->majorname?> <?=$key->gradename ?>-<?=$key->classname?>
                                                <?php } ?>
                                                </td>
                                                <td><?= $key->email ?></td>
                                                <td><?= $key->phonenumber ?></td>
                                                <td><?= $key->levelname ?></td>
                                                <td><?= $key->rolename ?></td>
                                                <td>
                                                <form action="/userdata/reset/{{ $key->userid }}" method="post">
                                                        @csrf
                                                    <button class="btn btn-primary">Reset Password</button>
                                                </form>
                                                </td>
                                                <td><a href="/userdata/delete/<?= $key->userid ?>" class="btn btn-danger btn-delete-user" data-id="<?= $key->userid ?>">Delete User</a></td>
                                            </tr>
                                                <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

<div class="modal fade" id="addUser" tabindex="-1" role="dialog" aria-labelledby="scrollableModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scrollableModalTitle">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addUserForm" action="/userdata/save" method="post">
                @csrf
                <div class="modal-body">
                    <div id="addUserError" class="alert alert-danger d-none"></div>
                    
                    <label>ID Number</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username') }}">
                    <div class="invalid-feedback" id="error-username"></div>
                    
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                    <div class="invalid-feedback" id="error-name"></div>
                    
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                    <div class="invalid-feedback" id="error-email"></div>
                    
                    <label>Phonenumber</label>
                    <input type="text" name="phonenumber" class="form-control" value="{{ old('phonenumber') }}">
                    <div class="invalid-feedback" id="error-phonenumber"></div>
                    
                    <label>Level</label>
                    <select name="level" id="levelSelect" class="form-control">
                        <option value="" disabled selected>Choose Level</option>
                        @foreach ($level as $lvl)
                            <option value="{{ $lvl->levelid }}" {{ old('level') == $lvl->levelid ? 'selected' : '' }}>
                                {{ $lvl->levelname }}
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback" id="error-level"></div>
                    
                    <div id="classWrapper" style="margin-top: 10px; display: none;">
                        <label>Class</label>
                        <select name="classid" id="classSelect" class="form-control">
                            <option value="" disabled selected>Choose Class</option>
                            <?php foreach ($classes as $c) { ?>
                                <option value="<?= $c->classid ?>" {{ old('classid') == $c->classid ? 'selected' : '' }}>
                                    <?= $c->gradename . ' ' . $c->classname . ' (' . ($c->majorname ?? 'No Major') . ')' ?>
                                </option>
                            <?php } ?>
                        </select>
                        <div class="invalid-feedback" id="error-classid"></div>
                    </div>

                    <div id="roleWrapper">
                        <label>Role</label>
                        <select name="role" id="roleSelect" class="form-control" {{ old('level') == 3 ? 'disabled' : '' }}>
                            <option value="" disabled selected>Choose Role</option>
                            <?php foreach ($role as $r) { ?>
                                <option value="<?= $r->roleid ?>" {{ old('role') == $r->roleid ? 'selected' : '' }}>
                                    <?= $r->rolename ?>
                                </option>
                            <?php } ?>
                        </select>
                        <div class="invalid-feedback" id="error-role"></div>
                    </div>

                    <div id="homeroomClassWrapper" style="margin-top: 10px; display: none;">
                        <label>Homeroom Class</label>
                        <select name="homeroom_classid" id="homeroomClassSelect" class="form-control">
                            <option value="" disabled selected>Choose Homeroom Class</option>
                            <?php foreach ($classes as $c) { ?>
                                <option value="<?= $c->classid ?>" {{ old('homeroom_classid') == $c->classid ? 'selected' : '' }}>
                                    <?= $c->gradename . ' ' . $c->classname . ' (' . ($c->majorname ?? 'No Major') . ')' ?>
                                </option>
                            <?php } ?>
                        </select>
                        <div class="invalid-feedback" id="error-homeroom_classid"></div>
                    </div>

                    <div id="gradeWrapper" style="margin-top: 10px; display: none;">
                        <label>Grade (For Counselling Teacher)</label>
                        <select name="counsel_gradeid" id="gradeSelect" class="form-control">
                            <option value="" disabled selected>Choose Grade</option>
                            <?php foreach ($grades as $g) { ?>
                                <option value="<?= $g->gradeid ?>" {{ old('counsel_gradeid') == $g->gradeid ? 'selected' : '' }}>
                                    <?= $g->gradename ?>
                                </option>
                            <?php } ?>
                        </select>
                        <div class="invalid-feedback" id="error-counsel_gradeid"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="btnSaveUser">Save changes</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<div class="modal fade" id="importUser" tabindex="-1" role="dialog" aria-labelledby="importUserTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importUserTitle">Import Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/userdata/import" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File (CSV, Excel .xls, atau .xlsx)</label>
                        <input type="file" name="file" class="form-control" accept=".csv,.xls,.xlsx" required>
                        <div class="small text-muted mt-2">
                            Kolom: userid(optional), username, name, password(optional), email, phonenumber, level, role(optional), class(optional), grade(optional), major(optional).
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

<script>
    const levelSelect = document.getElementById('levelSelect');
    const roleSelect = document.getElementById('roleSelect');
    const classWrapper = document.getElementById('classWrapper');
    const classSelect = document.getElementById('classSelect');
    const roleWrapper = document.getElementById('roleWrapper');
    const homeroomClassWrapper = document.getElementById('homeroomClassWrapper');
    const homeroomClassSelect = document.getElementById('homeroomClassSelect');
    const gradeWrapper = document.getElementById('gradeWrapper');
    const gradeSelect = document.getElementById('gradeSelect');

    function updateRoleOptions() {
        if (!levelSelect) return;
        
        const level = parseInt(levelSelect.value);
        
        // Reset role options state
        if (roleSelect) {
            for (let i = 0; i < roleSelect.options.length; i++) {
                roleSelect.options[i].disabled = false;
            }
        }

        if (level == 1) { // Employer
            disableRole([3,4]); // Disable Teacher roles
            if (roleSelect) roleSelect.disabled = false;
            if (roleWrapper) roleWrapper.style.display = 'block';
            if (classWrapper) classWrapper.style.display = 'none';
            if (homeroomClassWrapper) homeroomClassWrapper.style.display = 'none';
            if (gradeWrapper) gradeWrapper.style.display = 'none';
        } 
        else if (level == 2) { // Teacher
            disableRole([1,2]); // Disable Employer roles
            if (roleSelect) roleSelect.disabled = false;
            if (roleWrapper) roleWrapper.style.display = 'block';
            if (classWrapper) classWrapper.style.display = 'none';
            toggleRoleDependentFields(); // Check if homeroom/grade logic applies
        } 
        else if (level == 3) { // Student
            if (roleSelect) {
                roleSelect.disabled = true;
                roleSelect.value = "";
            }
            if (roleWrapper) roleWrapper.style.display = 'none';
            if (classWrapper) classWrapper.style.display = 'block';
            if (homeroomClassWrapper) homeroomClassWrapper.style.display = 'none';
            if (gradeWrapper) gradeWrapper.style.display = 'none';
        } 
        else {
            if (roleSelect) roleSelect.disabled = false;
            if (roleWrapper) roleWrapper.style.display = 'block';
            if (classWrapper) classWrapper.style.display = 'none';
            toggleRoleDependentFields();
        }
    }

    function disableRole(roleIds) {
        if (!roleSelect) return;
        for (let i = 0; i < roleSelect.options.length; i++) {
            const val = parseInt(roleSelect.options[i].value);
            if (roleIds.includes(val)) {
                roleSelect.options[i].disabled = true;

                if (roleSelect.value == val) {
                    roleSelect.value = "";
                }
            }
        }
    }

    function toggleRoleDependentFields() {
        if (!roleSelect) return;
        
        // Reset defaults
        if (homeroomClassWrapper) homeroomClassWrapper.style.display = 'none';
        if (homeroomClassSelect) {
            homeroomClassSelect.disabled = true;
            homeroomClassSelect.value = '';
        }
        if (gradeWrapper) gradeWrapper.style.display = 'none';
        if (gradeSelect) {
            gradeSelect.disabled = true;
            gradeSelect.value = '';
        }

        if (roleSelect.value == 4) { // Homeroom Teacher
            if (homeroomClassWrapper) homeroomClassWrapper.style.display = 'block';
            if (homeroomClassSelect) homeroomClassSelect.disabled = false;
        } else if (roleSelect.value == 3) { // Counselling Teacher
            if (gradeWrapper) gradeWrapper.style.display = 'block';
            if (gradeSelect) gradeSelect.disabled = false;
        }
    }

    if (levelSelect) {
        levelSelect.addEventListener('change', updateRoleOptions);
    }
    
    if (roleSelect) {
        roleSelect.addEventListener('change', toggleRoleDependentFields);
    }

    // jalan saat pertama load (old value)
    document.addEventListener('DOMContentLoaded', function() {
        if (levelSelect) {
            updateRoleOptions();
        }
    });

    // Auto open modal if validation error
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('addUser'));
            modal.show();
        });
    @endif

    // Handle AJAX form submission for adding user
     document.addEventListener('DOMContentLoaded', function() {
         // AJAX DELETE using Event Delegation
         document.body.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-delete-user');
            if (!btn) return;
            
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this user?')) return;
            
            const url = btn.getAttribute('href');
            const originalText = btn.textContent;
            
            btn.textContent = 'Deleting...';
            btn.classList.add('disabled');
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json();
                } else {
                    return { success: true };
                }
            })
            .then(data => {
                if (data.success) {
                    let successAlert = document.getElementById('pageSuccessAlert');
                    if (!successAlert) {
                        successAlert = document.createElement('div');
                        successAlert.id = 'pageSuccessAlert';
                        successAlert.className = 'alert alert-success alert-dismissible fade show';
                        successAlert.role = 'alert';
                        successAlert.innerHTML = `
                            <span id="pageSuccessMessage"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;
                        const tableCard = document.querySelector('.card');
                        tableCard.parentNode.insertBefore(successAlert, tableCard);
                    }
                    document.getElementById('pageSuccessMessage').textContent = data.message || 'User deleted successfully';
                    successAlert.style.display = 'block';
                    
                    const row = btn.closest('tr');
                    if (row) row.remove();
                } else {
                    alert('Failed to delete user');
                    btn.textContent = originalText;
                    btn.classList.remove('disabled');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error deleting user');
                btn.textContent = originalText;
                btn.classList.remove('disabled');
            });
         });
 
         const addUserForm = document.getElementById('addUserForm');
        if (addUserForm) {
            addUserForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Clear previous errors
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
                document.getElementById('addUserError').classList.add('d-none');
                
                const btn = document.getElementById('btnSaveUser');
                const originalBtnText = btn.textContent;
                btn.disabled = true;
                btn.textContent = 'Saving...';
                
                const formData = new FormData(this);
                
                fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    btn.disabled = false;
                    btn.textContent = originalBtnText;
                    
                    if (data.success) {
                        // Close modal without reload
                        const modalEl = document.getElementById('addUser');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        modal.hide();
                        
                        // Show success message (using SweetAlert or Toast would be better, but standard alert is OK)
                        // alert(data.message || 'User added successfully'); 
                        // User complained about alert causing refresh or annoyance.
                        // Let's use a temporary alert on page instead of blocking alert()
                        
                        // Create or reuse success alert container
                        let successAlert = document.getElementById('pageSuccessAlert');
                        if (!successAlert) {
                            successAlert = document.createElement('div');
                            successAlert.id = 'pageSuccessAlert';
                            successAlert.className = 'alert alert-success alert-dismissible fade show';
                            successAlert.role = 'alert';
                            successAlert.innerHTML = `
                                <span id="pageSuccessMessage"></span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            `;
                            // Insert before table
                            const tableCard = document.querySelector('.card');
                            tableCard.parentNode.insertBefore(successAlert, tableCard);
                        }
                        document.getElementById('pageSuccessMessage').textContent = data.message || 'User added successfully';
                        successAlert.style.display = 'block';

                        // Reset form
                        document.getElementById('addUserForm').reset();
                        
                        // NO RELOAD
                        // Optionally, if you want to show the new user in the table without reload,
                        // you would need to append a row to the table here using JavaScript.
                        // However, user specifically asked to NOT refresh.
                        // So we just show success message and stop.
                        
                        // setTimeout(() => {
                        //      location.reload();
                        // }, 1000); 
                    } else {
                        // Handle errors structure
                        const errors = data.errors || {};
                        const message = data.message;
                        
                        if (Object.keys(errors).length > 0) {
                             Object.keys(errors).forEach(field => {
                                 let inputName = field;
                                 const errorDiv = document.getElementById('error-' + inputName);
                                 const input = document.querySelector(`[name="${inputName}"]`);
                                 
                                 if (input) {
                                     input.classList.add('is-invalid');
                                 }
                                 if (errorDiv) {
                                     errorDiv.textContent = errors[field][0];
                                     errorDiv.style.display = 'block'; // Ensure visibility
                                 }
                             });
                        } else {
                             const errorAlert = document.getElementById('addUserError');
                             errorAlert.textContent = message || 'An error occurred';
                             errorAlert.classList.remove('d-none');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.disabled = false;
                    btn.textContent = originalBtnText;
                    const errorAlert = document.getElementById('addUserError');
                    errorAlert.textContent = 'An unexpected error occurred. Please try again.';
                    errorAlert.classList.remove('d-none');
                });
            });
        }
    });
</script>
