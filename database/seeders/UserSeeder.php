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
                'campus' => 'Bay-Main',
                'department' => 'Library',
                'course' => null,
                'grade' => null,
            ],
            [
                'fname' => 'Maria',
                'lname' => 'Librarian',
                'email' => 'librarian@jib.edu.ph',
                'password' => 'password',
                'role' => 'librarian',
                'campus' => 'Bay-Main',
                'department' => 'Library',
                'course' => null,
                'grade' => null,
            ],
            [
                'fname' => 'Juan',
                'lname' => 'Santos',
                'email' => 'juan.santos@jib.edu.ph',
                'password' => 'password',
                'role' => 'student',
                'campus' => 'Bay-Main',
                'department' => 'College of Business',
                'course' => 'BSTM',
                'grade' => 'College',
            ],
            [
                'fname' => 'Ana',
                'lname' => 'Reyes',
                'email' => 'ana.reyes@jib.edu.ph',
                'password' => 'password',
                'role' => 'student',
                'campus' => 'Tagum',
                'department' => 'College of IT',
                'course' => 'BSIS',
                'grade' => 'College',
            ],
            [
                'fname' => 'Carlos',
                'lname' => 'Mendoza',
                'email' => 'carlos.mendoza@jib.edu.ph',
                'password' => 'password',
                'role' => 'faculty',
                'campus' => 'Buenavista',
                'department' => 'College of Education',
                'course' => null,
                'grade' => null,
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
