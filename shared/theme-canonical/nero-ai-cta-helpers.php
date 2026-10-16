<?php
/**
 * CTA helpers for Nero longread pages (env resolution + external link attrs).
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('NERO_AI_DEFAULT_TELEGRAM_CHANNEL_URL')) {
    define('NERO_AI_DEFAULT_TELEGRAM_CHANNEL_URL', 'https://t.me/[REDACTED]'); // pragma: allowlist secret
}

if (!function_exists('nero_ai_resolve_env')) {
    function nero_ai_resolve_env(string $key): string
    {
        $candidates = [
            getenv($key),
            $_ENV[$key] ?? null,
            $_SERVER[$key] ?? null,
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return '';
    }
}

if (!function_exists('nero_ai_is_placeholder_cta_url')) {
    /**
     * True when href is empty or a non-action placeholder (#, /, #cta, #audit, …).
     */
    function nero_ai_is_placeholder_cta_url(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || $url === '#' || $url === '/') {
            return true;
        }

        if (!str_starts_with($url, '#')) {
            return false;
        }

        $anchor = strtolower(ltrim($url, '#'));
        $placeholders = [
            'cta',
            'audit',
            'audit-30-min',
            'contact',
            'form',
        ];

        return $anchor === '' || in_array($anchor, $placeholders, true);
    }
}

if (!function_exists('nero_ai_telegram_channel_url')) {
    function nero_ai_telegram_channel_url(): string
    {
        $url = nero_ai_resolve_env('TELEGRAM_CHANNEL_URL');
        if ($url !== '' && !nero_ai_is_placeholder_cta_url($url)) {
            return $url;
        }

        return NERO_AI_DEFAULT_TELEGRAM_CHANNEL_URL;
    }
}

if (!function_exists('nero_ai_primary_cta_url')) {
    /**
     * Resolve primary CTA URL: env PRIMARY_CTA_URL → Telegram default.
     */
    function nero_ai_primary_cta_url(?string $explicit = null): string
    {
        if ($explicit !== null && $explicit !== '' && !nero_ai_is_placeholder_cta_url($explicit)) {
            return $explicit;
        }

        $from_env = nero_ai_resolve_env('PRIMARY_CTA_URL');
        if ($from_env !== '' && !nero_ai_is_placeholder_cta_url($from_env)) {
            return $from_env;
        }

        return nero_ai_telegram_channel_url();
    }
}

if (!function_exists('nero_ai_external_link_attrs')) {
    function nero_ai_external_link_attrs(string $url): string
    {
        if (!str_starts_with($url, 'http')) {
            return '';
        }

        $home = function_exists('home_url') ? home_url() : '';
        if ($home !== '' && str_starts_with($url, $home)) {
            return '';
        }

        return ' target="_blank" rel="noopener noreferrer"';
    }
}

if (!function_exists('nero_ai_primary_cta_link_attrs')) {
    function nero_ai_primary_cta_link_attrs(string $url): string
    {
        return nero_ai_external_link_attrs($url);
    }
}
