<?php

namespace App\Support;

class Branding
{
    public static function institutionName(): string
    {
        return (string) config('branding.institution_name', config('app.name'));
    }

    public static function portalTitle(): string
    {
        return (string) config('branding.portal_title', 'Zendy Portal');
    }

    public static function logoUrl(): string
    {
        $path = (string) config('branding.logo_path', 'images/d.png');

        return asset($path);
    }

    public static function colors(): array
    {
        return config('branding.colors', []);
    }

    public static function cssPath(): ?string
    {
        $path = config('branding.css_path');

        return $path ? (string) $path : null;
    }

    public static function stylesheetUrl(): ?string
    {
        $path = self::cssPath();

        if (! $path) {
            return null;
        }

        $fullPath = public_path($path);
        $version = is_file($fullPath) ? (string) filemtime($fullPath) : '1';

        return asset($path).'?v='.$version;
    }

    public static function primaryRgba(float $alpha = 1): string
    {
        return self::hexToRgba(self::colors()['primary'] ?? '#2563eb', $alpha);
    }

    public static function primaryLight(): string
    {
        return self::lighten(self::colors()['primary'] ?? '#2563eb', 0.35);
    }

    private static function hexToRgba(string $hex, float $alpha): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6) {
            return 'rgba(37, 99, 235, '.$alpha.')';
        }

        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        return sprintf('rgba(%d, %d, %d, %s)', $red, $green, $blue, rtrim(rtrim(number_format($alpha, 2, '.', ''), '0'), '.'));
    }

    private static function lighten(string $hex, float $amount): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6) {
            return '#93c5fd';
        }

        $red = min(255, (int) round(hexdec(substr($hex, 0, 2)) + (255 * $amount)));
        $green = min(255, (int) round(hexdec(substr($hex, 2, 2)) + (255 * $amount)));
        $blue = min(255, (int) round(hexdec(substr($hex, 4, 2)) + (255 * $amount)));

        return sprintf('#%02x%02x%02x', $red, $green, $blue);
    }
}
