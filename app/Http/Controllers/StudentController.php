<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\PendingStudent;
use App\Models\Program;
use App\Models\BookLog;
use App\Models\Book;
use App\Models\StudentEditRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentsExport;
use App\Imports\StudentsImport;


class StudentController extends Controller
{
    // Show all students
    public function index(Request $request)
    {
        $query = Student::query();
        $programs = Program::orderBy('program_code')->get();

        // 🔍 Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('lastname', 'like', "%{$search}%")
                    ->orWhere('firstname', 'like', "%{$search}%")
                    ->orWhere('course', 'like', "%{$search}%")
                    ->orWhere('qrcode', 'like', "%{$search}%")
                    ->orWhere('id_number', 'like', "%{$search}%");
            });
        }

        // 🎓 Filter by Course
        if ($request->filled('course')) {
            $query->where('course', $request->course);
        }

        // 📚 Filter by Year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('program_id')) {
            $query->where('course', $request->program_id);
        }

        $students = $query->orderBy('lastname', 'asc')->paginate(15)->appends($request->all());

        return view('students.students', compact('students', 'programs'));
    }

    // Show form to create new student
    public function create()
    {
        $programs = Program::orderBy('program_name')->get();
        return view('students.create', compact('programs'));
    }

    // Store new student
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'id_number' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
            'course' => 'required|string|max:255',
            'year' => 'required|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'student_signature' => 'nullable|string', // base64
            'mobile_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'emergency_person' => 'nullable|string|max:255',
            'emergency_relationship' => 'nullable|string|max:255',
            'emergency_number' => 'nullable|string|max:20',
            'emergency_address' => 'nullable|string',
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '_profile_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $file->move(base_path('images/profile_pictures'), $filename);
            $validated['profile_picture'] = 'images/profile_pictures/' . $filename;
        }

        // Handle signature (base64)
        if (!empty($validated['student_signature']) && str_starts_with($validated['student_signature'], 'data:')) {

            [$meta, $contents] = explode(',', $validated['student_signature'], 2);

            $ext = 'png';
            if (preg_match('/data:image\/(jpeg|jpg)/i', $meta)) {
                $ext = 'jpg';
            }

            $sigName = time() . '_sig.' . $ext;

            if (!file_exists(base_path('images/student_signatures'))) {
                mkdir(base_path('images/student_signatures'), 0755, true);
            }

            file_put_contents(
                base_path('images/student_signatures/' . $sigName),
                base64_decode($contents)
            );

            $validated['student_signature'] = 'images/student_signatures/' . $sigName;
        }

        // Generate QR code (auto like S-00000001)
        $last = Student::orderBy('id', 'desc')->first();
        $nextNumber = 1;

        if ($last && !empty($last->qrcode) && str_starts_with($last->qrcode, 'S-')) {
            $lastNum = intval(Str::after($last->qrcode, 'S-'));
            $nextNumber = $lastNum + 1;
        }

        $validated['qrcode'] = 'S-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

        Student::create($validated);

        return redirect()->route('students.index')->with('success', 'Student Registered Successfully!');
    }

    // Edit form
    public function edit($id)
    {
        $student = Student::findOrFail($id);
        return view('students.edit', compact('student'));
    }

    // Update student
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
    
        $validated = $request->validate([
            'id_number' => 'required|string|unique:students,id_number,' . $id,
            'lastname' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
            'course' => 'nullable|string',
            'year' => 'nullable|string',
    
            'mobile_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'emergency_person' => 'nullable|string|max:255',
            'emergency_relationship' => 'nullable|string|max:255',
            'emergency_number' => 'nullable|string|max:20',
            'emergency_address' => 'nullable|string',
    
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'student_signature' => 'nullable|string',
        ]);
    
        /*
        |--------------------------------------------------------------------------
        | PROFILE PICTURE
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('profile_picture')) {
    
            if ($student->profile_picture && file_exists(base_path($student->profile_picture))) {
                unlink(base_path($student->profile_picture));
            }
    
            $image = $request->file('profile_picture');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $image->getClientOriginalName());
            $image->move(base_path('images/profile_pictures'), $filename);
    
            $validated['profile_picture'] = 'images/profile_pictures/' . $filename;
        }
    
        /*
        |--------------------------------------------------------------------------
        | SIGNATURE (ONLY IF NEW ONE DRAWN)
        |--------------------------------------------------------------------------
        */
        if (!empty($validated['student_signature']) && str_starts_with($validated['student_signature'], 'data:')) {
    
            [$meta, $contents] = explode(',', $validated['student_signature'], 2);
    
            $ext = 'png';
            if (preg_match('/data:image\/(jpeg|jpg)/i', $meta)) {
                $ext = 'jpg';
            }
    
            if (!file_exists(base_path('images/student_signatures'))) {
                mkdir(base_path('images/student_signatures'), 0755, true);
            }
    
            $sigName = time() . '_sig.' . $ext;
    
            file_put_contents(
                base_path('images/student_signatures/' . $sigName),
                base64_decode($contents)
            );
    
            $validated['student_signature'] = 'images/student_signatures/' . $sigName;
        } else {
            // Prevent overwriting existing signature with null
            unset($validated['student_signature']);
        }
    
        /*
        |--------------------------------------------------------------------------
        | DO NOT TOUCH QR CODE
        |--------------------------------------------------------------------------
        */
        unset($validated['qrcode']);
    
        $student->update($validated);
    
        return redirect()
            ->route('students.index')
            ->with('success', 'Student updated successfully!');
    }


    // Delete student
    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        if ($student->profile_picture) {
            Storage::disk('public')->delete($student->profile_picture);
        }

        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student Deleted Successfully!');
    }

    public function show($id)
    {
        $student = Student::findOrFail($id);
        return view('students.show', compact('student'));
    }

    // Pending list
    public function pending()
    {
        $pendingStudents = PendingStudent::orderBy('lastname')->get();
        return view('students.pending', compact('pendingStudents'));
    }

    // Approve pending student → move to students table
    public function approve($id)
    {
        DB::beginTransaction();

        try {
            $pending = PendingStudent::findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | Generate Proper QR Code (S-00000001 format)
            |--------------------------------------------------------------------------
            */
            $last = Student::orderBy('id', 'desc')->first();
            $nextNumber = 1;

            if ($last && str_starts_with($last->qrcode, 'S-')) {
                $lastNum = intval(substr($last->qrcode, 2));
                $nextNumber = $lastNum + 1;
            }

            $qrcode = $pending->qrcode
                ? $pending->qrcode
                : 'S-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

            /*
            |--------------------------------------------------------------------------
            | Prevent duplicate ID number (since students.id_number is UNIQUE)
            |--------------------------------------------------------------------------
            */
            if (Student::where('id_number', $pending->id_number)->exists()) {
                throw new \Exception('ID Number already exists in students table.');
            }

            /*
            |--------------------------------------------------------------------------
            | Create Student (INCLUDING NEW FIELDS)
            |--------------------------------------------------------------------------
            */
            Student::create([
                'id_number' => strtoupper($pending->id_number),
                'lastname' => strtoupper($pending->lastname),
                'firstname' => strtoupper($pending->firstname),
                'middle_initial' => strtoupper($pending->middle_initial ?? ''),
                'birthday' => $pending->birthday,
                'course' => strtoupper($pending->course),
                'year' => strtoupper($pending->year),

                // NEW FIELDS
                'mobile_number' => $pending->mobile_number,
                'address' => $pending->address,
                'emergency_person' => $pending->emergency_person,
                'emergency_relationship' => $pending->emergency_relationship,
                'emergency_number' => $pending->emergency_number,
                'emergency_address' => $pending->emergency_address,

                'profile_picture' => $pending->profile_picture,
                'student_signature' => $pending->student_signature,
                'qrcode' => $qrcode,
            ]);

            $pending->delete();

            DB::commit();

            return back()->with('success', 'Student approved and added to the students table.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // Reject pending student
    public function reject($id)
    {
        $pending = PendingStudent::findOrFail($id);
        $pending->delete();

        return back()->with('success', 'Registration rejected.');
    }


    public function profile($qrcode)
    {
        $student = Student::where('qrcode', $qrcode)->firstOrFail();
        session(['student_id' => $student->id]);

        $program = Program::where('program_code', $student->course)->first();
        $programs = Program::orderBy('program_name')->get();

        $fullName = "{$student->firstname} {$student->lastname}";

        $borrowedBooks = BookLog::with('book')
            ->where('patron_name', $fullName)
            ->whereNull('returned_date')
            ->get();

        $totalFine = $borrowedBooks->sum('fine_incurred');

        return view('students.profile', compact(
            'student',
            'program',
            'programs',
            'borrowedBooks',
            'totalFine'
        ));
    }

    public function submitEditRequest(Request $request)
    {
        $student = Student::findOrFail($request->student_id);

        // Prevent multiple pending
        if ($student->editRequests()->where('status', 'pending')->exists()) {
            return back()->with('error', 'You already have a pending request.');
        }

        $request->validate([
            'lastname' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
            'program_id' => 'nullable|exists:programs,id',
            'year' => 'nullable|string|max:10',

            'mobile_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',

            'emergency_person' => 'nullable|string|max:255',
            'emergency_relationship' => 'nullable|string|max:255',
            'emergency_number' => 'nullable|string|max:20',
            'emergency_address' => 'nullable|string',

            'profile_picture' => 'nullable|image|max:2048'
        ]);

        $photoPath = null;

        if ($request->hasFile('profile_picture')) {
        
            $image = $request->file('profile_picture');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $image->getClientOriginalName());
        
            $image->move(base_path('images/edits'), $filename);
        
            $photoPath = 'images/edits/' . $filename;
        }


        StudentEditRequest::create([
            'student_id' => $student->id,
            'lastname' => $request->lastname,
            'firstname' => $request->firstname,
            'middle_initial' => $request->middle_initial,
            'birthday' => $request->birthday,
            'program_id' => $request->program_id,
            'year' => $request->year,
            'mobile_number' => $request->mobile_number,
            'address' => $request->address,
            'emergency_person' => $request->emergency_person,
            'emergency_relationship' => $request->emergency_relationship,
            'emergency_number' => $request->emergency_number,
            'emergency_address' => $request->emergency_address,
            'profile_picture' => $photoPath,
        ]);

        return back()->with('success', 'Edit request submitted for approval.');
    }

    public function approveRequest($id)
    {
        $req = StudentEditRequest::findOrFail($id);
        $student = $req->student;
    
        // Handle profile picture transfer
        $newProfilePath = $student->profile_picture;
    
        if ($req->profile_picture) {
    
            // Delete old profile picture if exists
            if ($student->profile_picture && file_exists(base_path($student->profile_picture))) {
                unlink(base_path($student->profile_picture));
            }
    
            $newProfilePath = $req->profile_picture;
        }
    
        // If program_id maps to program_code
        $programCode = $student->course;
        if ($req->program_id) {
            $program = Program::find($req->program_id);
            $programCode = $program ? $program->program_code : $student->course;
        }
    
        $student->update([
            'lastname' => $req->lastname,
            'firstname' => $req->firstname,
            'middle_initial' => $req->middle_initial,
            'birthday' => $req->birthday,
            'course' => $programCode,
            'year' => $req->year,
            'mobile_number' => $req->mobile_number,
            'address' => $req->address,
            'emergency_person' => $req->emergency_person,
            'emergency_relationship' => $req->emergency_relationship,
            'emergency_number' => $req->emergency_number,
            'emergency_address' => $req->emergency_address,
            'profile_picture' => $newProfilePath,
        ]);
    
        $req->status = 'approved';
        $req->reviewed_at = now();
        $req->reviewed_by = auth()->id();
        $req->save();
    
        return back()->with('success', 'Request approved and changes applied.');
    }



    public function rejectRequest($id)
    {
        $req = StudentEditRequest::findOrFail($id);

        $req->status = 'rejected';
        $req->reviewed_at = now();
        $req->reviewed_by = auth()->id();
        $req->save();

        return back()->with('success', 'Request rejected.');
    }

    public function pendingRequests(Request $request)
    {
        $search = $request->search;

        // Pending only
        $pending = StudentEditRequest::with('student')
            ->where('status', 'pending')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('lastname', 'like', "%$search%")
                        ->orWhere('firstname', 'like', "%$search%");
                });
            })
            ->latest()
            ->paginate(10, ['*'], 'pending_page');

        // Logs (approved + rejected)
        $logs = StudentEditRequest::with('student')
            ->whereIn('status', ['approved', 'rejected'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('lastname', 'like', "%$search%")
                        ->orWhere('firstname', 'like', "%$search%");
                });
            })
            ->latest()
            ->paginate(10, ['*'], 'logs_page');

        return view('students.pending_requests', compact('pending', 'logs', 'search'));
    }
    
    public function export()
    {
        $fileName = 'students_export_' . date('Y-m-d_H-i-s') . '.csv';
    
        $students = \App\Models\Student::all();
    
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate",
            "Expires" => "0"
        ];
    
        $columns = [
            'ID',
            'ID Number',
            'Last Name',
            'First Name',
            'Middle Initial',
            'Birthday',
            'QR Code',
            'Course',
            'Year',
            'Mobile Number',
            'Address',
            'Emergency Person',
            'Emergency Relationship',
            'Emergency Number',
            'Emergency Address',
            'Profile Picture',
            'Student Signature',
            'Created At',
            'Updated At'
        ];
    
        $callback = function () use ($students, $columns) {
    
            $file = fopen('php://output', 'w');
    
            // Add header row
            fputcsv($file, $columns);
    
            foreach ($students as $student) {
    
                $row = [
                    $student->id,
                    $student->id_number,
                    $student->lastname,
                    $student->firstname,
                    $student->middle_initial,
                    $student->birthday,
                    $student->qrcode,
                    $student->course,
                    $student->year,
                    $student->mobile_number,
                    $student->address,
                    $student->emergency_person,
                    $student->emergency_relationship,
                    $student->emergency_number,
                    $student->emergency_address,
                    $student->profile_picture,
                    $student->student_signature,
                    $student->created_at,
                    $student->updated_at
                ];
    
                fputcsv($file, $row);
            }
    
            fclose($file);
        };
    
        return response()->stream($callback, 200, $headers);
    }
    
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);
    
        Excel::import(new StudentsImport, $request->file('file'));
    
        return redirect()->back()->with('success', 'Students imported successfully.');
    }


}
