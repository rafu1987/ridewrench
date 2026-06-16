<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final class RideWrench
{
    public static function bikeTypeIconClass(?string $bikeType): string
    {
        return match ($bikeType) {
            'gravel' => 'icon-ridewrench-gravel',
            'mtb' => 'icon-ridewrench-mtb',
            'indoor' => 'icon-ridewrench-indoor',
            'other' => 'icon-ridewrench-other',
            default => 'icon-ridewrench-road',
        };
    }

    public static function formatDate(null|string|CarbonInterface $date): string
    {
        if (!$date) {
            return __('common.notSet');
        }

        $format = config('ridewrench.languages.' . app()->getLocale() . '.date_format', 'Y-m-d');

        return self::toCarbon($date)?->format($format) ?? __('common.notSet');
    }

    public static function formatDateTime(null|string|CarbonInterface $date): string
    {
        if (!$date) {
            return __('common.notSet');
        }

        $format = config('ridewrench.languages.' . app()->getLocale() . '.datetime_format', 'Y-m-d H:i');

        return self::toCarbon($date)?->format($format) ?? __('common.notSet');
    }

    public static function formatNumber(float|int|string|null $number, int $decimals = 1): string
    {
        $languageConfig = config('ridewrench.languages.' . app()->getLocale(), []);

        return number_format(
            (float) ($number ?? 0),
            $decimals,
            $languageConfig['decimal_separator'] ?? '.',
            $languageConfig['thousands_separator'] ?? ',',
        );
    }

    private static function toCarbon(string|CarbonInterface $date): ?Carbon
    {
        if ($date instanceof CarbonInterface) {
            return Carbon::instance($date);
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            return null;
        }
    }
}
