<?php

namespace App\Support;

class InstitutionEmail
{
    public static function domains(): array
    {
        $raw = config('institution.allowed_email_domains');

        if ($raw === null || trim((string) $raw) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (string $domain) => strtolower(ltrim(trim($domain), '@')),
            explode(',', (string) $raw),
        )));
    }

    public static function isEnforced(): bool
    {
        return self::domains() !== [];
    }

    public static function isAllowed(string $email): bool
    {
        if (! self::isEnforced()) {
            return true;
        }

        $email = strtolower(trim($email));

        foreach (self::domains() as $domain) {
            if (str_ends_with($email, '@'.$domain)) {
                return true;
            }
        }

        return false;
    }

    public static function validationMessage(): string
    {
        $domains = self::domains();

        if ($domains === []) {
            return 'Please enter a valid email address.';
        }

        if (count($domains) === 1) {
            return 'Email must use @'.$domains[0];
        }

        $listed = implode(', ', array_map(fn (string $domain) => '@'.$domain, $domains));

        return "Email must use one of these domains: {$listed}";
    }

    public static function placeholder(): string
    {
        $domains = self::domains();

        return $domains !== [] ? 'you@'.$domains[0] : 'you@school.edu';
    }

    public static function htmlPattern(): ?string
    {
        $domains = self::domains();

        if ($domains === []) {
            return null;
        }

        $escaped = array_map(fn (string $domain) => preg_quote($domain, '/'), $domains);

        return '^[a-zA-Z0-9._%+-]+@('.implode('|', $escaped).')$';
    }

    public static function displayDomains(): string
    {
        return implode(', ', array_map(fn (string $domain) => '@'.$domain, self::domains()));
    }
}
