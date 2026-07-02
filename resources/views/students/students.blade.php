@extends('layouts.sec')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/students/students.css') }}">
@endsection

@section('content')

<script>
    const toggleBtn = document.getElementById('customMenuToggle');
    const closeBtn = document.getElementById('customMenuClose');
    const routeWrapper = document.getElementById('routeWrapper');

    toggleBtn.addEventListener('click', () => {
        routeWrapper.classList.add('open');
    });

    closeBtn.addEventListener('click', () => {
        routeWrapper.classList.remove('open');
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            routeWrapper.classList.remove('open');
        }
    });
</script>

<div class="container mt-5">

    <div class="card">

        <div class="card-header text-center">
            <h4>Registered Students</h4>
        </div>

        <div class="card-body">

            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif


            <!-- FILTER -->
            <form action="{{ route('students.index') }}" method="GET" class="row g-2 mb-4">

                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Search patrons..." value="{{ request('search') }}">
                </div>

                <div class="col-md-4">
                    <select name="program_id" class="form-select form-select-sm">

                        <option value="">All Courses</option>

                        @foreach ($programs as $program)
                        <option value="{{ $program->program_code }}" {{ request('program_id')==$program->program_code ?
                            'selected' : '' }}>
                            {{ $program->program_name }}
                        </option>
                        @endforeach

                    </select>
                </div>

                <div class="col-md-3">
                    <select name="year" class="form-select form-select-sm">

                        <option value="">All Years</option>
                        <option value="First Year" {{ request('year')=='First Year' ?'selected':'' }}>First Year
                        </option>
                        <option value="Second Year" {{ request('year')=='Second Year' ?'selected':'' }}>Second Year
                        </option>
                        <option value="Third Year" {{ request('year')=='Third Year' ?'selected':'' }}>Third Year
                        </option>
                        <option value="Fourth Year" {{ request('year')=='Fourth Year' ?'selected':'' }}>Fourth Year
                        </option>
                        <option value="Fifth Year" {{ request('year')=='Fifth Year' ?'selected':'' }}>Fifth Year
                        </option>

                    </select>
                </div>

                <div class="col-md-1">
                    <button class="btn btn-primary btn-sm w-100">
                        Filter
                    </button>
                </div>

            </form>


        <!-- IMPORT + REGISTER -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

    <div class="d-flex flex-column gap-2">

        <a href="{{ route('students.create') }}" class="btn btn-add">
            + Register Patron
        </a>

        <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data"
            class="d-flex gap-2 align-items-center">

            @csrf

            <input type="file" name="file" class="form-control form-control-sm" style="width:200px" required>

            <button class="btn btn-primary btn-sm">
                Import
            </button>

        </form>

    </div>


    <div class="d-flex flex-column gap-2">

        <a href="{{ route('pending.index') }}" class="btn btn-warning btn-sm">
            Pending Registrations
        </a>

        <a href="{{ route('students.pending.requests') }}" class="btn btn-warning btn-sm">
            Profile Edit Requests
        </a>

    </div>

</div>



            <!-- STUDENT / FACULTY -->
            <div class="mb-3 text-center">

                <a href="{{ route('students.index') }}" class="btn btn-outline-primary btn-sm active">
                    Students
                </a>

                <a href="{{ route('employees.index') }}" class="btn btn-outline-primary btn-sm" hidden>
                    Faculty
                </a>

            </div>



            <!-- TABLE -->
            <div class="table-responsive">

                <table class="table table-bordered table-hover text-center align-middle">

                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>QR Code</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Actions</th>
                            <th>Generate ID</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($students as $student)

                        <tr>

                            <td>
                                @if($student->profile_picture)
                                <img src="{{ asset($student->profile_picture) }}" class="profile-img">
                                @else
                                <span>No Image</span>
                                @endif
                            </td>

                            <td>{{ $student->lastname }}</td>
                            <td>{{ $student->firstname }}</td>
                            <td>{{ $student->qrcode }}</td>
                            <td>{{ $student->course }}</td>
                            <td>{{ $student->year }}</td>


                            <td>

                                <div class="dropdown">

                                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">

                                        Options

                                    </button>

                                    <ul class="dropdown-menu">

                                        <li>
                                            <a class="dropdown-item" href="{{ route('students.edit',$student->id) }}">
                                                Edit
                                            </a>
                                        </li>

                                        <li>

                                            <form action="{{ route('students.destroy',$student->id) }}" method="POST"
                                                onsubmit="return confirm('Are you sure?');">

                                                @csrf
                                                @method('DELETE')

                                                <button class="dropdown-item">
                                                    Delete
                                                </button>

                                            </form>

                                        </li>

                                    </ul>

                                </div>

                            </td>


                            <td>

                                <div class="dropdown">

                                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">

                                        Generate

                                    </button>

                                    <ul class="dropdown-menu">

                                        <li>
                                            <a class="dropdown-item" href="{{ url('idcard/front/'.$student->id) }}"
                                                target="_blank">
                                                Front
                                            </a>
                                        </li>

                                        <li>
                                            <a class="dropdown-item" href="{{ url('idcard/back/'.$student->id) }}"
                                                target="_blank">
                                                Back
                                            </a>
                                        </li>

                                        <li>
                                            <a class="dropdown-item" href="{{ url('idcard/download/'.$student->id) }}">
                                                Download ZIP
                                            </a>
                                        </li>

                                    </ul>

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>
                            <td colspan="8">
                                No students found.
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $students->withQueryString()->links('pagination::bootstrap-5') }}
            </div>



            <!-- EXPORT BUTTON -->
            <div class="text-end mt-3">

                <a href="{{ route('students.export') }}" class="btn btn-success btn-sm">
                    Export Students
                </a>

            </div>



            <a href="{{ route('books.index') }}" class="btn btn-back mt-3">
                ← Back to Books
            </a>


        </div>
    </div>
</div>

@endsection