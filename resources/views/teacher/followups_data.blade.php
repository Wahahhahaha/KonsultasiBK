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
        <div class="alert alert-info">No follow-ups found.</div>
    @endif
</div>