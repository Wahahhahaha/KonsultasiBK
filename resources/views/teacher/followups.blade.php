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
                        <div class="table-responsive">
                            <table class="table border table-striped table-bordered text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Class</th>
                                        <th>Slot</th>
                                        <th>Counsel Teacher</th>
                                        <th>Outcome</th>
                                        <th>Summary</th>
                                        <th>Submitted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $it)
                                    <tr>
                                        <td>{{ $it->student_name }}<br><small class="text-muted">{{ $it->student_phone }}</small></td>
                                        <td>{{ $it->gradename }} {{ $it->classname }}<br><small class="text-muted">{{ $it->majorname ?? 'No Major' }}</small></td>
                                        <td>
                                            @if($it->date)
                                                {{ date('d M Y', strtotime($it->date)) }}<br>
                                                <small class="text-muted">{{ substr($it->start_time,0,5) }} - {{ substr($it->end_time,0,5) }}</small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $it->counselor_name ?? '-' }}</td>
                                        <td>{{ $it->report_outcome }}</td>
                                        <td>{{ $it->follow_up_notes }}</td>
                                        <td>{{ $it->report_submitted_at ? date('d M Y H:i', strtotime($it->report_submitted_at)) : '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if(count($items) === 0)
                                <div class="alert alert-info">No follow-ups assigned to you.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
