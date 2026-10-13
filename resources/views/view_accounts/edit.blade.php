@extends('layouts.zen')

@section('page_title', 'Edit User')
@section('page_subtitle', 'Update account details and role')

@section('content')
<div class="page-header-actions">
    <a href="{{ route('users.index') }}" class="btn-app btn-outline-app btn-sm-app">← Back to users</a>
</div>

<div class="card-surface form-card" style="max-width: 720px;">
    @if ($errors->any())
        <div class="alert-app alert-danger-app">
            <ul style="margin: 0; padding-left: 18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.update', $user->id) }}" method="POST" id="editUserForm">
        @csrf
        @method('PUT')

        <div class="form-section">
            <h3 class="form-section-title">Account details</h3>
            <div class="form-row-2">
                <div class="form-group-app">
                    <label for="fname">First name</label>
                    <input type="text" name="fname" id="fname" class="form-control-app" value="{{ old('fname', $user->fname) }}" required>
                </div>
                <div class="form-group-app">
                    <label for="lname">Last name</label>
                    <input type="text" name="lname" id="lname" class="form-control-app" value="{{ old('lname', $user->lname) }}" required>
                </div>
            </div>

            <div class="form-group-app">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control-app" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="form-group-app">
                <label for="password">New password</label>
                <input type="password" name="password" id="password" class="form-control-app" minlength="6">
                <p class="form-hint">Leave blank to keep the current password.</p>
            </div>
        </div>

        <div class="form-section">
            <h3 class="form-section-title">Role & affiliation</h3>

            <div class="form-group-app">
                <label for="role">Role</label>
                <select id="role" name="role" class="form-control-app" required>
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" {{ old('role', $user->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-row-2">
                <div class="form-group-app">
                    <label for="campus">Campus</label>
                    @php $selectedCampus = old('campus', $user->campus); @endphp
                    <select id="campus" name="campus" class="form-control-app" required>
                        <option value="" disabled {{ $selectedCampus && in_array($selectedCampus, $campuses, true) ? '' : 'selected' }}>Select campus</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus }}" {{ $selectedCampus === $campus ? 'selected' : '' }}>{{ $campus }}</option>
                        @endforeach
                    </select>
                    @if($user->campus && ! in_array($user->campus, $campuses, true))
                        <p class="form-hint">Current value "{{ $user->campus }}" is not in the campus list — please choose a listed campus.</p>
                    @endif
                </div>
                <div class="form-group-app">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department" value="{{ old('department', $user->department) }}" class="form-control-app">
                </div>
            </div>

            <div class="form-group-app" id="yearLevelField">
                <label for="grade">Year level</label>
                <select id="grade" name="grade" class="form-control-app">
                    <option value="" disabled selected>Select year level</option>
                </select>
            </div>

            <div class="form-group-app" id="courseField">
                <label for="course">Course / track</label>
                <select id="course" name="course" class="form-control-app">
                    <option value="" disabled selected>Select course / track</option>
                </select>
                @if($user->course && ! in_array($user->course, \App\Models\User::courseOptions(), true))
                    <p class="form-hint">Current value "{{ $user->course }}" is a legacy course — consider updating to a listed option.</p>
                @endif
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('users.index') }}" class="btn-app btn-outline-app">Cancel</a>
            <button type="submit" class="btn-app btn-primary-app">Save changes</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@include('partials.affiliation-dropdowns-boot')
<script>
(function () {
    var roleSelect = document.getElementById('role');

    if (typeof initAffiliationDropdowns !== 'function') {
        console.error('affiliation-dropdowns.js failed to load');
        return;
    }

    initAffiliationDropdowns({
        catalog: window.AFFILIATION_CATALOG || {},
        roleSelectId: 'role',
        studentOnlyIds: ['yearLevelField', 'courseField'],
        isStudent: function () { return roleSelect.value === 'student'; },
        initialCampus: @json(old('campus', $user->campus)),
        initialYear: @json(old('grade', $user->grade)),
        initialCourse: @json(old('course', $user->course)),
    });
})();
</script>
@endpush
