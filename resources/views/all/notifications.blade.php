<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Notifications</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="/home" class="text-muted">Home</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Notifications</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="d-flex gap-2 mb-3 align-items-center">
            <button id="btnEdit" class="btn btn-primary">Edit</button>
            <button id="btnDelete" class="btn btn-danger d-none" disabled>Delete Selected</button>
            <button id="btnReadAll" class="btn btn-secondary">Mark All Read</button>
            <div id="selectAllWrap" class="form-check d-none ms-3">
                <input type="checkbox" class="form-check-input" id="checkAll">
                <label class="form-check-label" for="checkAll">Select All</label>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 40px;" id="thCheck" class="d-none"></th>
                                <th>Title</th>
                                <th>Body</th>
                                <th>Time</th>
                                <th style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="notifBody">
                            @foreach($rows as $n)
                            <tr data-id="{{ $n->notificationid }}" data-read="{{ (int)$n->is_read }}">
                                <td class="d-none td-check"><input type="checkbox" class="row-check"></td>
                                <td>{{ $n->title }}</td>
                                <td>{{ $n->body }}</td>
                                <td>{{ date('d M Y H:i', strtotime($n->created_at)) }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-success btn-mark-read" {{ $n->is_read ? 'disabled' : '' }}>Mark Read</button>
                                </td>
                            </tr>
                            @endforeach
                            @if(count($rows) === 0)
                            <tr><td colspan="4" class="text-muted">No notifications</td></tr>
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
    var editMode = false;
    var btnEdit = document.getElementById('btnEdit');
    var btnDelete = document.getElementById('btnDelete');
    var thCheck = document.getElementById('thCheck');
    var checkAllWrap = document.getElementById('selectAllWrap');
    var checkAll = document.getElementById('checkAll');
    var btnReadAll = document.getElementById('btnReadAll');
    function updateDeleteEnabled(){
        var ids = [];
        var rows = document.querySelectorAll('#notifBody tr');
        for (var i=0;i<rows.length;i++){
            var cb = rows[i].querySelector('.row-check');
            if (cb && cb.checked) ids.push(rows[i].getAttribute('data-id'));
        }
        btnDelete.disabled = ids.length === 0;
    }
    btnEdit.addEventListener('click', function(){
        editMode = !editMode;
        var checks = document.querySelectorAll('.td-check');
        for (var i=0;i<checks.length;i++){
            checks[i].classList.toggle('d-none', !editMode);
        }
        thCheck.classList.toggle('d-none', !editMode);
        btnDelete.classList.toggle('d-none', !editMode);
        btnDelete.disabled = true;
        checkAllWrap.classList.toggle('d-none', !editMode);
        btnEdit.textContent = editMode ? 'Done' : 'Edit';
    });
    checkAll.addEventListener('change', function(){
        var rows = document.querySelectorAll('#notifBody tr .row-check');
        for (var i=0;i<rows.length;i++){ rows[i].checked = checkAll.checked; }
        updateDeleteEnabled();
    });
    document.getElementById('notifBody').addEventListener('change', function(e){
        if (e.target && e.target.classList.contains('row-check')) {
            updateDeleteEnabled();
        }
    });
    btnDelete.addEventListener('click', function(){
        var ids = [];
        var rows = document.querySelectorAll('#notifBody tr');
        for (var i=0;i<rows.length;i++){
            var cb = rows[i].querySelector('.row-check');
            if (cb && cb.checked) {
                ids.push(rows[i].getAttribute('data-id'));
            }
        }
        if (ids.length === 0) return;
        fetch('/notifications/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ ids: ids })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                // remove rows
                for (var i=0;i<ids.length;i++){
                    var tr = document.querySelector('#notifBody tr[data-id="'+ids[i]+'"]');
                    if (tr) tr.parentNode.removeChild(tr);
                }
                alert('Deleted '+ids.length+' notifications');
                updateDeleteEnabled();
            } else {
                alert(data.message || 'Failed to delete');
            }
        }).catch(() => alert('Network error'));
    });
    document.getElementById('notifBody').addEventListener('click', function(e){
        if (e.target && e.target.classList.contains('btn-mark-read')) {
            var tr = e.target.closest('tr');
            var id = tr.getAttribute('data-id');
            fetch('/notifications/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ id: id })
            }).then(r=>r.json()).then(data=>{
                if (data.success) {
                    e.target.disabled = true;
                    tr.setAttribute('data-read','1');
                } else {
                    alert(data.message || 'Failed to mark read');
                }
            }).catch(()=>alert('Network error'));
        }
    });
    btnReadAll.addEventListener('click', function(){
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }).then(r=>r.json()).then(data=>{
            if (data.success) {
                var buttons = document.querySelectorAll('.btn-mark-read');
                for (var i=0;i<buttons.length;i++){ buttons[i].disabled = true; }
                var trs = document.querySelectorAll('#notifBody tr');
                for (var j=0;j<trs.length;j++){ trs[j].setAttribute('data-read','1'); }
                alert('All notifications marked as read');
            } else {
                alert(data.message || 'Failed to mark all read');
            }
        }).catch(()=>alert('Network error'));
    });
})();
</script>
