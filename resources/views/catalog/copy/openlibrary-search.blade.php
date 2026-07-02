@extends('layouts.sec')
<link rel="stylesheet" href="{{ asset('css/books/index.css') }}">

@section('content')
<div class="container mt-5">
    <h2>Copy Cataloging (ISBN)</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('catalog.copy.openlibrary.search') }}">
        @csrf

        <div class="mb-3">
            <label>ISBN</label>
            <input type="text" name="isbn" class="form-control" placeholder="Enter ISBN" required>
        </div>

        <button class="btn btn-primary">Search</button>
    </form>
</div>
@endsection
