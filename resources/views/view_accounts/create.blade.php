@extends('layouts.zen')

@section('page_title', 'Create User')
@section('page_subtitle', 'Add a new portal account')

@section('content')
<div class="page-header-actions">
    <a href="{{ route('users.index') }}" class="btn-app btn-outline-app btn-sm-app">← Back to users</a>
</div>

<div class="form-page-grid">
    <div class="card-surface form-card">
        @if ($errors->any())
            <div class="alert-app alert-danger-app">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('users.store') }}" method="POST" id="createUserForm">
            @csrf

            <div class="form-section">
                <h3 class="form-section-title">Account details</h3>
                <div class="form-row-2">
                    <div class="form-group-app">
                        <label for="fname">First name</label>
                        <input type="text" id="fname" name="fname" value="{{ old('fname') }}" class="form-control-app" required>
                    </div>
                    <div class="form-group-app">
                        <label for="lname">Last name</label>
                        <input type="text" id="lname" name="lname" value="{{ old('lname') }}" class="form-control-app" required>
                    </div>
                </div>

                <div class="form-group-app">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control-app" placeholder="{{ \App\Support\InstitutionEmail::placeholder() }}"
                        @if ($pattern = \App\Support\InstitutionEmail::htmlPattern())
                            pattern="{{ $pattern }}"
                            title="{{ \App\Support\InstitutionEmail::validationMessage() }}"
                        @endif
                        required>
                    @if (\App\Support\InstitutionEmail::isEnforced())
                        <p class="form-hint">{{ \App\Support\InstitutionEmail::validationMessage() }}</p>
                    @endif
                </div>

                <div class="form-group-app">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control-app" minlength="6" required>
                    <p class="form-hint">At least 6 characters.</p>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Role & affiliation</h3>

                <div class="form-group-app">
                    <label for="role">Role</label>
                    <select id="role" name="role" class="form-control-app" required>
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}" {{ old('role', 'student') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row-2">
                    <div class="form-group-app">
                        <label for="campus">Campus</label>
                        <input type="text" id="campus" name="campus" value="{{ old('campus') }}" class="form-control-app" placeholder="e.g. Bay" required>
                    </div>
                    <div class="form-group-app">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" value="{{ old('department') }}" class="form-control-app" placeholder="Optional">
                    </div>
                </div>

                <div class="form-group-app" id="courseField">
                    <label for="course">Course</label>
                    <input type="text" id="course" name="course" value="{{ old('course') }}" class="form-control-app" placeholder="e.g. BSIT">
                    <p class="form-hint" id="courseHint">Required for students.</p>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('users.index') }}" class="btn-app btn-outline-app">Cancel</a>
                <button type="submit" class="btn-app btn-primary-app">Create user</button>
            </div>
        </form>
    </div>

    <aside class="card-surface form-aside">
        <h3 style="margin: 0 0 12px; font-size: 1rem;">Role guide</h3>
        <ul class="role-guide-list">
            <li><strong>Student</strong> — Can launch Zendy and view their own activity.</li>
            <li><strong>Faculty</strong> — Same portal access as students.</li>
            <li><strong>Librarian</strong> — Same portal access as students and faculty.</li>
            <li><strong>Administrator</strong> — Full access to users, reports, and all activity.</li>
        </ul>
        <p class="form-hint" style="margin-top: 16px;">Need many accounts? Use CSV import from the Users page.</p>
    </aside>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var roleSelect = document.getElementById('role');
    var courseField = document.getElementById('courseField');
    var courseInput = document.getElementById('course');
    var courseHint = document.getElementById('courseHint');

    function syncCourseField() {
        var isStudent = roleSelect.value === 'student';
        courseField.style.display = isStudent ? '' : 'none';
        courseInput.required = isStudent;
        courseHint.textContent = isStudent ? 'Required for students.' : 'Only used for students.';
    }

    roleSelect.addEventListener('change', syncCourseField);
    syncCourseField();
})();
</script>
@endpush
