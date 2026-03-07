<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Trash</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="/home" class="text-muted">Home</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Trash</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="mb-3 d-flex align-items-center gap-2">
            <div>
                <label class="form-label mb-0">Action</label>
                <select id="filterAction" class="form-select">
                    <option value="">All</option>
                    <option value="update">Edit</option>
                    <option value="delete">Delete</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-0">Entity</label>
                <select id="filterEntity" class="form-select">
                    <option value="">All</option>
                    <option value="class">Class</option>
                    <option value="grade">Grade</option>
                    <option value="major">Major</option>
                    <option value="user">User</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-0">Role</label>
                <select id="filterRole" class="form-select">
                    <option value="">All</option>
                    <option value="student">Student</option>
                    <option value="counsellingteacher">Counselling Teacher</option>
                    <option value="homeroomteacher">Homeroom Teacher</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Superadmin</option>
                </select>
            </div>
            <div class="flex-grow-1">
                <label class="form-label mb-0">Search</label>
                <input id="filterSearch" type="text" class="form-control" placeholder="Search by actor, details, entity">
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Entity</th>
                                <th>Entity ID</th>
                                <th>Action</th>
                                <th>Actor</th>
                                <th>Level</th>
                                <th>IP</th>
                                <th>Details</th>
                                <th>Time</th>
                                <th>Restore</th>
                                <th>Delete Permanent</th>
                            </tr>
                        </thead>
                        <tbody id="trashBody">
                            @foreach($rows as $r)
                            <tr data-id="{{ $r->trashid }}">
                                <td>{{ $r->trashid }}</td>
                                <td>{{ $r->entity_type }}</td>
                                <td>{{ $r->entity_id }}</td>
                                <td>{{ $r->action }}</td>
                                <td>{{ $r->actor_username ?? '-' }} ({{ $r->actor_label ?? '-' }})</td>
                                <td>{{ $r->actor_level ?? '-' }}</td>
                                <td>{{ $r->ip_address ?? '-' }}</td>
                                <td>{{ $r->details ?? '-' }}</td>
                                <td>{{ date('d M Y H:i', strtotime($r->created_at)) }}</td>
                                <td><button class="btn btn-sm btn-success btn-restore">Restore</button></td>
                                <td><button class="btn btn-sm btn-danger btn-delete-permanent">Delete Permanent</button></td>
                            </tr>
                            @endforeach
                            @if(count($rows) === 0)
                            <tr><td colspan="6" class="text-muted">No items in trash</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function(){
    function renderRows(rows) {
        var body = document.getElementById('trashBody');
        body.innerHTML = '';
        if (!rows || rows.length === 0) {
            body.innerHTML = '<tr><td colspan="11" class="text-muted">No items found</td></tr>';
            return;
        }
        for (var i=0;i<rows.length;i++) {
            var r = rows[i];
            var tr = document.createElement('tr');
            tr.setAttribute('data-id', r.trashid);
            tr.innerHTML = ''
            + '<td>'+ r.trashid +'</td>'
            + '<td>'+ (r.entity_type || '-') +'</td>'
            + '<td>'+ (r.entity_id || '-') +'</td>'
            + '<td>'+ (r.action || '-') +'</td>'
            + '<td>'+ ((r.actor_username || '-') + ' (' + (r.actor_label || '-') + ')') +'</td>'
            + '<td>'+ (r.actor_level == null ? '-' : r.actor_level) +'</td>'
            + '<td>'+ (r.ip_address || '-') +'</td>'
            + '<td>'+ (r.details || '-') +'</td>'
            + '<td>'+ (r.created_at || '-') +'</td>'
            + '<td><button class="btn btn-sm btn-success btn-restore">Restore</button></td>'
            + '<td><button class="btn btn-sm btn-danger btn-delete-permanent">Delete Permanent</button></td>';
            body.appendChild(tr);
        }
    }
    var filterAction = document.getElementById('filterAction');
    var filterEntity = document.getElementById('filterEntity');
    var filterRole = document.getElementById('filterRole');
    var filterSearch = document.getElementById('filterSearch');
    var debounceTimer = null;
    function loadTrash() {
        var payload = {
            action: filterAction.value || null,
            entity: filterEntity.value || null,
            role: filterRole.value || null,
            q: filterSearch.value || ''
        };
        fetch('/trash/list', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        }).then(r=>r.json()).then(data=>{
            if (data.success) {
                renderRows(data.rows || []);
            }
        }).catch(()=>{});
    }
    filterAction.addEventListener('change', loadTrash);
    filterEntity.addEventListener('change', loadTrash);
    filterRole.addEventListener('change', loadTrash);
    filterSearch.addEventListener('input', function(){
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(loadTrash, 300);
    });
    // initial load
    loadTrash();
    document.getElementById('trashBody').addEventListener('click', function(e){
        if (e.target && e.target.classList.contains('btn-restore')) {
            var tr = e.target.closest('tr');
            var id = tr.getAttribute('data-id');
            fetch('/trash/restore', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ trashid: id })
            }).then(r=>r.json()).then(data=>{
                if (data.success) {
                    tr.parentNode.removeChild(tr);
                    alert('Restored and removed from trash');
                } else {
                    alert(data.message || 'Failed to restore');
                }
            }).catch(()=>alert('Network error'));
        }
        if (e.target && e.target.classList.contains('btn-delete-permanent')) {
            var tr = e.target.closest('tr');
            var id = tr.getAttribute('data-id');
            fetch('/trash/delete-permanent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ trashid: id })
            }).then(r=>r.json()).then(data=>{
                if (data.success) {
                    tr.parentNode.removeChild(tr);
                    alert('Deleted permanently');
                } else {
                    alert(data.message || 'Failed to delete permanently');
                }
            }).catch(()=>alert('Network error'));
        }
    });
})();
</script>
