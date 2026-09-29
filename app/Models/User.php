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

    public static function campusOptions(): array
    {
        return [
            'Buenavista',
            'Tagum',
            'Bay',
        ];
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

        if (preg_match('/\bbay\b/', $compact) === 1) {
            return 'Bay';
        }

        // Default unknowns to Tagum so charts/filters stay consistent;
        // accounts can be corrected later via Edit User.
        return 'Tagum';
    }
}
