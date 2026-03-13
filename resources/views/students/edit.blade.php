<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        canvas {
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .btn-save {
            background-color: #007bff;
            color: white;
        }

        .btn-save:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

    <div class="container mt-5 mb-5">
        <div class="card">
            <div class="card-header text-center">
                <h4>Edit Student Information</h4>
            </div>

            <div class="card-body">

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form id="studentForm" method="POST" action="{{ route('students.update', $student->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <h5 class="mb-3">Student Information</h5>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <input type="text" name="id_number" class="form-control" placeholder="ID Number"
                                value="{{ old('id_number', $student->id_number) }}" required>
                        </div>

                        <div class="col-md-6">
                            <input type="date" name="birthday" class="form-control"
                                value="{{ old('birthday', $student->birthday) }}">
                        </div>

                        <div class="col-md-6">
                            <input type="text" name="firstname" class="form-control" placeholder="First Name"
                                value="{{ old('firstname', $student->firstname) }}" required>
                        </div>

                        <div class="col-md-6">
                            <input type="text" name="lastname" class="form-control" placeholder="Last Name"
                                value="{{ old('lastname', $student->lastname) }}" required>
                        </div>

                        <div class="col-md-6">
                            <input type="text" name="middle_initial" class="form-control" placeholder="Middle Initial"
                                value="{{ old('middle_initial', $student->middle_initial) }}">
                        </div>

                        <!-- QR CODE (READ ONLY) -->
                        <div class="col-md-6">
                            <input type="text" class="form-control" value="{{ $student->qrcode }}" readonly>
                        </div>

                        <!-- COURSE (DROPDOWN - RECOMMENDED) -->
                        <div class="col-md-6">
                            <input type="text" name="course" class="form-control" placeholder="Course"
                                value="{{ old('course', $student->course) }}">
                        </div>

                        <div class="col-md-6">
                            <select name="year" class="form-select" required>
                                <option value="">Select Year</option>
                                <option value="First Year" {{ old('year', $student->year) == 'First Year' ? 'selected' :
                                    '' }}>First Year</option>
                                <option value="Second Year" {{ old('year', $student->year) == 'Second Year' ? 'selected'
                                    : '' }}>Second Year</option>
                                <option value="Third Year" {{ old('year', $student->year) == 'Third Year' ? 'selected' :
                                    '' }}>Third Year</option>
                                <option value="Fourth Year" {{ old('year', $student->year) == 'Fourth Year' ? 'selected'
                                    : '' }}>Fourth Year</option>
                                <option value="Fifth Year" {{ old('year', $student->year) == 'Fifth Year' ? 'selected' :
                                    '' }}>Fifth Year</option>
                            </select>
                        </div>

                        <!-- NEW FIELDS -->

                        <div class="col-md-6">
                            <input type="text" name="mobile_number" class="form-control" placeholder="Mobile Number"
                                value="{{ old('mobile_number', $student->mobile_number) }}">
                        </div>

                        <div class="col-12">
                            <textarea name="address" class="form-control"
                                placeholder="Address">{{ old('address', $student->address) }}</textarea>
                        </div>

                        <hr class="mt-4">

                        <h5 class="mt-3">Emergency Contact</h5>

                        <div class="col-md-6">
                            <input type="text" name="emergency_person" class="form-control"
                                placeholder="Emergency Contact Person"
                                value="{{ old('emergency_person', $student->emergency_person) }}">
                        </div>

                        <div class="col-md-6">
                            <input type="text" name="emergency_relationship" class="form-control"
                                placeholder="Relationship"
                                value="{{ old('emergency_relationship', $student->emergency_relationship) }}">
                        </div>

                        <div class="col-md-6">
                            <input type="text" name="emergency_number" class="form-control"
                                placeholder="Emergency Contact Number"
                                value="{{ old('emergency_number', $student->emergency_number) }}">
                        </div>

                        <div class="col-12">
                            <textarea name="emergency_address" class="form-control"
                                placeholder="Emergency Address">{{ old('emergency_address', $student->emergency_address) }}</textarea>
                        </div>

                        <!-- PROFILE PICTURE -->

                        <div class="col-md-6">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" name="profile_picture" class="form-control" accept=".jpg,.jpeg,.png">

                            @if($student->profile_picture)
                            <div class="mt-2">
                                <img src="{{ asset($student->profile_picture) }}" width="120" class="rounded">
                            </div>
                            @endif
                        </div>

                        <!-- SIGNATURE -->

                        <div class="col-12">
                            <label class="form-label">Signature (draw below)</label><br>
                            <canvas id="studentSignaturePad" width="500" height="150"></canvas>
                            <input type="hidden" name="student_signature" id="studentSignatureInput">

                            <div class="mt-2">
                                <button type="button" id="clearStudentSignature" class="btn btn-outline-danger btn-sm">
                                    Clear
                                </button>
                            </div>

                            @if($student->student_signature)
                            <div class="mt-3">
                                <p>Current Signature:</p>
                                <img src="{{ asset($student->student_signature) }}" height="80">
                            </div>
                            @endif
                        </div>

                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-save px-4">Update Student</button>
                        <a href="{{ route('students.index') }}" class="btn btn-secondary px-4">Back</a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('studentSignaturePad');
        const signaturePad = new SignaturePad(canvas);
        const input = document.getElementById('studentSignatureInput');

        document.getElementById('clearStudentSignature').addEventListener('click', () => {
            signaturePad.clear();
            input.value = '';
        });

        document.getElementById('studentForm').addEventListener('submit', () => {
            if (!signaturePad.isEmpty()) {
                input.value = signaturePad.toDataURL();
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>