@extends('layouts.zen')

@section('page_title', 'Pending Approvals')
@section('page_subtitle', 'Review and approve registration requests')

@section('content')
<form id="bulkPendingForm" method="POST" action="{{ route('pending.bulk-approve') }}">
    @csrf
</form>

<div class="card-surface" style="padding: 0; overflow: hidden;">
    <div class="bulk-bar">
        <button type="submit" class="btn-app btn-success-app btn-sm-app" id="bulkPendingButton" form="bulkPendingForm" disabled>Approve selected</button>
        <span id="bulkPendingCount" class="bulk-count">None selected</span>
    </div>
    <div class="table-wrap" style="border: none;">
        <table class="table-app">
            <thead>
                <tr>
                    <th style="width: 42px;">
                        <input type="checkbox" id="selectAllPending" class="row-check" aria-label="Select all registrations on this page">
                    </th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Campus</th>
                    <th>Year level</th>
                    <th>Course / track</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingUsers as $user)
                    <tr>
                        <td>
                            <input type="checkbox" class="row-check row-select" name="ids[]" value="{{ $user->id }}" form="bulkPendingForm" aria-label="Select {{ $user->fname }} {{ $user->lname }}">
                        </td>
                        <td>{{ $user->fname }} {{ $user->lname }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge-app">{{ ucfirst($user->role ?? '—') }}</span></td>
                        <td>{{ $user->campus ?? '—' }}</td>
                        <td>{{ ($user->role ?? '') === 'student' ? ($user->grade ?: '—') : '—' }}</td>
                        <td>{{ ($user->role ?? '') === 'student' ? ($user->course ?: '—') : '—' }}</td>
                        <td style="white-space: nowrap;">
                            <a href="{{ route('pending.approve', $user->id) }}" class="btn-app btn-success-app btn-sm-app">Approve</a>
                            <a href="{{ route('pending.reject', $user->id) }}" class="btn-app btn-danger-app btn-sm-app" onclick="return confirm('Reject this user?')">Reject</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 32px;">No pending registrations</td>
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

@push('scripts')
<script src="{{ asset('js/bulk-select.js') }}?v=1"></script>
<script>
initBulkSelect({
    formId: 'bulkPendingForm',
    selectAllId: 'selectAllPending',
    countId: 'bulkPendingCount',
    buttonId: 'bulkPendingButton',
    confirmMessage: (count) => 'Approve ' + count + ' selected registration' + (count === 1 ? '' : 's') + '?',
});
</script>
@endpush
