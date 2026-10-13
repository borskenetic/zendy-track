<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Rules\AllowedInstitutionEmail;
use App\Support\InstitutionEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    private function allowedRoles(): array
    {
        return array_keys(User::roleOptions());
    }

    private function allowedYearLevels(Request $request, ?User $user = null): array
    {
        $levels = User::yearLevelsForCampus($request->input('campus'));

        if ($user?->grade && ! in_array($user->grade, $levels, true)) {
            $levels[] = $user->grade;
        }

        return $levels;
    }

    private function allowedCourses(Request $request, ?User $user = null): array
    {
        $courses = User::coursesFor($request->input('campus'), $request->input('grade'));

        if ($courses === []) {
            $courses = User::courseOptions();
        }

        if ($user?->course && ! in_array($user->course, $courses, true)) {
            $courses[] = $user->course;
        }

        return $courses;
    }

    private function userValidationRules(Request $request, ?User $user = null): array
    {
        return [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                new AllowedInstitutionEmail,
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'role' => ['required', Rule::in($this->allowedRoles())],
            'campus' => ['required', Rule::in(User::campusOptions())],
            'department' => 'nullable|string|max:255',
            'grade' => [
                'nullable',
                'required_if:role,student',
                Rule::in($this->allowedYearLevels($request, $user)),
            ],
            'course' => [
                'nullable',
                'required_if:role,student',
                Rule::in($this->allowedCourses($request, $user)),
            ],
            'password' => $user ? 'nullable|string|min:6' : 'required|string|min:6',
        ];
    }

    public function downloadImportTemplate(): StreamedResponse
    {
        $filename = 'user_accounts_import_template.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = ['lname', 'fname', 'email', 'password', 'role', 'campus', 'department', 'course', 'grade'];

        return response()->streamDownload(function () use ($columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            fclose($out);
        }, $filename, $headers);
    }

    public function create()
    {
        return view('view_accounts.create', [
            'roles' => User::roleOptions(),
            'campuses' => User::campusOptions(),
            'courses' => User::courseOptions(),
            'grades' => User::gradeOptions(),
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = fopen($request->file('file'), 'r');

        $rowNumber = 0;
        $errors = [];
        $successCount = 0;

        while (($row = fgetcsv($file)) !== FALSE) {
            $rowNumber++;

            if ($rowNumber == 1) continue;

            $lname = trim($row[0] ?? '');
            $fname = trim($row[1] ?? '');
            $email = trim($row[2] ?? '');
            $password = trim($row[3] ?? '');
            $role = strtolower(trim($row[4] ?? ''));
            $campus = trim($row[5] ?? '');
            $department = trim($row[6] ?? '');
            $course = trim($row[7] ?? '');
            $grade = trim($row[8] ?? '');

            if (!$lname || !$fname || !$email || !$password || !$role || !$campus) {
                $errors[] = "Row $rowNumber: Missing fields.";
                continue;
            }

            if ($email && ! InstitutionEmail::isAllowed($email)) {
                $errors[] = "Row $rowNumber: Invalid email ($email). ".InstitutionEmail::validationMessage();
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $errors[] = "Row $rowNumber: Email already exists ($email)";
                continue;
            }

            if (!in_array($role, $this->allowedRoles(), true)) {
                $errors[] = "Row $rowNumber: Invalid role ($role).";
                continue;
            }

            if (! in_array($campus, User::campusOptions(), true)) {
                $errors[] = "Row $rowNumber: Invalid campus ($campus). Must be one of: ".implode(', ', User::campusOptions());
                continue;
            }

            if ($role === 'student' && $grade === '') {
                $errors[] = "Row $rowNumber: Year level is required for students.";
                continue;
            }

            if ($role === 'student' && $course === '') {
                $errors[] = "Row $rowNumber: Course is required for students.";
                continue;
            }

            if ($grade !== '' && ! in_array($grade, User::yearLevelsForCampus($campus), true)) {
                $errors[] = "Row $rowNumber: Invalid year level ($grade) for campus $campus.";
                continue;
            }

            if ($role === 'student' && $course !== '' && ! in_array($course, User::coursesFor($campus, $grade), true)) {
                $errors[] = "Row $rowNumber: Invalid course ($course) for $campus / $grade.";
                continue;
            }

            try {
                User::create([
                    'lname' => $lname,
                    'fname' => $fname,
                    'email' => $email,
                    'password' => Hash::make($password, ['rounds' => 12]),
                    'role' => $role,
                    'campus' => $campus,
                    'department' => $department ?: null,
                    'course' => $role === 'student' ? $course : null,
                    'grade' => $role === 'student' ? ($grade ?: null) : null,
                ]);

                $successCount++;

            } catch (\Exception $e) {
                $errors[] = "Row $rowNumber: Failed to insert.";
            }
        }

        fclose($file);

        return redirect()->route('users.index')->with([
            'success' => "$successCount users imported successfully.",
            'import_errors' => $errors
        ]);
    }

    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = fopen($request->file('file'), 'r');

        $data = [];
        $rowNumber = 0;

        while (($row = fgetcsv($file)) !== FALSE) {
            $rowNumber++;

            if ($rowNumber == 1) continue;

            if (empty(array_filter($row))) {
                continue;
            }

            $lname = trim($row[0] ?? '');
            $fname = trim($row[1] ?? '');
            $email = trim($row[2] ?? '');
            $password = trim($row[3] ?? '');
            $role = strtolower(trim($row[4] ?? ''));
            $campus = trim($row[5] ?? '');
            $department = trim($row[6] ?? '');
            $course = trim($row[7] ?? '');
            $grade = trim($row[8] ?? '');

            $errors = [];

            if (!$lname || !$fname || !$email || !$password || !$role || !$campus) {
                $errors[] = 'Missing required fields';
            }

            if ($email && ! InstitutionEmail::isAllowed($email)) {
                $errors[] = 'Invalid domain';
            }

            if ($email && User::where('email', $email)->exists()) {
                $errors[] = 'Duplicate email';
            }

            if ($role && !in_array($role, $this->allowedRoles(), true)) {
                $errors[] = 'Invalid role';
            }

            if ($campus && ! in_array($campus, User::campusOptions(), true)) {
                $errors[] = 'Invalid campus';
            }

            if ($role === 'student' && !$grade) {
                $errors[] = 'Year level required for students';
            }

            if ($role === 'student' && !$course) {
                $errors[] = 'Course required for students';
            }

            if ($grade !== '' && $campus && ! in_array($grade, User::yearLevelsForCampus($campus), true)) {
                $errors[] = 'Invalid year level';
            }

            if ($role === 'student' && $course && ! in_array($course, User::coursesFor($campus, $grade), true)) {
                $errors[] = 'Invalid course';
            }

            $data[] = [
                'lname' => $lname,
                'fname' => $fname,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'campus' => $campus,
                'department' => $department,
                'course' => $course,
                'grade' => $grade,
                'errors' => $errors
            ];
        }

        fclose($file);

        return view('view_accounts.import_preview', compact('data'));
    }

    public function importConfirm(Request $request)
    {
        $data = $request->input('data', []);

        $successCount = 0;

        foreach ($data as $row) {
            $rowErrors = $row['errors'] ?? [];
            if (is_string($rowErrors)) {
                $rowErrors = $rowErrors === '' ? [] : explode('|', $rowErrors);
            }
            if (!empty($rowErrors)) {
                continue;
            }

            User::create([
                'lname' => $row['lname'],
                'fname' => $row['fname'],
                'email' => $row['email'],
                'campus' => $row['campus'] ?? null,
                'department' => $row['department'] ?? null,
                'course' => $row['role'] === 'student' ? ($row['course'] ?? null) : null,
                'grade' => $row['role'] === 'student' ? (($row['grade'] ?? '') !== '' ? $row['grade'] : null) : null,
                'password' => Hash::make($row['password'], ['rounds' => 12]),
                'role' => $row['role']
            ]);

            $successCount++;
        }

        return redirect()->route('users.index')
            ->with('success', "$successCount users imported successfully.");
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->userValidationRules($request));

        $isStudent = $validated['role'] === 'student';

        User::create([
            'lname' => $validated['lname'],
            'fname' => $validated['fname'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'campus' => $validated['campus'],
            'department' => $validated['department'] ?? null,
            'course' => $isStudent ? ($validated['course'] ?? null) : null,
            'grade' => $isStudent ? ($validated['grade'] ?? null) : null,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('users.index')->with('success', 'User account created successfully.');
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('fname', 'like', "%$search%")
                  ->orWhere('lname', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('course', 'like', "%$search%")
                  ->orWhere('campus', 'like', "%$search%");
            });
        }

        if ($request->filled('course')) {
            if ($request->course === '__none__') {
                $query->where(function ($q) {
                    $q->whereNull('course')->orWhere('course', '');
                });
            } else {
                $query->where('course', $request->course);
            }
        }

        if ($request->filled('campus')) {
            $query->where('campus', $request->campus);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('grade')) {
            if ($request->grade === '__none__') {
                $query->where(function ($q) {
                    $q->whereNull('grade')->orWhere('grade', '');
                });
            } else {
                $query->where('grade', $request->grade);
            }
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $users = $query->orderBy('lname')->orderBy('fname')->paginate($perPage)->withQueryString();

        $courses = collect(User::courseOptions())
            ->merge(
                User::whereNotNull('course')
                    ->where('course', '!=', '')
                    ->distinct()
                    ->pluck('course')
            )
            ->unique()
            ->sort()
            ->values();

        return view('view_accounts.list', [
            'users' => $users,
            'courses' => $courses,
            'grades' => User::gradeOptions(),
            'campuses' => User::campusOptions(),
            'roles' => User::roleOptions(),
        ]);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('view_accounts.edit', [
            'user' => $user,
            'roles' => User::roleOptions(),
            'campuses' => User::campusOptions(),
            'courses' => User::courseOptions(),
            'grades' => User::gradeOptions(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate($this->userValidationRules($request, $user));

        $isStudent = $validated['role'] === 'student';

        $payload = [
            'fname' => $validated['fname'],
            'lname' => $validated['lname'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'campus' => $validated['campus'],
            'department' => $validated['department'] ?? null,
            'course' => $isStudent ? ($validated['course'] ?? null) : null,
            'grade' => $isStudent ? ($validated['grade'] ?? null) : null,
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
}
