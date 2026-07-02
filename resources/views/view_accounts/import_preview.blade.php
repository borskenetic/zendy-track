@extends('layouts.zen')

@section('page_title', 'Import Preview')
@section('page_subtitle', 'Review users before confirming import')

@section('content')
<div class="card-surface" style="padding: 0; overflow: hidden;">
    <form action="{{ route('users.import.confirm') }}" method="POST">
        @csrf
        <div class="table-wrap" style="border: none;">
            <table class="table-app">
                <thead>
                    <tr>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Campus</th>
                        <th>Department</th>
                        <th>Course</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $index => $row)
                        <tr>
                            <td>{{ $row['lname'] }}</td>
                            <td>{{ $row['fname'] }}</td>
                            <td>{{ $row['email'] }}</td>
                            <td>{{ $row['role'] }}</td>
                            <td>{{ $row['campus'] }}</td>
                            <td>{{ $row['department'] ?: '—' }}</td>
                            <td>{{ $row['course'] ?: '—' }}</td>
                            <td>
                                @if($row['errors'])
                                    <span style="color: var(--danger); font-size: 0.82rem;">{{ implode(', ', $row['errors']) }}</span>
                                @else
                                    <span class="badge-app" style="background:#dcfce7;color:#166534;">Ready</span>
                                @endif
                            </td>
                        </tr>
                        <input type="hidden" name="data[{{ $index }}][lname]" value="{{ $row['lname'] }}">
                        <input type="hidden" name="data[{{ $index }}][fname]" value="{{ $row['fname'] }}">
                        <input type="hidden" name="data[{{ $index }}][email]" value="{{ $row['email'] }}">
                        <input type="hidden" name="data[{{ $index }}][role]" value="{{ $row['role'] }}">
                        <input type="hidden" name="data[{{ $index }}][campus]" value="{{ $row['campus'] }}">
                        <input type="hidden" name="data[{{ $index }}][department]" value="{{ $row['department'] }}">
                        <input type="hidden" name="data[{{ $index }}][course]" value="{{ $row['course'] }}">
                        <input type="hidden" name="data[{{ $index }}][password]" value="{{ $row['password'] }}">
                        <input type="hidden" name="data[{{ $index }}][errors]" value="{{ implode('|', $row['errors']) }}">
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding: 20px; display: flex; gap: 12px;">
            <button type="submit" class="btn-app btn-success-app">Confirm import</button>
            <a href="{{ route('users.index') }}" class="btn-app btn-outline-app">Cancel</a>
        </div>
    </form>
</div>
@endsection
