<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PendingUser;
use App\Models\User;
use App\Rules\AllowedInstitutionEmail;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PendingStudentController extends Controller
{
    
    public function index()
    {
        $pendingUsers = PendingUser::paginate(10);
        return view('pending.index', compact('pendingUsers'));
    }

    public function create()
    {
        return view('pending.register');
    }

    public function store(Request $request)
    {
        $campus = $request->input('campus');
        $yearLevel = $request->input('grade');

        $validated = $request->validate([
            'role'        => ['required', Rule::in(array_diff(array_keys(User::roleOptions()), ['admin']))],
            'firstname'   => 'required|string|max:255',
            'lastname'    => 'required|string|max:255',
            'email'       => [
                'required',
                'email',
                new AllowedInstitutionEmail,
                'unique:pending_users,email',
                'unique:users,email',
            ],
            'password'    => 'required|min:6',
            'campus'      => ['required', Rule::in(User::campusOptions())],
            'grade'       => [
                'nullable',
                'required_if:role,student',
                Rule::in(User::yearLevelsForCampus($campus)),
            ],
            'course'      => [
                'nullable',
                'required_if:role,student',
                Rule::in(User::coursesFor($campus, $yearLevel)),
            ],
        ]);

        $isStudent = $validated['role'] === 'student';

        PendingUser::create([
            'fname'       => $validated['firstname'],
            'lname'       => $validated['lastname'],
            'email'       => $validated['email'],
            'password'    => Hash::make($validated['password']),
            'campus'      => $validated['campus'],
            'course'      => $isStudent ? ($validated['course'] ?? null) : null,
            'grade'       => $isStudent ? ($validated['grade'] ?? null) : null,
            'role'        => $validated['role'],
        ]);

        return back()->with('success', 'Registration submitted. Awaiting admin approval.');
    }

    /**
     * APPROVE pending student (QR GENERATED HERE)
     */
    public function approve($id)
    {
        DB::transaction(function () use ($id) {
            $this->approvePending(PendingUser::findOrFail($id));
        });

        return back()->with('success', 'User approved successfully.');
    }

    public function bulkApprove(Request $request)
    {
        $ids = collect($request->input('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Select at least one registration to approve.');
        }

        try {
            $count = 0;

            DB::transaction(function () use ($ids, &$count) {
                $pendingUsers = PendingUser::whereIn('id', $ids)->get();

                foreach ($pendingUsers as $pending) {
                    $this->approvePending($pending);
                    $count++;
                }
            });
        } catch (QueryException $e) {
            return back()->with('error', 'Could not approve the selected registrations. One of the emails may already have an account.');
        }

        if ($count === 0) {
            return back()->with('error', 'Those registrations are no longer pending.');
        }

        $message = $count === 1 ? '1 user approved.' : "{$count} users approved.";

        return back()->with('success', $message);
    }

    private function approvePending(PendingUser $pending): void
    {
        User::create([
            'fname' => $pending->fname,
            'lname' => $pending->lname,
            'email' => $pending->email,
            'password' => $pending->password,
            'campus' => $pending->campus,
            'course' => $pending->course,
            'grade' => $pending->grade,
            'department' => $pending->department,
            'role' => $pending->role,
        ]);

        $pending->delete();
    }
    
    public function reject($id)
    {
        $pending = PendingUser::findOrFail($id);
        $pending->delete();
    
        return back()->with('success', 'User rejected.');
    }
}
