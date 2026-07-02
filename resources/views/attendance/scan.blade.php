<!DOCTYPE html>
<html>
<head>
  <title>Library Attendance & Book RFID</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="{{ asset('css/attendance/scan.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    /* Footer marquee styling */
    .marquee-container {
      width: 100%;
      overflow: hidden;
      background-color: #222; /* dark footer */
      color: #fff;
      border-top: 2px solid #444;
      padding: 15px 0; /* slightly taller footer */
      box-sizing: border-box;
    }
    
    .marquee {
      display: inline-block;
      white-space: nowrap;
      padding-left: 100%; /* start offscreen */
      animation: scroll-text 15s linear infinite;
      font-family: 'Poppins', sans-serif;
      font-weight: 700;   /* bolder */
      font-size: 24px;    /* bigger font */
    }
    
    @keyframes scroll-text {
      0% { transform: translateX(0%); }
      100% { transform: translateX(-100%); }
    }
    </style>
</head>
<body>
  <header>
    <div class="header">
      <div class="logo-title">
        <img src="{{ asset('images/d.png') }}" alt="Logo">
        <div class="system-title">SMART DIGITAL LIBRARY</div>
        <a href="{{ route('book.index') }}" class="home-button">Home</a>
      </div>
    </div>
  </header>

  <div class="main">
    <div class="sidebar">
      <div class="date" id="currentDate">Date</div>
      <div class="time" id="currentTime">--:--:--</div>

      <div class="profile-pic">
        @if(isset($student) && $student->profile_picture)
          <img src="{{ asset($student->profile_picture) }}" alt="Profile">
        @else
          <img src="{{ asset('images/2x2_undifined_gender.jpg') }}" alt="Default Profile">
        @endif
      </div>

      <!-- ✅ Student log -->
      @if(isset($student))
        <div class="name-box">
          <div class="student-name">{{ $student->firstname }} {{ $student->lastname }}</div>
          <div class="label">Name</div>
          <div class="status-button {{ strtolower($status) === 'out' ? 'status-out' : '' }}">
            {{ $status }}
          </div>
          <div class="timestamp">
            {{ isset($log) ? \Carbon\Carbon::parse($log->scanned_at)->format('Y-m-d h:i:s A') : '' }}
          </div>
        </div>
      @endif

      <!-- ✅ Book check -->
      @if(isset($book))
        <div class="name-box">
          <div class="student-name">{{ $book->title_statement }}</div>
          <div class="label">Book Title</div>
          <div class="status-button {{ strtolower($bookStatus) === 'not checked out' ? 'status-out' : '' }}">
            {{ $bookStatus }}
          </div>
        </div>
      @endif

      <!-- ❌ Error -->
      @if(session('error'))
        <div class="name-box">
          <div class="student-name">{{ session('error') }}</div>
          <div class="label">Error</div>
        </div>
      @endif

    </div>

    <div class="right-content">
        <form id="scanForm">
            @csrf
            <input type="text" name="qrcode" id="qrcode" style="opacity:0; position:absolute;" autofocus>
        </form>

      <video muted autoplay loop controls class="ads-vid">
        <source src="{{ asset('videos/area51_product_slideshow.mp4') }}" type="video/mp4">
        Your browser does not support the video tag.
      </video>
    </div>
  </div>

    <!-- Footer with smooth scrolling marquee -->
    <footer>
      <div class="footer1">
        <div class="footer-logo">
          <div class="marquee-container">
            <div class="marquee">
              Welcome To Area 51 Information Technology Services
            </div>
          </div>
        </div>
      </div>
    </footer>


  <!-- ✅ Add alert sound -->
  <audio id="alertSound" src="{{ asset('sounds/alert.wav') }}" type="audio/wav"></audio>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('qrcode');
        const profileImg = document.querySelector('.profile-pic img');
        const sidebar = document.querySelector('.sidebar');
        const alertSound = document.getElementById('alertSound');
    
        let isCooldown = false;
        setInterval(() => input.focus(), 500);
        input.focus();
    
        function clearDisplay() {
            profileImg.src = "{{ asset('images/2x2_undifined_gender.jpg') }}";
            const boxes = document.querySelectorAll('.name-box');
            boxes.forEach(box => box.remove());
        }
    
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (isCooldown) return;
                isCooldown = true;
                setTimeout(() => isCooldown = false, 3000);
    
                const formData = new FormData();
                formData.append('qrcode', input.value);
                formData.append('_token', '{{ csrf_token() }}');
    
                fetch("{{ route('attendance.process') }}", {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    clearDisplay();
    
                    if (data.type === 'student') {
                        profileImg.src = "{{ asset('') }}" + data.student.profile_picture;
    
                        const div = document.createElement('div');
                        div.classList.add('name-box');
                        div.innerHTML = `
                            <div class="student-name">${data.student.firstname} ${data.student.lastname}</div>
                            <div class="label">Name</div>
                            <div class="status-button ${data.status.toLowerCase() === 'out' ? 'status-out' : ''}">${data.status}</div>
                            <div class="timestamp">${data.log.scanned_at}</div>
                        `;
                        sidebar.appendChild(div);
                    } 
                    else if (data.type === 'book') {
                        if (data.bookStatus.toLowerCase() === 'not checked out') alertSound.play();
    
                        const div = document.createElement('div');
                        div.classList.add('name-box');
                        div.innerHTML = `
                            <div class="student-name">${data.book.title_statement}</div>
                            <div class="label">Book Title</div>
                            <div class="status-button ${data.bookStatus.toLowerCase() === 'not checked out' ? 'status-out' : ''}">${data.bookStatus}</div>
                        `;
                        sidebar.appendChild(div);
                    }
                    else if (data.type === 'error') {
                        const div = document.createElement('div');
                        div.classList.add('name-box');
                        div.innerHTML = `
                            <div class="student-name">${data.message}</div>
                            <div class="label">Error</div>
                        `;
                        sidebar.appendChild(div);
                    }
    
                    input.value = '';
                    setTimeout(clearDisplay, 3000);
                })
                .catch(err => console.error(err));
            }
        });
    });
    
    document.addEventListener('DOMContentLoaded', function() {
        function updateDateTime() {
            const now = new Date();
            const options = { weekday: 'long', year:'numeric', month:'long', day:'numeric' };
    
            const dateEl = document.getElementById('currentDate');
            const timeEl = document.getElementById('currentTime');
    
            if (dateEl && timeEl) {
                dateEl.textContent = now.toLocaleDateString('en-GB', options);
                timeEl.textContent = now.toLocaleTimeString('en-US');
            }
        }
    
        updateDateTime();
        setInterval(updateDateTime, 1000);
    });
  </script>
</body>
</html>
