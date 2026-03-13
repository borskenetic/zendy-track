<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function create()
    {
        // Show the createuser form
        return view('books.createuser');
    }

    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'lname' => 'required|string|max:255',
            'fname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:student,staff,faculty',
        ]);

        // Create user
        User::create([
            'lname' => $validated['lname'],
            'fname' => $validated['fname'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.create')->with('success', 'User account created successfully!');
    }
    
    // show user
   public function index()
    {
        $users = User::all();
        return view('view_accounts.list', compact('users'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('view_accounts.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fname' => 'required|string',
            'lname' => 'required|string',
            'email' => 'required|email',
            'role' => 'required|string',
        ]);

        $user = User::findOrFail($id);
        $user->update($request->only(['fname', 'lname', 'email', 'role']));

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
}
