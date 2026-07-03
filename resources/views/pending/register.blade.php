@extends('layouts.guest')

@section('title', 'Register')

@section('content')
<div class="logo-wrap">
    <img src="{{ asset('images/d.png') }}" alt="JIB Logo">
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
        <input type="text" name="campus" id="campus" class="form-control-app" value="{{ old('campus') }}" placeholder="e.g. Bay" required>
    </div>

    <div class="form-group-app" id="wrapCourse">
        <label for="course">Course</label>
        <input type="text" name="course" id="course" class="form-control-app" value="{{ old('course') }}" placeholder="e.g. BSIT">
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
<script>
(function () {
    var roleInput = document.getElementById('inputRole');
    var wrapCourse = document.getElementById('wrapCourse');
    var courseInput = document.getElementById('course');

    function setRole(role) {
        roleInput.value = role;
        document.querySelectorAll('.role-pill').forEach(function (btn) {
            btn.classList.toggle('active', btn.getAttribute('data-role') === role);
        });

        if (role === 'student') {
            wrapCourse.classList.remove('hidden');
            courseInput.required = true;
        } else {
            wrapCourse.classList.add('hidden');
            courseInput.required = false;
            courseInput.value = '';
        }
    }

    document.querySelectorAll('.role-pill').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setRole(btn.getAttribute('data-role'));
        });
    });

    setRole('{{ old('role', 'student') }}');
})();
</script>
@endpush
