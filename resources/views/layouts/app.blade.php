<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Pantas Library')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @stack('styles')
</head>

<body>

    {{-- NAVBAR --}}
    <div class="d-flex align-items-center px-4 py-2 flex-wrap" style="background-color: white; position: relative;">
        <img src="{{ asset('images/d.png') }}" alt="New Logo" class="header-logo-img" />
        <h1 class="school-name mb-0 ms-2"></h1>

        <button id="customMenuToggle" class="d-md-none toggle-btn">&#9776;</button>

        <div id="routeWrapper" class="d-flex gap-2 flex-wrap ms-auto responsive-nav">
            <button id="customMenuClose" class="d-md-none close-btn">&times;</button>

            <a class="btn0 btn-sm">Home</a>

            <div class="attendance_dropdown">
                <button class="attendance_dropdown-button">Attendance</button>
                <div class="attendance_dropdown-content">
                    <a href="{{ route('attendance.scan') }}">Attendance</a>
                    <a href="{{ route('attendance_logs.index') }}">Attendance-logs</a>
                    <a href="{{ route('attendance.changeVideo') }}">Change Video</a>
                </div>
            </div>

            <a href="{{ route('landing') }}" class="btn2 btn-sm">OPAC</a>

            <div class="logs_dropdown">
                <button class="logs_dropdown-button">Create Account</button>
                <div class="logs_dropdown-content">
                    <a href="{{ route('users.create') }}">Create Account</a>
                    <a href="{{ route('users.index') }}">View Users</a>
                </div>
            </div>

            <a href="{{ route('prospectus.index') }}" class="btn3 btn-sm">Prospectus Manager</a>

            <div class="logs_dropdown">
                <button class="logs_dropdown-button">Circulation</button>
                <div class="logs_dropdown-content">
                    <a href="{{ route('logs.index') }}">Circulation</a>
                    <a href="{{ route('book.report.download') }}">Download Book Report</a>
                    <a href="{{ route('students.report') }}">Student Report</a>
                </div>
            </div>

            <a href="{{ route('files.index') }}" class="btn4 btn-sm">Repository</a>

            <div class="logs_dropdown">
                <button class="logs_dropdown-button">Room Reservations</button>
                <div class="logs_dropdown-content">
                    <a href="{{ route('rooms.index') }}">Manage Rooms</a>
                    <a href="{{ route('rooms.book') }}">Book a Room</a>
                    <a href="{{ route('rooms.schedule') }}">View Schedule</a>
                    <a href="{{ route('rooms.pending') }}">Pending Reservations</a>
                    <a href="{{ route('rooms.logs') }}">Reservation Logs</a>
                    @can('isAdmin')
                    <a href="{{ route('feedback.index') }}">Show Feedback</a>
                    @endcan
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="mb-0">
                @csrf
                <button type="submit" class="btn5">Logout</button>
            </form>

        </div>
    </div>

    {{-- BANNER --}}
    <div class="head">
        <img src="{{ asset('images/Bannernew.jpg') }}" alt="Banner" class="banner-img">
    </div>

    {{-- MAIN CONTENT --}}
    <div class="container py-3">
        @yield('content')
    </div>

    {{-- FOOTER --}}
    <footer>
        <div class="a51-footer">
            <h4 style="color: white; font-size:15px">Pantas © 2025. All Rights Reserved.</h4>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Scripts --}}
    <script>
        const toggleBtn = document.getElementById('customMenuToggle');
        const closeBtn = document.getElementById('customMenuClose');
        const routeWrapper = document.getElementById('routeWrapper');

        toggleBtn.addEventListener('click', () => routeWrapper.classList.add('open'));
        closeBtn.addEventListener('click', () => routeWrapper.classList.remove('open'));

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) routeWrapper.classList.remove('open');
        });
    </script>

    @stack('scripts')
</body>
</html>
