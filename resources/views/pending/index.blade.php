@extends('layouts.zen')

@section('page_title', 'Pending Approvals')
@section('page_subtitle', 'Review and approve registration requests')

@section('content')
<div class="card-surface" style="padding: 0; overflow: hidden;">
    <div class="table-wrap" style="border: none;">
        <table class="table-app">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Campus</th>
                    <th>Course</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingUsers as $user)
                    <tr>
                        <td>{{ $user->fname }} {{ $user->lname }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge-app">{{ ucfirst($user->role ?? '—') }}</span></td>
                        <td>{{ $user->campus ?? '—' }}</td>
                        <td>{{ ($user->role ?? '') === 'student' ? ($user->course ?? '—') : '—' }}</td>
                        <td style="white-space: nowrap;">
                            <a href="{{ route('pending.approve', $user->id) }}" class="btn-app btn-success-app btn-sm-app">Approve</a>
                            <a href="{{ route('pending.reject', $user->id) }}" class="btn-app btn-danger-app btn-sm-app" onclick="return confirm('Reject this user?')">Reject</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 32px;">No pending registrations</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding: 16px 20px;">
        {{ $pendingUsers->links() }}
    </div>
</div>
@endsection
