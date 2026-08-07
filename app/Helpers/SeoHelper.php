<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * SEO helper for generating meta tags, Open Graph tags, and JSON-LD.
 *
 * Following seo-specialist skill: structured data, meta optimization
 * Following security-auditor skill: output escaping
 */
class SeoHelper
{
    /**
     * Generate all SEO meta tags for a page.
     *
     * @param array<string, mixed> $data {title, description, url, image, type, site_name}
     * @return string HTML meta tags
     */
    public static function metaTags(array $data): string
    {
        $title = htmlspecialchars($data['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($data['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $url = htmlspecialchars($data['url'] ?? '', ENT_QUOTES, 'UTF-8');
        $image = htmlspecialchars($data['image'] ?? '', ENT_QUOTES, 'UTF-8');
        $type = htmlspecialchars($data['type'] ?? 'website', ENT_QUOTES, 'UTF-8');
        $siteName = htmlspecialchars($data['site_name'] ?? 'Platform', ENT_QUOTES, 'UTF-8');

        $html = '';

        // Basic meta
        $html .= '<meta name="description" content="' . $description . '">' . "\n";
        $html .= '<link rel="canonical" href="' . $url . '">' . "\n";

        // Open Graph
        $html .= '<meta property="og:title" content="' . $title . '">' . "\n";
        $html .= '<meta property="og:description" content="' . $description . '">' . "\n";
        $html .= '<meta property="og:type" content="' . $type . '">' . "\n";
        $html .= '<meta property="og:url" content="' . $url . '">' . "\n";
        $html .= '<meta property="og:site_name" content="' . $siteName . '">' . "\n";
        if ($image) {
            $html .= '<meta property="og:image" content="' . $image . '">' . "\n";
        }

        // Twitter Card
        $html .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
        $html .= '<meta name="twitter:title" content="' . $title . '">' . "\n";
        $html .= '<meta name="twitter:description" content="' . $description . '">' . "\n";
        if ($image) {
            $html .= '<meta name="twitter:image" content="' . $image . '">' . "\n";
        }

        return $html;
    }

    /**
     * Generate JSON-LD structured data for an article.
     *
     * @param array<string, mixed> $data {title, description, url, image, author, published_at, modified_at}
     * @return string JSON-LD script tag
     */
    public static function articleJsonLd(array $data): string
    {
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'url' => $data['url'] ?? '',
            'image' => $data['image'] ?? '',
            'author' => [
                '@type' => 'Person',
                'name' => $data['author'] ?? '',
            ],
            'datePublished' => $data['published_at'] ?? '',
            'dateModified' => $data['modified_at'] ?? $data['published_at'] ?? '',
            'publisher' => [
                '@type' => 'Organization',
                'name' => $data['site_name'] ?? 'Platform',
            ],
        ];

        return '<script type="application/ld+json">' . "\n"
            . json_encode($jsonLd, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
            . '</script>';
    }

    /**
     * Generate JSON-LD for a blog listing page.
     *
     * @param list<array<string, mixed>> $posts
     * @param string $baseUrl
     * @return string
     */
    public static function blogListingJsonLd(array $posts, string $baseUrl): string
    {
        $items = [];
        foreach ($posts as $post) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $post['position'] ?? 0,
                'url' => rtrim($baseUrl, '/') . '/blog/' . ($post['slug'] ?? ''),
                'name' => $post['title'] ?? '',
            ];
        }

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => $items,
        ];

        return '<script type="application/ld+json">' . "\n"
            . json_encode($jsonLd, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
            . '</script>';
    }

    /**
     * Generate breadcrumb JSON-LD.
     *
     * @param list<array{name: string, url: string}> $breadcrumbs
     * @return string
     */
    public static function breadcrumbJsonLd(array $breadcrumbs): string
    {
        $items = [];
        foreach ($breadcrumbs as $index => $crumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ];
        }

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];

        return '<script type="application/ld+json">' . "\n"
            . json_encode($jsonLd, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
            . '</script>';
    }

    /**
     * Truncate text to a specified length for meta description.
     *
     * @param string $text
     * @param int $maxLength
     * @return string
     */
    public static function truncateForMeta(string $text, int $maxLength = 160): string
    {
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        return mb_substr($text, 0, $maxLength - 3) . '...';
    }

    /**
     * Generate a reading time estimate.
     *
     * @param string $content
     * @param int $wordsPerMinute
     * @return string "X min read"
     */
    public static function readingTime(string $content, int $wordsPerMinute = 200): string
    {
        $wordCount = str_word_count(strip_tags($content));
        $minutes = max(1, (int) ceil($wordCount / $wordsPerMinute));
        return $minutes . ' min read';
    }
}
