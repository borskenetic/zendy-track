@extends('layouts.ot')
<link rel="stylesheet" href="{{ asset('public/css/students/students.css') }}">
@section('content')
<div class="container py-4">

    {{-- PROFILE --}}
    <div class="card mb-4">
        <div class="card-body d-flex align-items-center">
            <img src="{{ asset($student->profile_picture) }}"
                 class="rounded-circle me-4"
                 width="120" height="120">

            <div>
                <h4>{{ $student->firstname }} {{ $student->lastname }}</h4>
                <p class="mb-1">ID: {{ $student->id_number }}</p>
                <p class="mb-1">
                    {{ $program?->program_name ?? 'Program not set' }}
                </p>
                <p class="mb-1">
                    {{ $student->year }}
                </p>

                <button class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#editProfileModal">
                    Request Edit
                </button>
            </div>
        </div>
    </div>

    {{-- BORROWED BOOKS --}}
    <div class="card mb-4">
        <div class="card-header">Borrowed Books</div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Due Date</th>
                        <th>Fine</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($borrowedBooks as $log)
                        <tr>
                            <td>{{ $log->book->title_statement }}</td>
                            <td>{{ $log->due_date }}</td>
                            <td>₱{{ number_format($log->fine_incurred, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">
                                No borrowed books
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- FINES --}}
    <div class="alert alert-warning text-center">
        <strong>Total Outstanding Fine:</strong>
        ₱{{ number_format($totalFine, 2) }}
    </div>

</div>

@include('students.edit-modal')
@endsection
