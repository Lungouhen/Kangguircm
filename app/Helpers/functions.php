<?php
declare(strict_types=1);

/**
 * Blade helper functions for views.
 *
 * Following php-pro skill: strict types, PHPDoc, DRY helpers.
 */

if (!function_exists('collect')) {
    /**
     * Create a simple collection from array.
     *
     * @param array $items
     * @return \ArrayObject
     */
    function collect(array $items = []): \ArrayObject
    {
        return new \ArrayObject($items, \ArrayObject::ARRAY_AS_PROPS);
    }
}
