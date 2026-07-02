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
                    <input type="text" id="campus" name="campus" value="{{ old('campus', $user->campus) }}" class="form-control-app" required>
                </div>
                <div class="form-group-app">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department" value="{{ old('department', $user->department) }}" class="form-control-app">
                </div>
            </div>

            <div class="form-group-app" id="courseField">
                <label for="course">Course</label>
                <input type="text" name="course" id="course" class="form-control-app" value="{{ old('course', $user->course) }}">
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
<script>
(function () {
    var roleSelect = document.getElementById('role');
    var courseField = document.getElementById('courseField');
    var courseInput = document.getElementById('course');

    function syncCourseField() {
        var isStudent = roleSelect.value === 'student';
        courseField.style.display = isStudent ? '' : 'none';
        courseInput.required = isStudent;
    }

    roleSelect.addEventListener('change', syncCourseField);
    syncCourseField();
})();
</script>
@endpush
