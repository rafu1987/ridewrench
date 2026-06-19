<?php

namespace App\Support;

use Carbon\CarbonInterface;

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

        $carbon = self::toCarbon($date);

        if (!$carbon) {
            return __('common.notSet');
        }

        return $carbon->timezone(config('app.timezone', 'Europe/Berlin'))->format($format);
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

    private static function toCarbon(null|string|CarbonInterface $date): ?CarbonInterface
    {
        if (!$date) {
            return null;
        }

        if ($date instanceof CarbonInterface) {
            return $date;
        }

        try {
            return \Carbon\Carbon::parse($date, 'UTC');
        } catch (\Throwable) {
            return null;
        }
    }
}
