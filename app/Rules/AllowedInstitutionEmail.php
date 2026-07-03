<?php

namespace App\Rules;

use App\Support\InstitutionEmail;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllowedInstitutionEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! InstitutionEmail::isAllowed((string) $value)) {
            $fail(InstitutionEmail::validationMessage());
        }
    }
}
