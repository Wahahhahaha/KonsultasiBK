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
                
                <button type="button" class="btn btn-rounded btn-info" data-bs-toggle="modal"data-bs-target="#addUser">Add User</button>
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
                                                <td><?= $key->name ?></td>
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
                                                <td><a href="/userdata/delete/<?= $key->userid ?>" class="btn btn-danger">Delete User</a></td>
                                            </tr>
                                                <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

<div class="modal fade" id="addUser" tabindex="-1" role="dialog"aria-labelledby="scrollableModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable  modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scrollableModalTitle">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"aria-label="Close"></button>
            </div>
            <form action="/userdata/save" method="post">
                @csrf
            <div class="modal-body">
                <label>ID Number</label>
                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}">
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                
                <label>Name</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                
                <label>Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                
                <label>Phonenumber</label>
                <input type="text" name="phonenumber" class="form-control @error('phonenumber') is-invalid @enderror" value="{{ old('phonenumber') }}">
                @error('phonenumber')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                
                <label>Level</label>
                <select name="level" id="levelSelect" class="form-control @error('level') is-invalid @enderror">
                    <option value="" disabled selected>Choose Level</option>
                        <?php foreach ($level as $lvl) { ?>
                            <option value="<?= $lvl->levelid ?>" {{ old('level') == $lvl->levelid ? 'selected' : '' }}>
                                <?= $lvl->levelname ?>
                            </option>
                        <?php } ?>
                </select>
                @error('level')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                
                <div id="classWrapper" style="margin-top: 10px; display: none;">
                    <label>Class</label>
                    <select name="classid" id="classSelect" class="form-control @error('classid') is-invalid @enderror">
                        <option value="" disabled selected>Choose Class</option>
                        <?php foreach ($classes as $c) { ?>
                            <option value="<?= $c->classid ?>" {{ old('classid') == $c->classid ? 'selected' : '' }}>
                                <?= $c->gradename . ' ' . $c->classname . ' (' . ($c->majorname ?? 'No Major') . ')' ?>
                            </option>
                        <?php } ?>
                    </select>
                    @error('classid')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <label>Role</label>
                <select name="role" id="roleSelect" class="form-control @error('role') is-invalid @enderror" {{ old('level') == 3 ? 'disabled' : '' }}>
                    <option value="" disabled selected>Choose Role</option>
                        <?php foreach ($role as $r) { ?>
                            <option value="<?= $r->roleid ?>" {{ old('role') == $r->roleid ? 'selected' : '' }}>
                                <?= $r->rolename ?>
                            </option>
                        <?php } ?>
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
        </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<script>
    const levelSelect = document.getElementById('levelSelect');
    const roleSelect = document.getElementById('roleSelect');
    const classWrapper = document.getElementById('classWrapper');

    function updateRoleOptions() {
        const level = levelSelect.value;

        // reset semua option dulu
        for (let i = 0; i < roleSelect.options.length; i++) {
            roleSelect.options[i].disabled = false;
        }

        // kondisi berdasarkan level
        if (level == 1) {
            // disable role 3 & 4
            disableRole([3,4]);
            roleSelect.disabled = false;
            if (classWrapper) classWrapper.style.display = 'none';
        } 
        else if (level == 2) {
            // disable role 1 & 2
            disableRole([1,2]);
            roleSelect.disabled = false;
            if (classWrapper) classWrapper.style.display = 'none';
        } 
        else if (level == 3) {
            roleSelect.disabled = true;
            roleSelect.value = "";
            if (classWrapper) classWrapper.style.display = 'block';
        } 
        else {
            roleSelect.disabled = false;
            if (classWrapper) classWrapper.style.display = 'none';
        }
    }

    function disableRole(roleIds) {
        for (let i = 0; i < roleSelect.options.length; i++) {
            const val = parseInt(roleSelect.options[i].value);
            if (roleIds.includes(val)) {
                roleSelect.options[i].disabled = true;

                // kalau yang ke-select ternyata di-disable → reset
                if (roleSelect.value == val) {
                    roleSelect.value = "";
                }
            }
        }
    }

    levelSelect.addEventListener('change', updateRoleOptions);

    // jalan saat pertama load (old value)
    document.addEventListener('DOMContentLoaded', function() {
        updateRoleOptions();
        if (levelSelect.value == 3 && classWrapper) {
            classWrapper.style.display = 'block';
        }
    });

    // Auto open modal if validation error
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('addUser'));
            modal.show();
        });
    @endif
</script>
