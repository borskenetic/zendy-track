<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PendingUser;
use App\Models\User;
use App\Rules\AllowedInstitutionEmail;
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
            'campus'      => 'required|string|max:255',
            'course'      => 'required_if:role,student|nullable|string|max:255',
        ]);

        PendingUser::create([
            'fname'       => $validated['firstname'],
            'lname'       => $validated['lastname'],
            'email'       => $validated['email'],
            'password'    => Hash::make($validated['password']),
            'campus'      => $validated['campus'],
            'course'      => $validated['role'] === 'student' ? ($validated['course'] ?? null) : null,
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
    
            $pending = PendingUser::findOrFail($id);
    
            User::create([
                'fname'       => $pending->fname,
                'lname'       => $pending->lname,
                'email'       => $pending->email,
                'password'    => $pending->password,
                'campus'      => $pending->campus,
                'course'      => $pending->course,
                'department'  => $pending->department,
                'role'        => $pending->role,
            ]);
    
            $pending->delete();
        });
    
        return back()->with('success', 'User approved successfully.');
    }
    
    public function reject($id)
    {
        $pending = PendingUser::findOrFail($id);
        $pending->delete();
    
        return back()->with('success', 'User rejected.');
    }
}
