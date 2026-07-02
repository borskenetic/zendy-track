<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'fname' => 'Admin',
                'lname' => 'User',
                'email' => 'admin@jib.edu.ph',
                'password' => 'password',
                'role' => 'admin',
                'campus' => 'Bay',
                'department' => 'Library',
                'course' => null,
            ],
            [
                'fname' => 'Maria',
                'lname' => 'Librarian',
                'email' => 'librarian@jib.edu.ph',
                'password' => 'password',
                'role' => 'librarian',
                'campus' => 'Bay',
                'department' => 'Library',
                'course' => null,
            ],
            [
                'fname' => 'Juan',
                'lname' => 'Santos',
                'email' => 'juan.santos@jib.edu.ph',
                'password' => 'password',
                'role' => 'student',
                'campus' => 'Bay',
                'department' => 'College of Business',
                'course' => 'BSBA Marketing',
            ],
            [
                'fname' => 'Ana',
                'lname' => 'Reyes',
                'email' => 'ana.reyes@jib.edu.ph',
                'password' => 'password',
                'role' => 'student',
                'campus' => 'San Pablo',
                'department' => 'College of IT',
                'course' => 'BSIT',
            ],
            [
                'fname' => 'Carlos',
                'lname' => 'Mendoza',
                'email' => 'carlos.mendoza@jib.edu.ph',
                'password' => 'password',
                'role' => 'faculty',
                'campus' => 'Bay',
                'department' => 'College of Education',
                'course' => null,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }

        User::whereIn('role', ['staff', 'borrower'])->update(['role' => 'student']);
    }
}
