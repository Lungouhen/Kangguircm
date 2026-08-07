<?php
/**
 * View helpers - included in every view for safe output.
 * These functions prevent XSS by escaping all dynamic output.
 */

if (!function_exists('esc')) {
    /**
     * Escape a value for safe HTML output.
     */
    function esc(mixed $v): string
    {
        return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('badge')) {
    /**
     * Render a status badge with appropriate color.
     */
    function badge(string $s): string
    {
        $colors = [
            'draft' => 'bg-yellow-100 text-yellow-700',
            'published' => 'bg-green-100 text-green-700',
            'archived' => 'bg-gray-100 text-gray-700',
            'active' => 'bg-green-100 text-green-700',
            'pending' => 'bg-yellow-100 text-yellow-700',
            'approved' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-700',
            'sent' => 'bg-green-100 text-green-700',
            'scheduled' => 'bg-yellow-100 text-yellow-700',
            'sending' => 'bg-blue-100 text-blue-700',
            'unsubscribed' => 'bg-red-100 text-red-700',
            'bounced' => 'bg-yellow-100 text-yellow-700',
            'present' => 'bg-green-100 text-green-700',
            'absent' => 'bg-red-100 text-red-700',
            'late' => 'bg-yellow-100 text-yellow-700',
            'half_day' => 'bg-blue-100 text-blue-700',
        ];
        $cls = $colors[$s] ?? 'bg-gray-100 text-gray-700';
        return '<span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ' . $cls . '">' . esc($s) . '</span>';
    }
}

if (!function_exists('fdate')) {
    /**
     * Format a date for display.
     */
    function fdate(?string $d): string
    {
        if (!$d) return '-';
        $t = strtotime($d);
        return $t ? date('M d, Y', $t) : esc($d);
    }
}

if (!function_exists('fmoney')) {
    /**
     * Format a currency amount.
     */
    function fmoney(?float $v): string
    {
        return $v !== null ? '$' . number_format($v, 2) : '-';
    }
}
