@extends('layouts.guest')

@section('title', 'Register')

@section('content')
<div class="logo-wrap">
    <img src="{{ \App\Support\Branding::logoUrl() }}" alt="{{ \App\Support\Branding::institutionName() }}">
</div>

<h1>Create account</h1>
<p class="subtitle">Register for Zendy access — pending admin approval</p>

@if(session('success'))
    <div class="alert-app alert-success-app">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert-app alert-danger-app">
        <ul style="margin: 0; padding-left: 18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="role-pills">
    <button type="button" class="role-pill active" id="btnStudent" data-role="student">Student</button>
    <button type="button" class="role-pill" id="btnFaculty" data-role="faculty">Faculty</button>
    <button type="button" class="role-pill" id="btnLibrarian" data-role="librarian">Librarian</button>
</div>

<form id="registrationForm" method="POST" action="{{ route('pending.store') }}">
    @csrf
    <input type="hidden" name="role" id="inputRole" value="student">

    <div class="form-group-app">
        <label for="campus">Campus</label>
        <select name="campus" id="campus" class="form-control-app" required>
            <option value="" disabled {{ old('campus') ? '' : 'selected' }}>Select campus</option>
            @foreach(\App\Models\User::campusOptions() as $campus)
                <option value="{{ $campus }}" {{ old('campus') === $campus ? 'selected' : '' }}>{{ $campus }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group-app" id="wrapYearLevel">
        <label for="grade">Year level</label>
        <select name="grade" id="grade" class="form-control-app">
            <option value="" disabled selected>Select year level</option>
        </select>
    </div>

    <div class="form-group-app" id="wrapCourse">
        <label for="course">Course / track</label>
        <select name="course" id="course" class="form-control-app">
            <option value="" disabled selected>Select course / track</option>
        </select>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
        <div class="form-group-app">
            <label>First name</label>
            <input type="text" name="firstname" value="{{ old('firstname') }}" required>
        </div>
        <div class="form-group-app">
            <label>Last name</label>
            <input type="text" name="lastname" value="{{ old('lastname') }}" required>
        </div>
    </div>

    <div class="form-group-app">
        <label>Email</label>
        <input type="email" name="email"
            value="{{ old('email') }}"
            placeholder="{{ \App\Support\InstitutionEmail::placeholder() }}"
            @if ($pattern = \App\Support\InstitutionEmail::htmlPattern())
                pattern="{{ $pattern }}"
                title="{{ \App\Support\InstitutionEmail::validationMessage() }}"
            @endif
            required>
    </div>

    <div class="form-group-app">
        <label>Password</label>
        <input type="password" name="password" required>
    </div>

    <button type="submit" class="btn-app btn-primary-app" style="width: 100%; padding: 14px; margin-top: 8px;">Submit registration</button>
</form>

<div class="guest-links">
    Already have an account? <a href="{{ route('login') }}">Sign in</a>
</div>
@endsection

@push('scripts')
@include('partials.affiliation-dropdowns-boot')
<script>
(function () {
    var roleInput = document.getElementById('inputRole');
    var campusSelect = document.getElementById('campus');
    var affiliation;

    function setRole(role) {
        roleInput.value = role;
        document.querySelectorAll('.role-pill').forEach(function (btn) {
            btn.classList.toggle('active', btn.getAttribute('data-role') === role);
        });
        if (affiliation) {
            affiliation.refresh();
        }
    }

    document.querySelectorAll('.role-pill').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setRole(btn.getAttribute('data-role'));
        });
    });

    if (typeof initAffiliationDropdowns !== 'function') {
        console.error('affiliation-dropdowns.js failed to load');
        return;
    }

    affiliation = initAffiliationDropdowns({
        catalog: window.AFFILIATION_CATALOG || {},
        studentOnlyIds: ['wrapYearLevel', 'wrapCourse'],
        isStudent: function () { return roleInput.value === 'student'; },
        initialCampus: @json(old('campus', '')),
        initialYear: @json(old('grade', '')),
        initialCourse: @json(old('course', '')),
    });

    setRole(@json(old('role', 'student')));

    // Ensure a pre-selected campus (or browser-restored value) still syncs options.
    if (campusSelect && campusSelect.value) {
        affiliation.refresh({
            year: @json(old('grade', '')),
            course: @json(old('course', '')),
        });
    }
})();
</script>
@endpush
