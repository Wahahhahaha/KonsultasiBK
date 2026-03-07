<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Activity Logs</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="/home" class="text-muted">Home</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Activity Logs</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="mb-3 d-flex align-items-center gap-2">
            <div>
                <label class="form-label mb-0">Role</label>
                <?php $roleName = strtolower(str_replace(' ', '', session('role') ?? '')); ?>
                <select id="filterRole" class="form-select">
                    <option value="">All</option>
                    <option value="student">Student</option>
                    <option value="counsellingteacher">Counselling Teacher</option>
                    <option value="homeroomteacher">Homeroom Teacher</option>
                    <option value="admin">Admin</option>
                    <?php if ($roleName === 'superadmin') { ?>
                    <option value="superadmin">Superadmin</option>
                    <?php } ?>
                </select>
            </div>
            <div class="flex-grow-1">
                <label class="form-label mb-0">Search</label>
                <input id="filterSearch" type="text" class="form-control" placeholder="Search username, actor, action, details, IP">
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Username</th>
                                <th>Actor</th>
                                <th>Action</th>
                                <th>Detail</th>
                                <th>IP</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                            </tr>
                        </thead>
                        <tbody id="activityBody">
                            @foreach($logs as $log)
                            <tr>
                                <td>{{ $log->created_at }}</td>
                                <td>{{ $log->username }}</td>
                                <td>{{ $log->actor_label }}</td>
                                <td>{{ $log->action }}</td>
                                <td>{{ $log->details }}</td>
                                <td>{{ $log->ip_address }}</td>
                                <td>{{ $log->latitude }}</td>
                                <td>{{ $log->longitude }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function(){
    var filterRole = document.getElementById('filterRole');
    var filterSearch = document.getElementById('filterSearch');
    var debounceTimer = null;
    function renderRows(rows) {
        var body = document.getElementById('activityBody');
        body.innerHTML = '';
        if (!rows || rows.length === 0) {
            body.innerHTML = '<tr><td colspan="8" class="text-muted">No activities found</td></tr>';
            return;
        }
        for (var i=0;i<rows.length;i++) {
            var r = rows[i];
            var tr = document.createElement('tr');
            tr.innerHTML = ''
            + '<td>'+ (r.created_at || '-') +'</td>'
            + '<td>'+ (r.username || '-') +'</td>'
            + '<td>'+ (r.actor_label || '-') +'</td>'
            + '<td>'+ (r.action || '-') +'</td>'
            + '<td>'+ (r.details || '-') +'</td>'
            + '<td>'+ (r.ip_address || '-') +'</td>'
            + '<td>'+ (r.latitude == null ? '-' : r.latitude) +'</td>'
            + '<td>'+ (r.longitude == null ? '-' : r.longitude) +'</td>';
            body.appendChild(tr);
        }
    }
    function loadActivities() {
        var payload = {
            role: filterRole.value || null,
            q: filterSearch.value || ''
        };
        fetch('/activity-logs/list', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        }).then(r=>r.json()).then(data=>{
            if (data.success) renderRows(data.rows || []);
        }).catch(()=>{});
    }
    filterRole.addEventListener('change', loadActivities);
    filterSearch.addEventListener('input', function(){
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(loadActivities, 300);
    });
})();
</script>
