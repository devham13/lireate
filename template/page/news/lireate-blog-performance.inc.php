<?php
/** Responsive LCP covers. PHP 5.6 compatible, original image remains a fallback. */
if (!function_exists('lireate_blog_performance_cover')) {
    function lireate_blog_performance_cover($html)
    {
        if (strpos($html, '<picture') !== false || !preg_match('~<img\b[^>]*\bsrc=["\'](/template/images/blog/[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png))["\'][^>]*>~i', $html, $match)) {
            return $html;
        }
        $root = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') : '';
        $src = $match[1];
        if ($root === '' || !is_file($root . $src)) {
            return $html;
        }
        $size = @getimagesize($root . $src);
        if (!$size || empty($size[0]) || empty($size[1])) {
            return $html;
        }
        $img = preg_replace('~\s+(?:width|height|loading|decoding|fetchpriority)\s*=\s*["\'][^"\']*["\']~i', '', $match[0]);
        $img = preg_replace('~\s*/?>$~', ' width="' . (int) $size[0] . '" height="' . (int) $size[1] . '" loading="eager" decoding="async" fetchpriority="high">', $img);
        $base = preg_replace('~\.(jpg|jpeg|png)$~i', '', $src);
        $webp = $base . '.webp';
        $small = $base . '-430.webp';
        if (is_file($root . $webp) && is_file($root . $small)) {
            $img = '<picture><source type="image/webp" srcset="' . htmlspecialchars($small, ENT_QUOTES, 'UTF-8') . ' 430w, ' . htmlspecialchars($webp, ENT_QUOTES, 'UTF-8') . ' ' . (int) $size[0] . 'w" sizes="(max-width: 767px) calc(100vw - 34px), 662px">' . $img . '</picture>';
        }
        return str_replace($match[0], $img, $html);
    }
}
