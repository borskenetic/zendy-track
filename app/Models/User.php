<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fname',
        'lname',
        'email',
        'course',
        'grade',
        'department',
        'campus',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function roleOptions(): array
    {
        return [
            'student' => 'Student',
            'faculty' => 'Faculty',
            'librarian' => 'Librarian',
            'admin' => 'Administrator',
        ];
    }

    /**
     * Campus → year level → course/track catalog.
     *
     * @return array<string, array<string, list<string>>>
     */
    public static function affiliationCatalog(): array
    {
        return [
            'Tagum' => [
                'Grade 11' => ['B&E', 'ASSH', 'Tech-Pro', 'HE'],
                'Grade 12' => ['B&E', 'ASSH', 'Tech-Pro'],
                'College' => ['BSTM', 'BSIS', 'BSE', 'BSA', 'BSCRIM', 'BSAIS'],
            ],
            'Buenavista' => [
                'Grade 11' => ['B&E', 'ASSH', 'Tech-Pro'],
                'Grade 12' => ['B&E', 'ASSH', 'Tech-Pro'],
                'College' => ['BSE', 'BSTM'],
            ],
            'Bay-Main' => [
                'College' => [
                    'BSA',
                    'BSAIS',
                    'BS Entrepreneurship (Microfinance)',
                    'BSIS',
                    'BSTM',
                    'BSCRIM',
                ],
            ],
            'Bay 2' => [
                'Grade 11' => ['B&E', 'ASSH', 'Tech-Pro HE Cookery-Tourism', 'Tech-Pro ICT'],
                'Grade 12' => ['ABM', 'HUMSS', 'HE Cookery-Tourism', 'ICT'],
            ],
            'San Pablo' => [
                'Grade 11' => ['B&E', 'ASSH', 'Tech-Pro HE-Tourism', 'Tech-Pro ICT', 'STEM'],
                'Grade 12' => ['ABM', 'HUMSS', 'HE-Tourism', 'ICT', 'STEM'],
            ],
        ];
    }

    public static function campusOptions(): array
    {
        return array_keys(self::affiliationCatalog());
    }

    public static function gradeOptions(): array
    {
        return ['Grade 11', 'Grade 12', 'College'];
    }

    /** @return list<string> */
    public static function yearLevelsForCampus(?string $campus): array
    {
        if ($campus === null || $campus === '') {
            return [];
        }

        return array_keys(self::affiliationCatalog()[$campus] ?? []);
    }

    /** @return list<string> */
    public static function coursesFor(?string $campus, ?string $yearLevel): array
    {
        if ($campus === null || $campus === '' || $yearLevel === null || $yearLevel === '') {
            return [];
        }

        return self::affiliationCatalog()[$campus][$yearLevel] ?? [];
    }

    public static function courseOptions(): array
    {
        $courses = [];

        foreach (self::affiliationCatalog() as $yearLevels) {
            foreach ($yearLevels as $list) {
                foreach ($list as $course) {
                    $courses[$course] = true;
                }
            }
        }

        $courses = array_keys($courses);
        sort($courses);

        return $courses;
    }

    /**
     * Map free-text campus values onto the allowed dropdown options.
     * Unrecognized non-empty values default to Tagum.
     */
    public static function normalizeCampus(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        foreach (self::campusOptions() as $campus) {
            if (strcasecmp($trimmed, $campus) === 0) {
                return $campus;
            }
        }

        $compact = strtolower(preg_replace('/[^a-z0-9]+/i', ' ', $trimmed) ?? $trimmed);
        $compact = trim(preg_replace('/\s+/', ' ', $compact) ?? $compact);

        if (str_contains($compact, 'tagum')) {
            return 'Tagum';
        }

        if (str_contains($compact, 'buenavista')) {
            return 'Buenavista';
        }

        if (str_contains($compact, 'san pablo') || $compact === 'spc' || str_contains($compact, 'spc ')) {
            return 'San Pablo';
        }

        if (str_contains($compact, 'bay 2') || str_contains($compact, 'bay2')) {
            return 'Bay 2';
        }

        if (str_contains($compact, 'bay main') || str_contains($compact, 'baymain') || preg_match('/\bbay\b/', $compact) === 1) {
            return 'Bay-Main';
        }

        // Default unknowns to Tagum so charts/filters stay consistent;
        // accounts can be corrected later via Edit User.
        return 'Tagum';
    }
}
