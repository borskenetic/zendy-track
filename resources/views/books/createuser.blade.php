@extends('layouts.zen')

@section('page_title', 'Create User')
@section('page_subtitle', 'Add a new account manually')

@section('content')
<div class="card-surface" style="max-width: 520px;">
    @if ($errors->any())
        <div class="alert-app alert-danger-app">
            <ul style="margin: 0; padding-left: 18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <div class="form-group-app">
            <label for="lname">Last Name</label>
            <input type="text" id="lname" name="lname" value="{{ old('lname') }}" class="form-control-app" required>
        </div>

        <div class="form-group-app">
            <label for="fname">First Name</label>
            <input type="text" id="fname" name="fname" value="{{ old('fname') }}" class="form-control-app" required>
        </div>

        <div class="form-group-app">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control-app" required>
        </div>

        <div class="form-group-app">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control-app" required>
        </div>

        <button type="submit" class="btn-app btn-primary-app" style="width: 100%; margin-top: 8px;">Create user</button>
    </form>
</div>
@endsection
