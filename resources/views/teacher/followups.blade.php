<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Follow-ups</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="/home" class="text-muted">Home</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Follow-ups</li>
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
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <select id="filterClass" class="form-control">
                                    <option value="">All Classes</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->classid }}">{{ $c->gradename }} {{ $c->classname }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div id="followups-container">
                            @include('teacher.followups_data')
                        </div>
                        
                        <div id="pagination-links" class="d-flex justify-content-center mt-3">
                            {{ $items->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterClass = document.getElementById('filterClass');
    const container = document.getElementById('followups-container');
    const paginationLinks = document.getElementById('pagination-links');

    function loadFollowups(page = 1) {
        const classId = filterClass.value;
        const url = `/followups?page=${page}&classid=${classId}`;

        container.style.opacity = '0.5';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            container.innerHTML = data.html;
            paginationLinks.innerHTML = data.pagination;
            container.style.opacity = '1';
        })
        .catch(err => {
            console.error(err);
            container.style.opacity = '1';
        });
    }

    filterClass.addEventListener('change', function() {
        loadFollowups(1);
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const href = e.target.closest('.pagination a').getAttribute('href');
            if (href) {
                const url = new URL(href);
                const page = url.searchParams.get('page');
                loadFollowups(page);
            }
        }
    });
});
</script>
