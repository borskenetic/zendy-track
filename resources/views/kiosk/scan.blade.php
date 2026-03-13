<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student ID Scan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://unpkg.com/html5-qrcode"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container vh-100 d-flex flex-column justify-content-center align-items-center">

    <h3 class="mb-3">Scan or Enter Student ID</h3>

    {{-- MANUAL INPUT --}}
    <div class="mb-4 w-100" style="max-width: 400px;">
        <input type="text"
               id="manualInput"
               class="form-control form-control-lg text-center"
               placeholder="Enter / Scan QR Code"
               autofocus>
        <small class="text-muted d-block text-center mt-2">
            Press <b>Enter</b> to continue
        </small>
    </div>

    {{-- QR SCANNER --}}
    <div id="reader" style="width:300px;"></div>

</div>

<script>
    // Manual input handler
    document.getElementById('manualInput').addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && this.value.trim() !== '') {
            window.location.href = '/student/qr/' + this.value.trim();
        }
    });

    // QR Scanner
    const scanner = new Html5Qrcode("reader");

    scanner.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: 250 },
        qrCodeMessage => {
            window.location.href = '/student/qr/' + qrCodeMessage;
        },
        error => {}
    );
</script>

</body>
</html>
