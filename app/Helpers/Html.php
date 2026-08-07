<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * HTML output helper for XSS prevention.
 *
 * All dynamic output in views MUST be escaped through this class.
 * Follows the security-auditor skill: "Sanitize all system output
 * variables via structural htmlspecialchars() wrappers."
 */
class Html
{
    /**
     * Escape a value for safe HTML output.
     *
     * @param mixed $value Value to escape
     * @return string Escaped string safe for HTML context
     */
    public static function e(mixed $value): string
    {
        if ($value === null) return '';
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Escape a value for use inside an HTML attribute.
     *
     * @param mixed $value
     * @return string
     */
    public static function a(mixed $value): string
    {
        return self::e($value);
    }

    /**
     * Escape a value for use inside a JavaScript string context.
     *
     * @param mixed $value
     * @return string
     */
    public static function j(mixed $value): string
    {
        return json_encode((string)$value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * Escape a value for use inside a URL parameter.
     *
     * @param mixed $value
     * @return string
     */
    public static function u(mixed $value): string
    {
        return rawurlencode((string)$value);
    }

    /**
     * Output an escaped status badge.
     *
     * @param string $status
     * @return string HTML for the badge
     */
    public static function statusBadge(string $status): string
    {
        $colors = [
            'draft' => 'bg-yellow-100 text-yellow-700',
            'published' => 'bg-green-100 text-green-700',
            'archived' => 'bg-gray-100 text-gray-700',
            'active' => 'bg-green-100 text-green-700',
            'unsubscribed' => 'bg-red-100 text-red-700',
            'bounced' => 'bg-yellow-100 text-yellow-700',
            'sent' => 'bg-green-100 text-green-700',
            'sending' => 'bg-blue-100 text-blue-700',
            'scheduled' => 'bg-yellow-100 text-yellow-700',
            'pending' => 'bg-yellow-100 text-yellow-700',
            'approved' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-700',
            'present' => 'bg-green-100 text-green-700',
            'absent' => 'bg-red-100 text-red-700',
            'late' => 'bg-yellow-100 text-yellow-700',
            'half_day' => 'bg-blue-100 text-blue-700',
        ];

        $cls = $colors[$status] ?? 'bg-gray-100 text-gray-700';
        return '<span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ' . $cls . '">' . self::e($status) . '</span>';
    }

    /**
     * Format a date string for display.
     *
     * @param string|null $dateStr
     * @return string
     */
    public static function date(?string $dateStr): string
    {
        if (!$dateStr) return '-';
        $timestamp = strtotime($dateStr);
        if ($timestamp === false) return self::e($dateStr);
        return date('M d, Y', $timestamp);
    }

    /**
     * Format a currency amount.
     *
     * @param float|null $amount
     * @return string
     */
    public static function currency(?float $amount): string
    {
        if ($amount === null) return '-';
        return '$' . number_format($amount, 2);
    }
}
