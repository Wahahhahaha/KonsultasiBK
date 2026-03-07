<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Permission</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="/home" class="text-muted">Home</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Permission</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/permission/save">
                    @csrf
                    <input type="hidden" name="menus" value="{{ implode(',', $menus) }}">
                    <input type="hidden" name="subjects" value="{{ implode(',', $subjects) }}">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Role/Level</th>
                                    @foreach($menus as $m)
                                        <th>{{ ucfirst($m) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $map = [];
                                    foreach ($rows as $r) {
                                        $map[$r->subject][$r->menu_key] = (int)$r->allowed;
                                    }
                                @endphp
                                @foreach($subjects as $s)
                                    <tr>
                                        <td>
                                            @if($s === 'superadmin') Superadmin
                                            @elseif($s === 'admin') Admin
                                            @elseif($s === 'counselling_teacher') Counselling Teacher
                                            @elseif($s === 'homeroom_teacher') Homeroom Teacher
                                            @elseif($s === 'student') Student
                                            @else {{ $s }}
                                            @endif
                                        </td>
                                        @foreach($menus as $m)
                                            @php $checked = isset($map[$s][$m]) ? $map[$s][$m] : 0; @endphp
                                            <td class="text-center">
                                                <input type="checkbox" name="matrix[{{ $s }}][{{ $m }}]" {{ $checked ? 'checked' : '' }}>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
